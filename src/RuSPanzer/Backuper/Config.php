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

    private $backups = [];

    private $tmpDir;

    public function __construct($config = array())
    {
        $this->config = $config;

        if (!isset($config['backups']) || !is_array($config['backups'])) {
            throw new ConfigurationException("Invalid backups configuration");
        }

        foreach ($config['backups'] as $name => $backupConfig) {
            $this->backups[] = new Backup($name, $backupConfig, $this);
        }

        $tmpDir = isset($config['tmp-dir'])
            ? $config['tmp-dir']
            : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'RPZYaBackuper';

        $this->tmpDir = $tmpDir;
    }

    /**
     * @return Backup[]
     */
    public function getBackups()
    {
        return $this->backups;
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

    /**
     * @param $param
     *
     * @return array
     * @throws ConfigurationException
     */
    public function getYaDiskParam($param)
    {
        if (empty($this->config['ya-disk'][$param])) {
            throw new ConfigurationException(sprintf("Yandex disk param %s not found", $param));
        }

        return $this->config['ya-disk'][$param];
    }


    /**
     * @return string
     * @throws ConfigurationException
     */
    public function getYaDiskToken()
    {
        return $this->getYaDiskParam('token');
    }
}