<?php

namespace App\Models;

use CodeIgniter\Model;

class CartItemModel extends Model
{
    protected $table = 'cart_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['cart_id', 'product_id', 'variant_id', 'quantity'];
    protected $useTimestamps = true;
}
