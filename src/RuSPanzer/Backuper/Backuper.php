<?php
/**
 * Created by PhpStorm.
 * User: Mihail.Rybalka
 * Date: 30.10.2015
 * Time: 18:14
 */

namespace RuSPanzer\Backuper;

use Yandex\Disk\DiskClient;

class Backuper
{

    private $config;

    private $client;

    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    /**
     * @return string
     */
    public function getYaDiskToken()
    {
        return $this->config->getYaDiskToken();
    }

    /**\
     * @return DiskClient
     */
    public function getDiskClient()
    {
        if (!$this->client) {
            $this->client = new DiskClient($this->getYaDiskToken());
        }

        return $this->client;
    }

    /**
     * @return Backup[]
     */
    public function getBackups()
    {
        return $this->config->getBackups();
    }

    /**
     *
     */
    public function createBackup()
    {
        foreach ($this->getBackups() as $backup) {
            $archive = $backup->backup();

            //todo save backups and import to yadisk
        }
    }

}