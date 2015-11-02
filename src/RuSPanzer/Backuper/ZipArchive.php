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
     * @param $dirName
     * @param string $localName
     * @param array $excludedDirs
     */
    public function addDirectory($dirName, $localName = '', $excludedDirs = [])
    {
        if ($localName) {
            $this->addEmptyDir($localName);
        }
        $this->_addTree($dirName, $localName, $excludedDirs);
    }

    /**
     * @param $dirName
     * @param $localName
     * @param $excludedDirs
     */
    protected function _addTree($dirName, $localName, $excludedDirs = [])
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
                $this->_addTree($path, $localPath, $excludedDirs);
            }
            else if (is_file($path)) {
                $this->addFile($path, $localPath);
            }
        }
        closedir($dir);
    }
}