<?php

namespace Symfony\Bundle\FrameworkBundle\Secret;

use Symfony\Component\Filesystem\Filesystem;

class FilesSecretStorage implements SecretStorageInterface
{
    /**
     * @var string
     */
    private $secretsFolder;
    /**
     * @var string
     */
    private $encryptionKey;
    private $filesystem;

    public function __construct(string $secretsFolder, string $encryptionKey)
    {
        $this->secretsFolder = rtrim($secretsFolder, DIRECTORY_SEPARATOR);
        $this->encryptionKey = $encryptionKey;
        $this->filesystem = new Filesystem();
    }

    public function getSecret(string $key): string
    {
        return $this->decryptFile($this->getFilePath($key));
    }

    public function putSecret(string $key, string $secret): void
    {
        $nonce = random_bytes(SODIUM_CRYPTO_STREAM_NONCEBYTES);
        $ciphertext = sodium_crypto_stream_xor($secret, $nonce, $this->encryptionKey);

        sodium_memzero($secret);

        $message = new EncryptedMessage($ciphertext, $nonce);

        $this->filesystem->dumpFile($this->getFilePath($key), (string) $message);
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

        foreach (scandir($this->secretsFolder) as $fileName) {
            if ('.' === $fileName || '..' === $fileName) {
                continue;
            }

            $key = basename($fileName, '.bin');
            yield $key => $this->getSecret($key);
        }
    }

    private function decryptFile(string $filePath): string
    {
        $encrypted = file_get_contents($filePath);

        $message = EncryptedMessage::createFromString($encrypted);

        return sodium_crypto_stream_xor($message->getCiphertext(), $message->getNonce(), $this->encryptionKey);
    }

    private function getFilePath(string $key): string
    {
        return $this->secretsFolder.DIRECTORY_SEPARATOR.$key.'.bin';
    }
}
