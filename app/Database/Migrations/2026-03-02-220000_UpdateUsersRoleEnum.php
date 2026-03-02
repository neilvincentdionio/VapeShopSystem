<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateUsersRoleEnum extends Migration
{
    public function up()
    {
        // Update any existing 'staff' roles to 'customer' 
        $this->db->query("UPDATE users SET role = 'customer' WHERE role = 'staff'");
        
        // Then modify the ENUM to remove 'staff'
        $this->db->query("ALTER TABLE users MODIFY role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer'");
    }

    public function down()
    {
        // Revert back to include 'staff' ENUM
        $this->db->query("ALTER TABLE users MODIFY role ENUM('admin', 'staff', 'customer') NOT NULL DEFAULT 'customer'");
    }
}
