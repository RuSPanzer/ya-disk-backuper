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

    private $dbFiles = [];

    private $archive;

    private $backuper;

    private $config;

    public function __construct($name, array $backupConfig, Backuper $backuper)
    {
        $this->name = $name;

        $this->backuper = $backuper;

        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setDefaults([
                'previous-backups-count' => 3,
                'filesystem' => [],
                'databases' => [],
            ])
            ->setAllowedTypes('previous-backups-count', 'integer')
            ->setAllowedTypes('filesystem', 'array')
            ->setAllowedTypes('databases', 'array')
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

        foreach ($this->dbFiles as $file) {
            unlink($file);
        }

        $this->archive = $archive;

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
            $fileName = $this->getTmpDir() . DIRECTORY_SEPARATOR . $name . '-dump.sql';
            $dbDumper->start($fileName);
            $archive->addFile($fileName, 'dbs/' . basename($fileName));
            $this->dbFiles[] = $fileName;
        }
    }

    /**
     * @return ZipArchive
     * @throws BackupException
     */
    public function getArchive()
    {
        if ($this->archive === null) {
            throw new BackupException("Use Backup::backup() before getting archive file");
        }

        return $this->archive;
    }

    /**
     * @return int
     */
    public function getPreviousBackupsCount()
    {
        return $this->config['previous-backups-count'];
    }

}