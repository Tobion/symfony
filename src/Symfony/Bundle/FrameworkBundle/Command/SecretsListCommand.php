<?php

namespace Symfony\Bundle\FrameworkBundle\Command;

use Symfony\Bundle\FrameworkBundle\Secret\SecretStorageInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SecretsListCommand extends Command
{
    protected static $defaultName = 'secrets:list';

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
            ->setDescription('Lists all secrets.')
            ->addOption('reveal', 'r', InputOption::VALUE_NONE, 'display plain text value alongside keys')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reveal = $input->getOption('reveal');
        $table = new Table($output);
        $table->setHeaders(['key', 'plaintext secret']);

        foreach ($this->secretStorage->listKeys() as $key) {
            $table->addRow([$key, $reveal ? $this->secretStorage->getSecret($key) : '-']);
        }

        $table->render();
    }
}
