<?php

namespace Symfony\Bundle\FrameworkBundle\Command;

use Symfony\Bundle\FrameworkBundle\Secret\EncoderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SecretsGenerateKeyCommand extends Command
{
    protected static $defaultName = 'secrets:generate-key';
    private $encoder;

    public function __construct(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Prints a randomly generated encryption key.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $encryptionKey = $this->encoder->generateKey();

        $output->write($encryptionKey, false, OutputInterface::OUTPUT_RAW);
    }
}
