<?php
/**
 * Created by PhpStorm.
 * User: Mihail.Rybalka
 * Date: 30.10.2015
 * Time: 18:14
 */


require_once __DIR__ . '/vendor/autoload.php';

$config = [
    'ya-disk' => [
        'token' => '13b8174a029e406e8ffdb2f12322eb89',
    ],
    'backups' => [
        [
            'files' => [
                'dir' => '.'
            ],
            'databases' => [

            ],
            'params' => [
                'save-count' => 3,
            ]
        ]
    ]
];


$dumper = new \RuSPanzer\Dumper($config);

$dumper->dump();