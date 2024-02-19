#!/bin/bash

# Verificar se o Python está instalado
if ! command -v python &> /dev/null; then
    echo "Python não encontrado. Instalando..."
    pkg install -y python
else
    echo "Python já está instalado."
fi

# Lista de bibliotecas Python a serem instaladas
libraries=(
    "numpy"
    "pandas"
    "matplotlib"
    "tensorflow"
)

# Loop para instalar cada biblioteca
for lib in "${libraries[@]}"; do
    pkg install -y python-$lib
    echo "$lib instalado com sucesso."
done

# Salvar a lista de bibliotecas instaladas em um arquivo requirements.txt
pkg list-installed | grep python- > requirements.txt
echo "Lista de bibliotecas salva em requirements.txt."
