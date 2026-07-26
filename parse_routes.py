import re
import json

def parse_route_line(line):
    line = line.strip()
    
    # Regex for single routes: Route::method('/path', [Controller::class, 'method']);
    single_route_pattern = re.compile(r"Route::(get|post|put|patch|delete)\('([^']+)',\s*\[([^:]+)::class,\s*'([^']+)'\]\);")
    match = single_route_pattern.match(line)
    if match:
        return [{
            'method': match.group(1).upper(),
            'uri': match.group(2),
            'controller': match.group(3),
            'action': match.group(4),
            'type': 'single'
        }]

    # Regex for Route::apiResource and Route::resource, with optional only/except
    resource_route_pattern = re.compile(r"Route::(apiResource|resource)\('([^']+)',\s*([^:]+)::class\)(?:->(only|except)\(\[(.*?)\]\))?;")
    match = resource_route_pattern.match(line)
    if match:
        resource_type = match.group(1)
        base_uri = match.group(2)
        controller = match.group(3)
        modifier_type = match.group(4)
        modifier_actions_str = match.group(5)

        included_actions = []
        excluded_actions = []

        if modifier_type:
            # Extract method names from the 'only' or 'except' array string
            actions = re.findall(r"'([^']+)'", modifier_actions_str)
            if modifier_type == 'only':
                included_actions = actions
            else: # 'except'
                excluded_actions = actions
        
        # Standard resource actions map
        # 'create' and 'edit' are typically for web forms, often omitted in API
        resource_action_map = {
            'index': {'method': 'GET', 'uri_suffix': ''},
            'store': {'method': 'POST', 'uri_suffix': ''},
            'show': {'method': 'GET', 'uri_suffix': '/{id}'},
            'update': {'method': 'PUT', 'uri_suffix': '/{id}'}, # PATCH for apiResource
            'destroy': {'method': 'DELETE', 'uri_suffix': '/{id}'},
            'create': {'method': 'GET', 'uri_suffix': '/create'}, # Not common for API
            'edit': {'method': 'GET', 'uri_suffix': '/{id}/edit'}, # Not common for API
        }

        generated_routes = []
        for action_name, details in resource_action_map.items():
            # Filter based on 'only' / 'except'
            if (included_actions and action_name not in included_actions) or \
               (excluded_actions and action_name in excluded_actions):
                continue
            
            # Skip create/edit for apiResource by default, unless explicitly 'only' included
            if resource_type == 'apiResource' and action_name in ['create', 'edit'] and action_name not in included_actions:
                continue
            
            method = details['method']
            if action_name == 'update' and resource_type == 'apiResource':
                method = 'PATCH' # apiResource uses PATCH for update by default

            uri = base_uri + details['uri_suffix']
            
            generated_routes.append({
                'method': method,
                'uri': uri,
                'controller': controller,
                'action': action_name,
                'type': resource_type
            })
        return generated_routes

    return []

def main():
    routes_data = []
    with open('raw_routes.txt', 'r') as f:
        for line in f:
            parsed = parse_route_line(line)
            routes_data.extend(parsed)

    with open('extracted_routes.json', 'w') as f:
        json.dump(routes_data, f, indent=2)

if __name__ == '__main__':
    main()
