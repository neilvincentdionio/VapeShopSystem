<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'       => 'Christian Bermudez',
                'email'      => 'christian@gmail.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Jhondell Rañeses',
                'email'      => 'jhondell@gmail.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Neil Vincent Dionio',
                'email'      => 'neil@gmail.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Mike Cidric Santillan',
                'email'      => 'mike@gmail.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Jed Isaac Valenzuela',
                'email'      => 'jed@gmail.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Mark Owen Lu',
                'email'      => 'lu@gmail.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}