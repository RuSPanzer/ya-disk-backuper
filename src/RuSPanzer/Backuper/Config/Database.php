<?php
/**
 * Created by PhpStorm.
 * User: RuSPanzer
 * Date: 16.09.2016
 * Time: 16:22
 */

namespace RuSPanzer\Backuper\Config;

use Ifsnop\Mysqldump\Mysqldump;
use RuSPanzer\Backuper\Exception\ConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Database
{
    private $databases = [];

    private $backupName;

    public function __construct($backupName, array $config)
    {
        $this->backupName = $backupName;

        foreach ($config as $name => $dbConfig) {
            $optionsResolver = new OptionsResolver();
            $optionsResolver
                ->setRequired([
                    'host',
                    'dbname',
                    'user',
                    'pass',
                ])
                ->setDefaults([
                    'exclude-tables' => [],
                ])
                ->setAllowedTypes('host', 'string')
                ->setAllowedTypes('dbname', 'string')
                ->setAllowedTypes('user', 'string')
                ->setAllowedTypes('pass', 'string')
                ->setAllowedTypes('exclude-tables', 'array')
            ;
            $dbConfig = $optionsResolver->resolve($dbConfig);

            $this->addDatabase($name, $dbConfig);
        }
    }

    /**
     * @param $name
     * @param $config
     * @throws ConfigurationException
     */
    private function addDatabase($name, $config)
    {
        if (array_key_exists($name, $this->databases)) {
            throw new ConfigurationException(sprintf('Database "%s" in backup config "%s" already exists', $name, $this->backupName));
        }
        $db = new Mysqldump("mysql:host={$config['host']};dbname={$config['dbname']}", $config['user'], $config['pass'], [
            'compress' => Mysqldump::GZIP,
            'exclude-tables' => $config['exclude-tables']
        ]);


        $this->databases[$name] = $db;
    }

    /**
     * @return Mysqldump[]
     */
    public function getDatabases()
    {
        return $this->databases;
    }

}