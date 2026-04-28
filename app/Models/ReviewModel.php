<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table = 'product_reviews';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'product_id',
        'order_id',
        'user_id',
        'rating',
        'flavor_rating',
        'hit_strength_rating',
        'review_title',
        'review_text',
        'verified_purchase',
        'status',
        'admin_notes',
        'helpful_count',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'product_id' => 'required|integer|greater_than[0]',
        'order_id' => 'required|integer|greater_than[0]',
        'user_id' => 'required|integer|greater_than[0]',
        'rating' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[5]',
        'flavor_rating' => 'integer|greater_than_equal_to[1]|less_than_equal_to[5]',
        'hit_strength_rating' => 'integer|greater_than_equal_to[1]|less_than_equal_to[5]',
        'review_title' => 'max_length[255]',
        'review_text' => 'max_length[2000]',
        'status' => 'required|in_list[pending,approved,rejected]',
    ];

    protected $validationMessages = [
        'rating' => [
            'required' => 'Overall rating is required',
            'greater_than_equal_to' => 'Rating must be at least 1',
            'less_than_equal_to' => 'Rating cannot be more than 5',
        ],
        'flavor_rating' => [
            'greater_than_equal_to' => 'Flavor rating must be at least 1',
            'less_than_equal_to' => 'Flavor rating cannot be more than 5',
        ],
        'hit_strength_rating' => [
            'greater_than_equal_to' => 'Hit strength rating must be at least 1',
            'less_than_equal_to' => 'Hit strength rating cannot be more than 5',
        ],
    ];

    /**
     * Get reviews for a specific product
     */
    public function getProductReviews($productId, $status = 'approved', $limit = null, $offset = 0)
    {
        $builder = $this->db->table('product_reviews pr')
            ->select('pr.*, u.name as user_name, u.email as user_email, o.reference_number')
            ->join('users u', 'u.id = pr.user_id')
            ->join('orders o', 'o.id = pr.order_id')
            ->where('pr.product_id', $productId)
            ->where('pr.status', $status)
            ->orderBy('pr.created_at', 'DESC');

        if ($limit !== null) {
            $builder->limit((int) $limit, (int) $offset);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get reviews count by status for a product
     */
    public function getProductReviewCount($productId, $status = 'approved')
    {
        try {
            return $this->where('product_id', $productId)
                       ->where('status', $status)
                       ->countAllResults();
        } catch (\Exception $e) {
            // Table doesn't exist, return 0
            return 0;
        }
    }

    /**
     * Get average ratings for a product
     */
    public function getProductAverageRatings($productId)
    {
        try {
            $builder = $this->db->table('product_reviews')
                ->select('
                    AVG(rating) as avg_rating,
                    AVG(flavor_rating) as avg_flavor_rating,
                    AVG(hit_strength_rating) as avg_hit_strength_rating,
                    COUNT(*) as total_reviews
                ')
                ->where('product_id', $productId)
                ->where('status', 'approved');

            $result = $builder->get()->getRowArray();
            
            return [
                'avg_rating' => round((float) ($result['avg_rating'] ?? 0), 1),
                'avg_flavor_rating' => round((float) ($result['avg_flavor_rating'] ?? 0), 1),
                'avg_hit_strength_rating' => round((float) ($result['avg_hit_strength_rating'] ?? 0), 1),
                'total_reviews' => (int) ($result['total_reviews'] ?? 0),
            ];
        } catch (\Exception $e) {
            // Table doesn't exist, return default values
            return [
                'avg_rating' => 0.0,
                'avg_flavor_rating' => 0.0,
                'avg_hit_strength_rating' => 0.0,
                'total_reviews' => 0,
            ];
        }
    }

    /**
     * Get rating distribution for a product
     */
    public function getProductRatingDistribution($productId)
    {
        $builder = $this->db->table('product_reviews')
            ->select('rating, COUNT(*) as count')
            ->where('product_id', $productId)
            ->where('status', 'approved')
            ->groupBy('rating')
            ->orderBy('rating', 'DESC');

        $results = $builder->get()->getResultArray();
        
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($results as $row) {
            $distribution[(int) $row['rating']] = (int) $row['count'];
        }
        
        return $distribution;
    }

    /**
     * Check if user can review a product (has purchased and not already reviewed)
     */
    public function canUserReviewProduct($userId, $productId)
    {
        // Check if user has purchased this product in a completed order
        $orderItem = $this->db->table('order_items oi')
            ->join('orders o', 'o.id = oi.order_id')
            ->where('oi.product_id', $productId)
            ->where('o.user_id', $userId)
            ->where('o.status', 'completed')
            ->get()
            ->getRowArray();

        if (!$orderItem) {
            return ['can_review' => false, 'reason' => 'You must purchase this product first'];
        }

        // Check if user has already reviewed this product
        $existingReview = $this->where('user_id', $userId)
                              ->where('product_id', $productId)
                              ->first();

        if ($existingReview) {
            return ['can_review' => false, 'reason' => 'You have already reviewed this product'];
        }

        return ['can_review' => true, 'order_id' => $orderItem['order_id']];
    }

    /**
     * Get user's review for a specific product
     */
    public function getUserProductReview($userId, $productId)
    {
        return $this->where('user_id', $userId)
                   ->where('product_id', $productId)
                   ->first();
    }

    /**
     * Get reviews for admin management
     */
    public function getReviewsForAdmin($status = null, $productId = null, $limit = 20, $offset = 0)
    {
        $builder = $this->db->table('product_reviews pr')
            ->select('pr.*, u.name as user_name, u.email as user_email, p.name as product_name, o.reference_number')
            ->join('users u', 'u.id = pr.user_id')
            ->join('products p', 'p.id = pr.product_id')
            ->join('orders o', 'o.id = pr.order_id')
            ->orderBy('pr.created_at', 'DESC');

        if ($status !== null) {
            $builder->where('pr.status', $status);
        }

        if ($productId !== null) {
            $builder->where('pr.product_id', $productId);
        }

        if ($limit !== null) {
            $builder->limit((int) $limit, (int) $offset);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get reviews for a specific order
     */
    public function getReviewsByOrder($orderId, $status = null)
    {
        $builder = $this->db->table('product_reviews pr')
            ->select('pr.*, u.name as user_name, u.email as user_email, p.name as product_name, o.reference_number')
            ->join('users u', 'u.id = pr.user_id')
            ->join('products p', 'p.id = pr.product_id')
            ->join('orders o', 'o.id = pr.order_id')
            ->where('pr.order_id', $orderId)
            ->orderBy('pr.created_at', 'DESC');

        if ($status !== null) {
            $builder->where('pr.status', $status);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get reviews count by status for admin
     */
    public function getReviewsCountByStatus($status = null, $productId = null)
    {
        $builder = $this->builder();

        if ($status !== null) {
            $builder->where('status', $status);
        }

        if ($productId !== null) {
            $builder->where('product_id', $productId);
        }

        return $builder->countAllResults();
    }

    /**
     * Approve a review
     */
    public function approveReview($reviewId, $adminNotes = null)
    {
        $data = ['status' => 'approved'];
        if ($adminNotes !== null) {
            $data['admin_notes'] = $adminNotes;
        }
        
        return $this->update($reviewId, $data);
    }

    /**
     * Reject a review
     */
    public function rejectReview($reviewId, $adminNotes = null)
    {
        $data = ['status' => 'rejected'];
        if ($adminNotes !== null) {
            $data['admin_notes'] = $adminNotes;
        }
        
        return $this->update($reviewId, $data);
    }

    /**
     * Mark review as helpful
     */
    public function markHelpful($reviewId)
    {
        return $this->db->table('product_reviews')
                       ->where('id', $reviewId)
                       ->set('helpful_count', 'helpful_count + 1', false)
                       ->update();
    }

    /**
     * Get recent reviews for dashboard
     */
    public function getRecentReviews($limit = 5)
    {
        return $this->db->table('product_reviews pr')
            ->select('pr.*, u.name as user_name, p.name as product_name')
            ->join('users u', 'u.id = pr.user_id')
            ->join('products p', 'p.id = pr.product_id')
            ->where('pr.status', 'approved')
            ->orderBy('pr.created_at', 'DESC')
            ->limit((int) $limit)
            ->get()
            ->getResultArray();
    }
}
