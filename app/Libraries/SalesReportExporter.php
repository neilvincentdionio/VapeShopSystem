<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

class SalesReportExporter
{
    /**
     * @param array<string, mixed> $report
     */
    public function toPdf(array $report, string $shopName = 'QuickPuff Vape Shop'): string
    {
        $html = view('admin/reports/pdf', [
            'report' => $report,
            'shop_name' => $shopName,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Excel-compatible XML spreadsheet (.xls).
     *
     * @param array<string, mixed> $report
     */
    public function toExcel(array $report, string $shopName = 'QuickPuff Vape Shop'): string
    {
        $money = static fn ($v) => number_format((float) $v, 2, '.', '');

        $summary = $report['summary'] ?? [];
        $daily = $report['daily'] ?? [];
        $monthly = $report['monthly'] ?? [];
        $products = $report['top_products'] ?? [];
        $orders = $report['orders'] ?? [];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ';
        $xml .= 'xmlns:o="urn:schemas-microsoft-com:office:office" ';
        $xml .= 'xmlns:x="urn:schemas-microsoft-com:office:excel" ';
        $xml .= 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";

        $xml .= $this->excelStyles();

        $xml .= '<Worksheet ss:Name="Summary"><Table>';
        $xml .= $this->excelRow(['Sales Report — ' . $shopName]);
        $xml .= $this->excelRow(['Period', ($report['date_from'] ?? '') . ' to ' . ($report['date_to'] ?? '')]);
        $xml .= $this->excelRow(['Generated', $report['generated_at'] ?? date('Y-m-d H:i:s')]);
        $xml .= $this->excelRow([]);
        $xml .= $this->excelRow(['Metric', 'Value']);
        $xml .= $this->excelRow(['Total Revenue (PHP)', $money($summary['total_revenue'] ?? 0)]);
        $xml .= $this->excelRow(['Total Profit (PHP)', $money($summary['total_profit'] ?? 0)]);
        $xml .= $this->excelRow(['Total Orders', (string) ($summary['total_orders'] ?? 0)]);
        $xml .= $this->excelRow(['Products Sold', (string) ($summary['total_products_sold'] ?? 0)]);
        $xml .= $this->excelRow(['Average Order Value (PHP)', $money($summary['average_order_value'] ?? 0)]);
        $xml .= '</Table></Worksheet>';

        $xml .= '<Worksheet ss:Name="Daily Sales"><Table>';
        $xml .= $this->excelRow(['Date', 'Revenue (PHP)', 'Orders', 'Profit (PHP)']);
        foreach ($daily as $row) {
            $xml .= $this->excelRow([
                $row['date'] ?? '',
                $money($row['revenue'] ?? 0),
                (string) ($row['orders'] ?? 0),
                $money($row['profit'] ?? 0),
            ]);
        }
        $xml .= '</Table></Worksheet>';

        $xml .= '<Worksheet ss:Name="Monthly Sales"><Table>';
        $xml .= $this->excelRow(['Month', 'Revenue (PHP)', 'Orders', 'Profit (PHP)']);
        foreach ($monthly as $row) {
            $xml .= $this->excelRow([
                $row['month'] ?? '',
                $money($row['revenue'] ?? 0),
                (string) ($row['orders'] ?? 0),
                $money($row['profit'] ?? 0),
            ]);
        }
        $xml .= '</Table></Worksheet>';

        $xml .= '<Worksheet ss:Name="Top Products"><Table>';
        $xml .= $this->excelRow(['Product', 'Units Sold', 'Revenue (PHP)']);
        foreach ($products as $row) {
            $xml .= $this->excelRow([
                $row['product_name'] ?? 'Product',
                (string) ($row['units_sold'] ?? 0),
                $money($row['revenue'] ?? 0),
            ]);
        }
        $xml .= '</Table></Worksheet>';

        $xml .= '<Worksheet ss:Name="Orders"><Table>';
        $xml .= $this->excelRow(['Reference', 'Customer', 'Paid At', 'Amount (PHP)', 'Profit (PHP)', 'Payment', 'Status']);
        foreach ($orders as $row) {
            $xml .= $this->excelRow([
                $row['reference_number'] ?? ('#' . ($row['id'] ?? '')),
                $row['customer_name'] ?? 'Guest',
                $row['paid_at'] ?? ($row['created_at'] ?? ''),
                $money($row['total_amount'] ?? 0),
                $money($row['total_profit'] ?? 0),
                $row['payment_method'] ?? '',
                $row['status'] ?? '',
            ]);
        }
        $xml .= '</Table></Worksheet>';

        $xml .= '</Workbook>';

        return $xml;
    }

    private function excelStyles(): string
    {
        return '<Styles>
            <Style ss:ID="Header"><Font ss:Bold="1"/></Style>
        </Styles>';
    }

    /**
     * @param array<int, string|int|float> $cells
     */
    private function excelRow(array $cells): string
    {
        $row = '<Row>';
        foreach ($cells as $cell) {
            $value = (string) $cell;
            $type = is_numeric($cell) && $cell !== '' && !str_contains($value, '-') ? 'Number' : 'String';
            if ($type === 'Number' && !is_int($cell) && !is_float($cell) && !ctype_digit(str_replace('.', '', $value))) {
                $type = 'String';
            }
            if ($type === 'Number') {
                $row .= '<Cell><Data ss:Type="Number">' . htmlspecialchars($value, ENT_XML1) . '</Data></Cell>';
            } else {
                $row .= '<Cell><Data ss:Type="String">' . htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</Data></Cell>';
            }
        }
        $row .= '</Row>';

        return $row;
    }
}
