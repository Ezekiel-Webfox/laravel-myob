<?php

// config for Webfox/MYOB
return [
    'client_id'     => env('MYOB_CLIENT_ID'),
    'client_secret' => env('MYOB_CLIENT_SECRET'),
    'redirect_uri'  => env('MYOB_REDIRECT_URI', 'myob/callback'),
    'scope'         => env('MYOB_SCOPE', 'CompanyFile'),
    'grant_type'    => env('MYOB_GRANT_TYPE', 'authorization_code'),
    'config_model'  => 'Webfox\\MYOB\\Models\\MyobConfiguration',
];