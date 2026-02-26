<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameShopOwnerRoleToSeller extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $this->db->query("ALTER TABLE users MODIFY role ENUM('admin','shopowner','seller','customer') NOT NULL DEFAULT 'customer'");
        $this->db->query("UPDATE users SET role = 'seller' WHERE role = 'shopowner'");
        $this->db->query("ALTER TABLE users MODIFY role ENUM('admin','seller','customer') NOT NULL DEFAULT 'customer'");
    }

    public function down()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $this->db->query("ALTER TABLE users MODIFY role ENUM('admin','shopowner','seller','customer') NOT NULL DEFAULT 'customer'");
        $this->db->query("UPDATE users SET role = 'shopowner' WHERE role = 'seller'");
        $this->db->query("ALTER TABLE users MODIFY role ENUM('admin','shopowner','customer') NOT NULL DEFAULT 'customer'");
    }
}
