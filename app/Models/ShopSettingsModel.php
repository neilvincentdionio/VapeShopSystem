<?php

namespace App\Models;

use CodeIgniter\Model;

class ShopSettingsModel extends Model
{
    protected $table = 'shop_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'shop_name',
        'shop_address',
        'shop_latitude',
        'shop_longitude',
        'shop_phone',
        'updated_by',
    ];

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        $this->ensureSchema();

        $row = $this->find(1);
        if ($row) {
            return $row;
        }

        $defaults = $this->defaultSettings();
        $defaults['id'] = 1;
        $this->insert($defaults);

        return $this->find(1) ?? $defaults;
    }

    /**
     * @return array{lat: float, lng: float, address: string, name: string, phone: string}
     */
    public function getStoreLocation(): array
    {
        $settings = $this->getSettings();

        return [
            'lat'     => (float) ($settings['shop_latitude'] ?? 6.1352000),
            'lng'     => (float) ($settings['shop_longitude'] ?? 125.2179000),
            'address' => trim((string) ($settings['shop_address'] ?? 'Bula, General Santos City, South Cotabato, Philippines')),
            'name'    => trim((string) ($settings['shop_name'] ?? 'Quick Puff Vape Shop')),
            'phone'   => trim((string) ($settings['shop_phone'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function saveSettings(array $data, ?int $updatedBy = null): bool
    {
        $payload = [
            'shop_name'      => trim((string) ($data['shop_name'] ?? '')),
            'shop_address'   => trim((string) ($data['shop_address'] ?? '')),
            'shop_latitude'  => $data['shop_latitude'] ?? null,
            'shop_longitude' => $data['shop_longitude'] ?? null,
            'shop_phone'     => trim((string) ($data['shop_phone'] ?? '')),
            'updated_by'     => $updatedBy,
        ];

        if ($this->find(1)) {
            return $this->update(1, $payload);
        }

        $payload['id'] = 1;

        return (bool) $this->insert($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultSettings(): array
    {
        return [
            'shop_name'      => 'Quick Puff Vape Shop',
            'shop_address'   => 'Bula, General Santos City, South Cotabato, Philippines',
            'shop_latitude'  => 6.1352000,
            'shop_longitude' => 125.2179000,
            'shop_phone'     => null,
            'updated_by'     => null,
        ];
    }

    private function ensureSchema(): void
    {
        $db = \Config\Database::connect();
        if ($db->tableExists($this->table)) {
            return;
        }

        $rbac = new \App\Libraries\RbacService();
        if ($rbac->tablesAvailable()) {
            $rbac->migrateSchemaColumns();
        }
    }
}
