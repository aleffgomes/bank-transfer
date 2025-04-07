#!/bin/bash

# Aguardar MySQL e RabbitMQ estarem prontos
/var/www/html/docker/scripts/wait-for-db.sh mysql_local root password transfer /bin/true
/var/www/html/docker/scripts/wait-for-rabbitmq.sh rabbitmq guest guest /bin/true

# Garantir que o composer install foi executado
cd /var/www/html
if [ ! -d "vendor" ]; then
    composer install --no-interaction --optimize-autoloader
fi

# Função para tratamento de sinais
cleanup() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Recebido sinal de término. Encerrando processador de notificações..."
    exit 0
}

# Registrar handlers de sinais
trap cleanup SIGTERM SIGINT

# Iniciar processador
echo "$(date '+%Y-%m-%d %H:%M:%S') - Iniciando processador de notificações..."

cd /var/www/html

# Configure unbuffered output para PHP
export PYTHONUNBUFFERED=1
export PHP_CLI_SERVER_WORKERS=1

while true; do
    # Executar o comando diretamente sem redirecionar saída para arquivo
    php spark queue:process --sleep=5
    EXIT_CODE=$?
    
    # Se o comando falhar, aguardar antes de tentar novamente
    if [ $EXIT_CODE -ne 0 ]; then
        echo "$(date '+%Y-%m-%d %H:%M:%S') - Erro no processador de notificações. Aguardando 10 segundos antes de reiniciar..."
        sleep 10
    fi
done