<?php
/**
 * Created by PhpStorm.
 * User: ruspa
 * Date: 02.11.2015
 * Time: 21:38
 */

namespace RuSPanzer\Backuper;

use RuSPanzer\Backuper\Config as Config;
use RuSPanzer\Backuper\Exception\BackupException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Backup
{
    private $name;

    private $tmpFiles = [];

    private $backuper;

    private $config;

    private $filename;

    public function __construct($name, array $backupConfig, Backuper $backuper)
    {
        $this->name = $name;

        $this->backuper = $backuper;

        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setDefaults([
                'previous-backups-count' => 3,
                'crypt-key' => false,
                'filesystem' => [],
                'databases' => [],
            ])
            ->setAllowedTypes('previous-backups-count', 'integer')
            ->setAllowedTypes('filesystem', 'array')
            ->setAllowedTypes('databases', 'array')
            ->setAllowedTypes('crypt-key', ['bool', 'string'])
        ;

        $this->config = $optionsResolver->resolve($backupConfig);

        $this->filesystemConfig = new Config\Filesystem($name, $this->config['filesystem']);
        $this->databaseConfig = new Config\Database($name, $this->config['databases']);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTmpDir()
    {
        return $this->backuper->getTmpDir();
    }

    /**
     * @param $backupName
     * @return ZipArchive
     * @throws BackupException
     */
    private function createArchive($backupName)
    {
        $archive = new ZipArchive();
        $archiveName = $this->getTmpDir() . DIRECTORY_SEPARATOR . $backupName . '-' . date('Ymd-His') . '.zip';

        if ($archive->open($archiveName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new BackupException("Can not create backup archive " . $archiveName);
        }

        return $archive;
    }

    /**
     * @return Backup
     * @throws BackupException
     */
    public function backup()
    {
        $archive = $this->createArchive($this->getName());

        $this->backupDatabases($archive);
        $this->backupFiles($archive);
        $this->backupDirectories($archive);

        $archive->close();

        $this->setFilename($archive->getFileName());

        if (!empty($this->config['crypt-key'])) {
            $this->encryptArchive();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function encryptArchive()
    {
        $key = $this->config['crypt-key'];

        $fileCrypt = new FileCrypt();

        $source = $this->getFileName();
        $destination = $this->getFileName() . '.encrypted';

        $encryptResult = $fileCrypt->encryptFileChunks($source, $destination, $key);
        if ($encryptResult) {
            $this->setFilename($destination);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeTmpFiles()
    {
        foreach ($this->tmpFiles as $file) {
            @unlink($file);
        }

        return $this;
    }

    /**
     * @param ZipArchive $archive
     */
    protected function backupFiles(ZipArchive $archive)
    {
        foreach ($this->filesystemConfig->getFiles() as $file) {
            $fileName = basename($file);
            $archive->addFile($file, 'files/' . $fileName);
        }
    }

    /**
     * @param ZipArchive $archive
     */
    protected function backupDirectories(ZipArchive $archive)
    {
        foreach ($this->filesystemConfig->getDirectories() as $directory) {
            $dirName = basename($directory);
            $archive->addDirectory($directory, 'dirs' . DIRECTORY_SEPARATOR . $dirName, $this->filesystemConfig->getExcludedDirs());
        }
    }

    /**
     * @param ZipArchive $archive
     * @throws \Exception
     */
    protected function backupDatabases(ZipArchive $archive)
    {
        foreach ($this->databaseConfig->getDatabases() as $name => $dbDumper) {
            $fileName = $this->getTmpDir() . DIRECTORY_SEPARATOR . $name . '-dump.sql.gz';
            $dbDumper->start($fileName);
            $archive->addFile($fileName, 'dbs/' . basename($fileName));
            $this->addTmpFile($fileName);
        }
    }

    /**
     * @param $file
     * @return $this
     */
    public function addTmpFile($file)
    {
        $this->tmpFiles[] = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return Backup
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        $this->addTmpFile($filename);

        return $this;
    }

    /**
     * @return int
     */
    public function getPreviousBackupsCount()
    {
        return $this->config['previous-backups-count'];
    }

}