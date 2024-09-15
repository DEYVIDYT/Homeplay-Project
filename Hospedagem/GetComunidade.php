<?php

// Ler o conteúdo do arquivo txt.php
$txtFilePath = "txt.php";
$txtData = file_get_contents($txtFilePath);
$usersAndPasswords = json_decode($txtData, true);

// Verificar se o JSON foi decodificado com sucesso
if ($usersAndPasswords === null && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["error" => "Erro ao obter dados do arquivo txt.php."]);
    exit;
}

// Verificar se a array não está vazia
if (empty($usersAndPasswords)) {
    echo json_encode(["error" => "A array de usuários e senhas está vazia no arquivo txt.php."]);
    exit;
}

// Embaralhar a array de pares
shuffle($usersAndPasswords);

// Selecionar um par aleatoriamente
$selectedPair = $usersAndPasswords[array_rand($usersAndPasswords)];

// Construir a resposta JSON com os dados do par selecionado
$response = [
    "user_info" => [
        "username" => $selectedPair["username"],
        "password" => $selectedPair["password"],
        "message" => "Bem Vindo a MaisTV",
        "auth" => 1,
        "status" => "Active",
        "exp_date" => "1717470000",
        "is_trial" => "0",
        "active_cons" => "1",
        "created_at" => "1683335323",
        "max_connections" => "1",
        "allowed_output_formats" => ["m3u8", "ts"]
    ],
    "server_info" => [
        "url" => $selectedPair["url"],
        // Adicionar a porta somente se não for null
        "port" => $selectedPair["porta"] !== null ? $selectedPair["porta"] : "",
        "https_port" => "",
        "server_protocol" => "http",
        "rtmp_port" => "",
        "timezone" => "America/Sao_Paulo",
        "timestamp_now" => time(),
        "time_now" => date("Y-m-d H:i:s")
    ]
];

// Enviar a resposta JSON
echo json_encode($response, JSON_PRETTY_PRINT);
?>

