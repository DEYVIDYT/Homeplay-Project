<?php

// URL que contém o JSON
$url = 'http://homeplay.x10.mx/txt.php';

// Obtém o JSON da URL fornecida
$json = file_get_contents($url);

// Decodifica o JSON para um array PHP
$pares_array = json_decode($json, true);

// Escolhe aleatoriamente um par de dados
$par_aleatorio = $pares_array[array_rand($pares_array)];

// Constrói a nova URL com os dados do par escolhido
$new_url = "http://" . $par_aleatorio['url'];

// Adiciona a porta, se disponível
if (!is_null($par_aleatorio['porta'])) {
    $new_url .= ":" . $par_aleatorio['porta'];
}

$new_url .= "/get.php?username=" . $par_aleatorio['username'] . "&password=" . $par_aleatorio['password'] . "&type=m3u_plus";

// Faz um GET na nova URL e mostra o conteúdo raw
$resultado = file_get_contents($new_url);
echo $resultado;

