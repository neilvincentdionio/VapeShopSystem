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
                'address_line' => '101 Cloud Mint Avenue, Barangay City Heights',
                'barangay' => 'City Heights',
                'city' => 'General Santos City',
                'province' => 'South Cotabato',
                'postal_code' => '9500',
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
                'address_line' => '22 Vapor Street, Barangay Lagao',
                'barangay' => 'Lagao',
                'city' => 'General Santos City',
                'province' => 'South Cotabato',
                'postal_code' => '9500',
                'country' => 'Philippines',
                'legal_age_confirmed' => 1,
                'approval_status' => 'approved',
                'verification_id_path' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Rider',
                'email' => 'rider@vapeshop.com',
                'password' => 'password',
                'role' => 'rider',
                'phone_number' => '+63 900 000 0003',
                'address_line' => '33 Delivery Lane, Barangay Dadiangas West',
                'barangay' => 'Dadiangas West',
                'city' => 'General Santos City',
                'province' => 'South Cotabato',
                'postal_code' => '9500',
                'country' => 'Philippines',
                'legal_age_confirmed' => 1,
                'approval_status' => 'approved',
                'verification_id_path' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Peter Parker',
                'email' => 'customer1@vapeshop.com',
                'password' => 'password',
                'role' => 'customer',
                'phone_number' => '+63 900 000 0004',
                'address_line' => '44 Web Street, Barangay Lagao',
                'barangay' => 'Lagao',
                'city' => 'General Santos City',
                'province' => 'South Cotabato',
                'postal_code' => '9500',
                'country' => 'Philippines',
                'legal_age_confirmed' => 1,
                'approval_status' => 'approved',
                'verification_id_path' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Tony Stark',
                'email' => 'customer2@vapeshop.com',
                'password' => 'password',
                'role' => 'customer',
                'phone_number' => '+63 900 000 0005',
                'address_line' => '55 Arc Reactor Road, Barangay City Heights',
                'barangay' => 'City Heights',
                'city' => 'General Santos City',
                'province' => 'South Cotabato',
                'postal_code' => '9500',
                'country' => 'Philippines',
                'legal_age_confirmed' => 1,
                'approval_status' => 'approved',
                'verification_id_path' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Steve Rogers',
                'email' => 'customer3@vapeshop.com',
                'password' => 'password',
                'role' => 'customer',
                'phone_number' => '+63 900 000 0006',
                'address_line' => '66 Shield Avenue, Barangay Dadiangas West',
                'barangay' => 'Dadiangas West',
                'city' => 'General Santos City',
                'province' => 'South Cotabato',
                'postal_code' => '9500',
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
                $userModel->update((int) $existing['id'], $row);
                continue;
            }

            $userModel->createUser($row);
        }

        $this->db->query("UPDATE users SET role = 'customer' WHERE role = '' OR role IS NULL");
        $this->db->query("UPDATE users SET role = 'customer' WHERE role = 'staff'");
    }
}
