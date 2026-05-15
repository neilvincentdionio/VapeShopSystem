<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

abstract class BaseApiController extends BaseController
{
    protected function successResponse(mixed $data = null, string $message = 'Success', int $code = 200): ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data ?? (object) [],
        ]);
    }

    protected function errorResponse(string $message = 'Error', array $errors = [], int $code = 400): ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON([
            'success' => false,
            'message' => $message,
            'errors' => (object) $errors,
        ]);
    }

    protected function currentUserId(): int
    {
        return (int) ($this->request->user['id'] ?? 0);
    }
}
