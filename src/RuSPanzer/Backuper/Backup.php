<?php
/**
 * Created by PhpStorm.
 * User: ruspa
 * Date: 02.11.2015
 * Time: 21:38
 */

namespace RuSPanzer\Backuper;


use Ifsnop\Mysqldump\Mysqldump;
use RuSPanzer\Backuper\Exception\BackupException;
use RuSPanzer\Backuper\Exception\ConfigurationException;

class Backup
{
    private $files = [];

    private $directories= [];

    private $databases = [];

    private $name;

    private $previousBackupsCount = 5;

    private $globalConfig;

    private $excludedDirs = [];

    private $dbFiles = [];

    public function __construct($name, array $backupConfig, Config $config)
    {
        $this->name = $name;
        $this->globalConfig = $config;

        if (isset($backupConfig['previous-backups-count'])) {
            $this->previousBackupsCount = $backupConfig['previous-backups-count'];
        }

        if (isset($backupConfig['excluded-dirs'])) {
            foreach ($backupConfig['excluded-dirs'] as $dir) {
                $this->excludedDirs[] = realpath($dir);
            };
        }

        if (isset($backupConfig['files']) && is_array($backupConfig['files'])) {
            foreach ($backupConfig['files'] as $key => $path) {
                if ($key === 'file') {
                    $this->addFile($path);
                } elseif ($key === 'dir') {
                    $this->addDirectory($path);
                }
            }
        }

        if (isset($backupConfig['databases']) && is_array($backupConfig['databases'])) {
            foreach ($backupConfig['databases'] as $name => $dbConfig) {
                $this->addDatabase($name, $dbConfig);
            }
        }
    }

    /**
     * @param $path
     * @throws ConfigurationException
     */
    public function addDirectory($path)
    {
        $path = realpath($path);

        if (!is_dir($path) || !is_readable($path)) {
            throw new ConfigurationException(sprintf("Directory '%s' not exist or no readable"), $path);
        }

        $this->directories[sha1($path)] = $path;
    }

    /**
     * @param $path
     * @throws ConfigurationException
     */
    public function addFile($path)
    {
        $path = realpath($path);

        if (!file_exists($path) || !is_readable($path)) {
            throw new ConfigurationException(sprintf("File '%s' not exist or no readable"), $path);
        }

        $this->files[sha1($path)] = $path;
    }

    /**
     * @param $name
     * @param $config
     */
    public function addDatabase($name, $config)
    {
        $host = isset($config['host']) ? $config['host'] : null;
        $dbName = isset($config['dbname']) ? $config['dbname'] : null;
        $username = isset($config['user']) ? $config['user'] : null;
        $password = isset($config['pass']) ? $config['pass'] : null;

        $this->databases[$name] = new Mysqldump("mysql:host={$host};dbname={$dbName}", $username, $password, [
            'compress' => Mysqldump::GZIP,
        ]);
    }

    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return mixed
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    /**
     * @return Mysqldump[]
     */
    public function getDatabases()
    {
        return $this->databases;
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
        return $this->globalConfig->getTmpDir();
    }

    /**
     * @return array
     */
    public function getExcludedDirs()
    {
        return $this->excludedDirs;
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
     * @return ZipArchive
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

        return $archive;
    }

    /**
     * @param ZipArchive $archive
     */
    protected function backupFiles(ZipArchive $archive)
    {
        foreach ($this->getFiles() as $file) {
            $fileName = basename($file);
            $archive->addFile($file, 'files/' . $fileName);
        }
    }

    /**
     * @param ZipArchive $archive
     */
    protected function backupDirectories(ZipArchive $archive)
    {
        foreach ($this->getDirectories() as $directory) {
            $dirName = basename($directory);
            $archive->addDirectory($directory, 'dirs' . DIRECTORY_SEPARATOR . $dirName, $this->getExcludedDirs());
        }
    }

    /**
     * @param ZipArchive $archive
     * @throws \Exception
     */
    protected function backupDatabases(ZipArchive $archive)
    {
        foreach ($this->getDatabases() as $name => $dbDumper) {
            $fileName = $this->getTmpDir() . DIRECTORY_SEPARATOR . $name . '-dump.sql';
            $dbDumper->start($fileName);
            $archive->addFile($fileName, 'dbs/' . basename($fileName));
            $this->dbFiles[] = $fileName;
        }
    }

}