<?php

namespace Symfony\Bundle\FrameworkBundle\Secret;

class SodiumEncoder implements EncoderInterface
{
    private $encryptionKey;

    public function __construct(?string $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;
    }

    public function generateKey(): string
    {
        $encryptionKey = sodium_crypto_stream_keygen();
        $this->encryptionKey = $encryptionKey;
        sodium_memzero($encryptionKey);

        return $this->encryptionKey;
    }

    public function encrypt(string $message): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_STREAM_NONCEBYTES);
        $ciphertext = sodium_crypto_stream_xor($message, $nonce, $this->getKey());

        sodium_memzero($message);

        return $this->encode($nonce, $ciphertext);
    }

    public function decrypt(string $encryptedText): string
    {
        [$nonce, $ciphertext] = $this->decode($encryptedText);

        return sodium_crypto_stream_xor($ciphertext, $nonce, $this->getKey());
    }

    private function encode(string $nonce, string $ciphertext): string
    {
        return $nonce.$ciphertext;
    }

    private function decode(string $message): array
    {
        if (\strlen($message) < SODIUM_CRYPTO_STREAM_NONCEBYTES) {
            throw new \RuntimeException('Invalid ciphertext. Message is too short.');
        }

        $nonce = substr($message, 0, SODIUM_CRYPTO_STREAM_NONCEBYTES);
        $ciphertext = substr($message, SODIUM_CRYPTO_STREAM_NONCEBYTES);

        return [$nonce, $ciphertext];
    }

    private function getKey()
    {
        if ($this->encryptionKey === null) {
            throw new \InvalidArgumentException('The encryption key does not exists.');
        }

        return $this->encryptionKey;
    }
}
