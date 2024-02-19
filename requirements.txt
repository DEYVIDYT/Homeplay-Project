#!/bin/bash

# Lista de bibliotecas Python a serem instaladas
libraries=(
    "numpy"
    "pandas"
    "matplotlib"
    "tensorflow"
)

# Loop para instalar cada biblioteca
for lib in "${libraries[@]}"; do
    pip install $lib
    echo "$lib instalado com sucesso."
done

# Salvar a lista de bibliotecas instaladas em um arquivo requirements.txt
pip freeze > requirements.txt
echo "Lista de bibliotecas salva em requirements.txt."
