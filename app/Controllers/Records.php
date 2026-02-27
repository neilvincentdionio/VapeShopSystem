<?php

namespace App\Controllers;

use App\Models\RecordModel;

class Records extends BaseController
{
    protected $recordModel;
    protected $session;
    private const ALLOWED_ROLES = ['admin'];
    private ?bool $hasExpectedSchema = null;

    public function __construct()
    {
        $this->recordModel = new RecordModel();
        $this->session = session();
    }

    private function checkAuth()
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/login');
        }

        if (!in_array((string) $this->session->get('user_role'), self::ALLOWED_ROLES, true)) {
            return redirect()->to('/dashboard')
                ->with('error', 'Access denied.');
        }

        return true;
    }

    private function ensureSchema()
    {
        if ($this->hasExpectedRecordsSchema()) {
            return true;
        }

        return redirect()->to('/dashboard')->with(
            'error',
            'Records module schema is outdated. Run `php spark migrate:fresh` then re-seed.'
        );
    }

    private function hasExpectedRecordsSchema(): bool
    {
        if ($this->hasExpectedSchema !== null) {
            return $this->hasExpectedSchema;
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('records')) {
            $this->hasExpectedSchema = false;
            return false;
        }

        $requiredFields = [
            'record_type',
            'reference_number',
            'title',
            'quantity',
            'unit_price',
            'total_amount',
            'payment_method',
            'payment_status',
            'record_date',
            'status',
            'created_by',
        ];

        foreach ($requiredFields as $field) {
            if (! $db->fieldExists($field, 'records')) {
                $this->hasExpectedSchema = false;
                return false;
            }
        }

        $this->hasExpectedSchema = true;
        return true;
    }

    private function hasRecordsField(string $field): bool
    {
        $db = \Config\Database::connect();
        return $db->tableExists('records') && $db->fieldExists($field, 'records');
    }

    public function index()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }
        $schemaCheck = $this->ensureSchema();
        if ($schemaCheck !== true) {
            return $schemaCheck;
        }

        $search = trim((string) $this->request->getGet('q'));
        $recordType = trim((string) $this->request->getGet('record_type'));
        $status = trim((string) $this->request->getGet('status'));

        $search = strip_tags($search);
        $recordType = strip_tags($recordType);
        $status = strip_tags($status);

        $query = $this->recordModel;

        if ($search !== '') {
            $query = $query->groupStart()
                ->like('reference_number', $search)
                ->orLike('title', $search)
                ->orLike('description', $search)
                ->orLike('payment_method', $search)
                ->groupEnd();
        }

        if ($recordType !== '') {
            $query = $query->where('record_type', $recordType);
        }

        if (in_array($status, ['pending', 'completed', 'cancelled'], true)) {
            $query = $query->where('status', $status);
        }

        $records = $query->orderBy('id', 'DESC')->paginate(10);
        $pager = $this->recordModel->pager;
        $recordTypes = $this->recordModel
            ->select('record_type')
            ->groupBy('record_type')
            ->orderBy('record_type', 'ASC')
            ->findAll();

        return view('records/index', [
            'page_title' => 'Records Module',
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'user_shop_name' => $this->session->get('user_shop_name'),
            'records' => $records,
            'pager' => $pager,
            'search' => $search,
            'record_type' => $recordType,
            'status' => $status,
            'record_types' => $recordTypes,
        ]);
    }

    public function create()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }
        $schemaCheck = $this->ensureSchema();
        if ($schemaCheck !== true) {
            return $schemaCheck;
        }

        return view('records/form', [
            'page_title' => 'Add Record',
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'user_shop_name' => $this->session->get('user_shop_name'),
            'record' => null,
            'errors' => session()->getFlashdata('errors') ?? [],
            'is_edit' => false,
        ]);
    }

    public function store()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }
        $schemaCheck = $this->ensureSchema();
        if ($schemaCheck !== true) {
            return $schemaCheck;
        }

        $data = $this->sanitizePayload();
        $data['created_by'] = (int) $this->session->get('user_id');
        $shopName = trim((string) $this->session->get('user_shop_name'));
        if ($shopName !== '' && $this->hasRecordsField('shop_name')) {
            $data['shop_name'] = $shopName;
        }

        if (!$this->recordModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->recordModel->errors());
        }

        return redirect()->to('/records')->with('success', 'Record added successfully.');
    }

    public function edit($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }
        $schemaCheck = $this->ensureSchema();
        if ($schemaCheck !== true) {
            return $schemaCheck;
        }

        $record = $this->recordModel->find((int) $id);
        if (!$record) {
            return redirect()->to('/records')->with('error', 'Record not found.');
        }

        return view('records/form', [
            'page_title' => 'Edit Record',
            'user_name' => $this->session->get('user_name'),
            'user_email' => $this->session->get('user_email'),
            'user_role' => $this->session->get('user_role'),
            'user_shop_name' => $this->session->get('user_shop_name'),
            'record' => $record,
            'errors' => session()->getFlashdata('errors') ?? [],
            'is_edit' => true,
        ]);
    }

    public function update($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }
        $schemaCheck = $this->ensureSchema();
        if ($schemaCheck !== true) {
            return $schemaCheck;
        }

        $recordId = (int) $id;
        $record = $this->recordModel->find($recordId);
        if (!$record) {
            return redirect()->to('/records')->with('error', 'Record not found.');
        }

        $data = $this->sanitizePayload();
        $shopName = trim((string) $this->session->get('user_shop_name'));
        if ($shopName !== '' && $this->hasRecordsField('shop_name')) {
            $data['shop_name'] = $shopName;
        }

        if (!$this->recordModel->update($recordId, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->recordModel->errors());
        }

        return redirect()->to('/records')->with('success', 'Record updated successfully.');
    }

    public function delete($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }
        $schemaCheck = $this->ensureSchema();
        if ($schemaCheck !== true) {
            return $schemaCheck;
        }

        $record = $this->recordModel->find((int) $id);
        if (!$record) {
            return redirect()->to('/records')->with('error', 'Record not found.');
        }

        $this->recordModel->delete((int) $id);

        return redirect()->to('/records')->with('success', 'Record deleted successfully.');
    }

    private function sanitizePayload()
    {
        $quantity = (int) trim(strip_tags((string) $this->request->getPost('quantity')));
        $unitPrice = (float) trim(strip_tags((string) $this->request->getPost('unit_price')));
        $totalAmount = $quantity * $unitPrice;

        return [
            'record_type' => trim(strip_tags((string) $this->request->getPost('record_type'))),
            'reference_number' => trim(strip_tags((string) $this->request->getPost('reference_number'))),
            'title' => trim(strip_tags((string) $this->request->getPost('title'))),
            'description' => trim(strip_tags((string) $this->request->getPost('description'))),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'payment_method' => trim(strip_tags((string) $this->request->getPost('payment_method'))),
            'payment_status' => trim(strip_tags((string) $this->request->getPost('payment_status'))),
            'record_date' => trim(strip_tags((string) $this->request->getPost('record_date'))),
            'status' => trim(strip_tags((string) $this->request->getPost('status'))),
            'notes' => trim(strip_tags((string) $this->request->getPost('notes'))),
        ];
    }

}
