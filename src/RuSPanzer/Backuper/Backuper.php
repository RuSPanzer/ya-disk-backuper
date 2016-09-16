<?php
/**
 * Created by PhpStorm.
 * User: Mihail.Rybalka
 * Date: 30.10.2015
 * Time: 18:14
 */

namespace RuSPanzer\Backuper;

use RuSPanzer\Backuper\Exception\ConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yandex\Disk\DiskClient;
use Yandex\Disk\Exception\DiskRequestException;

class Backuper
{

    private $config;

    private $client;

    /** @var Backup[] */
    private $backups;

    public function __construct(array $config)
    {
        $optionsResolver = new OptionsResolver();

        $optionsResolver
            ->setRequired([
                'token',
                'backups'
            ])
            ->setDefaults( [
                'tmp-dir' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'RPYaBackuper',
                'remote-backups-dir' => '/Backups/'
            ])
            ->setAllowedTypes('backups', 'array')
            ->setAllowedTypes('remote-backups-dir', 'string')
            ->setAllowedTypes('token', 'string')

        ;

        $this->config = $optionsResolver->resolve($config);

        if (empty($this->config['backups'])) {
            throw new ConfigurationException("Empty backups config");
        }

        foreach ($this->config['backups'] as $name => $backupConfig) {
            if (!is_array($backupConfig)) {
                throw new ConfigurationException(sprintf('Backup config "%s" must be array', $name));
            }
            $this->backups[] = new Backup($name, $backupConfig, $this);
        }
    }

    /**
     * @return DiskClient
     */
    private function getDiskClient()
    {
        if (!$this->client) {
            $this->client = new DiskClient($this->config['token']);
        }

        return $this->client;
    }

    public function createBackups()
    {
        foreach ($this->backups as $backup) {
            $backup->backup();

            $fileName = $backup->getArchive()->getFileName();
            $uploadDir = $this->prepareUploadDirectory($backup);

            $this->getDiskClient()->uploadFile($uploadDir, [
                'path' => $fileName,
                'size' => filesize($fileName),
                'name' => basename($fileName),
            ]);
            unlink($fileName);
        }
    }

    /**
     * @return string
     */
    public function getTmpDir()
    {
        $tmpDir = $this->config['tmp-dir'];

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        return $tmpDir;
    }

    /**
     * @param Backup $backup
     * @return string
     * @throws DiskRequestException
     * @internal param DiskClient $client
     */
    private function prepareUploadDirectory(Backup $backup)
    {
        $client = $this->getDiskClient();

        $uploadDir = $this->config['remote-backups-dir'] . $backup->getName() . '/';

        try {
            $dirInfo = $client->directoryContents($uploadDir);
        } catch (DiskRequestException $exception) {
            if ($exception->getCode() == 404) {
                $this->createDirectoryRecursive(trim($uploadDir, '/'));
                $dirInfo = $client->directoryContents($uploadDir);
            } else {
                throw $exception;
            }
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
     * @param $dir
     * @throws DiskRequestException
     */
    private function createDirectoryRecursive($dir)
    {
        $client = $this->getDiskClient();
        $dirParts = explode('/', $dir);

        $path = '/';
        foreach ($dirParts as $part) {
            $path .= $part . '/';

            try {
                $client->directoryContents($path);
            } catch (DiskRequestException $exception) {
                if ($exception->getCode() == 404) {
                    $client->createDirectory($path);
                } else {
                    throw $exception;
                }
            }
        }
    }
}