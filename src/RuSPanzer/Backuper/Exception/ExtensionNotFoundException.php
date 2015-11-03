<?php
/**
 * Created by PhpStorm.
 * User: Mihail.Rybalka
 * Date: 03.11.2015
 * Time: 13:23
 */

namespace RuSPanzer\Backuper\Exception;

class ExtensionNotFoundException extends \Exception
{
    public function __construct($extension)
    {
        return parent::__construct(sprintf("Extension %s not found. Please install this before using Backuper", $extension));
    }
}