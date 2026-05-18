<?php

namespace App\Commands;

use App\Libraries\RbacService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ReactivateAdminRole extends BaseCommand
{
    protected $group = 'RBAC';
    protected $name = 'rbac:reactivate-admin';
    protected $description = 'Reactivates admin role, restores all permissions, and relinks admin users.';

    public function run(array $params)
    {
        $rbac = new RbacService();
        $rbac->repairAdminAccess();
        CLI::write('Admin access repaired (role active, permissions synced, users relinked).', 'green');
        CLI::write('Log out and log back in if the browser still shows access errors.', 'yellow');
    }
}
