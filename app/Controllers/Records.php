<?php

namespace App\Controllers;

use App\Models\RecordModel;

class Records extends BaseController
{
    protected $recordModel;
    protected $session;
    private const ALLOWED_ROLES = ['admin'];
    private const RECORD_TYPES = ['sales', 'purchase', 'inventory', 'expense'];
    private const RECORD_STATUSES = ['pending', 'completed', 'cancelled'];
    private const PAYMENT_METHODS = ['cash', 'card', 'gcash', 'bank_transfer'];
    private const PAYMENT_STATUSES = ['paid', 'partial', 'unpaid'];
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
            'Records module schema is outdated. Run `php spark migrate` then `php spark db:seed RecordSeeder`.'
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
            'date',
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
        $recordType = strtolower(trim((string) $this->request->getGet('record_type')));
        $status = trim((string) $this->request->getGet('status'));
        $fromDate = $this->normalizeFilterDate(trim((string) $this->request->getGet('from_date')));
        $toDate = $this->normalizeFilterDate(trim((string) $this->request->getGet('to_date')));
        $dateSort = strtolower(trim((string) $this->request->getGet('date_sort')));

        $search = strip_tags($search);
        $recordType = strip_tags($recordType);
        $status = strtolower(strip_tags($status));
        if (! in_array($dateSort, ['asc', 'desc'], true)) {
            $dateSort = 'desc';
        }

        if (! in_array($recordType, self::RECORD_TYPES, true)) {
            $recordType = '';
        }
        if (! in_array($status, self::RECORD_STATUSES, true)) {
            $status = '';
        }
        if ($fromDate !== '' && $toDate !== '' && $fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        $recordsModel = new RecordModel();
        $query = $recordsModel;

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

        if ($status !== '') {
            $query = $query->where('status', $status);
        }

        if ($fromDate !== '') {
            $query = $query->where('date >=', $fromDate);
        }
        if ($toDate !== '') {
            $query = $query->where('date <=', $toDate);
        }

        $records = $query
            ->orderBy('date', strtoupper($dateSort))
            ->orderBy('id', $dateSort === 'asc' ? 'ASC' : 'DESC')
            ->paginate(10);
        $pager = $recordsModel->pager;
        $recordTypes = (new RecordModel())
            ->select('record_type')
            ->groupBy('record_type')
            ->orderBy('record_type', 'ASC')
            ->findAll();

        $sortParams = array_filter([
            'q' => $search,
            'record_type' => $recordType,
            'status' => $status,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'date_sort' => $dateSort === 'asc' ? 'desc' : 'asc',
        ], static fn ($value) => $value !== '');

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
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'date_sort' => $dateSort,
            'date_sort_label' => $dateSort === 'asc' ? 'Date (Asc)' : 'Date (Desc)',
            'date_sort_url' => site_url('records') . '?' . http_build_query($sortParams),
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

    public function show($id)
    {
        if (! $this->session->get('logged_in')) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Please login first.',
                ]);
        }

        if (! in_array((string) $this->session->get('user_role'), self::ALLOWED_ROLES, true)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'Access denied.',
                ]);
        }

        if (! $this->hasExpectedRecordsSchema()) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Records schema is outdated.',
                ]);
        }

        $record = $this->recordModel->find((int) $id);
        if (! $record) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'success' => false,
                    'message' => 'Record not found.',
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'record' => $record,
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
        $dateInput = trim(strip_tags((string) $this->request->getPost('date')));
        if ($dateInput === '') {
            $dateInput = trim(strip_tags((string) $this->request->getPost('record_date')));
        }
        $date = $this->normalizeRecordDate($dateInput);

        $recordType = strtolower(trim(strip_tags((string) $this->request->getPost('record_type'))));
        if (! in_array($recordType, self::RECORD_TYPES, true)) {
            $recordType = 'sales';
        }

        $quantityValue = str_replace(',', '', trim(strip_tags((string) $this->request->getPost('quantity'))));
        $quantity = is_numeric($quantityValue) ? (int) $quantityValue : 0;
        $quantity = max(0, $quantity);

        $unitPriceValue = trim(strip_tags((string) $this->request->getPost('unit_price')));
        if (substr_count($unitPriceValue, ',') === 1 && strpos($unitPriceValue, '.') === false) {
            $unitPriceValue = str_replace(',', '.', $unitPriceValue);
        } else {
            $unitPriceValue = str_replace(',', '', $unitPriceValue);
        }
        $unitPrice = is_numeric($unitPriceValue) ? (float) $unitPriceValue : 0.0;
        $unitPrice = max(0, $unitPrice);

        $paymentMethod = strtolower(trim(strip_tags((string) $this->request->getPost('payment_method'))));
        if (! in_array($paymentMethod, self::PAYMENT_METHODS, true)) {
            $paymentMethod = null;
        }

        $paymentStatus = strtolower(trim(strip_tags((string) $this->request->getPost('payment_status'))));
        if (! in_array($paymentStatus, self::PAYMENT_STATUSES, true)) {
            $paymentStatus = 'unpaid';
        }

        $status = strtolower(trim(strip_tags((string) $this->request->getPost('status'))));
        if (! in_array($status, self::RECORD_STATUSES, true)) {
            $status = 'pending';
        }

        return [
            'record_type' => $recordType,
            'date' => $date,
            'reference_number' => trim(strip_tags((string) $this->request->getPost('reference_number'))),
            'title' => trim(strip_tags((string) $this->request->getPost('title'))),
            'description' => trim(strip_tags((string) $this->request->getPost('description'))),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => round($quantity * $unitPrice, 2),
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'record_date' => $date,
            'status' => $status,
            'notes' => trim(strip_tags((string) $this->request->getPost('notes'))),
        ];
    }

    private function normalizeRecordDate(string $value): string
    {
        if ($value === '') {
            return date('Y-m-d');
        }

        $supportedFormats = ['Y-m-d', 'm/d/Y', 'n/j/Y', 'm-d-Y', 'n-j-Y'];
        foreach ($supportedFormats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return $value;
    }

    private function normalizeFilterDate(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $normalized = $this->normalizeRecordDate($value);
        $date = \DateTime::createFromFormat('Y-m-d', $normalized);

        return ($date && $date->format('Y-m-d') === $normalized) ? $normalized : '';
    }

}
