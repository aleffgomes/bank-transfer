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

## Clonagem do Repositório

Para obter o código-fonte do projeto, siga estes passos:

```bash
git clone https://github.com/seu-usuario/bank-transfer.git

cd bank-transfer
```

Caso você não tenha acesso ao repositório original, você pode fazer um fork antes de clonar.

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

> **Importante:** Os valores monetários devem ser enviados com ponto decimal (.) para separar os centavos, por exemplo: 100.55 para representar 100 reais e 55 centavos. O formato 100,55 não será aceito pela API.

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

O sistema utiliza o filtro `CheckAuth.php` para verificar a autorização de requisições. Este filtro:

- É implementado como um middleware do CodeIgniter 4
- Verifica automaticamente se uma transação está autorizada antes de processá-la
- Utiliza o serviço de autorização configurado no sistema
- Retorna erro 401 (Unauthorized) se a requisição não for autorizada

Este filtro é aplicado globalmente a endpoints críticos, simplificando o código dos controladores e centralizando a lógica de autorização.

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

- **ProcessNotificationQueue.php**: Processa a fila de notificações mal sucedidas armazenadas no RabbitMQ. Este comando pode ser configurado para ser executado periodicamente através de um cron job.

  Para executar este comando manualmente:
  ```bash
  php spark queue:process --timeout=60
  ```
  Onde `timeout` é o tempo máximo em segundos que o processo ficará em execução tentando processar notificações pendentes. O padrão é 30 segundos.

  Este comando é fundamental para garantir que as notificações que falharam durante o processo de transferência sejam eventualmente entregues, melhorando a confiabilidade do sistema.

- **ResetDatabase.php**: Permite resetar o banco de dados completamente, recriando todas as tabelas e aplicando as migrations.

  Para executar este comando:
  ```bash
  php spark db:reset
  ```

  Este comando é útil durante o desenvolvimento ou para reiniciar o ambiente para testes. Ele:
  1. Remove todas as tabelas existentes
  2. Recria a estrutura do banco de dados usando as migrations
  3. Aplica os seeders se configurados

## Sistema de Manipulação de Valores Monetários

### Classe Money

O projeto utiliza a classe `Money.php` para lidar com valores monetários de forma precisa e segura. Esta abordagem resolve problemas comuns ao trabalhar com valores monetários:

- **Evita problemas de precisão com números de ponto flutuante**: Armazena valores em centavos como inteiros para evitar erros de arredondamento.
- **Garante operações matemáticas precisas**: Adição, subtração, multiplicação e divisão são implementadas com precisão.
- **Imutabilidade**: Cada operação retorna uma nova instância de Money, evitando efeitos colaterais.
- **Comparações confiáveis**: Métodos como `isGreaterThan()`, `isEqualTo()` garantem comparações corretas.

Esta classe é essencial para aplicações financeiras, garantindo que os cálculos monetários sejam precisos e confiáveis.

## Tratamento de Race Conditions

O sistema implementa bloqueio otimista de linhas (row locking) utilizando `FOR UPDATE` nas transações críticas para evitar condições de corrida (race conditions) durante transferências.

## Prevenção de Inconsistências com Transações

O sistema utiliza transações do banco de dados para garantir que operações críticas (como transferência de fundos) sejam atômicas, mantendo a consistência dos dados mesmo em caso de falhas.



