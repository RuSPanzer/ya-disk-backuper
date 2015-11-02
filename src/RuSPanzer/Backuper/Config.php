<?php
/**
 * Created by PhpStorm.
 * User: Mihail.Rybalka
 * Date: 30.10.2015
 * Time: 18:47
 */

namespace RuSPanzer\Backuper;

use RuSPanzer\Backuper\Exception\ConfigurationException;

class Config
{

    /**
     * @var
     */
    private $config = [];

    private $backupsConfig = [];

    private $tmpDir;

    public function __construct($config = array())
    {
        $this->config = $config;

        if (!isset($config['backups']) || !is_array($config['backups'])) {
            throw new ConfigurationException("Invalid backups configuration");
        }

        foreach ($config['backups'] as $name => $backupConfig) {
            $this->backupsConfig[] = new Backup($name, $backupConfig, $this);
        }

        $tmpDir = isset($config['tmp-dir'])
            ? $config['tmp-dir']
            : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'RPZYaBackuper';

        $this->tmpDir = $tmpDir;
    }

    /**
     * @return string
     * @throws ConfigurationException
     */
    public function getYaDiskToken()
    {
        if (empty($this->config['ya-disk']['token'])) {
            throw new ConfigurationException("Yandex disk token not found");
        }

        return $this->config['ya-disk']['token'];
    }

    /**
     * @return Backup[]
     */
    public function getBackups()
    {
        return $this->backupsConfig;
    }

    /**
     * @return string
     */
    public function getTmpDir()
    {
        $tmpDir = $this->tmpDir;

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        return $this->tmpDir;
    }
}