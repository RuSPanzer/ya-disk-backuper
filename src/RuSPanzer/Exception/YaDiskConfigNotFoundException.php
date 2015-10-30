<?php
/**
 * Created by PhpStorm.
 * User: Mihail.Rybalka
 * Date: 30.10.2015
 * Time: 18:48
 */

namespace RuSPanzer\Exception;

class YaDiskConfigNotFoundException extends \Exception
{
    public function __construct()
    {
        return parent::__construct('Yandex disk config not found');
    }
}