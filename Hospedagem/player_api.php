<?php

function readCache($filename) {
    return file_exists($filename) ? json_decode(file_get_contents($filename), true) : null;
}

function writeCache($filename, $data) {
    file_put_contents($filename, json_encode($data));
}

$cacheFile = 'cache.json';
$cacheData = readCache($cacheFile);

if ($cacheData === null || time() - $cacheData['timestamp'] >= 60) {
    // Se a cache estiver vazia ou se passou mais de 1 minuto desde a última atualização
    $data = json_decode(file_get_contents('txt.php'), true);
    $randomData = $data[array_rand($data)];
    writeCache($cacheFile, ['timestamp' => time(), 'data' => $randomData]);
} else {
    // Usar os dados salvos na cache se ainda estiverem dentro do prazo de 1 minuto
    $randomData = $cacheData['data'];
}

$urlBase = 'http://' . $randomData['url'];
if (!empty($randomData['porta'])) {
    $urlBase .= ':' . $randomData['porta'];
}

if (isset($_GET['action'])) {
    // Construir a URL com base nos dados salvos aleatoriamente
    $generatedUrl = $urlBase . '/player_api.php?username=' . $randomData['username'] . '&password=' . $randomData['password'] . '&action=' . $_GET['action'];

    // Fazer a solicitação GET para a URL gerada usando cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $generatedUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // Enviar a resposta bruta ao usuário
    echo $response;
    exit(); // Termina a execução após enviar a resposta
}

// Se a ação não for encontrada ou não for especificada, retorna os dados já presentes no código
$response = [
    "user_info" => [
        "username" => $randomData['username'],
        "password" => $randomData['password'],
        "message" => null,
        "auth" => 1,
        "status" => "Active",
        "exp_date" => "1707951600",
        "is_trial" => "0",
        "active_cons" => 2,
        "created_at" => null,
        "max_connections" => "2",
        "allowed_output_formats" => ["m3u8", "ts", "rtmp"]
    ],
    "server_info" => [
        "xui" => true,
        "version" => "1.5.5",
        "revision" => 2,
        "url" => $randomData['url'],
        "port" => "",
        "https_port" => "443",
        "server_protocol" => "http",
        "rtmp_port" => "8880",
        "timestamp_now" => 1706388007,
        "time_now" => "2024-01-27 17:40:07",
        "timezone" => "America/Sao_Paulo"
    ]
];

header('Content-Type: application/json');
echo json_encode($response);
?>

