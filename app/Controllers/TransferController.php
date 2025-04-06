<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Exception;
use Config\Services;
use Config\Swagger as SwaggerConfig;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'App Transfer API',
    attachables: [new OA\Attachable()]
)]

class TransferController extends ResourceController
{
    protected $transferService;
    protected $swaggerConfig;

    public function __construct()
    {
        $this->transferService = Services::transferService();
        $this->swaggerConfig = new SwaggerConfig();
    }

    // Documentation
    #[OA\Post(path: '/transfer', summary: 'Transfer money from one user to another')]
    #[OA\RequestBody(required: true, description: 'Transfer money from one user to another. Note: Use decimal point (.) for monetary values, not comma.', content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'payer', type: 'integer', description: 'Payer user id'),
            new OA\Property(property: 'payee', type: 'integer', description: 'Payee user id'),
            new OA\Property(property: 'value', type: 'number', format: 'float', description: 'Amount to transfer. Use decimal point (.) for cents, e.g. 100.55'),
        ],
        required: ['payer', 'payee', 'value']
    ))]

    #[OA\Response(response: 200, description: 'Transfer successful')]
    #[OA\Response(response: 400, description: 'Bad Request - Validation errors or invalid parameters')]
    #[OA\Response(response: 401, description: 'Unauthorized - The request was not authorized by the external service')]
    #[OA\Response(response: 403, description: 'Forbidden - Insufficient balance or merchant trying to send money')]
    #[OA\Response(response: 404, description: 'Not Found - User or wallet not found')]
    #[OA\Response(response: 500, description: 'Internal Server Error')]
    // End of documentation
    
    public function transfer(): \CodeIgniter\HTTP\Response
    {
        $rules = [
            'payer' => 'required|integer',
            'payee' => 'required|integer',
            'amount' => 'required|numeric',
        ];

        if (!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $requestData = $this->request->getJSON(true);

        $payerId = $requestData['payer'];
        $payeeId = $requestData['payee'];
        $amount = $requestData['amount'];

        try {
            $db = \Config\Database::connect();
            $db->transStart();

            $result = $this->transferService->transfer($payerId, $payeeId, $amount);

            $db->transComplete();

            if (!$db->transStatus()) {
                return $this->fail($db->error()['message'], $db->error()['code']);
            }

            return $this->respond($result, $result['code']);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), $e->getCode());
        }
    }

}
