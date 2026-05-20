<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $imageMap = $this->loadImageMap();
        $timestamp = date('Y-m-d H:i:s');
        $seedProducts = $this->getSeedProducts($timestamp);

        foreach ($seedProducts as &$product) {
            $seedName = trim((string) ($product['name'] ?? ''));
            if ($seedName !== '' && isset($imageMap[$seedName]) && trim((string) $imageMap[$seedName]) !== '') {
                $product['image_url'] = trim((string) $imageMap[$seedName]);
            }

            $this->applySeedProductPricing($product);
            $this->applySeedComplianceDefaults($product);
        }
        unset($product);

        foreach ($seedProducts as $seedProduct) {
            $this->insertSeedProduct($seedProduct, $timestamp);
        }
    }

    private function getSeedProducts(string $timestamp): array
    {
        return [
            // PODS Category - multiple flavors
            [
                'name' => 'BLACK ELITE V2',
                'category' => 'Pods',
                'brand' => 'BLACK',
                'image_url' => null,
                'selling_price' => 400.00,
                'unit_price' => 350.00,
                'is_active' => 1,
                'puffs' => 12000,
                'nicotine_level' => '3mg',
                'expires_at' => '2027-12-31',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Red Pulp (Watermelon)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Yellow Summer (Mango)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Rainbow Punch (Kool-Aid)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Very Baguio (Strawberry)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Green Tokyo (Matcha)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Bacteria Monster (Yakult)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Trouble Purple (Grapes)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Very More (Mixedberries)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Sweet Forest (Green Apple)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Yellow Green (Lemon Lime)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Black Wave (Black Currant)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Sticky Worms (Gummy Worms)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Tangy Plump (Nerdz)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Round Melo (Melon)', 'selling_price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                ],
            ],
            [
                'name' => 'BLACK ELITE V1',
                'category' => 'Pods',
                'brand' => 'BLACK',
                'image_url' => null,
                'selling_price' => 400.00,
                'unit_price' => 350.00,
                'is_active' => 1,
                'puffs' => 8000,
                'nicotine_level' => '3mg',
                'expires_at' => '2027-11-30',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Red Pulp (Watermelon)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Trouble Purple (Grapes)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Sparkle Squeeze (Lemon Cola)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Ice Monkey (Banana)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Very Baguio (Strawberry)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Black Wave (Black Currant)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Cheer Blast (Lychee)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Pitch Perfect (Peach)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Blue Freeze (Blueberry)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Very More (Mixed Berries)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Beer Sparkle (Rootbeer)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Red Cannon (Bazooka)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Green Tokyo (Matcha)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Bacteria Monster (Yakult)', 'stock_qty' => 10, 'puffs' => 8000],
                    ['name' => 'Fresh Menthol (Mint)', 'stock_qty' => 10, 'puffs' => 8000],
                ],
            ],

            [
                'name' => 'BLACK? V2',
                'category' => 'Pods',
                'brand' => 'BLACK',
                'image_url' => null,
                'selling_price' => 500.00,
                'unit_price' => 450.00,
                'is_active' => 1,
                'puffs' => 25000,
                'nicotine_level' => '5mg',
                'expires_at' => '2028-01-15',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'YKT (Yakult)', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Red Fresh (Watermelon)', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Mix Gem (Mixed Berries)', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Purple Gem (Grapes)', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Cheer Blast (Lychee)', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Midnight Gem (Blackcurrant)', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Kong Ice (Banana Ice)', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Garden Fresh (Strawberry)', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Bomb (Bubblegum)', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Yellow Fresh (Mango Ice)', 'stock_qty' => 10, 'puffs' => 25000],
                ],
            ],

            [
                'name' => 'CRYSM ELITE',
                'category' => 'Pods',
                'brand' => 'CRYSM',
                'image_url' => null,
                'selling_price' => 500.00,
                'unit_price' => 450.00,
                'is_active' => 1,
                'puffs' => 30000,
                'nicotine_level' => '3mg',
                'expires_at' => '2027-10-31',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Melon Milk Shake', 'stock_qty' => 10, 'puffs' => 30000],
                    ['name' => 'Matcha', 'stock_qty' => 10, 'puffs' => 30000],
                    ['name' => 'Avocado Milk Shake', 'stock_qty' => 10, 'puffs' => 30000],
                    ['name' => 'Strawberry Ice Cream', 'stock_qty' => 10, 'puffs' => 30000],
                    ['name' => 'Mix Berries', 'stock_qty' => 10, 'puffs' => 30000],
                    ['name' => 'Black Currant Grapes', 'stock_qty' => 10, 'puffs' => 30000],
                    ['name' => 'Taro Ice Cream', 'stock_qty' => 10, 'puffs' => 30000],
                    ['name' => 'Watermelon Ice', 'stock_qty' => 10, 'puffs' => 30000],
                    ['name' => 'Yakult', 'stock_qty' => 10, 'puffs' => 30000],
                    ['name' => 'Bubblegum', 'stock_qty' => 10, 'puffs' => 30000],
                ],
            ],

            [
                'name' => 'VAPOR ZERO',
                'category' => 'Pods',
                'brand' => 'VAPOR',
                'image_url' => null,
                'selling_price' => 500.00,
                'unit_price' => 450.00,
                'is_active' => 1,
                'puffs' => 25000,
                'nicotine_level' => '0mg',
                'expires_at' => '2027-12-15',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Purple Mamba (Black Currant Grape)', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Fresh Red (Watermelon)', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Triple Mango', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Matcha', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Sea Salt Lemon', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Avocado', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Mint', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Lychee', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Ube', 'stock_qty' => 10, 'puffs' => 25000],
                    ['name' => 'Creme Brulee', 'stock_qty' => 10, 'puffs' => 25000],
                ],
            ],

            [
                'name' => 'UOTOFO',
                'category' => 'Pods',
                'brand' => 'UOTOFO',
                'image_url' => null,
                'selling_price' => 500.00,
                'unit_price' => 450.00,
                'is_active' => 1,
                'puffs' => 20000,
                'nicotine_level' => '5mg',
                'expires_at' => '2027-11-15',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Big Red (Watermelon)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Big Heart (Strawberry)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Big Lush (Lychee)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Big Frost (Mint)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Big Sparkle (Lemon Cola)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Big Blue (Bubble Gum)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Big Shirota (Yakult)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Big Purple (Grapes)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Big Rizz (Mix Berries)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Big Black (Black Currant)', 'stock_qty' => 10, 'puffs' => 20000],
                ],
            ],
            [
                'name' => 'XVAPE SLIMBAR',
                'category' => 'Pods',
                'brand' => 'XVAPE',
                'image_url' => null,
                'selling_price' => 395.00,
                'unit_price' => 345.00,
                'is_active' => 1,
                'puffs' => 15000,
                'nicotine_level' => '5mg',
                'expires_at' => '2027-10-20',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Strawberry Ice', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Blueberry Ice', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Mixed Berries', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Yakult', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Mango Ice', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Watermelon Bubblegum', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Grapes', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Taro Ice Cream', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Tobacco', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Blackcurrant Ice', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Watermelon Ice', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Lychee Ice', 'stock_qty' => 10, 'puffs' => 15000],
                ],
            ],
            [
                'name' => 'KALO V2',
                'category' => 'Pods',
                'brand' => 'KALO',
                'image_url' => null,
                'selling_price' => 270.00,
                'unit_price' => 220.00,
                'is_active' => 1,
                'puffs' => 20000,
                'nicotine_level' => '3mg',
                'expires_at' => '2027-09-30',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Red (Watermelon)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Heart (Strawberry)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Lush (Lychee)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Frost (Mint)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Sparkle (Lemon Cola)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Blue (Bubble Gum)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Shirota (Yakult)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Purple (Grapes)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Rizz (Mix Berries)', 'stock_qty' => 10, 'puffs' => 20000],
                    ['name' => 'Black (Black Currant)', 'stock_qty' => 10, 'puffs' => 20000],
                ],
            ],

            // Device Category - no flavors
            [
                'name' => 'MINICAN',
                'category' => 'Device',
                'brand' => 'ASPIRE',
                'image_url' => null,
                'selling_price' => 800.00,
                'unit_price' => 750.00,
                'stock_qty' => 35,
                'is_active' => 1,
                'flavor' => null,
                'puffs' => null,
                'device_type' => 'pod_mod',
                'battery_capacity' => 700,
                'wattage_range' => '10-15W',
                'charging_port' => 'USB-C',
                'compatibility' => 'Aspire Minican pods',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'BLACK V1 BATTERY',
                'category' => 'Device',
                'brand' => 'BLACK',
                'image_url' => null,
                'selling_price' => 300.00,
                'unit_price' => 250.00,
                'stock_qty' => 75,
                'is_active' => 1,
                'flavor' => null,
                'puffs' => null,
                'device_type' => 'battery',
                'battery_capacity' => 500,
                'charging_port' => 'USB-C',
                'compatibility' => 'BLACK V1 / BLACK ELITE pods',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'X-VAPE SLIMBAR DEVICE',
                'category' => 'Device',
                'brand' => 'X-VAPE',
                'image_url' => null,
                'selling_price' => 395.00,
                'unit_price' => 345.00,
                'stock_qty' => 60,
                'is_active' => 1,
                'flavor' => null,
                'puffs' => null,
                'device_type' => 'pod_device',
                'battery_capacity' => 650,
                'charging_port' => 'USB-C',
                'compatibility' => 'XVAPE SLIMBAR pods',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],

            // E-liquid Category - multiple flavors (puffs column stores bottle volume in ml)
            [
                'name' => 'POD FORMULA',
                'category' => 'E-Liquid',
                'brand' => 'CODED',
                'image_url' => null,
                'selling_price' => 180.00,
                'unit_price' => 130.00,
                'is_active' => 1,
                'puffs' => 10,
                'nicotine_level' => '6mg',
                'expires_at' => '2027-06-30',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Tobacco', 'stock_qty' => 25, 'puffs' => 10],
                    ['name' => 'Vanilla', 'stock_qty' => 30, 'puffs' => 10],
                    ['name' => 'Menthol', 'stock_qty' => 20, 'puffs' => 10],
                    ['name' => 'Strawberry', 'stock_qty' => 35, 'puffs' => 10],
                    ['name' => 'Coffee', 'stock_qty' => 28, 'puffs' => 10],
                ],
            ],
            // Disposable Category - multiple flavors
            [
                'name' => 'STORM',
                'category' => 'Disposable',
                'brand' => 'STORM',
                'image_url' => null,
                'selling_price' => 450.00,
                'unit_price' => 400.00,
                'is_active' => 1,
                'puffs' => 15000,
                'battery_capacity' => 650,
                'eliquid_capacity' => 12,
                'nicotine_level' => '5mg',
                'expires_at' => '2027-09-30',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Watermelon', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Strawberry Ice Cream', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Grape Jelly', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Yakult', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Banana', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Matcha', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Blueberry', 'stock_qty' => 10, 'puffs' => 15000],
                    ['name' => 'Green Mango', 'stock_qty' => 10, 'puffs' => 15000],
                ],
            ],
            [
                'name' => 'BL?CK',
                'category' => 'Disposable',
                'brand' => 'BL?ACK',
                'image_url' => null,
                'selling_price' => 380.00,
                'unit_price' => 330.00,
                'is_active' => 1,
                'puffs' => 30000,
                'battery_capacity' => 900,
                'eliquid_capacity' => 18,
                'nicotine_level' => '5mg',
                'expires_at' => '2028-02-28',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Bubblegum', 'stock_qty' => 10],
                    ['name' => 'Yakult', 'stock_qty' => 10],
                    ['name' => 'Watermelon', 'stock_qty' => 10],
                    ['name' => 'Blackcurrant', 'stock_qty' => 10],
                    ['name' => 'Mango', 'stock_qty' => 10],
                    ['name' => 'Grapes', 'stock_qty' => 10],
                    ['name' => 'Lychee', 'stock_qty' => 10],
                    ['name' => 'Strawberry', 'stock_qty' => 10],
                    ['name' => 'Mix Berries', 'stock_qty' => 10],
                    ['name' => 'Banana Ice', 'stock_qty' => 10],
                ],
            ],
            [
                'name' => 'VI BAR',
                'category' => 'Disposable',
                'brand' => 'VI BAR',
                'image_url' => null,
                'selling_price' => 380.00,
                'unit_price' => 330.00,
                'is_active' => 1,
                'puffs' => 30000,
                'battery_capacity' => 850,
                'eliquid_capacity' => 16,
                'nicotine_level' => '3mg',
                'expires_at' => '2028-03-31',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Red Blast (Watermelon Ice)', 'stock_qty' => 10],
                    ['name' => 'Double Yellow (Double Mango)', 'stock_qty' => 10],
                    ['name' => 'Very Baguio (Strawberry Ice)', 'stock_qty' => 10],
                    ['name' => 'Green Tokyo (Matcha Ice)', 'stock_qty' => 10],
                    ['name' => 'Shirota (Yakult)', 'stock_qty' => 10],
                    ['name' => 'Trouble Purple (Grapes Ice)', 'stock_qty' => 10],
                    ['name' => 'Mixed Garden (Mixed Berries)', 'stock_qty' => 10],
                    ['name' => 'Crisp Green (Green Apple)', 'stock_qty' => 10],
                    ['name' => 'Black Ice (Black Currant)', 'stock_qty' => 10],
                    ['name' => 'Sour Neon (Gummy Bears)', 'stock_qty' => 10],
                    ['name' => 'Round Melo (Melon Ice)', 'stock_qty' => 10],
                    ['name' => 'Ice Sparkle (Iced Cola)', 'stock_qty' => 10],
                    ['name' => 'Bluebomb (Bubblegum Ice)', 'stock_qty' => 10],
                    ['name' => 'Puple Snow (Taro Ice Cream)', 'stock_qty' => 10],
                    ['name' => 'Pink Snow (Strawberry Ice Cream)', 'stock_qty' => 10],
                    ['name' => 'Cheesecake Supreme (Classic Cheesecake)', 'stock_qty' => 10],
                    ['name' => 'Milky Almond (Nougat)', 'stock_qty' => 10],
                    ['name' => 'Blueberry Cake (Blueberry Cheesecake)', 'stock_qty' => 10],
                    ['name' => 'Purple Yam Swirl (Ube Swirl)', 'stock_qty' => 10],
                    ['name' => 'Starbucks (Cappuccino Ice)', 'stock_qty' => 10],
                ],
            ],
        ];
    }

    private function expandSeedData(array $seedProducts, string $timestamp): array
    {
        $products = [];

        foreach ($seedProducts as $seedProduct) {
            foreach ($this->expandSeedProduct($seedProduct, $timestamp) as $product) {
                $products[] = $product;
            }
        }

        return $products;
    }

    private function expandSeedProduct(array $seedProduct, string $timestamp): array
    {
        $baseProduct = [
            'name' => trim((string) ($seedProduct['name'] ?? '')),
            'category' => trim((string) ($seedProduct['category'] ?? '')),
            'brand' => $this->normalizeNullableString($seedProduct['brand'] ?? null),
            'image_url' => $this->normalizeNullableString($seedProduct['image_url'] ?? null),
            'is_active' => array_key_exists('is_active', $seedProduct) ? (int) $seedProduct['is_active'] : 1,
            'created_at' => $seedProduct['created_at'] ?? $timestamp,
            'updated_at' => $seedProduct['updated_at'] ?? $timestamp,
        ];

        $flavors = $seedProduct['flavors'] ?? null;
        if (is_array($flavors) && $flavors !== []) {
            $products = [];

            foreach ($flavors as $flavorSeed) {
                $products[] = $baseProduct + $this->buildVariantData($seedProduct, $flavorSeed);
            }

            return $products;
        }

        return [
            $baseProduct + $this->buildVariantData($seedProduct, [
                'flavor' => $seedProduct['flavor'] ?? null,
                'selling_price' => $seedProduct['selling_price'] ?? 0,
                'stock_qty' => $seedProduct['stock_qty'] ?? 0,
                'puffs' => $seedProduct['puffs'] ?? null,
            ]),
        ];
    }

    private function buildVariantData(array $seedProduct, $variantSeed): array
    {
        if (!is_array($variantSeed)) {
            $variantSeed = ['flavor' => $variantSeed];
        }

        $flavor = $variantSeed['flavor'] ?? $variantSeed['name'] ?? null;

        $sellingPrice = (float) (
            $variantSeed['selling_price']
            ?? $variantSeed['price']
            ?? $seedProduct['selling_price']
            ?? $seedProduct['price']
            ?? 0
        );

        return [
            'price' => $sellingPrice,
            'selling_price' => $sellingPrice,
            'stock_qty' => (int) ($variantSeed['stock_qty'] ?? $seedProduct['stock_qty'] ?? 0),
            'flavor' => $this->normalizeNullableString($flavor),
            'puffs' => $this->normalizeNullableInt($variantSeed['puffs'] ?? $seedProduct['puffs'] ?? null),
            'expires_at' => null,
        ];
    }

    private function normalizeDeviceType($value): ?string
    {
        helper('product');

        return normalize_device_type($value);
    }

    private function normalizeExpirationDate($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string, mixed> $seedProduct
     */
    private function applySeedComplianceDefaults(array &$seedProduct): void
    {
        $category = strtolower(trim((string) ($seedProduct['category'] ?? '')));
        if (! $this->usesSeedComplianceFields($category)) {
            return;
        }

        $nicotineOptions = ['3mg', '5mg', '6mg', '0mg', '12mg'];
        $productKey = strtolower(trim((string) ($seedProduct['name'] ?? '')) . '|' . $category);
        $nicotineIndex = abs(crc32($productKey)) % count($nicotineOptions);

        if (trim((string) ($seedProduct['nicotine_level'] ?? '')) === '') {
            $seedProduct['nicotine_level'] = $this->resolveSeedNicotineLevel($seedProduct, $nicotineOptions[$nicotineIndex]);
        }

        $baseExpiry = $this->normalizeExpirationDate($seedProduct['expires_at'] ?? null)
            ?? $this->defaultProductExpirationDate($productKey);

        $seedProduct['expires_at'] = $baseExpiry;

        $flavors = $seedProduct['flavors'] ?? null;
        if (! is_array($flavors) || $flavors === []) {
            return;
        }

        foreach ($flavors as &$flavorSeed) {
            if (! is_array($flavorSeed)) {
                continue;
            }

            unset($flavorSeed['expires_at']);

            if (! isset($flavorSeed['puffs']) && isset($seedProduct['puffs'])) {
                $flavorSeed['puffs'] = $seedProduct['puffs'];
            }
        }
        unset($flavorSeed);

        $seedProduct['flavors'] = $flavors;
    }

    private function usesSeedComplianceFields(string $category): bool
    {
        return in_array($category, ['pods', 'disposable', 'e-liquid', 'eliquid'], true)
            || str_contains($category, 'liquid');
    }

    /**
     * @param array<string, mixed> $seedProduct
     */
    private function resolveSeedNicotineLevel(array $seedProduct, string $fallback): string
    {
        $name = strtolower((string) ($seedProduct['name'] ?? ''));
        $category = strtolower(trim((string) ($seedProduct['category'] ?? '')));

        if (str_contains($category, 'liquid') || $category === 'e-liquid') {
            if (str_contains($name, 'zero') || str_contains($name, '0mg')) {
                return '0mg';
            }

            return '6mg';
        }

        if (str_contains($name, 'zero') || str_contains($name, 'vapor zero')) {
            return '0mg';
        }

        if (str_contains($name, 'black?') || str_contains($name, 'uotofo') || str_contains($name, 'storm') || str_contains($name, 'bl?ck') || str_contains($name, 'vi bar')) {
            return '5mg';
        }

        if (str_contains($name, 'crysm') || str_contains($name, 'kalo') || str_contains($name, 'elite')) {
            return '3mg';
        }

        return $fallback;
    }

    private function defaultProductExpirationDate(string $productKey): string
    {
        $monthOffset = (abs(crc32($productKey)) % 18) + 6;

        return date('Y-m-d', strtotime('+' . $monthOffset . ' months'));
    }

    private function staggerFlavorExpirationDate(string $baseExpiry, int $flavorIndex): string
    {
        $timestamp = strtotime($baseExpiry . ' +' . ($flavorIndex * 14) . ' days');

        return $timestamp !== false ? date('Y-m-d', $timestamp) : $baseExpiry;
    }

    /**
     * Cost price is always PHP 50 below the selling price (minimum 0).
     */
    private function deriveUnitPriceFromSelling(float $sellingPrice): float
    {
        return round(max(0.0, $sellingPrice - 50.0), 2);
    }

    /**
     * @param array<string, mixed> $seedProduct
     */
    private function applySeedProductPricing(array &$seedProduct): void
    {
        $sellingPrice = (float) ($seedProduct['selling_price'] ?? $seedProduct['price'] ?? 0);

        if ($sellingPrice <= 0 && ! empty($seedProduct['flavors']) && is_array($seedProduct['flavors'])) {
            $firstFlavor = $seedProduct['flavors'][0];
            if (is_array($firstFlavor)) {
                $sellingPrice = (float) (
                    $firstFlavor['selling_price']
                    ?? $firstFlavor['price']
                    ?? $seedProduct['selling_price']
                    ?? $seedProduct['price']
                    ?? 0
                );
            }
        }

        if ($sellingPrice <= 0) {
            return;
        }

        $unitPrice = (float) ($seedProduct['unit_price'] ?? 0);
        if ($unitPrice <= 0) {
            $unitPrice = $this->deriveUnitPriceFromSelling($sellingPrice);
        }

        $seedProduct['selling_price'] = $sellingPrice;
        $seedProduct['price'] = $sellingPrice;
        $seedProduct['unit_price'] = $unitPrice;
    }

    private function normalizeNullableString($value): ?string
    {
        $normalized = trim((string) ($value ?? ''));
        return $normalized === '' ? null : $normalized;
    }

    private function normalizeNullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function insertSeedProduct(array $seedProduct, string $timestamp): void
    {
        $variants = $this->buildSeedVariants($seedProduct);
        $totalStock = array_sum(array_map(static fn ($variant) => (int) ($variant['stock_qty'] ?? 0), $variants));
        $firstVariant = $variants[0] ?? [
            'selling_price' => $seedProduct['selling_price'] ?? $seedProduct['price'] ?? 0,
            'puffs' => $seedProduct['puffs'] ?? null,
            'stock_qty' => $seedProduct['stock_qty'] ?? 0,
        ];

        $sellingPrice = (float) (
            $seedProduct['selling_price']
            ?? $firstVariant['selling_price']
            ?? $firstVariant['price']
            ?? 0
        );
        $unitPrice = (float) ($seedProduct['unit_price'] ?? 0);
        if ($unitPrice <= 0 && $sellingPrice > 0) {
            $unitPrice = $this->deriveUnitPriceFromSelling($sellingPrice);
        }

        helper('product');

        $productData = [
            'name' => trim((string) ($seedProduct['name'] ?? '')),
            'category' => normalize_product_category($seedProduct['category'] ?? ''),
            'brand' => $this->normalizeNullableString($seedProduct['brand'] ?? null),
            'flavor' => null,
            'unit_price' => $unitPrice,
            'selling_price' => $sellingPrice,
            'price' => $sellingPrice,
            'puffs' => $this->normalizeNullableInt($seedProduct['puffs'] ?? $firstVariant['puffs'] ?? null),
            'nicotine_level' => $this->normalizeNullableString($seedProduct['nicotine_level'] ?? null),
            'expires_at' => $this->normalizeExpirationDate($seedProduct['expires_at'] ?? null),
            'battery_capacity' => $this->normalizeNullableInt($seedProduct['battery_capacity'] ?? null),
            'eliquid_capacity' => $this->normalizeNullableInt($seedProduct['eliquid_capacity'] ?? null),
            'device_type' => $this->normalizeDeviceType($seedProduct['device_type'] ?? null),
            'wattage_range' => $this->normalizeNullableString($seedProduct['wattage_range'] ?? null),
            'charging_port' => $this->normalizeNullableString($seedProduct['charging_port'] ?? null),
            'compatibility' => $this->normalizeNullableString($seedProduct['compatibility'] ?? null),
            'image_url' => $this->normalizeNullableString($seedProduct['image_url'] ?? null),
            'stock_qty' => $totalStock,
            'is_active' => array_key_exists('is_active', $seedProduct) ? (int) $seedProduct['is_active'] : 1,
            'created_at' => $seedProduct['created_at'] ?? $timestamp,
            'updated_at' => $seedProduct['updated_at'] ?? $timestamp,
        ];

        $this->db->table('products')->insert($productData);
        $productId = (int) $this->db->insertID();

        if (! $this->db->tableExists('product_variants')) {
            return;
        }

        $variantRows = [];
        foreach ($variants as $variant) {
            $variantRows[] = [
                'product_id' => $productId,
                'flavor' => $this->normalizeNullableString($variant['flavor'] ?? null),
                'puffs' => $this->normalizeNullableInt($variant['puffs'] ?? null),
                'expires_at' => null,
                'price' => (float) ($variant['price'] ?? 0),
                'stock_qty' => (int) ($variant['stock_qty'] ?? 0),
                'is_active' => (int) ($seedProduct['is_active'] ?? 1),
                'created_at' => $seedProduct['created_at'] ?? $timestamp,
                'updated_at' => $seedProduct['updated_at'] ?? $timestamp,
            ];
        }

        if ($variantRows !== []) {
            $this->db->table('product_variants')->insertBatch($variantRows);
        }
    }

    private function buildSeedVariants(array $seedProduct): array
    {
        $flavors = $seedProduct['flavors'] ?? null;
        if (is_array($flavors) && $flavors !== []) {
            return array_map(fn ($flavorSeed) => $this->buildVariantData($seedProduct, $flavorSeed), $flavors);
        }

        return [
            $this->buildVariantData($seedProduct, [
                'flavor' => $seedProduct['flavor'] ?? null,
                'selling_price' => $seedProduct['selling_price'] ?? $seedProduct['price'] ?? 0,
                'stock_qty' => $seedProduct['stock_qty'] ?? 0,
                'puffs' => $seedProduct['puffs'] ?? null,
            ]),
        ];
    }

    private function loadImageMap(): array
    {
        $mapPath = WRITEPATH . 'product_image_seed_map.json';
        if (!is_file($mapPath)) {
            return [];
        }

        $raw = @file_get_contents($mapPath);
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $normalizedMap = [];
        $mapUpdated = false;

        foreach ($decoded as $name => $imageUrl) {
            $normalizedName = trim((string) $name);
            if ($normalizedName === '') {
                $mapUpdated = true;
                continue;
            }

            $normalizedImageUrl = trim((string) (normalize_product_image_path($imageUrl, true) ?? ''));
            if ($normalizedImageUrl === '') {
                $mapUpdated = true;
                continue;
            }

            $normalizedMap[$normalizedName] = $normalizedImageUrl;
            if ($normalizedImageUrl !== trim((string) $imageUrl)) {
                $mapUpdated = true;
            }
        }

        if ($mapUpdated) {
            @file_put_contents(
                $mapPath,
                json_encode($normalizedMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        return $normalizedMap;
    }
}
