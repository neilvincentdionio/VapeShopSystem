<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'VapeHub X Pod Kit',
                'category' => 'Pods',
                'description' => 'Compact pod system with smooth draw and advanced coil technology. Perfect for beginners and experienced vapers alike.',
                'price' => 1250.00,
                'stock' => 25,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Salt Mint E-Liquid',
                'category' => 'E-Liquid',
                'description' => 'Refreshing mint flavor with smooth nicotine salt formulation. 30ml bottle with 50mg nicotine strength.',
                'price' => 320.00,
                'stock' => 50,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Replacement Coil Pack',
                'category' => 'Accessories',
                'description' => 'Long-life mesh coils (pack of 3). Compatible with most pod systems. Provides excellent flavor and vapor production.',
                'price' => 450.00,
                'stock' => 8,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Portable Charger Case',
                'category' => 'Accessories',
                'description' => 'Fast charging pocket case with 2000mAh battery. USB-C compatible with LED indicators. Keep your device powered all day.',
                'price' => 680.00,
                'stock' => 15,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Mango Tango E-Liquid',
                'category' => 'E-Liquid',
                'description' => 'Tropical mango and tangerine blend with sweet and tangy notes. 30ml bottle with 35mg nicotine salt.',
                'price' => 350.00,
                'stock' => 30,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Pro Vapor Mod',
                'category' => 'Devices',
                'description' => 'Advanced box mod with temperature control and variable wattage. 200W maximum output with large color display.',
                'price' => 2800.00,
                'stock' => 5,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Menthol Chill Disposable',
                'category' => 'Disposable',
                'description' => 'Ice-cold menthol disposable vape with smooth draw and long-lasting flavor.',
                'price' => 450.00,
                'stock' => 0,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Ceramic Tank',
                'category' => 'Accessories',
                'description' => '2ml capacity ceramic tank with top-fill design. Leak-proof construction with adjustable airflow.',
                'price' => 550.00,
                'stock' => 12,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '18650 Battery Pack',
                'category' => 'Accessories',
                'description' => 'High-drain 3000mAh batteries (pack of 2). 35A continuous discharge with protection circuit.',
                'price' => 750.00,
                'stock' => 20,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Berry Blast E-Liquid',
                'category' => 'E-Liquid',
                'description' => 'Mixed berry explosion with strawberry, blueberry, and raspberry. Sweet and tangy with smooth finish.',
                'price' => 330.00,
                'stock' => 18,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('products')->insertBatch($products);
    }
}
