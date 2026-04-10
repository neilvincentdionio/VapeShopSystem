<?php

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();

        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@vapeshop.com',
                'password' => 'password',
                'role' => 'admin',
                'shop_name' => 'Quick Puff Vape Shop',
                'phone_number' => '+63 900 000 0001',
                'address_line' => '101 Cloud Mint Avenue',
                'barangay' => 'San Isidro',
                'city' => 'Manila',
                'province' => 'Metro Manila',
                'postal_code' => '1000',
                'country' => 'Philippines',
                'legal_age_confirmed' => 1,
                'approval_status' => 'approved',
                'verification_id_path' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Customer',
                'email' => 'customer@vapeshop.com',
                'password' => 'password',
                'role' => 'customer',
                'phone_number' => '+63 900 000 0002',
                'address_line' => '22 Vapor Street',
                'barangay' => 'Bagong Pag-asa',
                'city' => 'Quezon City',
                'province' => 'Metro Manila',
                'postal_code' => '1100',
                'country' => 'Philippines',
                'legal_age_confirmed' => 1,
                'approval_status' => 'approved',
                'verification_id_path' => null,
                'is_active' => 1,
            ],
        ];

        foreach ($users as $row) {
            $existing = $userModel->findUserByEmail($row['email']);

            if ($existing) {
                $updateData = $row;
                $updateData['password'] = password_hash((string) $row['password'], PASSWORD_DEFAULT);
                $userModel->update((int) $existing['id'], $updateData);
                continue;
            }

            $userModel->createUser($row);
        }

        $this->db->query("UPDATE users SET role = 'customer' WHERE role = '' OR role IS NULL");
        $this->db->query("UPDATE users SET role = 'customer' WHERE role = 'staff'");
    }
}
