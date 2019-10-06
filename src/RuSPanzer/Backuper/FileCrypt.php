<?php

namespace RuSPanzer\Backuper;
use RuSPanzer\Backuper\Exception\BackupException;

/**
 * Class FileCrypt
 * @package RuSPanzer\Backuper
 * @see http://stackoverflow.com/questions/16175154/best-approach-to-encrypt-big-files-with-php
 */
class FileCrypt
{

    var $CHUNK_SIZE;

    function __construct()
    {
        $this->CHUNK_SIZE = 100 * 1024; // 100Kb

        if (extension_loaded('mcrypt') !== true) {
            throw new BackupException('Extension mcrypt not found in your system');
        }
    }

    /**
     * @param $string
     * @param $key
     * @return bool|string
     */
    public function encrypt($string, $key)
    {
        $key = pack('H*', $key);

        return mcrypt_encrypt(MCRYPT_BLOWFISH, substr($key, 0, mcrypt_get_key_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB)), $string, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB), MCRYPT_RAND));
    }

    /**
     * @param $string
     * @param $key
     * @return bool|string
     */
    public function decrypt($string, $key)
    {
        $key = pack('H*', $key);

        return mcrypt_decrypt(MCRYPT_BLOWFISH, substr($key, 0, mcrypt_get_key_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB)), $string, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB), MCRYPT_RAND));
    }

    /**
     * @param $source
     * @param $destination
     * @param $key
     * @return bool
     */
    public function encryptFileChunks($source, $destination, $key)
    {
        return $this->cryptFileChunks($source, $destination, $key, 'encrypt');
    }

    /**
     * @param $source
     * @param $destination
     * @param $key
     * @return bool
     */
    public function decryptFileChunks($source, $destination, $key)
    {
        return $this->cryptFileChunks($source, $destination, $key, 'decrypt');
    }

    /**
     * @param $source
     * @param $destination
     * @param $key
     * @param $op
     * @return bool
     */
    private function cryptFileChunks($source, $destination, $key, $op)
    {

        if ($op != "encrypt" && $op != "decrypt") {
            return false;
        }

        $inHandle = fopen($source, 'rb');
        $outHandle = fopen($destination, 'wb+');

        if ($inHandle === false || $outHandle === false) {
            return false;
        }

        while (!feof($inHandle)) {
            $buffer = fread($inHandle, $this->CHUNK_SIZE);
            $buffer = $this->$op($buffer, $key);
            fwrite($outHandle, $buffer);
        }
        fclose($inHandle);
        fclose($outHandle);

        return true;
    }

    /**
     * @param $source
     * @param $key
     * @return bool
     */
    public function printFileChunks($source, $key)
    {
        $inHandle = fopen($source, 'rb');

        if ($inHandle === false) {
            return false;
        }

        while (!feof($inHandle)) {
            $buffer = fread($inHandle, $this->CHUNK_SIZE);
            $buffer = $this->decrypt($buffer, $key);
            echo $buffer;
        }
        return fclose($inHandle);
    }
}