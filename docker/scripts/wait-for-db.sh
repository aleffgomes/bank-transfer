#!/bin/bash
set -e

# Hostname do MySQL e outras configurações
host="$1"
shift
user="$1"
shift
password="$1"
shift
database="$1"
shift
max_attempts="20"
wait_time="5"

cmd="$@"

# Função para checar a conexão com MySQL
mysql_ready() {
    mysqladmin ping -h "$host" -u "$user" --password="$password" > /dev/null 2>&1
}

echo "Waiting for MySQL to become available..."
attempt=0

# Loop até que o MySQL responda ou o número máximo de tentativas seja atingido
until mysql_ready || [ $attempt -ge $max_attempts ]; do
    attempt=$((attempt + 1))
    echo "MySQL not available yet (Attempt: $attempt/$max_attempts)... waiting $wait_time seconds"
    sleep $wait_time
done

# Verifica se o MySQL está disponível após todas as tentativas
if [ $attempt -ge $max_attempts ]; then
    echo "MySQL did not become available in time. Aborting."
    exit 1
fi

echo "MySQL is up and running!"

# Tenta criar o banco de dados se ele não existir
echo "Creating database $database if it doesn't exist..."
mysql -h "$host" -u "$user" --password="$password" -e "CREATE DATABASE IF NOT EXISTS $database;"

echo "MySQL database is ready!"

# Executa o comando fornecido como argumento
exec $cmd 