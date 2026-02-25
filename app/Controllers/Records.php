<?php

namespace App\Controllers;

use App\Models\RecordModel;
use CodeIgniter\Controller;

class Records extends Controller
{
    protected $recordModel;
    protected $session;

    public function __construct()
    {
        $this->recordModel = new RecordModel();
        $this->session = session();
        helper('text');
    }

    /**
     * Display all records with search functionality
     */
    public function index()
    {
        $search = $this->request->getGet('search');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Get records with pagination
        $result = $this->recordModel->getRecords($search, $perPage, $offset);
        $records = $result['records'];
        $totalRecords = $result['total'];

        // Calculate pagination
        $totalPages = ceil($totalRecords / $perPage);
        $pager = [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'totalRecords' => $totalRecords
        ];

        $data = [
            'title' => 'Records Management',
            'records' => $records,
            'search' => $search,
            'pager' => $pager,
            'user_role' => $this->session->get('user_role'),
            'user_name' => $this->session->get('user_name')
        ];

        return view('records/index', $data);
    }

    /**
     * Show create record form
     */
    public function create()
    {
        $data = [
            'title' => 'Create New Record',
            'validation' => \Config\Services::validation(),
            'user_role' => $this->session->get('user_role'),
            'user_name' => $this->session->get('user_name')
        ];

        return view('records/create', $data);
    }

    /**
     * Save new record
     */
    public function store()
    {
        // Validate input
        $validation = \Config\Services::validation();
        $validation->setRules([
            'title' => 'required|min_length[3]|max_length[255]|alpha_numeric_space',
            'description' => 'max_length[1000]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()
                           ->withInput()
                           ->with('validation', $validation);
        }

        // Prepare data
        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description')
        ];

        // Insert record
        if ($this->recordModel->createRecord($data)) {
            return redirect()->to('/records')
                           ->with('success', 'Record created successfully!');
        } else {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to create record. Please try again.');
        }
    }

    /**
     * Show edit form for specific record
     */
    public function edit($id)
    {
        // Get record
        $record = $this->recordModel->getRecord($id);

        if (!$record) {
            return redirect()->to('/records')
                           ->with('error', 'Record not found.');
        }

        $data = [
            'title' => 'Edit Record',
            'record' => $record,
            'validation' => \Config\Services::validation(),
            'user_role' => $this->session->get('user_role'),
            'user_name' => $this->session->get('user_name')
        ];

        return view('records/edit', $data);
    }

    /**
     * Update existing record
     */
    public function update($id)
    {
        // Check if record exists
        $record = $this->recordModel->getRecord($id);
        if (!$record) {
            return redirect()->to('/records')
                           ->with('error', 'Record not found.');
        }

        // Validate input
        $validation = \Config\Services::validation();
        $validation->setRules([
            'title' => 'required|min_length[3]|max_length[255]|alpha_numeric_space',
            'description' => 'max_length[1000]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()
                           ->withInput()
                           ->with('validation', $validation);
        }

        // Prepare data
        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description')
        ];

        // Update record
        if ($this->recordModel->updateRecord($id, $data)) {
            return redirect()->to('/records')
                           ->with('success', 'Record updated successfully!');
        } else {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to update record. Please try again.');
        }
    }

    /**
     * Delete record (admin only)
     */
    public function delete($id)
    {
        // Check if user is admin
        $userRole = $this->session->get('user_role');
        if ($userRole !== 'admin') {
            return redirect()->to('/records')
                           ->with('error', 'Access denied. Only administrators can delete records.');
        }

        // Check if record exists
        $record = $this->recordModel->getRecord($id);
        if (!$record) {
            return redirect()->to('/records')
                           ->with('error', 'Record not found.');
        }

        // Delete record
        if ($this->recordModel->deleteRecord($id)) {
            return redirect()->to('/records')
                           ->with('success', 'Record deleted successfully!');
        } else {
            return redirect()->to('/records')
                           ->with('error', 'Failed to delete record. Please try again.');
        }
    }

    /**
     * Search records (AJAX or GET)
     */
    public function search()
    {
        $search = $this->request->getGet('q');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Get records with search
        $result = $this->recordModel->getRecords($search, $perPage, $offset);
        $records = $result['records'];
        $totalRecords = $result['total'];

        // Calculate pagination
        $totalPages = ceil($totalRecords / $perPage);
        $pager = [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'totalRecords' => $totalRecords
        ];

        $data = [
            'title' => 'Search Results',
            'records' => $records,
            'search' => $search,
            'pager' => $pager,
            'user_role' => $this->session->get('user_role'),
            'user_name' => $this->session->get('user_name')
        ];

        return view('records/index', $data);
    }
}
