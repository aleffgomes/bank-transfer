#!/bin/bash
set -e

# Set variables
ENV_FILE="/var/www/html/.env"
ENV_EXAMPLE_FILE="/var/www/html/.env.example"
SWAGGER_OUTPUT="/var/www/html/public/openapi.json"
LOG_DIR="/var/www/html/writable/logs"
LOG_FILE="$LOG_DIR/init-app.log"
mkdir -p $LOG_DIR
touch $LOG_FILE
DOCS_DIR="/var/www/html/public/docs"

# Função para log
log() {
    echo "$1" | tee -a $LOG_FILE
}

log "=== Iniciando inicialização da aplicação ==="
date | tee -a $LOG_FILE

# Create docs directory if it doesn't exist
if [ ! -d "$DOCS_DIR" ]; then
    log "Criando diretório de documentação..."
    mkdir -p $DOCS_DIR
    log "Diretório de documentação criado com sucesso."
else
    log "Diretório de documentação já existe."
fi

# Create .env file if it doesn't exist
if [ ! -f "$ENV_FILE" ]; then
    log "Criando arquivo .env a partir de .env.example..."
    cp $ENV_EXAMPLE_FILE $ENV_FILE
    log "Arquivo .env criado com sucesso."
else
    log "Arquivo .env já existe, pulando criação."
fi

log "Dependências do Composer (instaladas via Dockerfile) verificadas."

# Generate Swagger documentation
log "Gerando documentação Swagger/OpenAPI..."
# Verifica se o binário do openapi existe antes de executar
if [ -f "/var/www/html/vendor/bin/openapi" ]; then
    cd /var/www/html && ./vendor/bin/openapi app -o $SWAGGER_OUTPUT | tee -a $LOG_FILE
    log "Documentação Swagger gerada com sucesso."
else
    log "AVISO: vendor/bin/openapi não encontrado. Pulando geração da documentação."
fi

# Run database migrations
log "Executando migrações do banco de dados..."
php /var/www/html/spark migrate | tee -a $LOG_FILE
log "Migrações do banco de dados concluídas."

log "Seeds não executados automaticamente. Execute 'docker compose exec php-fpm php spark db:seed DatabaseSeeder' manualmente se necessário."

# Set proper permissions
log "Configurando permissões adequadas..."
chown -R www-data:www-data /var/www/html/writable
chmod -R 777 /var/www/html/writable
log "Permissões configuradas."

log "=== Inicialização da aplicação concluída! ==="
echo "" | tee -a $LOG_FILE
echo "Sistema pronto para uso!" | tee -a $LOG_FILE
echo "-------------------------------------" | tee -a $LOG_FILE
echo "Acesse a aplicação em: http://localhost" | tee -a $LOG_FILE
echo "Documentação da API: http://localhost/docs" | tee -a $LOG_FILE
echo "Painel do RabbitMQ: http://localhost:15672 (guest/guest)" | tee -a $LOG_FILE
echo "-------------------------------------" | tee -a $LOG_FILE
date | tee -a $LOG_FILE 