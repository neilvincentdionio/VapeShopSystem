<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'name'       => 'Administrator',
                'email'      => 'admin@vapeshop.com',
                'password'   => password_hash('password', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Customer',
                'email'      => 'customer@vapeshop.com',
                'password'   => password_hash('password', PASSWORD_DEFAULT),
                'role'       => 'customer',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($data as $row) {
            $existing = $this->db->table('users')->where('email', $row['email'])->get()->getRowArray();

            if ($existing) {
                $this->db->table('users')->where('email', $row['email'])->update([
                    'name' => $row['name'],
                    'role' => $row['role'],
                    'is_active' => $row['is_active'],
                    'updated_at' => $now,
                ]);
                continue;
            }

            $this->db->table('users')->insert($row);
        }

        // Fix user roles - moved from FixUserRolesSeeder
        // Update users with empty or null roles to 'customer'
        $this->db->query("UPDATE users SET role = 'customer' WHERE role = '' OR role IS NULL");
        
        // Update any existing 'staff' roles to 'customer'
        $this->db->query("UPDATE users SET role = 'customer' WHERE role = 'staff'");
    }
}
