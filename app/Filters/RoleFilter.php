<?php

namespace App\Filters;

use App\Libraries\AccessControlService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
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

        $requiredRoles = is_array($arguments) ? $arguments : [];
        if ($requiredRoles === []) {
            return null;
        }

        foreach ($requiredRoles as $requiredRole) {
            if ($access->hasRole((string) $requiredRole, $user)) {
                return null;
            }
        }

        if ($access->isApiRequest()) {
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Forbidden. Role requirement not met.',
            ])->setStatusCode(403);
        }

        return redirect()->to('/dashboard')->with('error', 'Access denied. Insufficient role.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

