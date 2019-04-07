<?php

namespace Symfony\Bundle\FrameworkBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

final class SecretsGenerateKeyCommand extends Command
{
    protected static $defaultName = 'secrets:generate-key';
    private $encryptionKeyPath;
    private $filesystem;

    public function __construct(string $encryptionKeyPath)
    {
        $this->encryptionKeyPath = $encryptionKeyPath;
        $this->filesystem = new Filesystem();
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Prints a randomly generated encryption key.')
            ->addArgument('keyLocation', InputArgument::OPTIONAL, 'Path to the key', $this->encryptionKeyPath)
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);
        $path = $input->getArgument('keyLocation');
        $io = new SymfonyStyle($input, $output);
        $io->section('Generating a new key');
        if ($this->filesystem->exists($path)) {
            if (!$io->confirm(sprintf('An old key already exists in "%s". Do you want to replace it?', $path), false)) {
                throw new \RuntimeException('Aborted');
            }
            $io->warning('If secrets have been generated with the old key, you have to regenerate them.');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $encryptionKey = sodium_crypto_stream_keygen();
        $path = $input->getArgument('keyLocation');

        $this->filesystem->dumpFile($path, $encryptionKey);

        sodium_memzero($encryptionKey);

        $io = new SymfonyStyle($input, $output);
        $io->success(sprintf('A new key has been generated in "%s"', $path));
        $io->caution('DO NOT COMMIT that file');

        $io->text('Next Steps:');
        $nextSteps = [
            'Deploy the key on your servers.',
            'Configure the env variable <info>SECRET_KEY_PATH</info> to reference the location of the key.'
        ];
        $nextSteps = array_map(function ($step) {
            return sprintf('  - %s', $step);
        }, $nextSteps);
        $io->text($nextSteps);
    }
}
