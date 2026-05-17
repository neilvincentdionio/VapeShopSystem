<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table = 'product_reviews';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'review_text',
        'admin_reply',
        'replied_by',
        'replied_at',
        'status',
    ];

    public function getReviewForOrderProduct(int $orderId, int $productId, int $userId): ?array
    {
        $row = $this->where('order_id', $orderId)
            ->where('product_id', $productId)
            ->where('user_id', $userId)
            ->first();

        return $row ?: null;
    }

    public function getCustomerReviewsForOrders(array $orderIds, int $userId): array
    {
        $orderIds = array_values(array_filter(array_map('intval', $orderIds), static fn (int $id): bool => $id > 0));
        if ($orderIds === []) {
            return [];
        }

        $rows = $this->where('user_id', $userId)
            ->whereIn('order_id', $orderIds)
            ->findAll();

        $reviews = [];
        foreach ($rows as $row) {
            $key = (int) ($row['order_id'] ?? 0) . ':' . (int) ($row['product_id'] ?? 0);
            $reviews[$key] = $row;
        }

        return $reviews;
    }

    public function getProductReviewSummary(int $productId): array
    {
        $row = $this->builder()
            ->select('COUNT(*) AS total_reviews, COALESCE(AVG(rating), 0) AS average_rating', false)
            ->where('product_id', $productId)
            ->where('status', 'approved')
            ->get()
            ->getRowArray();

        return [
            'total_reviews' => (int) ($row['total_reviews'] ?? 0),
            'average_rating' => round((float) ($row['average_rating'] ?? 0), 1),
        ];
    }

    public function getProductReviewDataForProducts(array $productIds): array
    {
        $productIds = array_values(array_filter(array_unique(array_map('intval', $productIds)), static fn (int $id): bool => $id > 0));
        if ($productIds === []) {
            return [];
        }

        $summaryRows = $this->builder()
            ->select('product_id, COUNT(*) AS total_reviews, COALESCE(AVG(rating), 0) AS average_rating', false)
            ->whereIn('product_id', $productIds)
            ->where('status', 'approved')
            ->groupBy('product_id')
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($productIds as $productId) {
            $data[$productId] = [
                'summary' => [
                    'total_reviews' => 0,
                    'average_rating' => 0.0,
                ],
                'reviews' => [],
            ];
        }

        foreach ($summaryRows as $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            if (! isset($data[$productId])) {
                continue;
            }

            $data[$productId]['summary'] = [
                'total_reviews' => (int) ($row['total_reviews'] ?? 0),
                'average_rating' => round((float) ($row['average_rating'] ?? 0), 1),
            ];
        }

        $reviewRows = $this->db->table($this->table . ' pr')
            ->select('pr.product_id, pr.rating, pr.review_text, pr.admin_reply, pr.replied_at, pr.created_at, u.name AS user_name')
            ->join('users u', 'u.id = pr.user_id', 'left')
            ->whereIn('pr.product_id', $productIds)
            ->where('pr.status', 'approved')
            ->orderBy('pr.created_at', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($reviewRows as $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            if (! isset($data[$productId]) || count($data[$productId]['reviews']) >= 5) {
                continue;
            }

            $data[$productId]['reviews'][] = [
                'rating' => (int) ($row['rating'] ?? 0),
                'review_text' => (string) ($row['review_text'] ?? ''),
                'admin_reply' => (string) ($row['admin_reply'] ?? ''),
                'replied_at' => (string) ($row['replied_at'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'user_name' => (string) ($row['user_name'] ?? 'Customer'),
            ];
        }

        return $data;
    }

    public function getAdminReviewDataForProducts(array $productIds): array
    {
        $productIds = array_values(array_filter(array_unique(array_map('intval', $productIds)), static fn (int $id): bool => $id > 0));
        if ($productIds === []) {
            return [];
        }

        $rows = $this->db->table($this->table)
            ->select(
                'product_id, COUNT(*) AS total_reviews, COALESCE(AVG(rating), 0) AS average_rating, ' .
                'SUM(CASE WHEN admin_reply IS NULL OR admin_reply = "" THEN 1 ELSE 0 END) AS unreplied_reviews, ' .
                'MAX(created_at) AS latest_review_at',
                false
            )
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id')
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($productIds as $productId) {
            $data[$productId] = [
                'total_reviews' => 0,
                'average_rating' => 0.0,
                'unreplied_reviews' => 0,
                'latest_review_at' => null,
            ];
        }

        foreach ($rows as $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            if (! isset($data[$productId])) {
                continue;
            }

            $data[$productId] = [
                'total_reviews' => (int) ($row['total_reviews'] ?? 0),
                'average_rating' => round((float) ($row['average_rating'] ?? 0), 1),
                'unreplied_reviews' => (int) ($row['unreplied_reviews'] ?? 0),
                'latest_review_at' => $row['latest_review_at'] ?? null,
            ];
        }

        return $data;
    }

    public function getAdminReviewNotificationSummary(): array
    {
        $row = $this->db->table($this->table)
            ->select(
                'COUNT(*) AS total_reviews, ' .
                'SUM(CASE WHEN admin_reply IS NULL OR admin_reply = "" THEN 1 ELSE 0 END) AS unreplied_reviews, ' .
                'MAX(created_at) AS latest_review_at',
                false
            )
            ->get()
            ->getRowArray();

        return [
            'total_reviews' => (int) ($row['total_reviews'] ?? 0),
            'unreplied_reviews' => (int) ($row['unreplied_reviews'] ?? 0),
            'latest_review_at' => $row['latest_review_at'] ?? null,
        ];
    }

    public function getReviewsForProduct(int $productId, ?string $status = null): array
    {
        $builder = $this->db->table($this->table . ' pr')
            ->select('pr.*, p.name AS product_name, u.name AS user_name')
            ->join('products p', 'p.id = pr.product_id', 'left')
            ->join('users u', 'u.id = pr.user_id', 'left')
            ->where('pr.product_id', $productId)
            ->orderBy('pr.created_at', 'DESC');

        if ($status !== null) {
            $builder->where('pr.status', $status);
        }

        return $builder->get()->getResultArray();
    }

    public function getRecentProductReviews(int $limit = 5): array
    {
        return $this->db->table($this->table . ' pr')
            ->select('pr.*, p.name AS product_name, u.name AS user_name, NULL AS flavor_rating, NULL AS hit_strength_rating', false)
            ->join('products p', 'p.id = pr.product_id', 'left')
            ->join('users u', 'u.id = pr.user_id', 'left')
            ->orderBy('pr.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResult();
    }
}
