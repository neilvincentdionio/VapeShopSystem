<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateUserRolesToAdminShopownerCustomer extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        // Expand enum first so conversion values are valid.
        $this->db->query("ALTER TABLE users MODIFY role ENUM('admin','staff','shopowner','seller','customer') NOT NULL DEFAULT 'customer'");

        // Migrate legacy "staff" role to "customer".
        $this->db->query("UPDATE users SET role = 'customer' WHERE role = 'staff'");
        $this->db->query("UPDATE users SET role = 'seller' WHERE role = 'shopowner'");
        $this->db->query("UPDATE users SET role = 'customer' WHERE role = '' OR role IS NULL");
        $this->db->query("ALTER TABLE users MODIFY role ENUM('admin','seller','customer') NOT NULL DEFAULT 'customer'");
    }

    public function down()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        // Map new roles back to legacy format.
        $this->db->query("UPDATE users SET role = 'staff' WHERE role IN ('seller','customer')");
        $this->db->query("ALTER TABLE users MODIFY role ENUM('admin','staff') NOT NULL DEFAULT 'staff'");
    }
}
