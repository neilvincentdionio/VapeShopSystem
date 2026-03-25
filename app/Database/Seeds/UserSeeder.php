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
                'phone_number' => '+63 900 000 0001',
                'address_line' => '101 Cloud Mint Avenue',
                'city' => 'Manila',
                'province' => 'Metro Manila',
                'postal_code' => '1000',
                'legal_age_confirmed' => 1,
                'approval_status' => 'approved',
                'verification_id_path' => null,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Customer',
                'email'      => 'customer@vapeshop.com',
                'password'   => password_hash('password', PASSWORD_DEFAULT),
                'role'       => 'customer',
                'phone_number' => '+63 900 000 0002',
                'address_line' => '22 Vapor Street',
                'city' => 'Quezon City',
                'province' => 'Metro Manila',
                'postal_code' => '1100',
                'legal_age_confirmed' => 1,
                'approval_status' => 'approved',
                'verification_id_path' => null,
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
                    'phone_number' => $row['phone_number'],
                    'address_line' => $row['address_line'],
                    'city' => $row['city'],
                    'province' => $row['province'],
                    'postal_code' => $row['postal_code'],
                    'legal_age_confirmed' => $row['legal_age_confirmed'],
                    'approval_status' => $row['approval_status'],
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
