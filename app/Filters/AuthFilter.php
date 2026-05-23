<?php

namespace App\Filters;

use App\Libraries\AccessControlService;
use App\Libraries\RbacService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $access = new AccessControlService();
        
        // Check if user is logged in
        if (!$session->get('logged_in')) {
            return redirect()->to('/login');
        }

        // Check session timeout (30 minutes)
        $lastActivity = $session->get('last_activity');
        $timeout = 30 * 60; // 30 minutes

        if ($lastActivity && (time() - $lastActivity) > $timeout) {
            $session->destroy();
            return redirect()->to('/login')
                           ->with('error', 'Session expired. Please login again.');
        }

        // Update last activity
        $session->set('last_activity', time());

        $currentUser = $access->getCurrentUser();
        if (
            is_array($currentUser)
            && strtolower((string) $session->get('user_role')) === 'admin'
            && strtolower((string) ($currentUser['role'] ?? '')) === 'customer'
            && trim((string) ($currentUser['shop_name'] ?? '')) !== ''
        ) {
            \Config\Database::connect()->table('users')
                ->where('id', (int) ($currentUser['id'] ?? 0))
                ->update(['role' => 'admin']);
            $session->remove('admin_access_repaired');
            $currentUser = $access->getCurrentUser();
        }

        if (
            strtolower((string) $session->get('user_role')) === 'admin'
            && !$session->get('admin_access_repaired')
        ) {
            (new RbacService())->repairAdminAccess();
            $session->set('admin_access_repaired', true);
        }

        // Check role-based access if arguments are provided
        if ($arguments && !empty($arguments)) {
            $granted = false;
            foreach ((array) $arguments as $requiredRole) {
                $requiredRole = strtolower(trim((string) $requiredRole));
                if ($requiredRole === '') {
                    continue;
                }

                if ($requiredRole === 'admin') {
                    $currentUser = $access->getCurrentUser();
                    $resolvedRole = strtolower(trim((string) ($currentUser['role'] ?? $session->get('user_role') ?? '')));

                    // Allow custom back-office roles (e.g. assistant_admin).
                    // Customer and rider roles must not pass admin-only routes.
                    if ($resolvedRole !== '' && !in_array($resolvedRole, ['customer', 'rider'], true)) {
                        $granted = true;
                        break;
                    }
                }

                if ($access->hasRole($requiredRole)) {
                    $granted = true;
                    break;
                }
            }

            if (!$granted) {
                return redirect()->to('/dashboard')
                               ->with('error', 'Access denied. Insufficient privileges.');
            }
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }
}
