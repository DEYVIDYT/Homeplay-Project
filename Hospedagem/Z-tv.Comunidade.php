<?php
if (isset($_GET['activecode']) || isset($_GET['code'])) {
    $codigo = isset($_GET['activecode']) ? $_GET['activecode'] : $_GET['code'];

    if ($codigo === '7623') {
        $raw_content = file_get_contents('http://homeplay.x10.mx/GetComunidade.php');
        echo $raw_content;
        exit;
    }

    $firebase_url = "https://z-tv-play-default-rtdb.firebaseio.com/LoginDB.json";
    $firebase_response = file_get_contents($firebase_url);

    if ($firebase_response === false) {
        echo "Erro ao obter dados da Firebase de LoginDB.";
    } else {
        $firebase_data = json_decode($firebase_response, true);

        if (!empty($firebase_data)) {
            foreach ($firebase_data as $key => $item) {
                if (isset($item['codigo']) && $item['codigo'] === $codigo) {
                    $exp_date = strtotime($item['exp_date']);

                    if (time() < $exp_date) {
                        $live_firebase_url = "https://z-tv-play-default-rtdb.firebaseio.com/LiveDB.json";
                        $live_firebase_response = file_get_contents($live_firebase_url);

                        if ($live_firebase_response === false) {
                            echo "Erro ao obter dados da Firebase de LiveDB.";
                        } else {
                            $live_firebase_data = json_decode($live_firebase_response, true);

                            if (!empty($live_firebase_data)) {
                                $random_index = array_rand($live_firebase_data);
                                $random_info = $live_firebase_data[$random_index];

                                $userInfo = array(
                                    "username" => $random_info['usuario'],
                                    "password" => $random_info['password'],
                                    "message" => "Welcome to Xtream Codes Reborn",
                                    "auth" => 1,
                                    "status" => "Active",
                                    "exp_date" => "1989088881",
                                    "is_trial" => "0",
                                    "active_cons" => "2",
                                    "created_at" => "1668297669",
                                    "max_connections" => "4",
                                    "allowed_output_formats" => ["m3u8", "ts", "rtmp"],
                                    // ... (other user info)
                                );

                                $serverInfo = array(
                                    "url" => $random_info['url'],
                                    "port" => $random_info['porta'],
                                    "https_port" => "41002",
                                    "server_protocol" => "http",
                                    "rtmp_port" => "41003",
                                    "timezone" => "Africa/Cairo",
                                    "timestamp_now" => time(),
                                    "time_now" => date("Y-m-d H:i:s"),
                                    // ... (other server info)
                                );

                                $responseArray = array(
                                    "user_info" => $userInfo,
                                    "server_info" => $serverInfo
                                );

                                header('Content-Type: application/json');
                                echo json_encode($responseArray, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                                exit;
                            } else {
                                echo "Erro ao obter dados da Firebase de LiveDB.";
                            }
                        }
                    } else {
                        echo "Código expirado ou inativo.";
                        exit;
                    }
                }
            }
            echo "Código de ativação inválido.";
        } else {
            echo "Erro ao obter dados da Firebase de LoginDB.";
        }
    }
} else {
    echo "Parâmetro 'activecode' ou 'code' não fornecido na URL.";
}
?>
