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
        'client_id' => 'ecbf7d1f83964b7942g1c78e580ec9d5', // for support methods for get token
        'secret' => '07bd39163cc040iu43k0b5095255d8d0',// for support methods for get token
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

/** create backup */
// $dumper->createBackup();

/** get oauth token code url */
//$dumper->requestYaDiskAuthUrl();

/** get oauth token by code */
//$dumper->requestYaDiskToken($code);