<?php

return [

    'fetch' => [
        'environment' => 'production',
        'certificate' => storage_path().'/Certificates.pem',
        'passPhrase'  => 'Prd4577nh!',
        'service'     => 'apns',
    ],

];
