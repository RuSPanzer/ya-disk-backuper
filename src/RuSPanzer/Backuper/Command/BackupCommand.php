<?php
/**
 * Created by PhpStorm.
 * User: RuSPanzer
 * Date: 16.09.2016
 * Time: 12:15
 */

namespace RuSPanzer\Backuper\Command;

use RuSPanzer\Backuper\Backuper;
use RuSPanzer\Backuper\Exception\ConfigurationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BackupCommand
 * @package RuSPanzer\Backuper\Command
 */
class BackupCommand extends Command
{

    /**
     * Configures command.
     */
    protected function configure()
    {
        $this
            ->setName('backuper:backup')
            ->setDescription('Create backups')
            ->addArgument('config', InputArgument::REQUIRED, 'config.json file path')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws ConfigurationException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getArgument('config');
        $path = realpath($configFile);

        if (!file_exists($path) || !is_readable($path)) {
            throw new ConfigurationException(sprintf('Error with read config file "%s"', $configFile));
        }

        $config = @json_decode(file_get_contents($path), true);

        if (empty($config) || !is_array($config)) {
            throw new ConfigurationException("Invalid config file");
        }

        $backuper = new Backuper($config);

        $backuper->createBackups();
    }
}