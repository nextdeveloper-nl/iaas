<?php

return [
    'scopes'    =>  [
        'global' => [
            '\NextDeveloper\IAM\Database\Scopes\AuthorizationScope',
            '\NextDeveloper\Commons\Database\GlobalScopes\LimitScope',
        ]
    ],
    'regulations'   =>  [
        'pci_dss'   =>  [
            'change_names'  =>  env('PCI_DSS_CHANGE_NAMES', false),
        ]
    ],
    'limits'    =>  [
        'minimum'   =>  [
            'cpu'   =>  -1,
            'ram'   =>  4,
            'disk'  =>  40
        ],
        'simple'    =>  [
            'cpu'   =>  -1,
            'ram'   =>  128,
            'disk'  =>  1280
        ]
    ]
];
