<?php
/**
 * Created by PhpStorm.
 * User: ruspa
 * Date: 02.11.2015
 * Time: 21:39
 */

namespace RuSPanzer\Backuper\Exception;


class ConfigurationException extends \Exception
{
    public function __construct($message, $code = null)
    {
        return parent::__construct($message, $code);
    }
}