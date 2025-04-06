<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Swagger extends BaseConfig
{
    public $url;
    public $title;
    public $description;
    public $version;
    /**
     * Função para inicializar com valores do .env
     */
    public function __construct()
    {
        parent::__construct();

        // Carregar variáveis do .env se estiverem disponíveis
        if (isset($_ENV['swagger.url'])) {
            $this->url = $_ENV['swagger.url'];
        }

        if (isset($_ENV['swagger.title'])) {
            $this->title = $_ENV['swagger.title'];
        }

        if (isset($_ENV['swagger.description'])) {
            $this->description = $_ENV['swagger.description'];
        }

        if (isset($_ENV['swagger.version'])) {
            $this->version = $_ENV['swagger.version'];
        }
    }
} 