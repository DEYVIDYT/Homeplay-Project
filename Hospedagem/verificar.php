<?php
// Garantir que o script envie os dados imediatamente
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', false);
@ini_set('implicit_flush', true);
@ini_set('max_execution_time', 0);
while (ob_get_level()) ob_end_clean();
ob_implicit_flush(true);

// Função para fazer requisições assíncronas com curl_multi e timeout
function validar_pares_async($unique_pairs, $timeout = 10) {
    $multi_handle = curl_multi_init();
    $curl_handles = [];
    $result = [];

    // Configurar cada requisição curl
    foreach ($unique_pairs as $i => $par) {
        $url = $par['url'];
        $username = $par['username'];
        $password = $par['password'];
        $porta = isset($par['porta']) ? $par['porta'] : null;

        $url_teste = $porta ? "http://$url:$porta/player_api.php?username=$username&password=$password" : 
                              "http://$url/player_api.php?username=$username&password=$password";

        $curl_handles[$i] = curl_init($url_teste);
        curl_setopt($curl_handles[$i], CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handles[$i], CURLOPT_TIMEOUT, $timeout); // Adicionando timeout
        curl_multi_add_handle($multi_handle, $curl_handles[$i]);
    }

    // Executar as requisições em paralelo com verificação de status
    do {
        $status = curl_multi_exec($multi_handle, $running);
        if ($status != CURLM_OK) {
            // Se houver um erro, interromper o loop
            break;
        }
        // Aguardar atividade de rede para reduzir a carga da CPU
        curl_multi_select($multi_handle);
    } while ($running > 0);

    // Obter respostas e fechar handles
    foreach ($curl_handles as $i => $ch) {
        $response = curl_multi_getcontent($ch);
        curl_multi_remove_handle($multi_handle, $ch);
        curl_close($ch);

        // Verificar se a resposta é válida
        $json_response = json_decode($response, true);
        if (is_array($json_response) && isset($json_response['user_info'])) {
            $result[] = $unique_pairs[$i]; // Adicionar par válido
        }
    }

    curl_multi_close($multi_handle);
    return $result;
}

// Função para exibir o progresso com barra de progresso estilizada
function exibir_progresso($i, $total_pares) {
    $i = min($i, $total_pares); // Garante que o progresso não ultrapasse o total de pares
    $percent = round(($i / $total_pares) * 100);
    echo "<script>
        document.getElementById('progress-bar').style.width = '$percent%';
        document.getElementById('progress-text').innerHTML = 'Progresso: $i/$total_pares - $percent%';
    </script>";
    ob_flush();
    flush();
}

// Carregar o JSON do arquivo txt.php
$data = json_decode(file_get_contents('txt.php'), true);

// Verificar se a leitura do JSON foi bem-sucedida
if ($data === null) {
    die("Erro ao carregar o arquivo JSON.");
}

// Remover pares repetidos
$unique_pairs = [];
$seen_pairs = [];

foreach ($data as $par) {
    $key = $par['url'] . $par['username'] . $par['password'] . (isset($par['porta']) ? $par['porta'] : '');
    if (!in_array($key, $seen_pairs)) {
        $seen_pairs[] = $key;
        $unique_pairs[] = $par;
    }
}

$total_pares = count($unique_pairs);

// Exibir interface de progresso com layout aprimorado no estilo iOS
echo "<div style='width: 100%; margin-top: 20px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif, \"Apple Color Emoji\", \"Segoe UI Emoji\", \"Segoe UI Symbol\";'>
        <div style='width: 100%; background-color: #f0f0f5; border-radius: 10px; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);'>
            <div id='progress-bar' style='width: 0%; height: 20px; background-color: #007aff; border-radius: 10px; text-align: center; color: white;'></div>
        </div>
        <div id='progress-text' style='text-align: center; margin-top: 15px; color: #8e8e93;'>Iniciando validação...</div>
      </div>";

// Validar pares de forma assíncrona com timeout
$i = 0;
$batch_size = 10; // Tamanho do lote para validação assíncrona
$pares_validos = [];

while ($i < $total_pares) {
    // Validar um lote de pares por vez
    $lote = array_slice($unique_pairs, $i, $batch_size);
    $pares_validos = array_merge($pares_validos, validar_pares_async($lote));
    
    $i += $batch_size;
    exibir_progresso($i, $total_pares);
}

// Ajustar o progresso para garantir que não ultrapasse o total de pares
if ($i > $total_pares) {
    exibir_progresso($total_pares, $total_pares);
}

// Atualizar o arquivo txt.php apenas com os pares válidos
file_put_contents('txt.php', json_encode($pares_validos, JSON_PRETTY_PRINT));

// Exibir mensagem de conclusão com resumo
$total_validos = count($pares_validos);
echo "<script>
    document.getElementById('progress-text').innerHTML += '<br>Processo concluído!<br>Total de pares: $total_pares<br>Pares ativos: $total_validos';
</script>";
?>
