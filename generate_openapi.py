import json
import yaml
import re
import os
import ast # To parse Python-like structures, but not directly PHP

# --- Configuration ---
ROUTES_FILE = 'extracted_routes.json'
OPENAPI_TEMPLATE = 'openapi.yaml' # Assuming basic structure already exists
OUTPUT_OPENAPI_FILE = 'openapi.yaml'
MODULES_PATH = 'app/Modules'

# --- Global caches ---
CONTROLLER_FILES = {}
REQUEST_FILES = {}

def get_controller_and_request_paths():
    """
    Glob all controller and request files for easy lookup.
    Populates global CONTROLLER_FILES and REQUEST_FILES dicts.
    """
    global CONTROLLER_FILES, REQUEST_FILES
    
    for root, _, files in os.walk(MODULES_PATH):
        for file in files:
            if file.endswith('Controller.php'):
                full_path = os.path.join(root, file)
                # Extract class name from path (e.g., AuthController)
                class_name = file.replace('.php', '')
                CONTROLLER_FILES[class_name] = full_path
            elif file.endswith('Request.php'):
                full_path = os.path.join(root, file)
                class_name = file.replace('.php', '')
                REQUEST_FILES[class_name] = full_path

get_controller_and_request_paths() # Initialize caches

def get_controller_method_details(controller_name, method_name):
    """
    Reads a controller file and extracts method signature, docblock, and FormRequest.
    Returns: dict with 'form_request_class', 'docblock'
    """
    controller_path = CONTROLLER_FILES.get(controller_name)
    if not controller_path or not os.path.exists(controller_path):
        return None

    with open(controller_path, 'r') as f:
        content = f.read()

    # Find the method definition (with optional access modifier and return type)
    method_pattern = re.compile(rf'(public|protected|private)\s+function\s+{re.escape(method_name)}\((.*?)\)(?:\s*:\s*\S+)?\s*{{\s*', re.DOTALL)
    method_match = method_pattern.search(content)

    if not method_match:
        return None

    params_string = method_match.group(2)
    
    form_request_class = None
    # Look for FormRequest injection (e.g., RequestName $request)
    # This regex is more robust for namespace or direct class names
    form_request_match = re.search(r'(?:App\\\\Modules\\\\.*?\\\\Http\\\\Requests\\\\)?(\w+Request)\s+\$\w+', params_string)
    if form_request_match:
        form_request_class = form_request_match.group(1) # e.g., 'LoginRequest'

    # Extract docblock immediately preceding the method definition
    # This looks for /** ... */ block ending right before the function definition
    docblock = None
    docblock_pattern = re.compile(r'/\*\*.*?\*/\s*(?=(public|protected|private)\s+function\s+' + re.escape(method_name) + ')', re.DOTALL)
    
    # Find all potential docblocks, and pick the one immediately before the method
    all_docblocks = [(m.start(), m.end(), m.group(0)) for m in docblock_pattern.finditer(content)]
    
    method_start = method_match.start()
    
    # Find the closest docblock that ends before the method starts
    closest_docblock = None
    min_dist = float('inf')
    for start, end, text in all_docblocks:
        if end < method_start:
            distance = method_start - end
            if distance < min_dist:
                min_dist = distance
                closest_docblock = text
    
    if closest_docblock:
        docblock = closest_docblock.strip()

    return {
        'form_request_class': form_request_class,
        'docblock': docblock
    }

def extract_summary_from_docblock(docblock):
    if not docblock:
        return None
    # Extract the first line of the docblock description, removing leading/trailing asterisks and whitespace
    lines = docblock.split('\n')
    for line in lines:
        line = line.strip()
        if line.startswith('*') and len(line) > 1 and not line.startswith('*/') and not line.startswith('/**'):
            clean_line = line.lstrip('* ').strip()
            if clean_line:
                return clean_line
    return None

def map_laravel_rule_to_openapi_type(rule_str, current_props=None):
    """
    Maps a single Laravel validation rule string to OpenAPI schema properties.
    current_props is used to pass along already inferred types for min/max
    """
    if current_props is None:
        current_props = {}

    schema_props = current_props.copy() # Start with existing properties

    rule_parts = rule_str.split(':')
    rule_name = rule_parts[0]
    rule_value = rule_parts[1] if len(rule_parts) > 1 else None

    # First, determine primary type if explicit rule exists
    if rule_name == 'string':
        schema_props['type'] = 'string'
    elif rule_name == 'integer':
        schema_props['type'] = 'integer'
    elif rule_name == 'numeric':
        schema_props['type'] = 'number'
        schema_props['format'] = 'float'
    elif rule_name == 'boolean':
        schema_props['type'] = 'boolean'
    elif rule_name == 'email':
        schema_props['type'] = 'string'
        schema_props['format'] = 'email'
    elif rule_name == 'date' or rule_name == 'date_format':
        schema_props['type'] = 'string'
        schema_props['format'] = 'date' # or date-time
    elif rule_name == 'array':
        schema_props['type'] = 'array'
        schema_props['items'] = {'type': 'string'} # Default items type
    elif rule_name == 'file' or rule_name == 'image' or rule_name == 'mimes':
        schema_props['type'] = 'string'
        schema_props['format'] = 'binary'
        if rule_name == 'mimes' and rule_value:
            schema_props['description'] = f"Allowed mimes: {rule_value.replace(',', ', ')}"
        elif rule_name == 'image':
            schema_props['description'] = "Image file"
    
    # Handle min/max based on inferred type
    if rule_name == 'min' and rule_value:
        try:
            if schema_props.get('type') == 'string':
                # min/max for string often means length
                schema_props['minLength'] = int(rule_value)
            elif schema_props.get('type') == 'integer':
                schema_props['minimum'] = int(rule_value)
            elif schema_props.get('type') == 'number':
                schema_props['minimum'] = float(rule_value)
            else: # If type is not yet known, try to infer from rule_value
                if '.' in rule_value:
                    schema_props['type'] = 'number'
                    schema_props['minimum'] = float(rule_value)
                else:
                    schema_props['type'] = 'integer'
                    schema_props['minimum'] = int(rule_value)
        except ValueError:
            print(f"Warning: Could not convert '{rule_value}' to numeric for min rule on type {schema_props.get('type', 'unknown')}. Rule: {rule_str}")

    elif rule_name == 'max' and rule_value:
        try:
            if schema_props.get('type') == 'string':
                schema_props['maxLength'] = int(rule_value)
            elif schema_props.get('type') == 'integer':
                schema_props['maximum'] = int(rule_value)
            elif schema_props.get('type') == 'number':
                schema_props['maximum'] = float(rule_value)
            else: # If type is not yet known, try to infer from rule_value
                if '.' in rule_value:
                    schema_props['type'] = 'number'
                    schema_props['maximum'] = float(rule_value)
                else:
                    schema_props['type'] = 'integer'
                    schema_props['maximum'] = int(rule_value)
        except ValueError:
            print(f"Warning: Could not convert '{rule_value}' to numeric for max rule on type {schema_props.get('type', 'unknown')}. Rule: {rule_str}")

    elif rule_name == 'in' and rule_value:
        enum_values = [v.strip() for v in rule_value.split(',')]
        schema_props['enum'] = enum_values
    elif rule_name == 'required': # This rule doesn't add to property schema, only to 'required' array
        pass
    
    return schema_props

def parse_php_array_to_python_dict(php_array_str):
    """
    Attempts to parse a PHP array string (like the content of a rules() method)
    into a Python dictionary. This version is more robust for typical Laravel rules.
    """
    parsed_rules = {}
    
    # Clean up string for easier parsing
    php_array_str = re.sub(r'//.*', '', php_array_str) # Remove single-line comments
    php_array_str = re.sub(r'/\*.*?\*/', '', php_array_str, flags=re.DOTALL) # Remove multi-line comments
    php_array_str = php_array_str.strip()

    # Pattern to match 'key' => [rules] or 'key' => 'rule_string'
    # This tries to be flexible with whitespace and line breaks
    # It accounts for Rule::in(...) and similar complex rules by capturing them as a single string
    # It also handles nested arrays if they are simple (e.g., ['required', 'string'])
    rule_entry_pattern = re.compile(
        r"'(?P<field>[^']+)'\s*=>\s*"
        r"(?P<value_array>\[(?P<array_content>(?:[^\]]*?\[.*?\][^\]]*?|[^\]]*?))\])" # Matches ['rule', 'rule'] potentially with nested
        r"|"
        r"'(?P<value_string>[^']*)'" # Matches 'rule|rule'
        r"(?:,\s*|\n|$)", # End of entry, either comma, newline, or end of string
        re.DOTALL | re.MULTILINE 
    )

    for match in rule_entry_pattern.finditer(php_array_str):
        field = match.group('field')
        rules_list = []

        if match.group('value_string'):
            rules_list = [r.strip() for r in match.group('value_string').split('|')]
        elif match.group('value_array'):
            # This is still tricky. Let's assume simple string array for now.
            # We need to extract each item from the array content.
            array_content = match.group('array_content').strip()
            
            # Match quoted strings or Rule:: calls within the array content
            array_items = re.findall(r"'(.*?)'|\b(\w+::\w+\(.*\))", array_content)
            for item in array_items:
                if item[0]: # Quoted string
                    rules_list.append(item[0].strip())
                elif item[1]: # Rule:: call
                    rules_list.append(item[1].strip())
        
        if field:
            parsed_rules[field] = [r for r in rules_list if r] # Filter empty strings

    return parsed_rules


def get_form_request_rules(request_name):
    """
    Reads a FormRequest file and extracts validation rules from the 'rules()' method.
    Returns: dict of {field_name: [rules_list]}
    """
    request_path = REQUEST_FILES.get(request_name)
    if not request_path or not os.path.exists(request_path):
        return {}

    with open(request_path, 'r') as f:
        content = f.read()

    rules_method_pattern = re.compile(r'public function rules\(\)\s*:\s*array\s*{.*?return\s*\[(.*?)\];\s*}', re.DOTALL)
    rules_match = rules_method_pattern.search(content)

    if not rules_match:
        return {}
    
    rules_content = rules_match.group(1).strip()
    
    return parse_php_array_to_python_dict(rules_content)

def generate_request_body_schema(form_request_rules):
    """
    Generates an OpenAPI requestBody schema from Laravel validation rules.
    """
    if not form_request_rules:
        return None

    properties = {}
    required_fields = []

    for field, rules in form_request_rules.items():
        # Ensure rules is always a list for consistent processing
        if isinstance(rules, str):
            rules = [rules]
        elif not isinstance(rules, list):
            rules = [str(rules)] # Fallback for unexpected types

        field_props = {}
        
        # First pass to determine type, as min/max depend on it
        for rule_str in rules:
            temp_props = map_laravel_rule_to_openapi_type(rule_str, current_props=field_props)
            field_props.update(temp_props)

        # Second pass to handle min/max if type is already established
        for rule_str in rules:
            if 'required' == rule_str.lower() or rule_str.lower().startswith('required:'):
                if field not in required_fields:
                    required_fields.append(field)
            else:
                final_props = map_laravel_rule_to_openapi_type(rule_str, current_props=field_props)
                field_props.update(final_props)
        
        if field_props: # Only add if actual properties were mapped
            properties[field] = field_props

    schema = {
        'type': 'object',
        'properties': properties
    }
    if required_fields:
        schema['required'] = sorted(list(set(required_fields))) # Ensure unique and sorted

    if not properties: # If no properties were generated, return None
        return None
    
    return {
        'required': True, # requestBody is required if a form request is present
        'content': {
            'application/json': {
                'schema': schema
            }
        }
    }

def get_tag_name(controller_name):
    """Simple mapping from Controller name to a human-readable tag."""
    tag_map = {
        'AuthController': 'Authentication',
        'ProductQueryController': 'Products',
        'ProductCrudController': 'Products',
        'ProductMetricController': 'Products',
        'ProductRentalController': 'Products',
        'OrderTransactionController': 'Orders',
        'OrderQueryController': 'Orders',
        'CategoryController': 'Categories',
        'ShopController': 'Shops',
        'ProfileController': 'Profile',
        'UserSecurityController': 'Security',
        'AttributeController': 'Attributes',
        'AttributeValueController': 'Attribute Values',
        'AuthorController': 'Authors',
        'ManufacturerController': 'Manufacturers',
        'TypeController': 'Types',
        'AttachmentController': 'Attachments',
        'DeliveryTimeController': 'Delivery Times',
        'LanguageController': 'Languages',
        'TagController': 'Tags',
        'RefundReasonController': 'Refund Reasons',
        'ResourceController': 'Resources',
        'CouponController': 'Coupons',
        'SettingsController': 'Settings',
        'ReviewController': 'Reviews',
        'QuestionController': 'Questions',
        'FeedbackController': 'Feedbacks',
        'CheckoutController': 'Checkout',
        'PaymentIntentController': 'Payments',
        'FaqsController': 'FAQs',
        'TermsAndConditionsController': 'Terms & Conditions',
        'FlashSaleController': 'Flash Sales',
        'RefundPolicyController': 'Refund Policies',
        'StoreNoticeController': 'Store Notices',
        'DownloadController': 'Downloads',
        'BecameSellerController': 'Become Seller',
        'AbusiveReportController': 'Abuse Reports',
        'ConversationController': 'Conversations',
        'MessageController': 'Messages',
        'WishlistController': 'Wishlists',
        'AddressController': 'Addresses',
        'PaymentMethodController': 'Payment Methods',
        'NotifyLogsController': 'Notifications',
        'WithdrawController': 'Withdrawals',
        'UserManagementController': 'User Management',
        'TaxController': 'Taxes',
        'ShippingController': 'Shipping',
        'FlashSaleRequestController': 'Flash Sale Requests',
        'OwnershipTransferController': 'Ownership Transfer',
        'AnalyticsController': 'Analytics',
    }
    return tag_map.get(controller_name, 'General') # Default tag

def generate_openapi_spec():
    """Main function to generate the OpenAPI spec."""
    with open(OPENAPI_TEMPLATE, 'r') as f:
        openapi_spec = yaml.safe_load(f)
    
    if 'paths' not in openapi_spec:
        openapi_spec['paths'] = {}
    if 'components' not in openapi_spec:
        openapi_spec['components'] = {}
    if 'schemas' not in openapi_spec['components']:
        openapi_spec['components']['schemas'] = {}

    with open(ROUTES_FILE, 'r') as f:
        routes = json.load(f)
    
    # Pre-define some common schemas here if not already in template
    if 'AuthSuccessResponse' not in openapi_spec['components']['schemas']:
        openapi_spec['components']['schemas']['AuthSuccessResponse'] = {
            'type': 'object',
            'properties': {
                'message': {'type': 'string', 'example': 'Login successful'},
                'data': {
                    'type': 'object',
                    'properties': {
                        'token': {'type': 'string', 'example': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'},
                        'permissions': {'type': 'array', 'items': {'type': 'string'}, 'example': ["customer"]},
                        'email_verified': {'type': 'boolean', 'example': True},
                        'role': {'type': 'string', 'example': 'customer'},
                        'session_id': {'type': 'string', 'example': 'some-uuid-session-id'},
                    }
                }
            }
        }
    if 'ErrorResponse' not in openapi_spec['components']['schemas']:
        openapi_spec['components']['schemas']['ErrorResponse'] = {
            'type': 'object',
            'properties': {
                'message': {'type': 'string', 'example': 'Error message.'},
                'errors': {'type': 'object'}
            }
        }
    if 'ValidationError' not in openapi_spec['components']['schemas']:
        openapi_spec['components']['schemas']['ValidationError'] = {
            'type': 'object',
            'properties': {
                'message': {'type': 'string', 'example': 'The given data was invalid.'},
                'errors': {
                    'type': 'object',
                    'additionalProperties': {
                        'type': 'array',
                        'items': {'type': 'string'}
                    },
                    'example': {'email': ['The email field is required.']}
                }
            }
        }
    
    # Placeholder for generic paginated response
    if 'PaginatedResponse' not in openapi_spec['components']['schemas']:
        openapi_spec['components']['schemas']['PaginatedResponse'] = {
            'type': 'object',
            'properties': {
                'message': {'type': 'string', 'example': 'List retrieved successfully'},
                'data': {'type': 'array', 'items': {'type': 'object'}}, # generic object for items
                'links': {'type': 'object'},
                'meta': {'type': 'object'}
            }
        }

    for route in routes:
        method = route['method'].lower()
        uri = route['uri']
        controller_name = route['controller']
        action_name = route['action']

        # Clean up URI for OpenAPI path (e.g., replace {shop:slug} with {shop})
        openapi_uri = re.sub(r'\{(\w+):[^}]+\}', r'{\1}', uri)
        
        # Initialize path object if it doesn't exist
        if openapi_uri not in openapi_spec['paths']:
            openapi_spec['paths'][openapi_uri] = {}
        
        # Generate summary and description from docblock
        controller_details = get_controller_method_details(controller_name, action_name)
        summary = None
        description = None
        if controller_details and controller_details['docblock']:
            summary = extract_summary_from_docblock(controller_details['docblock'])
            # Could extract full description if multi-line
        
        if not summary:
            # Fallback to generic summary: "ActionName (ControllerName) - /uri"
            summary = f"{action_name.replace('_', ' ').title()} ({controller_name.replace('Controller', '')}) - {uri}"

        path_item = {
            'summary': summary,
            'operationId': f"{action_name}{controller_name.replace('Controller', '')}", # e.g., registerAuth
            'tags': [get_tag_name(controller_name)],
            'responses': {
                '200': {'description': 'Successful operation', 'content': {'application/json': {'schema': {'type': 'object'}}}}, # Default generic success response
                '401': {'description': 'Unauthorized', 'content': {'application/json': {'schema': {'$ref': '#/components/schemas/ErrorResponse'}}}},
                '403': {'description': 'Forbidden', 'content': {'application/json': {'schema': {'$ref': '#/components/schemas/ErrorResponse'}}}},
                '404': {'description': 'Not Found', 'content': {'application/json': {'schema': {'$ref': '#/components/schemas/ErrorResponse'}}}},
            }
        }
        
        # Add security for all routes except public ones
        # This list should contain the EXACT openapi_uri strings for PUBLIC routes.
        # It's better to build this list by identifying public route controllers/actions.
        # For now, manually refine the list based on routes/api.php sections
        explicit_public_routes = {
            ('/register', 'post'), ('/login', 'post'), ('/social-login', 'post'),
            ('/password/forgot', 'post'), ('/password/reset', 'post'),
            ('/popular-products', 'get'), ('/best-selling-products', 'get'),
            ('/check-availability', 'get'), ('/products/calculate-rental-price', 'get'),
            ('/products', 'get'), # ProductQueryController::index
            ('/products/{id}', 'get'), # ProductQueryController::show
            ('/products/search', 'get'),
            ('/top-authors', 'get'), ('/authors', 'get'), ('/authors/{id}', 'get'),
            ('/top-manufacturers', 'get'), ('/manufacturers', 'get'), ('/manufacturers/{id}', 'get'),
            ('/types', 'get'), ('/types/{id}', 'get'),
            ('/attachments', 'get'), ('/attachments/{id}', 'get'),
            ('/categories', 'get'), ('/categories/{id}', 'get'), ('/featured-categories', 'get'),
            ('/delivery-times', 'get'), ('/delivery-times/{id}', 'get'),
            ('/languages', 'get'), ('/languages/{id}', 'get'),
            ('/tags', 'get'), ('/tags/{id}', 'get'),
            ('/refund-reasons', 'get'), ('/refund-reasons/{id}', 'get'),
            ('/resources', 'get'), ('/resources/{id}', 'get'),
            ('/coupons/verify', 'post'),
            ('/attributes', 'get'), ('/attributes/{id}', 'get'),
            ('/import-attributes', 'post'), # Check if this is truly public
            ('/export-attributes/{shop_id}', 'get'),
            ('/shops', 'get'), ('/shops/{slug}', 'get'), ('/near-by-shop', 'get'),
            ('/settings', 'get'), ('/settings/{id}', 'get'), # API Resource, so settings/{id} show
            ('/reviews', 'get'), ('/reviews/{id}', 'get'),
            ('/questions', 'get'), ('/questions/{id}', 'get'),
            ('/feedbacks', 'get'), ('/feedbacks/{id}', 'get'),
            ('/orders/checkout/verify', 'post'),
            ('/orders/track/{identifier}', 'get'),
            ('/payment-intent', 'get'),
            ('/faqs', 'get'), ('/faqs/{id}', 'get'),
            ('/terms-and-conditions', 'get'), ('/terms-and-conditions/{id}', 'get'),
            ('/flash-sale', 'get'), ('/flash-sale/{id}', 'get'),
            ('/refund-policies', 'get'), ('/refund-policies/{id}', 'get'),
            ('/store-notices', 'get'), # API resource only index
            ('/download_url/token/{token}', 'get'),
            ('/became-seller', 'get'), ('/became-seller/{id}', 'get'), # apiResource only index, show
        }
        
        is_public = (openapi_uri, method) in explicit_public_routes
        
        if not is_public:
            path_item['security'] = [{'BearerAuth': []}]

        # Extract parameters from URI (e.g., {id})
        path_params = re.findall(r'\{(\w+)\}', openapi_uri)
        if path_params:
            if 'parameters' not in path_item:
                path_item['parameters'] = []
            for param in path_params:
                # Avoid adding duplicates if already present from template or previous run
                if not any(p.get('name') == param and p.get('in') == 'path' for p in path_item['parameters']):
                    path_item['parameters'].append({
                        'in': 'path',
                        'name': param,
                        'required': True,
                        'schema': {'type': 'string'}, # Use string for flexibility, can refine later
                        'description': f"Identifier for {param.replace('_id', '').replace('Id', ' ')}"
                    })
        
        # Process FormRequest for requestBody
        if controller_details and controller_details['form_request_class']:
            form_request_rules = get_form_request_rules(controller_details['form_request_class'])
            request_body = generate_request_body_schema(form_request_rules)
            if request_body:
                path_item['requestBody'] = request_body
                # Add 422 response for validation errors
                if '422' not in path_item['responses']:
                    path_item['responses']['422'] = {
                        'description': 'Validation error',
                        'content': {
                            'application/json': {
                                'schema': {'$ref': '#/components/schemas/ValidationError'}
                            }
                        }
                    }
        
        # Add the operation to the OpenAPI spec
        if method in openapi_spec['paths'][openapi_uri]:
            # Merge if operation already exists (e.g. from template or previous run)
            openapi_spec['paths'][openapi_uri][method].update(path_item)
        else:
            openapi_spec['paths'][openapi_uri][method] = path_item

    # --- Write to file ---
    with open(OUTPUT_OPENAPI_FILE, 'w') as f:
        yaml.dump(openapi_spec, f, sort_keys=False, indent=2)

if __name__ == '__main__':
    generate_openapi_spec()
