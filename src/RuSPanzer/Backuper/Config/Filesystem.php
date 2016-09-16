<?php
/**
 * Created by PhpStorm.
 * User: RuSPanzer
 * Date: 16.09.2016
 * Time: 16:22
 */

namespace RuSPanzer\Backuper\Config;


use RuSPanzer\Backuper\Exception\ConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Filesystem
{

    private $files = [];

    private $directories= [];

    private $excludedDirs = [];

    private $backupName;

    public function __construct($backupName, array $config)
    {
        $this->backupName = $backupName;

        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setDefaults([
                'files' => [],
                'dirs' => [],
                'excluded-dirs' => [],
            ])
            ->setAllowedTypes('files', 'array')
            ->setAllowedTypes('dirs', 'array')
            ->setAllowedTypes('excluded-dirs', 'array')
        ;

        $config = $optionsResolver->resolve($config);

        foreach ($config['excluded-dirs'] as $dir) {
            $this->excludedDirs[] = realpath($dir);
        };

        foreach ($config['files'] as $path) {
            $this->addFile($path);
        }

        foreach ($config['dirs'] as $path) {
            $this->addDirectory($path);
        }
    }

    /**
     * @param $path
     * @throws ConfigurationException
     */
    protected function addDirectory($path)
    {
        $path = realpath($path);

        if (!is_dir($path) || !is_readable($path)) {
            throw new ConfigurationException(sprintf("Directory '%s' not exist or no readable", $path));
        }

        $this->directories[sha1($path)] = $path;
    }

    /**
     * @param $path
     * @throws ConfigurationException
     */
    protected function addFile($path)
    {
        $path = realpath($path);

        if (!file_exists($path) || !is_readable($path)) {
            throw new ConfigurationException(sprintf("File '%s' not exist or no readable"), $path);
        }

        $this->files[sha1($path)] = $path;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
    }


    /**
     * @return array
     */
    public function getExcludedDirs()
    {
        return $this->excludedDirs;
    }
}