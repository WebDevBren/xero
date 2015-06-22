<?php

return [

    'default' => [
        'application_type' => 'Public',

        'consumer_key' => 'NMJIHFMUNDNH7D1GOAZNNVSLOBNWCW',
        'shared_secret' => '1CH2IHTDJIANDTK00ZWLMTB08LMZ90',

        'rsa_public_key' => storage_path('/Oauth/Xero/public_key.pub'),

        // API versions
        'core_version' => '2.0',
        'payroll_version' => '1.0',
        'file_version' => '1.0',

        'user_agent' => "tabsview_new",

        'request_token_path' => 'oauth/RequestToken',
        'access_token_path' => 'oauth/AccessToken',
        'authorize_path' => 'oauth/Authorize',

        'oauth_callback' => url('/test/198')

    ]

];