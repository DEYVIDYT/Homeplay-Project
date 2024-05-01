import ftplib
import json
import requests
import concurrent.futures
import sys

# Função para fazer o GET request e verificar se o par é válido
def validar_par(par):
    url = par['url']
    username = par['username']
    password = par['password']
    porta = par.get('porta')  # Obtém a porta, se existir
    if porta:
        url_teste = f"http://{url}:{porta}/player_api.php?username={username}&password={password}"
    else:
        url_teste = f"http://{url}/player_api.php?username={username}&password={password}"
    try:
        response = requests.get(url_teste)
        json_response = response.json()
        if "user_info" in json_response:
            return par
    except (json.JSONDecodeError, requests.exceptions.ConnectionError):
        pass
    return None

# Conectar ao servidor FTPS
ftp = ftplib.FTP_TLS('ipdahospedagem-Ftps')
ftp.login(user='usuarioHOSPEDAGEM-FTPS', passwd='senhaHOSPEDAGEM-FTPS')
ftp.cwd('/domains/homeplay.x10.mx/public_html/') #Local onde estão o arquivo txt.php que estão os loguins 

# Baixar o arquivo txt.php
with open('txt.php', 'wb') as file:
    ftp.retrbinary('RETR txt.php', file.write)

# Ler e carregar o JSON do arquivo
with open('txt.php', 'r') as file:
    data = json.load(file)

# Lista para armazenar pares válidos
pares_validos = []

# Função para exibir o progresso em tempo real
def print_progress(i, total_pares, pares_ativos):
    sys.stdout.write(f"\rProgresso: {i}/{total_pares} - Pares ativos: {pares_ativos}")
    sys.stdout.flush()

# Testar cada par usando múltiplas threads
with concurrent.futures.ThreadPoolExecutor() as executor:
    futures = {executor.submit(validar_par, par): par for par in data}
    for i, future in enumerate(concurrent.futures.as_completed(futures), start=1):
        result = future.result()
        if result:
            pares_validos.append(result)
        print_progress(i, len(data), len(pares_validos))

# Atualizar o arquivo txt.php apenas com os pares válidos
with open('txt.php', 'w') as file:
    json.dump(pares_validos, file)

# Enviar o arquivo atualizado de volta para o diretório FTP
with open('txt.php', 'rb') as file:
    ftp.storbinary('STOR txt.php', file)

# Fechar conexão FTP
ftp.quit()

print("\nProcesso concluído.")

