<?php

namespace App\Controllers;

use App\Libraries\SalesReportExporter;
use App\Models\SalesReportModel;

class SalesReports extends BaseController
{
    protected SalesReportModel $reportModel;
    protected SalesReportExporter $exporter;

    public function __construct()
    {
        $this->reportModel = new SalesReportModel();
        $this->exporter = new SalesReportExporter();
    }

    public function index()
    {
        try {
            $dateFrom = $this->request->getGet('date_from');
            $dateTo = $this->request->getGet('date_to');
            $report = $this->reportModel->buildReport(
                is_string($dateFrom) ? $dateFrom : null,
                is_string($dateTo) ? $dateTo : null
            );

            return view('admin/reports/index', [
                'page_title' => 'Sales Reports',
                'user_name' => session()->get('user_name') ?? 'Administrator',
                'report' => $report,
                'filters' => [
                    'date_from' => $report['date_from'],
                    'date_to' => $report['date_to'],
                ],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Sales Reports Error: ' . $e->getMessage());
            return redirect()->to('/dashboard')->with('error', 'Error loading sales reports: ' . $e->getMessage());
        }
    }

    public function exportPdf()
    {
        try {
            $report = $this->buildReportFromRequest();
            $shopName = (string) (session()->get('user_shop_name') ?: 'QuickPuff Vape Shop');
            $pdf = $this->exporter->toPdf($report, $shopName);
            $filename = 'sales_report_' . $report['date_from'] . '_to_' . $report['date_to'] . '.pdf';

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
                ->setBody($pdf);
        } catch (\Exception $e) {
            log_message('error', 'PDF Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        try {
            $report = $this->buildReportFromRequest();
            $shopName = (string) (session()->get('user_shop_name') ?: 'QuickPuff Vape Shop');
            $xml = $this->exporter->toExcel($report, $shopName);
            $filename = 'sales_report_' . $report['date_from'] . '_to_' . $report['date_to'] . '.xls';

            return $this->response
                ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
                ->setBody($xml);
        } catch (\Exception $e) {
            log_message('error', 'Excel Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating Excel: ' . $e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReportFromRequest(): array
    {
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');

        return $this->reportModel->buildReport(
            is_string($dateFrom) ? $dateFrom : null,
            is_string($dateTo) ? $dateTo : null
        );
    }
}
