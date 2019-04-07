<?php

namespace Symfony\Bundle\FrameworkBundle\Secret;

interface EncoderInterface
{
    public function generateKey(): string;

    public function encrypt(string $message): string;

    public function decrypt(string $encryptedText): string;
}
