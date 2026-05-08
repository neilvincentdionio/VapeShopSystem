<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessagingTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'customer_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'assigned_admin_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'assigned_rider_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => false,
                'default'    => 'Customer Support',
            ],
            'support_mode' => [
                'type'    => "ENUM('bot','human')",
                'null'    => false,
                'default' => 'bot',
            ],
            'priority' => [
                'type'    => "ENUM('normal','urgent')",
                'null'    => false,
                'default' => 'normal',
            ],
            'status' => [
                'type'    => "ENUM('open','pending','resolved')",
                'null'    => false,
                'default' => 'open',
            ],
            'last_message_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'escalated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('customer_id');
        $this->forge->addKey('assigned_admin_id');
        $this->forge->addKey('order_id');
        $this->forge->addKey('assigned_rider_id');
        $this->forge->addKey('support_mode');
        $this->forge->addKey('status');
        $this->forge->addKey('last_message_at');
        $this->forge->addForeignKey('customer_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_admin_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('assigned_rider_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('message_conversations', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'conversation_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'sender_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'sender_role' => [
                'type'    => "ENUM('customer','chatbot','admin','rider')",
                'null'    => false,
                'default' => 'customer',
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'message_type' => [
                'type'    => "ENUM('text','system','bot')",
                'null'    => false,
                'default' => 'text',
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'read_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('conversation_id');
        $this->forge->addKey('sender_id');
        $this->forge->addKey('sender_role');
        $this->forge->addKey('is_read');
        $this->forge->addForeignKey('conversation_id', 'message_conversations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sender_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('conversation_messages', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'conversation_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'message' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('conversation_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('is_read');
        $this->forge->addForeignKey('conversation_id', 'message_conversations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('chat_notifications', true);
    }

    public function down()
    {
        $this->forge->dropTable('chat_notifications', true);
        $this->forge->dropTable('conversation_messages', true);
        $this->forge->dropTable('message_conversations', true);
    }
}
