<?php

namespace Symfony\Bundle\FrameworkBundle\Command;

use Symfony\Bundle\FrameworkBundle\Secret\SecretStorageInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SecretsAddCommand extends Command
{
    protected static $defaultName = 'secrets:add';

    /**
     * @var SecretStorageInterface
     */
    private $secretStorage;

    public function __construct(SecretStorageInterface $secretStorage)
    {
        $this->secretStorage = $secretStorage;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Adds a secret with the key.')
            ->addArgument(
                'key',
                InputArgument::REQUIRED
            )
            ->addArgument(
                'secret',
                InputArgument::REQUIRED
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getArgument('key');
        $secret = $input->getArgument('secret');

        $this->secretStorage->putSecret($key, $secret);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $question = new Question('Key of the secret', $input->getArgument('key'));
        $key = $io->askQuestion($question);
        if (empty($key)) {
            throw new \RuntimeException('The "key" can not be empty');
        }
        $input->setArgument('key', $key);

        $question = new Question('Plaintext secret value', $input->getArgument('secret'));
        $question->setHidden(true);
        $secret = $io->askQuestion($question);
        if (empty($secret)) {
            throw new \RuntimeException('The "secret" can not be empty');
        }
        $input->setArgument('secret', $secret);
    }
}
