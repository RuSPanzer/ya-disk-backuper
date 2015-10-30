<?php
/**
 * Created by PhpStorm.
 * User: Mihail.Rybalka
 * Date: 30.10.2015
 * Time: 18:47
 */

namespace RuSPanzer\Dumper;

use RuSPanzer\Exception\DiskTokenNotFoundException;
use RuSPanzer\Exception\YaDiskConfigNotFoundException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class Config
{

    /**
     * @var
     */
    private $config = array();

    private $accessor;

    public function __construct($config = array())
    {
        $this->config = $config;

        $this->accessor = new PropertyAccessor();
    }

    /**
     * @return string
     * @throws DiskTokenNotFoundException
     * @throws YaDiskConfigNotFoundException
     */
    public function getYaDiskToken()
    {
        $token = $this->accessor->getValue($this->config, '[ya-disk][token]');

        if (!$token) {
            throw new DiskTokenNotFoundException();
        }

        return $token;
    }
}