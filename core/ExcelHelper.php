<?php
/**
 * Portal BIP - Excel & CSV Export/Import Helper
 * 
 * Reusable helper for handling CSV and Excel Spreadsheet exports,
 * templates, and file uploads parsing without external dependencies.
 */

class ExcelHelper
{
    /**
     * Export data to downloadable CSV (UTF-8 BOM compatible with MS Excel)
     */
    public static function exportCSV(string $filename, array $headers, array $data): void
    {
        if (!str_ends_with(strtolower($filename), '.csv')) {
            $filename .= '.csv';
        }

        // Clean any output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM for Excel support
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write headers
        fputcsv($output, $headers, ';');

        // Write rows
        foreach ($data as $row) {
            fputcsv($output, array_values($row), ';');
        }

        fclose($output);
        exit;
    }

    /**
     * Export data to Native XML Spreadsheet 2003 (.xls) with formatting
     */
    public static function exportXLS(string $filename, array $headers, array $data, string $title = 'Data'): void
    {
        if (!str_ends_with(strtolower($filename), '.xls')) {
            $filename .= '.xls';
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        ?>
        <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                  xmlns:o="urn:schemas-microsoft-com:office:office"
                  xmlns:x="urn:schemas-microsoft-com:office:excel"
                  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
                  xmlns:html="http://www.w3.org/TR/REC-html40">
            <Styles>
                <Style ss:ID="Default" ss:Name="Normal">
                    <Alignment ss:Vertical="Center"/>
                    <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#000000"/>
                </Style>
                <Style ss:ID="Header">
                    <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                    <Borders>
                        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
                        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
                        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
                        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
                    </Borders>
                    <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>
                    <Interior ss:Color="#0284C7" ss:Pattern="Solid"/>
                </Style>
                <Style ss:ID="DataRow">
                    <Borders>
                        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                    </Borders>
                </Style>
            </Styles>
            <Worksheet ss:Name="<?= htmlspecialchars($title) ?>">
                <Table>
                    <Row ss:Height="26">
                        <?php foreach ($headers as $h): ?>
                            <Cell ss:StyleID="Header"><Data ss:Type="String"><?= htmlspecialchars($h) ?></Data></Cell>
                        <?php endforeach; ?>
                    </Row>
                    <?php foreach ($data as $row): ?>
                        <Row ss:Height="20">
                            <?php foreach ($row as $val): ?>
                                <Cell ss:StyleID="DataRow"><Data ss:Type="String"><?= htmlspecialchars((string)($val ?? '')) ?></Data></Cell>
                            <?php endforeach; ?>
                        </Row>
                    <?php endforeach; ?>
                </Table>
            </Worksheet>
        </Workbook>
        <?php
        exit;
    }

    /**
     * Download Template for Import (CSV with headers and sample rows)
     */
    public static function downloadTemplate(string $filename, array $headers, array $sampleRows = []): void
    {
        self::exportCSV($filename, $headers, $sampleRows);
    }

    /**
     * Parse uploaded spreadsheet or CSV file
     * Returns array of rows (each row is associative array if headers match, or array of strings)
     */
    public static function parseUpload(array $file): array
    {
        if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            throw new Exception("File upload tidak ditemukan.");
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Terjadi kesalahan saat upload file (Kode: {$file['error']}).");
        }

        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, ['csv', 'txt', 'xls', 'xlsx'])) {
            throw new Exception("Format file harus berupa CSV (.csv) atau Excel (.xls).");
        }

        $filePath = $file['tmp_name'];
        $content = file_get_contents($filePath);

        // Detect delimiter (semicolon, comma, tab)
        $firstLine = strtok($content, "\r\n");
        $delimiter = ';';
        if (substr_count($firstLine, ',') > substr_count($firstLine, ';')) {
            $delimiter = ',';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ';')) {
            $delimiter = "\t";
        }

        $rows = [];
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception("Gagal membaca isi file yang diupload.");
        }

        // Remove BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = null;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Filter out empty rows
            $hasData = false;
            foreach ($data as $cell) {
                if (trim((string)$cell) !== '') {
                    $hasData = true;
                    break;
                }
            }
            if (!$hasData) continue;

            if ($headers === null) {
                // Header row
                $headers = array_map(function($h) {
                    return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
                }, $data);
            } else {
                $row = [];
                foreach ($headers as $index => $colName) {
                    $row[$colName] = isset($data[$index]) ? trim($data[$index]) : '';
                }
                $row['_raw'] = $data;
                $rows[] = $row;
            }
        }

        fclose($handle);
        return [
            'headers' => $headers ?? [],
            'rows' => $rows
        ];
    }
}
