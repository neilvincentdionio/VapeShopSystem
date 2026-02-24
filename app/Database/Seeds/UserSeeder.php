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
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Staff Member',
                'email'      => 'staff@vapeshop.com',
                'password'   => password_hash('password', PASSWORD_DEFAULT),
                'role'       => 'staff',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Christian Bermudez',
                'email'      => 'christian@vapeshop.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'staff',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Jhondell Rañeses',
                'email'      => 'jhondell@vapeshop.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'staff',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Neil Vincent Dionio',
                'email'      => 'neilvincentdionio@gmail.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Mike Cidric Santillan',
                'email'      => 'mike@vapeshop.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'staff',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Jed Isaac Valenzuela',
                'email'      => 'jed@vapeshop.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'staff',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Mark Owen Lu',
                'email'      => 'mark@vapeshop.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'staff',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}