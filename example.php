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
        'some-site' => [
            'previous-backups-count' => 3,
            'files' => [
                'dir' => '../somedir',
                'file' => './somelog.txt'
            ],
            'excluded-dirs' => [
                '../somedir/vendor',
            ],
            'databases' => [
                'some-database' => [
                    'host' => 'localhost',
                    'dbname' => 'test',
                    'user' => 'test',
                    'pass' => 'test',
                ]
            ],
        ]
    ]
];


$dumper = new \RuSPanzer\Backuper\Backuper($config);

$dumper->createBackup();