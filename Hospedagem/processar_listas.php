<?php

// Obtém as URLs do formulário
$rawUrls = explode("\n", $_POST['urls']);
$urls = array_map(function($url) { return rtrim($url); }, $rawUrls); // Remove espaços em branco no final das URLs

// Processa cada URL e adiciona ao array
foreach ($urls as $url) {
    if (preg_match('/^https?:\/\/[^\?]+\?username=.+&password=.+$/', $url)) {
        $urlParts = parse_url($url);
        parse_str($urlParts['query'], $queryParams);

        $key = $urlParts['host'] . $queryParams['username'] . $queryParams['password'];

        $newList[$key] = [
            "url" => $urlParts['host'],
            "username" => $queryParams['username'],
            "password" => $queryParams['password'],
            "porta" => isset($urlParts['port']) ? $urlParts['port'] : null
        ];
    } else {
        // URL não suportada
        header("Location: index.html?message=Essa URL não é suportada. Por favor, coloque uma URL no formato m3u.&messageType=error");
        exit(); // Encerra o script após redirecionar
    }
}

// Obtém o conteúdo atual do arquivo txt.php
$currentData = file_get_contents("txt.php");

// Converte o JSON para um array associativo em PHP
$data = json_decode($currentData, true);

// Mescla os arrays antigo e novo
$data = array_replace_recursive($data, $newList);

// Reindexa o array para evitar índices numéricos
$data = array_values($data);

// Converte o array de volta para JSON
$newJson = json_encode($data, JSON_PRETTY_PRINT);

// Atualiza o arquivo txt.php com o novo JSON, mantendo o conteúdo anterior
file_put_contents("txt.php", $newJson);

// Redireciona de volta para a página principal com uma mensagem de agradecimento
header("Location: index.html?message=Obrigado por apoiar o aplicativo Z-TV Play!&messageType=success");

// Executa o VerificarURL.php
if (function_exists('exec')) {
    exec("php VerificarURL.php");
} else {
    // Handle the case where exec() is not available
    echo "exec() function is not available";
}
?>
