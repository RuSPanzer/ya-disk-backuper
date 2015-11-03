<?php
/**
 * Created by PhpStorm.
 * User: Mihail.Rybalka
 * Date: 30.10.2015
 * Time: 18:14
 */

namespace RuSPanzer\Backuper;

use Yandex\Disk\DiskClient;
use Yandex\Disk\Exception\DiskRequestException;
use Yandex\OAuth\OAuthClient;

class Backuper
{

    private $config;

    private $client;

    private $yaDiskOauthClient;

    /** @var Backup[] */
    private $completedBackups = [];

    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    /**\
     * @return DiskClient
     */
    public function getDiskClient()
    {
        if (!$this->client) {
            $this->client = new DiskClient($this->config->getYaDiskToken());
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
            $this->completedBackups[] = $backup->backup();
        }

        $client = $this->getDiskClient();

        foreach ($this->completedBackups as $backup) {
            $fileName = $backup->getArchive()->getFileName();

            $uploadDir = $this->prepareUploadDirectory($client, $backup);

            $client->uploadFile($uploadDir, [
                'path' => $fileName,
                'size' => filesize($fileName),
                'name' => basename($fileName),
            ]);
            unlink($fileName);
        }
    }

    /**
     * @param DiskClient $client
     * @param Backup     $backup
     *
     * @return string
     */
    private function prepareUploadDirectory(DiskClient $client, Backup $backup)
    {
        $uploadDir = '/Backups/' . $backup->getName() . '/';

        try {
            $dirInfo = $client->directoryContents($uploadDir);
        } catch (DiskRequestException $exception) {
            $client->createDirectory($uploadDir);
            $dirInfo = $client->directoryContents($uploadDir);
        }

        $existedBackups = [];
        foreach ($dirInfo as $itemInfo) {
            if ($itemInfo['resourceType'] == 'file'
                && $itemInfo['contentType'] == 'application/zip'
                && strpos($itemInfo['href'], $backup->getName())
            ) {
                $date = new \DateTime($itemInfo['creationDate']);
                $existedBackups[$date->getTimestamp()] = $itemInfo['href'];
            }
        }
        ksort($existedBackups);
        $existedBackups = array_reverse($existedBackups, true);
        if (count($existedBackups) >= $backup->getPreviousBackupsCount()) {
            $itemsForDelete = array_slice($existedBackups, $backup->getPreviousBackupsCount());
            foreach ($itemsForDelete as $item) {
                $client->delete($item);
            }
        }

        return $uploadDir;
    }

    /**
     * @return OAuthClient
     * @throws Exception\ConfigurationException
     */
    protected function getYaDiskOauthClient()
    {
        if (!$this->yaDiskOauthClient) {
            $clientId =  $this->config->getYaDiskParam('client_id');
            $secret = $this->config->getYaDiskParam('secret');
            $this->yaDiskOauthClient = new OAuthClient($clientId, $secret);
        }

        return $this->yaDiskOauthClient;
    }

    /**
     * @return string
     */
    public function requestYaDiskAuthUrl()
    {
        $oauthClient = $this->getYaDiskOauthClient();

        return $oauthClient->getAuthUrl();
    }

    /**
     * @param $code
     *
     * @return OAuthClient
     * @throws \Yandex\OAuth\Exception\AuthRequestException
     * @throws \Yandex\OAuth\Exception\AuthResponseException
     */
    public function requestYaDiskToken($code)
    {
        $oauthClient = $this->getYaDiskOauthClient();

        return $oauthClient->requestAccessToken($code)->getAccessToken();
    }
}