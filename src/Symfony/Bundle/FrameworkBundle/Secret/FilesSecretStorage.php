<?php

namespace Symfony\Bundle\FrameworkBundle\Secret;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FilesSecretStorage implements SecretStorageInterface
{
    private const FILE_SUFFIX = '.bin';

    /**
     * @var string
     */
    private $secretsFolder;
    /**
     * @var string
     */
    private $filesystem;
    private $encoder;

    public function __construct(string $secretsFolder, EncoderInterface $encoder)
    {
        $this->secretsFolder = rtrim($secretsFolder, DIRECTORY_SEPARATOR);
        $this->filesystem = new Filesystem();
        $this->encoder = $encoder;
    }

    public function getSecret(string $key): string
    {
        return $this->decryptFile($this->getFilePath($key));
    }

    public function putSecret(string $key, string $secret): void
    {
        $this->filesystem->dumpFile($this->getFilePath($key), $this->encoder->encrypt($secret));
    }

    public function deleteSecret(string $key): void
    {
        $this->filesystem->remove($this->getFilePath($key));
    }

    public function listSecrets(): iterable
    {
        if (!$this->filesystem->exists($this->secretsFolder)) {
            return;
        }

        /** @var File $file */
        foreach ((new Finder())->in($this->secretsFolder)->files() as $file) {
            $key = $file->getBasename(SELF::FILE_SUFFIX);
            yield $key => $this->getSecret($key);
        }
    }

    private function decryptFile(string $filePath): string
    {
        return $this->encoder->decrypt(file_get_contents($filePath));
    }

    private function getFilePath(string $key): string
    {
        return $this->secretsFolder.DIRECTORY_SEPARATOR.$key.self::FILE_SUFFIX;
    }
}
