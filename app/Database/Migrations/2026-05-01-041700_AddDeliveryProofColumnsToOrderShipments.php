<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryProofColumnsToOrderShipments extends Migration
{
    public function up()
    {
        $this->forge->addColumn('order_shipments', [
            'delivery_proof_image' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Filename of delivery proof image'
            ],
            'delivery_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Rider notes about delivery'
            ],
            'delivery_proof_submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Timestamp when delivery proof was submitted'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('order_shipments', [
            'delivery_proof_image',
            'delivery_notes', 
            'delivery_proof_submitted_at'
        ]);
    }
}
