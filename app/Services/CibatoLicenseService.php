<?php

namespace App\Services;

class CibatoLicenseService
{
    public static function currentDomain(): string
    {
        return (string) ($_SERVER['SERVER_NAME'] ?? request()->getHost() ?? 'localhost');
    }

    public static function checkEcommerceKey(string $key): ?string
    {
        return self::sendGet('check_ecommerce', ['key' => $key]);
    }

    public static function verifyPurchaseCode(string $purchaseCode): ?string
    {
        return self::sendPost('verify_purchase_code', ['purchase_code' => $purchaseCode], ['code' => $purchaseCode]);
    }

    public static function itemInfo(string $purchaseCode): ?string
    {
        return self::sendGet('item_info', ['purchase_code' => $purchaseCode]);
    }

    public static function registeredAddonInfo(string $purchaseCode): ?string
    {
        return self::sendGet('registered_addon_info', ['purchase_code' => $purchaseCode]);
    }

    public static function registeredAddonList(string $purchaseCode): ?string
    {
        return self::sendGet('registered_addon_list', ['purchase_code' => $purchaseCode]);
    }

    public static function checkAddonActivation(string $uniqueIdentifier, ?string $mainItem = null, ?string $domain = null): ?string
    {
        return self::sendPost('check_addon_activation', [], [
            'url' => $domain ?: self::currentDomain(),
            'unique_identifier' => $uniqueIdentifier,
            'main_item' => $mainItem ?: (get_setting('item_name') ?? 'Cibato Commerce'),
        ]);
    }

    public static function checkActivationByDomain(?string $domain = null): ?string
    {
        return self::sendPost('check_activation', [], [
            'url' => $domain ?: self::currentDomain(),
        ]);
    }

    public static function checkFlutterActivation(string $key): ?string
    {
        return self::sendGet('check_flutter', ['key' => $key]);
    }

    private static function endpoint(string $name): string
    {
        return (string) config("services.cibato_license.endpoints.$name", '');
    }

    private static function sendGet(string $endpointName, array $replace = []): ?string
    {
        $endpoint = self::endpoint($endpointName);
        if ($endpoint === '') {
            return null;
        }
        $url = strtr($endpoint, [
            '{key}' => (string) ($replace['key'] ?? ''),
            '{purchase_code}' => (string) ($replace['purchase_code'] ?? ''),
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response === false ? null : $response;
    }

    private static function sendPost(string $endpointName, array $replace = [], array $payload = []): ?string
    {
        $endpoint = self::endpoint($endpointName);
        if ($endpoint === '') {
            return null;
        }
        $url = strtr($endpoint, [
            '{key}' => (string) ($replace['key'] ?? ''),
            '{purchase_code}' => (string) ($replace['purchase_code'] ?? ''),
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response === false ? null : $response;
    }
}
