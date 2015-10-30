<?php
/**
 * Created by PhpStorm.
 * User: Mihail.Rybalka
 * Date: 30.10.2015
 * Time: 18:14
 */

namespace RuSPanzer;

use RuSPanzer\Dumper\Config;
use Yandex\Disk\DiskClient;

class Dumper
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
    public function getToken()
    {
        return $this->config->getYaDiskToken();
    }

    /**\
     * @return DiskClient
     */
    public function getDiskClient()
    {
        if (!$this->client) {
            $this->client = new DiskClient($this->getToken());
        }

        return $this->client;
    }

    /**
     *
     */
    public function dump()
    {
        $client = $this->getDiskClient();
        
        dump($client);
        die;
    }

}