<?php

return [
    /*
     * 'paths' — which routes should allow CORS requests
     * 'api/*' means all routes under /api/
     */
    'paths' => ['api/*'],

    /*
     * Which HTTP methods to allow
     */
    'allowed_methods' => ['*'],

    /*
     * Which origins (domains) can make requests
     * During development, we allow localhost:5173 (Vite default port)
     */
    'allowed_origins' => ['http://localhost:5173', 'http://localhost:5174', 'https://boms.test', 'https://boms-frontend.test'],

    'allowed_origins_patterns' => [],

    /*
     * Which headers the browser is allowed to send
     */
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
     * Whether cookies should be sent with cross-origin requests
     * Set to false since we have no authentication
     */
    'supports_credentials' => false,
];