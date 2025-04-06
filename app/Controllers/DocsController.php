<?php

namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;

class DocsController extends ResourceController
{
    public function docsJson()
    {
        $json = file_get_contents(FCPATH . 'openapi.json');
        if (!$json) {
            return $this->failNotFound('Documentation not found. Please generate it first.');
        }

        return $this->respond(json_decode($json), 200);
    }

    public function docs()
    {
        if (!file_exists(FCPATH . 'openapi.json')) {
            $output = [];
            $return_var = 0;
            exec('cd ' . ROOTPATH . ' && ./vendor/bin/openapi app -o public/openapi.json 2>&1', $output, $return_var);
            
            if ($return_var !== 0) {
                return $this->fail('Failed to generate OpenAPI documentation. Please try again or contact support.');
            }
        }
        
        return view('swagger-ui');
    }
}
