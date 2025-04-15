<?php
// Генерация JWT токена
function generate_jwt($payload) {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $secret_key = 'YOUR_SECRET_KEY'; // Уникальный секретный ключ для подписи

    // Кодируем header и payload в base64
    $encoded_header = base64_encode($header);
    $encoded_payload = base64_encode(json_encode($payload));

    // Создаем подпись
    $signature = hash_hmac('sha256', "$encoded_header.$encoded_payload", $secret_key, true);
    $encoded_signature = base64_encode($signature);

    // Возвращаем готовый токен
    return "$encoded_header.$encoded_payload.$encoded_signature";
}

// Проверка JWT токена
function validate_jwt($jwt) {
    $secret_key = 'YOUR_SECRET_KEY'; // Тот же секретный ключ

    // Разбиваем токен на 3 части
    $token_parts = explode('.', $jwt);
    if (count($token_parts) !== 3) {
        return false; // Неверный формат токена
    }

    // Получаем части токена
    list($encoded_header, $encoded_payload, $encoded_signature) = $token_parts;

    // Декодируем header и payload
    $header = json_decode(base64_decode($encoded_header), true);
    $payload = json_decode(base64_decode($encoded_payload), true);

    // Проверяем алгоритм
    if ($header['alg'] !== 'HS256') {
        return false; // Алгоритм не совпадает
    }

    // Проверяем срок действия токена
    if (time() > $payload['exp']) {
        return false; // Токен истек
    }

    // Создаем подпись и сравниваем с переданной подписью
    $signature = base64_decode($encoded_signature);
    $data_to_sign = "$encoded_header.$encoded_payload";
    $expected_signature = hash_hmac('sha256', $data_to_sign, $secret_key, true);

    // Сравниваем подписи
    if ($signature !== $expected_signature) {
        return false; // Подписи не совпадают
    }

    return $payload; // Если все в порядке, возвращаем полезную нагрузку
}
?>