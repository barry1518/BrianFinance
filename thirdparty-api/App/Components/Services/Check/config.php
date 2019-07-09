<?php
return [
    'params' => [
        // Emailage configuration params
        'emailage' => [
            'sId'               => '49129D28A8DE4604924584F6D0478278',
            'authToken'         => '0E5D0F7F907E4B2197DC146F932ED5F0',
            'url'               => 'https://api.emailage.com/EmailAgeValidator/',
            'requestFormat'     => 'json',
            'oauthVersion'      => '1.0',
            'signatureMethod'   => 'HMAC-SHA256',
            'method'            => 'POST'
        ],
        // Maxmind configuration params
        'maxmind' => [
            'mxId'              => '108545',
            'mxLicense'         => 'T9jOlRtj77bt',
            'mxFraudInsights'   => 'https://minfraud.maxmind.com/minfraud/v2.0/insights',
            'mxCert'            => 'cacert.pem'
        ]
    ]
];
