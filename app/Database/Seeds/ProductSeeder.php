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
                'is_active' => 1,
                'puffs' => 12000,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Red Pulp (Watermelon)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Yellow Summer (Mango)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Rainbow Punch (Kool-Aid)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Very Baguio (Strawberry)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Green Tokyo (Matcha)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Bacteria Monster (Yakult)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Trouble Purple (Grapes)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Very More (Mixedberries)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Sweet Forest (Green Apple)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Yellow Green (Lemon Lime)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Black Wave (Black Currant)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Sticky Worms (Gummy Worms)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Tangy Plump (Nerdz)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                    ['name' => 'Round Melo (Melon)', 'price' => 400.00, 'stock_qty' => 10, 'puffs' => 12000],
                ],
            ],
            [
                'name' => 'BLACK ELITE V1',
                'category' => 'Pods',
                'brand' => 'BLACK',
                'image_url' => null,
                'price' => 400.00,
                'is_active' => 1,
                'puffs' => 8000,
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
                'brand' => 'BLACK?',
                'image_url' => null,
                'price' => 500.00,
                'is_active' => 1,
                'puffs' => 25000,
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
                'price' => 500.00,
                'is_active' => 1,
                'puffs' => 30000,
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
                'price' => 500.00,
                'is_active' => 1,
                'puffs' => 25000,
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
                'price' => 500.00,
                'is_active' => 1,
                'puffs' => 20000,
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
                'price' => 395.00,
                'is_active' => 1,
                'puffs' => 15000,
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
                'price' => 270.00,
                'is_active' => 1,
                'puffs' => 20000,
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
                'price' => 800.00,
                'stock_qty' => 35,
                'is_active' => 1,
                'flavor' => null,
                'puffs' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'BLACK V1 BATTERY',
                'category' => 'Device',
                'brand' => 'BLACK',
                'image_url' => null,
                'price' => 300.00,
                'stock_qty' => 75,
                'is_active' => 1,
                'flavor' => null,
                'puffs' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'X-VAPE SLIMBAR DEVICE',
                'category' => 'Device',
                'brand' => 'X-VAPE',
                'image_url' => null,
                'price' => 395.00,
                'stock_qty' => 60,
                'is_active' => 1,
                'flavor' => null,
                'puffs' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],

            // E-liquid Category - multiple flavors
            [
                'name' => 'POD FORMULA',
                'category' => 'E-liquid',
                'brand' => 'CODED',
                'image_url' => null,
                'price' => 180.00,
                'is_active' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Tobacco', 'stock_qty' => 25],
                    ['name' => 'Vanilla', 'stock_qty' => 30],
                    ['name' => 'Menthol', 'stock_qty' => 20],
                    ['name' => 'Strawberry', 'stock_qty' => 35],
                    ['name' => 'Coffee', 'stock_qty' => 28],
                ],
            ],
            // Disposable Category - multiple flavors
            [
                'name' => 'STORM',
                'category' => 'Disposable',
                'brand' => 'STORM',
                'image_url' => null,
                'price' => 450.00,
                'is_active' => 1,
                'puffs' => 15000,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'flavors' => [
                    ['name' => 'Watermelon', 'stock_qty' => 10],
                    ['name' => 'Strawberry Ice Cream', 'stock_qty' => 10],
                    ['name' => 'Grape Jelly', 'stock_qty' => 10],
                    ['name' => 'Yakult', 'stock_qty' => 10],
                    ['name' => 'Banana', 'stock_qty' => 10],
                    ['name' => 'Matcha', 'stock_qty' => 10],
                    ['name' => 'Blueberry', 'stock_qty' => 10],
                    ['name' => 'Green Mango', 'stock_qty' => 10],
                ],
            ],
            [
                'name' => 'BL?CK',
                'category' => 'Disposable',
                'brand' => 'BL?ACK',
                'image_url' => null,
                'price' => 380.00,
                'is_active' => 1,
                'puffs' => 30000,
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
                'price' => 380.00,
                'is_active' => 1,
                'puffs' => 30000,
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
                'price' => $seedProduct['price'] ?? 0,
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

        return [
            'price' => (float) ($variantSeed['price'] ?? $seedProduct['price'] ?? 0),
            'stock_qty' => (int) ($variantSeed['stock_qty'] ?? $seedProduct['stock_qty'] ?? 0),
            'flavor' => $this->normalizeNullableString($flavor),
            'puffs' => $this->normalizeNullableInt($variantSeed['puffs'] ?? $seedProduct['puffs'] ?? null),
        ];
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
            'price' => $seedProduct['price'] ?? 0,
            'puffs' => $seedProduct['puffs'] ?? null,
            'stock_qty' => $seedProduct['stock_qty'] ?? 0,
        ];

        $productData = [
            'name' => trim((string) ($seedProduct['name'] ?? '')),
            'category' => trim((string) ($seedProduct['category'] ?? '')),
            'brand' => $this->normalizeNullableString($seedProduct['brand'] ?? null),
            'flavor' => null,
            'price' => (float) ($firstVariant['price'] ?? 0),
            'puffs' => $this->normalizeNullableInt($firstVariant['puffs'] ?? null),
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
                'price' => $seedProduct['price'] ?? 0,
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
