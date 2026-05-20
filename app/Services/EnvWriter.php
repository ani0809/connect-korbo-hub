<?php

namespace App\Services;

class EnvWriter
{
    public static function write(array $data): void
    {
        $envPath = base_path('.env');
        $envContent = file_exists($envPath) ? (string) file_get_contents($envPath) : '';

        foreach ($data as $key => $value) {
            $value = (string) $value;
            if (str_contains($value, ' ')) {
                $value = '"'.$value.'"';
            }

            if (preg_match('/^'.preg_quote($key, '/').'=.*/m', $envContent)) {
                $envContent = (string) preg_replace('/^'.preg_quote($key, '/').'=.*/m', $key.'='.$value, $envContent);
            } else {
                $envContent .= PHP_EOL.$key.'='.$value;
            }
        }

        file_put_contents($envPath, $envContent);

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    public static function get(string $key): ?string
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return null;
        }

        $content = (string) file_get_contents($envPath);
        if (preg_match('/^'.preg_quote($key, '/')."=(.*)$/m", $content, $matches) === 1) {
            return trim($matches[1], "\"' ");
        }

        return null;
    }
}
