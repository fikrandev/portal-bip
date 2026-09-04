<?php
/**
 * Portal BIP - Excel & CSV Export/Import Helper
 * 
 * Reusable helper for handling Excel (.xlsx / .xls) and CSV spreadsheet exports,
 * formatted templates, and file upload parsing.
 */

class ExcelHelper
{
    /**
     * Helper to convert Excel column letters (A, B, AA, ...) to 0-based index
     */
    public static function colLetterToIndex(string $cellRef): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($cellRef));
        $len = strlen($letters);
        if ($len === 0) return 0;
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + (ord($letters[$i]) - 64);
        }
        return max(0, $num - 1);
    }

    /**
     * Export data to Native XML Spreadsheet 2003 (.xls) with beautiful formatting
     */
    public static function exportXLS(string $filename, array $headers, array $data, string $title = 'Data'): void
    {
        if (str_ends_with(strtolower($filename), '.csv')) {
            $filename = substr($filename, 0, -4) . '.xls';
        } elseif (!str_ends_with(strtolower($filename), '.xls')) {
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
                    <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#0F172A"/>
                </Style>
                <Style ss:ID="Header">
                    <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
                    <Borders>
                        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0284C7"/>
                        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0284C7"/>
                        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0284C7"/>
                        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0284C7"/>
                    </Borders>
                    <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>
                    <Interior ss:Color="#0284C7" ss:Pattern="Solid"/>
                </Style>
                <Style ss:ID="DataRow">
                    <Alignment ss:Vertical="Center"/>
                    <Borders>
                        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
                    </Borders>
                </Style>
                <Style ss:ID="DataRowCenter">
                    <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
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
                    <?php foreach ($headers as $h): ?>
                        <Column ss:Width="<?= max(120, strlen($h) * 9) ?>"/>
                    <?php endforeach; ?>
                    <Row ss:Height="30">
                        <?php foreach ($headers as $h): ?>
                            <Cell ss:StyleID="Header"><Data ss:Type="String"><?= htmlspecialchars($h) ?></Data></Cell>
                        <?php endforeach; ?>
                    </Row>
                    <?php foreach ($data as $row): ?>
                        <Row ss:Height="22">
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
     * Download Excel Template for Import
     */
    public static function downloadTemplate(string $filename, array $headers, array $sampleRows = []): void
    {
        if (str_ends_with(strtolower($filename), '.csv')) {
            $filename = substr($filename, 0, -4) . '.xls';
        } elseif (!str_ends_with(strtolower($filename), '.xls')) {
            $filename .= '.xls';
        }
        self::exportXLS($filename, $headers, $sampleRows, 'Template Import');
    }

    /**
     * Export data to downloadable CSV (UTF-8 BOM compatible with MS Excel)
     */
    public static function exportCSV(string $filename, array $headers, array $data): void
    {
        if (!str_ends_with(strtolower($filename), '.csv')) {
            $filename .= '.csv';
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $headers, ';');
        foreach ($data as $row) {
            fputcsv($output, array_values($row), ';');
        }
        fclose($output);
        exit;
    }

    /**
     * Parse uploaded spreadsheet file (Supports XLSX, XLS XML, HTML Table, and CSV)
     */
    public static function parseUpload(array $file): array
    {
        if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            throw new Exception("File upload tidak ditemukan.");
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Terjadi kesalahan saat upload file (Kode: {$file['error']}).");
        }

        $filePath = $file['tmp_name'];
        $origName = $file['name'] ?? '';
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        // Read file header bytes
        $handle = fopen($filePath, 'rb');
        $magic = fread($handle, 8);
        fclose($handle);

        // 1. Check if XLSX (Zip format starting with 'PK\x03\x04')
        if (str_starts_with($magic, "PK\x03\x04") || $ext === 'xlsx') {
            try {
                return self::parseXLSX($filePath);
            } catch (Throwable $e) {
                // Fallback to text parsing if zip fails
            }
        }

        $content = file_get_contents($filePath);

        // 2. Check if XML Spreadsheet 2003
        if (stripos($content, '<Workbook') !== false && stripos($content, '<Table') !== false) {
            return self::parseXMLSpreadsheet($content);
        }

        // 3. Check if HTML Table (.xls generated from HTML)
        if (stripos($content, '<table') !== false && stripos($content, '<tr') !== false) {
            return self::parseHTMLTable($content);
        }

        // 4. Default: Parse as CSV / TXT
        return self::parseCSV($filePath);
    }

    /**
     * Parse modern Microsoft Excel OpenXML (.xlsx) files
     */
    public static function parseXLSX(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            throw new Exception("Ekstensi ZipArchive PHP tidak aktif.");
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception("Gagal membuka file Excel (.xlsx). Pastikan file tidak terkunci atau rusak.");
        }

        // 1. Read Shared Strings
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $prev = libxml_use_internal_errors(true);
            $xmlSS = simplexml_load_string($sharedStringsXml);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);

            if ($xmlSS !== false) {
                foreach ($xmlSS->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string)$r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Locate Sheet 1
        $sheetXmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXmlContent === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (preg_match('#xl/worksheets/sheet\d+\.xml#i', $stat['name'])) {
                    $sheetXmlContent = $zip->getFromName($stat['name']);
                    break;
                }
            }
        }

        $zip->close();

        if ($sheetXmlContent === false) {
            throw new Exception("Worksheet lembar kerja Excel tidak ditemukan.");
        }

        $prev = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($sheetXmlContent);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        if ($xml === false || !isset($xml->sheetData)) {
            throw new Exception("Format isi Excel tidak valid atau kosong.");
        }

        $rawRows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            $maxCol = -1;
            foreach ($row->c as $c) {
                $ref = (string)$c['r'];
                $colIdx = self::colLetterToIndex($ref);
                $type = (string)$c['t'];
                $val = '';

                if ($type === 's') {
                    $sIndex = intval((string)$c->v);
                    $val = $sharedStrings[$sIndex] ?? '';
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $val = (string)$c->is->t;
                } elseif (isset($c->v)) {
                    $val = (string)$c->v;
                }

                $rowData[$colIdx] = trim($val);
                if ($colIdx > $maxCol) {
                    $maxCol = $colIdx;
                }
            }

            if ($maxCol >= 0) {
                $normalizedRow = [];
                for ($i = 0; $i <= $maxCol; $i++) {
                    $normalizedRow[$i] = $rowData[$i] ?? '';
                }

                $hasData = false;
                foreach ($normalizedRow as $cell) {
                    if ($cell !== '') {
                        $hasData = true;
                        break;
                    }
                }
                if ($hasData) {
                    $rawRows[] = $normalizedRow;
                }
            }
        }

        if (empty($rawRows)) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map('trim', array_shift($rawRows));
        $rows = [];
        foreach ($rawRows as $row) {
            $rowObj = [];
            foreach ($headers as $idx => $header) {
                $rowObj[$header] = $row[$idx] ?? '';
            }
            $rowObj['_raw'] = $row;
            $rows[] = $rowObj;
        }

        return [
            'headers' => $headers,
            'rows' => $rows
        ];
    }

    /**
     * Parse XML Spreadsheet 2003 (.xls)
     */
    public static function parseXMLSpreadsheet(string $content): array
    {
        $prev = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        if ($xml === false) {
            throw new Exception("Gagal membaca format XML Spreadsheet.");
        }

        $xml->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
        $tables = $xml->xpath('//ss:Worksheet[1]//ss:Table');
        if (empty($tables)) {
            $tables = $xml->xpath('//Table');
        }

        $rawRows = [];
        if (!empty($tables)) {
            $table = $tables[0];
            $rows = $table->xpath('.//ss:Row');
            if (empty($rows)) {
                $rows = $table->xpath('.//Row');
            }

            foreach ($rows as $row) {
                $cells = $row->xpath('.//ss:Cell');
                if (empty($cells)) {
                    $cells = $row->xpath('.//Cell');
                }

                $rowData = [];
                $colIdx = 0;
                foreach ($cells as $cell) {
                    $indexAttr = (string)($cell->attributes('urn:schemas-microsoft-com:office:spreadsheet')['Index'] ?? '');
                    if ($indexAttr !== '') {
                        $colIdx = max(0, intval($indexAttr) - 1);
                    }
                    $dataTags = $cell->xpath('.//ss:Data');
                    if (empty($dataTags)) {
                        $dataTags = $cell->xpath('.//Data');
                    }
                    $val = !empty($dataTags) ? (string)$dataTags[0] : (string)$cell;
                    $rowData[$colIdx] = trim($val);
                    $colIdx++;
                }

                $maxCol = !empty($rowData) ? max(array_keys($rowData)) : -1;
                if ($maxCol >= 0) {
                    $normalizedRow = [];
                    for ($i = 0; $i <= $maxCol; $i++) {
                        $normalizedRow[$i] = $rowData[$i] ?? '';
                    }

                    $hasData = false;
                    foreach ($normalizedRow as $c) {
                        if ($c !== '') {
                            $hasData = true;
                            break;
                        }
                    }
                    if ($hasData) {
                        $rawRows[] = $normalizedRow;
                    }
                }
            }
        }

        if (empty($rawRows)) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map('trim', array_shift($rawRows));
        $rows = [];
        foreach ($rawRows as $row) {
            $rowObj = [];
            foreach ($headers as $idx => $header) {
                $rowObj[$header] = $row[$idx] ?? '';
            }
            $rowObj['_raw'] = $row;
            $rows[] = $rowObj;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Parse HTML table format (.xls exported from web)
     */
    public static function parseHTMLTable(string $content): array
    {
        $dom = new DOMDocument();
        $prev = libxml_use_internal_errors(true);
        @$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $trs = $dom->getElementsByTagName('tr');
        $rawRows = [];
        foreach ($trs as $tr) {
            $cells = [];
            foreach ($tr->childNodes as $node) {
                if (in_array(strtolower($node->nodeName), ['th', 'td'])) {
                    $cells[] = trim($node->textContent);
                }
            }
            if (!empty($cells)) {
                $hasData = false;
                foreach ($cells as $c) {
                    if ($c !== '') { $hasData = true; break; }
                }
                if ($hasData) {
                    $rawRows[] = $cells;
                }
            }
        }

        if (empty($rawRows)) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map('trim', array_shift($rawRows));
        $rows = [];
        foreach ($rawRows as $row) {
            $rowObj = [];
            foreach ($headers as $idx => $header) {
                $rowObj[$header] = $row[$idx] ?? '';
            }
            $rowObj['_raw'] = $row;
            $rows[] = $rowObj;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Parse CSV file with auto-detected delimiter
     */
    public static function parseCSV(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $firstLine = strtok($content, "\r\n");
        $delimiter = ';';
        if (substr_count($firstLine, ',') > substr_count($firstLine, ';')) {
            $delimiter = ',';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ';')) {
            $delimiter = "\t";
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception("Gagal membaca file spreadsheet.");
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = null;
        $rows = [];
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $hasData = false;
            foreach ($data as $cell) {
                if (trim((string)$cell) !== '') {
                    $hasData = true;
                    break;
                }
            }
            if (!$hasData) continue;

            if ($headers === null) {
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
