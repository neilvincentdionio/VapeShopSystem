<?php

namespace App\Controllers;

use App\Models\RecordModel;

class Records extends BaseController
{
    protected $recordModel;
    protected $session;
    private const RECORD_TYPES = ['purchase', 'inventory', 'expense', 'sales'];
    private const RECORD_STATUSES = ['pending', 'completed', 'cancelled', 'return_refund'];
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
        if (! $this->session->get('logged_in')) {
            return redirect()->to('/login');
        }

        $role = strtolower(trim((string) $this->session->get('user_role')));
        if ($role === '' || in_array($role, ['customer', 'rider'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }
        if (! $this->hasPermission('manage_records') && ! $this->hasPermission('manage_users')) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
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
            'Records module schema is outdated. Run `php spark migrate`.'
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
            'record_date',
            'reference_number',
            'title',
            'quantity',
            'unit_price',
            'payment_method',
            'payment_status',
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
            $query = $query->where('record_date >=', $fromDate);
        }
        if ($toDate !== '') {
            $query = $query->where('record_date <=', $toDate);
        }

        $records = $query
            ->orderBy('record_date', strtoupper($dateSort))
            ->orderBy('id', $dateSort === 'asc' ? 'ASC' : 'DESC')
            ->paginate(10);
        $pager = $recordsModel->pager;

        $recordTypes = array_map(
            static fn ($type) => ['record_type' => $type],
            self::RECORD_TYPES
        );

        $sortParams = array_filter([
            'q' => $search,
            'record_type' => $recordType,
            'status' => $status,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'date_sort' => $dateSort === 'asc' ? 'desc' : 'asc',
        ], static fn ($value) => $value !== '');

        return view('admin/records/index', [
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

        return view('admin/records/form', [
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
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Please login first.',
            ]);
        }

        $role = strtolower(trim((string) $this->session->get('user_role')));
        if ($role === '' || in_array($role, ['customer', 'rider'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }
        if (! $this->hasPermission('manage_records') && ! $this->hasPermission('manage_users')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Access denied.',
            ]);
        }

        if (! $this->hasExpectedRecordsSchema()) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Records schema is outdated.',
            ]);
        }

        $record = $this->recordModel->find((int) $id);
        if (! $record) {
            return $this->response->setStatusCode(404)->setJSON([
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

        if (! $this->recordModel->insert($data)) {
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
        if (! $record) {
            return redirect()->to('/records')->with('error', 'Record not found.');
        }

        return view('admin/records/form', [
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
        if (! $record) {
            return redirect()->to('/records')->with('error', 'Record not found.');
        }

        $data = $this->sanitizePayload();
        if (! $this->recordModel->update($recordId, $data)) {
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
        if (! $record) {
            return redirect()->to('/records')->with('error', 'Record not found.');
        }

        $this->recordModel->delete((int) $id);

        return redirect()->to('/records')->with('success', 'Record deleted successfully.');
    }

    public function exportCSV()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }
        $schemaCheck = $this->ensureSchema();
        if ($schemaCheck !== true) {
            return $schemaCheck;
        }

        // Get the same filters as the index method
        $search = trim((string) $this->request->getGet('q'));
        $recordType = strtolower(trim((string) $this->request->getGet('record_type')));
        $status = trim((string) $this->request->getGet('status'));
        $fromDate = $this->normalizeFilterDate(trim((string) $this->request->getGet('from_date')));
        $toDate = $this->normalizeFilterDate(trim((string) $this->request->getGet('to_date')));

        $search = strip_tags($search);
        $recordType = strip_tags($recordType);
        $status = strtolower(strip_tags($status));

        if (! in_array($recordType, self::RECORD_TYPES, true)) {
            $recordType = '';
        }
        if (! in_array($status, self::RECORD_STATUSES, true)) {
            $status = '';
        }
        if ($fromDate !== '' && $toDate !== '' && $fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

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

        if ($status !== '') {
            $query = $query->where('status', $status);
        }

        if ($fromDate !== '') {
            $query = $query->where('record_date >=', $fromDate);
        }
        if ($toDate !== '') {
            $query = $query->where('record_date <=', $toDate);
        }

        $records = $query->orderBy('record_date', 'DESC')->orderBy('id', 'DESC')->findAll();

        // Generate CSV
        $filename = 'records_' . date('Y-m-d_H-i-s') . '.csv';
        
        $csvContent = "ID,Record Type,Date,Reference Number,Title,Description,Quantity,Unit Price,Total Amount,Payment Method,Payment Status,Status,Notes,Created At\n";
        
        foreach ($records as $record) {
            $totalAmount = $record['quantity'] * $record['unit_price'];
            $csvContent .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%.2f,%.2f,%s,%s,%s,%s,%s\n",
                $record['id'],
                ucfirst($record['record_type']),
                $record['record_date'],
                $record['reference_number'],
                $this->escapeCSV($record['title']),
                $this->escapeCSV($record['description'] ?? ''),
                $record['quantity'],
                $record['unit_price'],
                $totalAmount,
                ucfirst($record['payment_method'] ?? ''),
                ucfirst($record['payment_status']),
                $this->formatRecordStatusLabel((string) ($record['status'] ?? 'pending')),
                $this->escapeCSV($record['notes'] ?? ''),
                $record['created_at']
            );
        }

        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers and output
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: ' . strlen($csvContent));
        
        echo $csvContent;
        exit;
    }

    public function exportExcel()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }
        $schemaCheck = $this->ensureSchema();
        if ($schemaCheck !== true) {
            return $schemaCheck;
        }

        // Get the same filters as the index method
        $search = trim((string) $this->request->getGet('q'));
        $recordType = strtolower(trim((string) $this->request->getGet('record_type')));
        $status = trim((string) $this->request->getGet('status'));
        $fromDate = $this->normalizeFilterDate(trim((string) $this->request->getGet('from_date')));
        $toDate = $this->normalizeFilterDate(trim((string) $this->request->getGet('to_date')));

        $search = strip_tags($search);
        $recordType = strip_tags($recordType);
        $status = strtolower(strip_tags($status));

        if (! in_array($recordType, self::RECORD_TYPES, true)) {
            $recordType = '';
        }
        if (! in_array($status, self::RECORD_STATUSES, true)) {
            $status = '';
        }
        if ($fromDate !== '' && $toDate !== '' && $fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

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

        if ($status !== '') {
            $query = $query->where('status', $status);
        }

        if ($fromDate !== '') {
            $query = $query->where('record_date >=', $fromDate);
        }
        if ($toDate !== '') {
            $query = $query->where('record_date <=', $toDate);
        }

        $records = $query->orderBy('record_date', 'DESC')->orderBy('id', 'DESC')->findAll();

        // Generate Excel-compatible CSV (tab-separated)
        $filename = 'records_' . date('Y-m-d_H-i-s') . '.xls';
        
        $excelContent = "ID\tRecord Type\tDate\tReference Number\tTitle\tDescription\tQuantity\tUnit Price\tTotal Amount\tPayment Method\tPayment Status\tStatus\tNotes\tCreated At\n";
        
        foreach ($records as $record) {
            $totalAmount = $record['quantity'] * $record['unit_price'];
            $excelContent .= sprintf(
                "%s\t%s\t%s\t%s\t%s\t%s\t%s\t%.2f\t%.2f\t%s\t%s\t%s\t%s\t%s\n",
                $record['id'],
                ucfirst($record['record_type']),
                $record['record_date'],
                $record['reference_number'],
                $record['title'],
                $record['description'] ?? '',
                $record['quantity'],
                $record['unit_price'],
                $totalAmount,
                ucfirst($record['payment_method'] ?? ''),
                ucfirst($record['payment_status']),
                $this->formatRecordStatusLabel((string) ($record['status'] ?? 'pending')),
                $record['notes'] ?? '',
                $record['created_at']
            );
        }

        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers and output
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: ' . strlen($excelContent));
        
        echo $excelContent;
        exit;
    }

    private function escapeCSV($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        // Escape quotes and wrap in quotes if contains comma, quote, or newline
        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            $value = str_replace('"', '""', $value);
            return '"' . $value . '"';
        }
        return $value;
    }

    public function printView()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }
        $schemaCheck = $this->ensureSchema();
        if ($schemaCheck !== true) {
            return $schemaCheck;
        }

        // Get the same filters as the index method
        $search = trim((string) $this->request->getGet('q'));
        $recordType = strtolower(trim((string) $this->request->getGet('record_type')));
        $status = trim((string) $this->request->getGet('status'));
        $fromDate = $this->normalizeFilterDate(trim((string) $this->request->getGet('from_date')));
        $toDate = $this->normalizeFilterDate(trim((string) $this->request->getGet('to_date')));

        $search = strip_tags($search);
        $recordType = strip_tags($recordType);
        $status = strtolower(strip_tags($status));

        if (! in_array($recordType, self::RECORD_TYPES, true)) {
            $recordType = '';
        }
        if (! in_array($status, self::RECORD_STATUSES, true)) {
            $status = '';
        }
        if ($fromDate !== '' && $toDate !== '' && $fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

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

        if ($status !== '') {
            $query = $query->where('status', $status);
        }

        if ($fromDate !== '') {
            $query = $query->where('record_date >=', $fromDate);
        }
        if ($toDate !== '') {
            $query = $query->where('record_date <=', $toDate);
        }

        $records = $query->orderBy('record_date', 'DESC')->orderBy('id', 'DESC')->findAll();

        return view('admin/records/print', [
            'records' => $records,
            'search' => $search,
            'record_type' => $recordType,
            'status' => $status,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'generated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function generatePDF()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) {
            return $authCheck;
        }
        $schemaCheck = $this->ensureSchema();
        if ($schemaCheck !== true) {
            return $schemaCheck;
        }

        // Get the same filters as the index method
        $search = trim((string) $this->request->getGet('q'));
        $recordType = strtolower(trim((string) $this->request->getGet('record_type')));
        $status = trim((string) $this->request->getGet('status'));
        $fromDate = $this->normalizeFilterDate(trim((string) $this->request->getGet('from_date')));
        $toDate = $this->normalizeFilterDate(trim((string) $this->request->getGet('to_date')));

        $search = strip_tags($search);
        $recordType = strip_tags($recordType);
        $status = strtolower(strip_tags($status));

        if (! in_array($recordType, self::RECORD_TYPES, true)) {
            $recordType = '';
        }
        if (! in_array($status, self::RECORD_STATUSES, true)) {
            $status = '';
        }
        if ($fromDate !== '' && $toDate !== '' && $fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

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

        if ($status !== '') {
            $query = $query->where('status', $status);
        }

        if ($fromDate !== '') {
            $query = $query->where('record_date >=', $fromDate);
        }
        if ($toDate !== '') {
            $query = $query->where('record_date <=', $toDate);
        }

        $records = $query->orderBy('record_date', 'DESC')->orderBy('id', 'DESC')->findAll();

        // Generate HTML for PDF
        $html = $this->generatePDFHTML($records, $search, $recordType, $status, $fromDate, $toDate);

        // Generate simple PDF using HTML (browser-based approach)
        // For a more robust solution, you would use a library like TCPDF or DomPDF
        $filename = 'records_' . date('Y-m-d_H-i-s') . '.html';
        
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers for HTML file that can be printed to PDF
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: ' . strlen($html));
        
        echo $html;
        exit;
    }

    private function generatePDFHTML($records, $search, $recordType, $status, $fromDate, $toDate): string
    {
        $filterInfo = [];
        if ($search !== '') $filterInfo[] = 'Search: ' . $search;
        if ($recordType !== '') $filterInfo[] = 'Type: ' . ucfirst($recordType);
        if ($status !== '') $filterInfo[] = 'Status: ' . ucfirst($status);
        if ($fromDate !== '') $filterInfo[] = 'From: ' . $fromDate;
        if ($toDate !== '') $filterInfo[] = 'To: ' . $toDate;

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Records Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; color: #333; }
        .report-info { text-align: center; margin-bottom: 20px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <h1>Records Report</h1>
    <div class="report-info">
        <p>Generated: ' . date('Y-m-d H:i:s') . '</p>';
        
        if (!empty($filterInfo)) {
            $html .= '<p>Filters: ' . implode(' | ', $filterInfo) . '</p>';
        }
        
        $html .= '<p>Total Records: ' . count($records) . '</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Date</th>
                <th>Reference</th>
                <th>Title</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($records as $record) {
            $totalAmount = $record['quantity'] * $record['unit_price'];
            $html .= '<tr>
                <td>' . $record['id'] . '</td>
                <td>' . ucfirst($record['record_type']) . '</td>
                <td>' . $record['record_date'] . '</td>
                <td>' . htmlspecialchars($record['reference_number']) . '</td>
                <td>' . htmlspecialchars($record['title']) . '</td>
                <td>' . $record['quantity'] . '</td>
                <td>₱' . number_format($record['unit_price'], 2) . '</td>
                <td>₱' . number_format($totalAmount, 2) . '</td>
                <td>' . ucfirst($record['payment_method'] ?? '') . '</td>
                <td>' . $this->formatRecordStatusLabel((string) ($record['status'] ?? 'pending')) . '</td>
            </tr>';
        }
        
        $grandTotal = array_sum(array_map(function($r) { return $r['quantity'] * $r['unit_price']; }, $records));
        
        $html .= '</tbody>
        <tfoot>
            <tr>
                <td colspan="7" style="text-align: right; font-weight: bold;">Grand Total:</td>
                <td colspan="3" style="font-weight: bold;">₱' . number_format($grandTotal, 2) . '</td>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>Quick Puff Vape Shop System - Records Report</p>
        <p class="no-print">Use Ctrl+P to save as PDF</p>
    </div>
</body>
</html>';

        return $html;
    }

    private function sanitizePayload(): array
    {
        $recordDate = $this->normalizeRecordDate(trim(strip_tags((string) $this->request->getPost('date'))));
        $recordType = strtolower(trim(strip_tags((string) $this->request->getPost('record_type'))));
        if (! in_array($recordType, self::RECORD_TYPES, true)) {
            $recordType = 'expense';
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
        if ($status === 'completed') {
            $paymentStatus = 'paid';
        }

        return [
            'record_type' => $recordType,
            'record_date' => $recordDate,
            'reference_number' => trim(strip_tags((string) $this->request->getPost('reference_number'))),
            'title' => trim(strip_tags((string) $this->request->getPost('title'))),
            'description' => trim(strip_tags((string) $this->request->getPost('description'))),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
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

    private function formatRecordStatusLabel(string $status): string
    {
        $labels = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'return_refund' => 'Return/Refund',
        ];

        return $labels[$status] ?? ucfirst($status);
    }
}
