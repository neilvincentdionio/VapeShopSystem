<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'product_id' => 1,
                'order_id' => 1,
                'user_id' => 3, // Christian Bermudez (customer)
                'rating' => 5,
                'flavor_rating' => 5,
                'hit_strength_rating' => 4,
                'review_title' => 'Excellent Product!',
                'review_text' => 'This vape product is amazing! Great flavor and smooth hit. Will definitely buy again.',
                'verified_purchase' => 1,
                'status' => 'approved',
                'admin_notes' => null,
                'helpful_count' => 3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'product_id' => 2,
                'order_id' => 1,
                'user_id' => 3, // Christian Bermudez (customer)
                'rating' => 4,
                'flavor_rating' => 4,
                'hit_strength_rating' => 5,
                'review_title' => 'Good Quality',
                'review_text' => 'Nice product with strong hit. Flavor could be better but overall satisfied.',
                'verified_purchase' => 1,
                'status' => 'pending',
                'admin_notes' => null,
                'helpful_count' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('product_reviews')->insertBatch($data);
    }
}
