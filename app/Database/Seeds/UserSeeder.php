<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'       => 'Administrator',
                'email'      => 'admin@vapeshop.com',
                'password'   => password_hash('password', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'shop_name'  => null,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Staff Member',
                'email'      => 'customer@vapeshop.com',
                'password'   => password_hash('password', PASSWORD_DEFAULT),
                'role'       => 'customer',
                'shop_name'  => null,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Seller',
                'email'      => 'shopowner@vapeshop.com',
                'password'   => password_hash('password', PASSWORD_DEFAULT),
                'role'       => 'seller',
                'shop_name'  => 'Quick Puff VapeShop',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Christian Bermudez',
                'email'      => 'christian@vapeshop.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'customer',
                'shop_name'  => null,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Jhondell Rañeses',
                'email'      => 'jhondell@vapeshop.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'customer',
                'shop_name'  => null,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Neil Vincent Dionio',
                'email'      => 'neilvincentdionio@gmail.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'shop_name'  => null,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Mike Cidric Santillan',
                'email'      => 'mike@vapeshop.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'customer',
                'shop_name'  => null,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Jed Isaac Valenzuela',
                'email'      => 'jed@vapeshop.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'customer',
                'shop_name'  => null,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Mark Owen Lu',
                'email'      => 'mark@vapeshop.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'customer',
                'shop_name'  => null,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($data as $row) {
            $existing = $this->db->table('users')->where('email', $row['email'])->get()->getRowArray();

            if ($existing) {
                $this->db->table('users')->where('email', $row['email'])->update([
                    'name' => $row['name'],
                    'role' => $row['role'],
                    'shop_name' => $row['shop_name'],
                    'is_active' => $row['is_active'],
                ]);
                continue;
            }

            $this->db->table('users')->insert($row);
        }
    }
}
