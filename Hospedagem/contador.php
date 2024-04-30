<?php

function getRealUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function contarVisitasUnicas() {
    $ip = getRealUserIP();
    $arquivoVisitas = 'visitas.txt';
    
    if (!file_exists($arquivoVisitas)) {
        // Cria o arquivo se não existir
        file_put_contents($arquivoVisitas, '[]');
    }

    $visitas = json_decode(file_get_contents($arquivoVisitas), true);

    if (!in_array($ip, $visitas)) {
        $visitas[] = $ip;
        file_put_contents($arquivoVisitas, json_encode($visitas));
    }

    return count($visitas);
}

$totalVisitas = contarVisitasUnicas();
echo json_encode(['totalVisitas' => $totalVisitas]);

?>

