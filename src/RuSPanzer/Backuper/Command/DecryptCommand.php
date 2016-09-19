<?php
/**
 * Created by PhpStorm.
 * User: RuSPanzer
 * Date: 19.09.2016
 * Time: 11:05
 */

namespace RuSPanzer\Backuper\Command;

use RuSPanzer\Backuper\Exception\DecryptException;
use RuSPanzer\Backuper\FileCrypt;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DecryptCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('backuper:decrypt')
            ->setDescription('Decrypt encrypted archive')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to encrypted file')
            ->addOption('key', null, InputOption::VALUE_REQUIRED, 'Crypt key for decrypt')
        ;

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws DecryptException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getOption('key');

        if (empty($key)) {
            throw new DecryptException('Missing encrypt key');
        }

        $file = $input->getArgument('file');
        $path = realpath($file);

        if (!$path || !is_readable($path)) {
            throw new DecryptException(sprintf('File "%s" not found or not readable', $file));
        }

        $finfo = new \SplFileInfo($path);

        if ($finfo->getExtension() !== 'encrypted') {
            throw new DecryptException(sprintf('File "%s" must be with ".encrypted" extension', $file));
        }

        $fileCrypt = new FileCrypt();

        $result = $fileCrypt->decryptFileChunks($path, str_replace('.encrypted', '', $path), $key);

        if ($result) {
            $output->writeln(sprintf('File "%s" decrypt successful', $file));
        } else {
            $output->writeln(sprintf('Error with encrypting file "%s"', $file));
        }

        return;
    }

}