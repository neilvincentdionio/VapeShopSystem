<?php

namespace App\Filters;

use App\Libraries\AccessControlService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');
        $access = new AccessControlService();
        $user = $access->getCurrentUser();

        if (!is_array($user)) {
            if ($access->isApiRequest()) {
                return $response->setJSON([
                    'status' => 'error',
                    'message' => 'Unauthorized.',
                ])->setStatusCode(401);
            }

            return redirect()->to('/login')->with('error', 'Please sign in first.');
        }

        $requiredPermissions = is_array($arguments) ? $arguments : [];
        if ($requiredPermissions === []) {
            return null;
        }

        foreach ($requiredPermissions as $permission) {
            if ($access->hasPermission((string) $permission, $user)) {
                return null;
            }
        }

        if ($access->isApiRequest()) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Forbidden. Permission requirement not met.',
            ])->setStatusCode(403);
        }

        return redirect()->to('/dashboard')->with('error', 'Access denied. Insufficient permission.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

