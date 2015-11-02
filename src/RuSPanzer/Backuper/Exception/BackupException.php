<?php
/**
 * Created by PhpStorm.
 * User: ruspa
 * Date: 02.11.2015
 * Time: 22:25
 */

namespace RuSPanzer\Backuper\Exception;


class BackupException extends \Exception
{
    public function __construct($message, $code = null)
    {
        return parent::__construct($message, $code);
    }
}