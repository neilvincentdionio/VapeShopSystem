<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FixUserRolesSeeder extends Seeder
{
    public function run()
    {
        // Update users with empty or null roles to 'customer'
        $this->db->query("UPDATE users SET role = 'customer' WHERE role = '' OR role IS NULL");
        
        // Update any existing 'staff' roles to 'customer'
        $this->db->query("UPDATE users SET role = 'customer' WHERE role = 'staff'");
        
        echo "User roles have been fixed - staff roles converted to customer.\n";
    }
}
