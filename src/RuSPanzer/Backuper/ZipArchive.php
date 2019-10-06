<?php
/**
 * Created by PhpStorm.
 * User: ruspa
 * Date: 02.11.2015
 * Time: 23:05
 */

namespace RuSPanzer\Backuper;

class ZipArchive extends \ZipArchive
{

    /**
     * @var
     */
    private $fileName;

    /**
     * @param $dirName
     * @param string $localName
     * @param array $excludedDirs
     */
    public function addDirectory($dirName, $localName = '', $excludedDirs = [])
    {
        if ($localName) {
            $this->addEmptyDir($localName);
        }
        $this->addTree($dirName, $localName, $excludedDirs);
    }

    /**
     * @param $dirName
     * @param $localName
     * @param $excludedDirs
     */
    protected function addTree($dirName, $localName, $excludedDirs = [])
    {
        $dir = opendir($dirName);
        while ($filename = readdir($dir)) {
            if ($filename == '.' || $filename == '..')
                continue;

            $path = $dirName . DIRECTORY_SEPARATOR . $filename;
            $localPath = $localName ? ($localName . '/' . $filename) : $filename;
            if (is_dir($path)) {
                if (in_array($path, $excludedDirs)) {
                    continue;
                }
                $this->addEmptyDir($localPath);
                $this->addTree($path, $localPath, $excludedDirs);
            }
            else if (is_file($path)) {
                $this->addFile($path, $localPath);
            }
        }
        closedir($dir);
    }

    /**
     * @param string $filename
     * @param null   $flags
     *
     * @return mixed
     */
    public function open($filename, $flags = null)
    {
        $this->setFileName($filename);
        return parent::open($filename, $flags);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param $fileName
     * @return $this
     */
    protected function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }
}