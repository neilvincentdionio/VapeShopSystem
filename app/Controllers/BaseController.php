<?php

namespace App\Controllers;

use App\Libraries\AccessControlService;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    protected $helpers = ['rbac'];
    protected ?AccessControlService $accessControl = null;

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    protected function hasRole(string $roleName): bool
    {
        return $this->accessControl()->hasRole($roleName);
    }

    protected function hasPermission(string $permissionName): bool
    {
        return $this->accessControl()->hasPermission($permissionName);
    }

    /**
     * @return array<string,mixed>|null
     */
    protected function getAuthenticatedUser(): ?array
    {
        return $this->accessControl()->getCurrentUser();
    }

    protected function accessControl(): AccessControlService
    {
        if ($this->accessControl === null) {
            $this->accessControl = new AccessControlService();
        }

        return $this->accessControl;
    }
}
