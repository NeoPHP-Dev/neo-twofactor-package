<?php

declare(strict_types=1);

namespace Vendor\NeoPHP\TwoFactorPackage\Service;

final class TotpManager
{
    private const int PERIOD = 30;
    private const int DIGITS = 6;

    public function generateSecret(): string
    {
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ($i = 0; $i < 32; $i++) {
            $secret .= $base32Chars[random_int(0, 31)];
        }

        return $secret;
    }

    public function getQrCodeUrl(string $secret, string $label, string $issuer = 'NeoPHP'): string
    {
        $otpauth = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&period=%d&digits=%d',
            rawurlencode($issuer),
            rawurlencode($label),
            $secret,
            rawurlencode($issuer),
            self::PERIOD,
            self::DIGITS
        );

        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpauth);
    }

    public function verify(string $secret, string $code): bool
    {
        $timestamp = (int) floor(time() / self::PERIOD);

        for ($offset = -1; $offset <= 1; $offset++) {
            if (hash_equals($this->generateCode($secret, $timestamp + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    private function generateCode(string $secret, int $timestamp): string
    {
        $key = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timestamp);

        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord($hash[19]) & 0xf;

        $code = (
                ((ord($hash[$offset]) & 0x7f) << 24) |
                ((ord($hash[$offset + 1]) & 0xff) << 16) |
                ((ord($hash[$offset + 2]) & 0xff) << 8) |
                (ord($hash[$offset + 3]) & 0xff)
            ) % (10 ** self::DIGITS);

        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper($secret);
        $binary = '';

        foreach (str_split($secret) as $char) {
            $pos = strpos($base32Chars, $char);
            if ($pos === false) {
                continue;
            }
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) === 8) {
                $bytes .= chr(bindec($byte));
            }
        }

        return $bytes;
    }
}