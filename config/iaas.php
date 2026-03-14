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
    ],

    'cloud-init'    =>  [
        'available' =>  env('IAAS_CLOUD_INIT_AVAILABLE', false),
    ],

    'console'   =>  [
        //  Due to security there items should not have default values
        'key'   =>  env('IAAS_CONSOLE_KEY' ),
        'iv'    =>  env('IAAS_CONSOLE_IV' )
    ],

    'platforms' =>  [
        'xenserver82'   =>  ''
    ],

    /*
    |--------------------------------------------------------------------------
    | Virtual Machine Lifecycle & CRUD Hooks
    |--------------------------------------------------------------------------
    | Register handler classes per hook. Each class must implement
    | NextDeveloper\IAAS\Contracts\VirtualMachineHandlerInterface.
    |
    | Lifecycle hooks : booting, booted, shutting_down, shutdown,
    |                   suspended, resumed, deploying, deployed
    | CRUD hooks      : creating, created, updating, updated, deleting, deleted
    |
    | Example:
    | 'booting' => [\App\Handlers\VirtualMachines\VncTokenGenerator::class],
    */
    'vm_hooks'  =>  [
        'booting'       => [],
        'booted'        => [],
        'shutting_down' => [],
        'shutdown'      => [],
        'suspended'     => [],
        'resumed'       => [],
        'deploying'     => [],
        'deployed'      => [],
        'creating'      => [],
        'created'       => [],
        'updating'      => [],
        'updated'       => [],
        'deleting'      => [],
        'deleted'       => [],
    ],
];
