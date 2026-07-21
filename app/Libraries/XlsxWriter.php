<?php

namespace App\Libraries;

/**
 * Minimal, dependency-free .xlsx writer — this app vendors CodeIgniter
 * directly (no working Composer/vendor pipeline in the Docker build), so
 * a package like PhpSpreadsheet isn't installable here. Uses only PHP's
 * core ZipArchive extension to produce a single-sheet workbook Excel can
 * open directly, using inline strings so no sharedStrings part is needed.
 */
class XlsxWriter
{
    private array $rows = [];
    private string $sheetName;

    public function __construct(string $sheetName = 'Sheet1')
    {
        $this->sheetName = $this->sanitizeSheetName($sheetName);
    }

    public function addRow(array $cells): void
    {
        $this->rows[] = $cells;
    }

    public function addRows(array $rows): void
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    /** Write the workbook to disk and return the path. */
    public function save(string $path): string
    {
        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml());

        $zip->close();

        return $path;
    }

    /** Build the file and stream it as a download, then clean up the temp file. */
    public function download(string $filename): void
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid('xlsx_', true) . '.xlsx';
        $this->save($tmp);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmp));
        readfile($tmp);
        unlink($tmp);
    }

    private function sanitizeSheetName(string $name): string
    {
        $name = preg_replace('/[\[\]\*\/\\\\\?:]/', '', $name);

        return substr($name === '' ? 'Sheet1' : $name, 0, 31);
    }

    private function contentTypesXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML;
    }

    private function rootRelsXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    private function workbookXml(): string
    {
        $name = htmlspecialchars($this->sheetName, ENT_XML1);

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="{$name}" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML;
    }

    private function workbookRelsXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML;
    }

    private function sheetXml(): string
    {
        $xmlRows = '';

        foreach ($this->rows as $rowIndex => $cells) {
            $rowNum   = $rowIndex + 1;
            $xmlRows .= '<row r="' . $rowNum . '">';

            foreach (array_values($cells) as $colIndex => $value) {
                $ref = $this->cellRef($colIndex, $rowNum);

                // Numeric-looking values become real number cells, except leading-zero
                // strings (e.g. employee numbers "0012") which must stay text.
                if (is_numeric($value) && ! preg_match('/^0[0-9]/', (string) $value)) {
                    $xmlRows .= '<c r="' . $ref . '"><v>' . htmlspecialchars((string) $value, ENT_XML1) . '</v></c>';
                } else {
                    $text     = htmlspecialchars((string) $value, ENT_XML1);
                    $xmlRows .= '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">' . $text . '</t></is></c>';
                }
            }

            $xmlRows .= '</row>';
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>{$xmlRows}</sheetData>
</worksheet>
XML;
    }

    private function cellRef(int $colIndex, int $rowNum): string
    {
        $letters = '';
        $col     = $colIndex;

        do {
            $letters = chr(65 + ($col % 26)) . $letters;
            $col     = intdiv($col, 26) - 1;
        } while ($col >= 0);

        return $letters . $rowNum;
    }
}
