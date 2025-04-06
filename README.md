# Transfer Service

## Descrição

Este projeto é um sistema de transferência de dinheiro entre usuários. Ele utiliza o CodeIgniter 4 (CI4) como framework principal, MySQL como banco de dados e RabbitMQ para gerenciamento de filas de notificações. Todo o ambiente é orquestrado utilizando Docker.

## Tecnologias Utilizadas

- CodeIgniter 4 (CI4)
- MySQL
- RabbitMQ
- Docker

## Requisitos

- Docker
- Docker Compose

## Instalação e Inicialização

Apenas uma comando é necessário para iniciar todo o sistema:

```bash
docker compose up -d
```

Este comando:
- Constrói e inicia todos os contêineres necessários
- Cria o arquivo .env a partir do .env.example automaticamente (se não existir)
- Instala as dependências do Composer
- Executa migrações do banco de dados
- Popula o banco de dados com dados iniciais (caso o banco esteja vazio)
- Gera a documentação OpenAPI/Swagger

O sistema estará pronto para uso em alguns instantes! Você pode acompanhar o progresso com:

```bash
docker logs -f APP
```

## Serviços Disponíveis

Após a inicialização, os seguintes serviços estarão disponíveis:

- **Aplicação API**: http://localhost
- **Documentação API**: http://localhost/docs
- **RabbitMQ Admin**: http://localhost:15672 (credenciais: guest/guest)
- **MySQL**: localhost:3306 (credenciais: root/password)

## API Endpoints

O principal endpoint disponível para transferência está em:
- **Endpoint de Transferência**: `POST http://localhost/transfer`

Corpo da requisição (exemplo):
```json
{
  "value": 100.0,
  "payer": 4,
  "payee": 15
}
```

Consulte a documentação em http://localhost/docs para mais detalhes.

## Documentação da Arquitetura do Projeto

### Introdução

Esta documentação descreve a arquitetura e os principais componentes do projeto utilizando CodeIgniter 4. Inclui informações sobre a estrutura de pastas, padrões de design, camadas de aplicação, e como diferentes componentes interagem entre si.

### Estrutura de Pastas

A estrutura de pastas típica em um projeto CodeIgniter 4 é organizada da seguinte maneira:

```php
project-root/
│
├── app/
│   ├── Commands/
│   ├── Config/
│   ├── Controllers/
│   ├── Database/
│   ├── Entities/
│   ├── Filters/
│   ├── Interfaces/
│   ├── Models/
│   ├── Services/
│   └── Views/
│
├── tests/
│   └── Services/
│
├── writable/
│   ├── cache/
│   ├── logs/
│   └── session/
│
├── docker/
│   ├── config/
│   ├── php/
│   └── scripts/
│
├── env
├── composer.json
├── phpunit.xml
├── DockerFile
└── docker-compose.yaml
```

app/: Contém a lógica principal da aplicação.

Config/: Configurações da aplicação.
Controllers/: Controladores da aplicação.
Database/: Migrations e seeds.
Filters/: Filtros personalizados (middlewares)
Helpers/: Funções auxiliares globais.
Libraries/: Bibliotecas customizadas.
Models/: Modelos de dados.
Services/: Lógica de negócios separada dos controladores.
Entities/: Entidades para representação de objetos de domínio.
ThirdParty/: Pacotes de terceiros.
Views/: Arquivos de visualização.
public/: Ponto de entrada da aplicação.

tests/: Testes automatizados.

writable/: Arquivos que a aplicação pode escrever.

env: Arquivo de variáveis de ambiente.
spark e composer.json: Gerenciamento de dependências.
phpunit.xml: Configuração do PHPUnit.

## Provider (Injeção de Dependências)

O sistema utiliza o padrão de injeção de dependências através do Service Provider do CodeIgniter 4, que centraliza a criação de serviços e facilita o gerenciamento de dependências.

### Implementação

O Service Provider está localizado em `app/Config/Services.php` e contém métodos estáticos para criar instâncias dos diversos serviços utilizados pela aplicação.

### Funcionamento

1. **Lazy Loading**: Os serviços são criados apenas quando necessários.
2. **Singleton**: É possível configurar para que apenas uma instância seja criada.
3. **Substituição de Serviços**: Facilita a substituição de implementações para testes.

### Exemplo

```php
// Em app/Config/Services.php
public static function transferService($getShared = true): TransferServiceInterface
{
    if ($getShared) return static::getSharedInstance('transferService');

    return new TransferService(
        static::userModel(),
        static::walletModel(),
        static::transactionModel(),
        static::transactionStatusModel(),
        static::notificationService(),
        static::authorizationService()
    );
}
```

## Entidades

As entidades representam objetos de domínio na aplicação, tais como Usuário, Carteira, etc.

### Objetivo

- Encapsular dados e comportamentos relativos a um objeto de domínio
- Fornecer validações específicas do domínio
- Separar lógica de negócio do acesso a dados

### Exemplos de Entidades

- **User**: Representa um usuário, com métodos como `isCommonUser()` e `isMerchant()`.
- **Wallet**: Representa uma carteira, com métodos como `hasSufficientBalance()`, `debit()` e `credit()`.

### Interação com Models

Os Models são configurados para retornar instâncias de Entidades, ao invés de arrays.

```php
// Em app/Models/UserModel.php
protected $returnType = 'App\Entities\User';
```

## Sistema de Filas com RabbitMQ

O sistema utiliza o RabbitMQ para gerenciar filas de notificações, principalmente para retentar notificações que falharam.

### Funcionamento

1. **Processamento Assíncrono**: Notificações são processadas de forma assíncrona, permitindo que a transação principal seja concluída mesmo se a notificação falhar.
2. **Durabilidade**: Mensagens são persistidas, garantindo que não sejam perdidas mesmo que o servidor reinicie.
3. **Tentativas Múltiplas**: O sistema tenta reenviar notificações falhas várias vezes antes de descartá-las.

### Implementação

O serviço `NotificationService` encapsula a lógica de envio e gerenciamento da fila de notificações.

## Filtro de Autorização

O filtro de autorização (AuthorizationFilter) é um middleware utilizado para verificar se o usuário possui permissão para acessar determinadas rotas da aplicação. Ele é aplicado antes de a requisição atingir o controlador correspondente.

### Implementação

O filtro é implementado na classe AuthorizationFilter localizada em App\Filters\AuthorizationFilter.php.
Utiliza o serviço authorizationService para verificar se o usuário possui autorização.
Retorna uma resposta não autorizada se a autorização falhar.

### Registro

Registrado no arquivo de configuração app\Config\Filters.php.
O alias checkauth é associado à classe CheckAuth.
Configurado para ser executado antes de determinadas rotas protegidas.

## Camada de Serviços (Services)

### Objetivo

A camada de serviços encapsula a lógica de negócios da aplicação, separando-a dos controladores para promover reutilização e facilitar testes automatizados.

### Exemplo de Estrutura

Services/

- TransferService.php: Implementa a lógica de transferência de dinheiro entre usuários.
- NotificationService.php: Envia notificações aos usuários.

## Controllers

### Objetivo

Os controladores recebem requisições HTTP, interagem com os serviços e retornam respostas para o cliente.

### Exemplo de Estrutura

Controllers/

- TransferController.php: Controla as operações de transferência de dinheiro.

## Models

### Objetivo

Os modelos representam e interagem com os dados do banco de dados, retornando entidades que encapsulam a lógica de domínio.

### Exemplo de Estrutura

Models/

- UserModel.php: Modelo para gerenciamento de usuários.
- WalletModel.php: Modelo para gerenciamento de carteiras.
- TransactionModel.php: Modelo para gerenciamento de transações.
- TransactionStatusModel.php: Modelo para gerenciamento de status de transações.

## Migrations

### Objetivo

As migrations são scripts PHP que criam e modificam a estrutura do banco de dados de forma controlada.

### Exemplo de Estrutura

Database/Migrations/

- 20240101000000_create_users_table.php: Criação da tabela de usuários.
- 20240102000000_create_wallets_table.php: Criação da tabela de carteiras.

## Seeds

### Objetivo

Os seeds são scripts PHP que inserem dados iniciais no banco de dados.

### Exemplo de Estrutura

Database/Seeds/

- UsersSeeder.php: Popula a tabela de usuários com dados de exemplo.
- WalletsSeeder.php: Popula a tabela de carteiras com dados de exemplo.

## Commands

### Objetivo

Criar comandos para serem executados via CLI.

### Exemplo de Estrutura

Commands/

- ProcessNotificationQueue.php: É um exemplo de como processar uma fila de notificações mal sucedidas armazenadas no RabbitMQ. Este comando pode ser configurado para ser executado periodicamente, por exemplo, através de um cron job.

## Tratamento de Race Conditions

O sistema implementa bloqueio otimista de linhas (row locking) utilizando `FOR UPDATE` nas transações críticas para evitar condições de corrida (race conditions) durante transferências.

## Prevenção de Inconsistências com Transações

O sistema utiliza transações do banco de dados para garantir que operações críticas (como transferência de fundos) sejam atômicas, mantendo a consistência dos dados mesmo em caso de falhas.



