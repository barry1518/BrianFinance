<?php
/**
* tunnel make easy connection to other remote services
* Every tunnel should be declared in this file
* your tunnel will be usable bye calling : $this->tunnel_{name}->get();
* params system for different environment is the same are services
  'name' => [
    'params' => [
                'url',
                'url_params_key' => true, //if true /getContact/id/23, if false /getContact/23
                'verify_ssl'     => true,
                'ssl_certificate'=> '', // mycertificate.crt, required if verify_ssl is true
                'auth_type'      => 'Bearer' //Basic, Bearer, Digest, HOBA
                ],
    'params_dev' => [
                'url',
                'url_params_key' => true, //if true /getContact/id/23, if false /getContact/23
                'verify_ssl'     => true,
                'ssl_certificate'=> '', // mycertificate.crt, required if verify_ssl is true, your certificate should be in App/Certificate
                'auth_type'      => 'Bearer' //Basic, Bearer, Digest, HOBA
                ],
    'params_preprod' => [
                'url',
                'url_params_key' => true, //if true /getContact/id/23, if false /getContact/23
                'verify_ssl'     => true,
                'ssl_certificate'=> '', // mycertificate.crt, required if verify_ssl is true
                'auth_type'      => 'Bearer' //Basic, Bearer, Digest, HOBA
                ],
    'params_prod' => [
                'url',
                'url_params_key' => true, //if true /getContact/id/23, if false /getContact/23
                'verify_ssl'     => true,
                'ssl_certificate'=> '', // mycertificate.crt, required if verify_ssl is true
                'auth_type'      => 'Bearer' //Basic, Bearer, Digest, HOBA
                ]
  ]
*/
return [
    'lastNameOrigin' => [
        'params_dev' => [
            'url'               => 'http://forebears.io',
            'url_params_key'    => false,
            'verify_ssl'        => false,
            'ssl_certificate'   => '',
            'auth_type'         => ''
        ]
    ]
];