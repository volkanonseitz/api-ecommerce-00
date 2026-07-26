<?php
\$content = file_get_contents('routes/api.php');
\$routes = [];

// Regex for single routes: Route::method('/path', [Controller::class, 'method']);
// Ensure backslashes are correctly escaped in regex patterns for literal backslashes
preg_match_all('/Route::(get|post|put|patch|delete)\(\'([^\']+)\',\s*\[([a-zA-Z0-9\\\\]+)::class,\s*\'([^\']+)\'\]\);/', \$content, \$matches, PREG_SET_ORDER);
foreach (\$matches as \$match) {
    \$routes[] = [
        'method' => strtoupper(\$match[1]),
        'uri' => \$match[2],
        'controller' => \$match[3],
        'action' => \$match[4],
        'type' => 'single'
    ];
}

// Regex for Route::apiResource and Route::resource, including chained methods like only/except
preg_match_all('/Route::(apiResource|resource)\(\'([^\']+)\',\s*([a-zA-Z0-9\\\\]+)::class\)(.*?);/s', \$content, \$resourceMatches, PREG_SET_ORDER);

\$resourceActionMap = [
    'index' => ['method' => 'GET', 'uri_suffix' => ''],
    'create' => ['method' => 'GET', 'uri_suffix' => '/create'], // Not typically for API
    'store' => ['method' => 'POST', 'uri_suffix' => ''],
    'show' => ['method' => 'GET', 'uri_suffix' => '/{id}'],
    'edit' => ['method' => 'GET', 'uri_suffix' => '/{id}/edit'], // Not typically for API
    'update' => ['method' => 'PUT', 'uri_suffix' => '/{id}'], // or PATCH
    'destroy' => ['method' => 'DELETE', 'uri_suffix' => '/{id}']
];

foreach (\$resourceMatches as \$match) {
    \$resourceType = \$match[1]; // apiResource or resource
    \$baseUri = \$match[2];
    \$controller = \$match[3];
    \$chainedCalls = \$match[4]; // e.g., "->only(['index', 'show'])"

    \$includedActions = [];
    \$excludedActions = [];

    // Parse 'only' or 'except' clauses
    if (preg_match('/->only\(\[([^\]]+)\]\)/', \$chainedCalls, \$onlyMatch)) {
        preg_match_all('/\'([^\']+)\'/', \$onlyMatch[1], \$actionNames);
        \$includedActions = \$actionNames[1];
    } elseif (preg_match('/->except\(\[([^\]]+)\]\)/', \$chainedCalls, \$exceptMatch)) {
        preg_match_all('/\'([^\']+)\'/', \$exceptMatch[1], \$actionNames);
        \$excludedActions = \$actionNames[1];
    }

    foreach (\$resourceActionMap as \$actionName => \$actionDetails) {
        // Skip 'create' and 'edit' for apiResource, or if explicitly excluded by resource
        if ((\$resourceType === 'apiResource' && in_array(\$actionName, ['create', 'edit'])) || in_array(\$actionName, \$excludedActions)) {
            continue;
        }

        // If 'only' is specified, skip if not in included actions
        if (!empty(\$includedActions) && !in_array(\$actionName, \$includedActions)) {
            continue;
        }

        \$method = \$actionDetails['method'];
        \$uri = \$baseUri . \$actionDetails['uri_suffix'];

        // Special handling for update: apiResource often uses PATCH, resource uses PUT/PATCH
        if (\$actionName === 'update') {
            if (\$resourceType === 'apiResource') {
                \$method = 'PATCH';
            } else {
                \$method = 'PUT';
            }
        }
        
        \$routes[] = [
            'method' => \$method,
            'uri' => \$uri,
            'controller' => \$controller,
            'action' => \$actionName,
            'type' => 'resource'
        ];
    }
}

echo json_encode(\$routes, JSON_PRETTY_PRINT);
