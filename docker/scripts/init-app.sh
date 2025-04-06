#!/bin/bash
set -e

# Set variables
ENV_FILE="/var/www/html/.env"
ENV_EXAMPLE_FILE="/var/www/html/.env.example"
SWAGGER_OUTPUT="/var/www/html/public/openapi.json"
LOG_FILE="/var/www/html/writable/logs/init-app.log"
DOCS_DIR="/var/www/html/public/docs"

echo "=== Iniciando inicialização da aplicação ===" | tee -a $LOG_FILE
date | tee -a $LOG_FILE

# Create docs directory if it doesn't exist
if [ ! -d "$DOCS_DIR" ]; then
    echo "Criando diretório de documentação..." | tee -a $LOG_FILE
    mkdir -p $DOCS_DIR
    echo "Diretório de documentação criado com sucesso." | tee -a $LOG_FILE
else
    echo "Diretório de documentação já existe." | tee -a $LOG_FILE
fi

# Create .env file if it doesn't exist
if [ ! -f "$ENV_FILE" ]; then
    echo "Criando arquivo .env a partir de .env.example..." | tee -a $LOG_FILE
    cp $ENV_EXAMPLE_FILE $ENV_FILE
    echo "Arquivo .env criado com sucesso." | tee -a $LOG_FILE
else
    echo "Arquivo .env já existe, pulando criação." | tee -a $LOG_FILE
fi

# Install dependencies
echo "Instalando dependências PHP com Composer..." | tee -a $LOG_FILE
cd /var/www/html && composer install --no-interaction --no-dev --optimize-autoloader | tee -a $LOG_FILE
echo "Dependências do Composer instaladas com sucesso." | tee -a $LOG_FILE

# Generate Swagger documentation
echo "Gerando documentação Swagger/OpenAPI..." | tee -a $LOG_FILE
cd /var/www/html && ./vendor/bin/openapi app -o $SWAGGER_OUTPUT | tee -a $LOG_FILE
echo "Documentação Swagger gerada com sucesso." | tee -a $LOG_FILE

# Run database migrations
echo "Executando migrações do banco de dados..." | tee -a $LOG_FILE
php /var/www/html/spark migrate | tee -a $LOG_FILE
echo "Migrações do banco de dados concluídas." | tee -a $LOG_FILE

# Run database seeds
echo "Executando seeds do banco de dados..." | tee -a $LOG_FILE
php /var/www/html/spark db:seed DatabaseSeeder | tee -a $LOG_FILE
echo "Seeds do banco de dados concluídos." | tee -a $LOG_FILE

# Set proper permissions
echo "Configurando permissões adequadas..." | tee -a $LOG_FILE
chown -R www-data:www-data /var/www/html/writable
chmod -R 777 /var/www/html/writable
echo "Permissões configuradas." | tee -a $LOG_FILE

echo "=== Inicialização da aplicação concluída! ===" | tee -a $LOG_FILE
echo "" | tee -a $LOG_FILE
echo "Sistema pronto para uso!" | tee -a $LOG_FILE
echo "-------------------------------------" | tee -a $LOG_FILE
echo "Acesse a aplicação em: http://localhost" | tee -a $LOG_FILE
echo "Documentação da API: http://localhost/docs" | tee -a $LOG_FILE
echo "Painel do RabbitMQ: http://localhost:15672 (guest/guest)" | tee -a $LOG_FILE
echo "-------------------------------------" | tee -a $LOG_FILE
date | tee -a $LOG_FILE 