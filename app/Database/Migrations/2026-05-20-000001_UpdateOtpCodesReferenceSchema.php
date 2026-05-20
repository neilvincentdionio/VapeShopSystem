<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateOtpCodesReferenceSchema extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('otp_codes')) {
            return;
        }

        $this->addMissingColumn('email', [
            'type'       => 'VARCHAR',
            'constraint' => 255,
            'null'       => true,
            'after'      => 'user_id',
        ]);

        $this->addMissingColumn('challenge_token_hash', [
            'type'       => 'VARCHAR',
            'constraint' => 64,
            'null'       => true,
            'after'      => 'otp_hash',
        ]);

        $this->addMissingColumn('expires_at', [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'expiry_time',
        ]);

        $this->addMissingColumn('max_attempts', [
            'type'       => 'TINYINT',
            'constraint' => 3,
            'unsigned'   => true,
            'null'       => false,
            'default'    => 3,
            'after'      => 'attempts',
        ]);

        $this->addMissingColumn('is_used', [
            'type'       => 'TINYINT',
            'constraint' => 1,
            'null'       => false,
            'default'    => 0,
            'after'      => 'max_attempts',
        ]);

        $this->addMissingColumn('used_at', [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'is_used',
        ]);

        $this->addMissingColumn('last_sent_at', [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'used_at',
        ]);

        $this->addMissingColumn('invalidated_at', [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'last_sent_at',
        ]);

        $this->addMissingColumn('updated_at', [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'created_at',
        ]);

        if ($this->db->fieldExists('attempts', 'otp_codes')) {
            $this->forge->modifyColumn('otp_codes', [
                'attempts' => [
                    'name'       => 'attempts',
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'null'       => false,
                    'default'    => 0,
                ],
            ]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('otp_codes')) {
            return;
        }

        foreach ([
            'updated_at',
            'invalidated_at',
            'last_sent_at',
            'used_at',
            'is_used',
            'max_attempts',
            'expires_at',
            'challenge_token_hash',
            'email',
        ] as $column) {
            if ($this->db->fieldExists($column, 'otp_codes')) {
                $this->forge->dropColumn('otp_codes', $column);
            }
        }
    }

    /**
     * @param array<string,mixed> $field
     */
    private function addMissingColumn(string $column, array $field): void
    {
        if ($this->db->fieldExists($column, 'otp_codes')) {
            return;
        }

        $this->forge->addColumn('otp_codes', [
            $column => $field,
        ]);
    }
}
