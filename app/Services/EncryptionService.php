<?php

namespace App\Services;

use App\Models\Precinct;

class EncryptionService
{
    public function decrypt(Precinct $precinct, string $encryptedPayload): string
    {
        $aesKey = decrypt($precinct->aes_key_encrypted);
        $binary = base64_decode($encryptedPayload);

        $iv = substr($binary, 0, 12);
        $tag = substr($binary, -16);
        $ciphertext = substr($binary, 12, -16);

        $decrypted = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            hex2bin($aesKey),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
        );

        if ($decrypted === false) {
            throw new \RuntimeException('Failed to decrypt payload');
        }

        return $decrypted;
    }

    public function validateChecksum(string $payload, string $expectedChecksum): bool
    {
        $computed = hash('sha256', $payload);

        return hash_equals($computed, $expectedChecksum);
    }

    public function encrypt(array $ballots, string $aesKey): string
    {
        $payload = json_encode($ballots);
        $iv = random_bytes(12);

        $ciphertext = openssl_encrypt(
            $payload,
            'aes-256-gcm',
            hex2bin($aesKey),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
        );

        return base64_encode($iv.$ciphertext.$tag);
    }

    public function computeChecksum(string $payload): string
    {
        return hash('sha256', $payload);
    }
}
