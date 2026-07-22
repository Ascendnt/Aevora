<?php

namespace App\Libraries;

/**
 * Minimal, dependency-free PDF writer — same rationale as XlsxWriter: this
 * app vendors CodeIgniter directly with no working Composer/vendor pipeline,
 * so a library like Dompdf/mPDF isn't installable here. Hand-rolls just
 * enough of the PDF 1.4 object model (single base-14 font, plain text pages,
 * no images/embedding) to turn a generated document's plain-text content
 * into a real, openable PDF — a genuine step up from the printable-HTML-only
 * fallback used elsewhere, for the common case of plain text templates.
 *
 * Deliberately simple: character-count word-wrap (no real font metrics,
 * since Helvetica's AFM widths aren't worth embedding for this use case),
 * Letter-sized pages, a single font, no color/image support. Good enough for
 * HR documents (contracts, NDAs, letters) which are just formatted text.
 */
class PdfWriter
{
    private const PAGE_WIDTH  = 612.0; // Letter, points
    private const PAGE_HEIGHT = 792.0;
    private const MARGIN      = 54.0;
    private const FONT_SIZE   = 10.5;
    private const LINE_HEIGHT = 14.0;
    private const CHARS_PER_LINE = 92;

    private string $title;

    /** @var list<string> raw text lines, already wrapped to fit the page width */
    private array $lines = [];

    public function __construct(string $title = 'Document')
    {
        $this->title = $title;
    }

    public function addText(string $content): void
    {
        foreach (explode("\n", str_replace("\r\n", "\n", $content)) as $rawLine) {
            $rawLine = rtrim($rawLine);

            if ($rawLine === '') {
                $this->lines[] = '';
                continue;
            }

            foreach ($this->wrap($rawLine) as $wrapped) {
                $this->lines[] = $wrapped;
            }
        }
    }

    /** @return string[] */
    private function wrap(string $line): array
    {
        if (mb_strlen($line) <= self::CHARS_PER_LINE) {
            return [$line];
        }

        $words = explode(' ', $line);
        $out   = [];
        $cur   = '';

        foreach ($words as $word) {
            $candidate = $cur === '' ? $word : $cur . ' ' . $word;

            if (mb_strlen($candidate) > self::CHARS_PER_LINE && $cur !== '') {
                $out[] = $cur;
                $cur   = $word;
            } else {
                $cur = $candidate;
            }
        }

        if ($cur !== '') {
            $out[] = $cur;
        }

        return $out;
    }

    /** Splits the wrapped lines into pages' worth of lines, based on how many fit in the page height. */
    private function paginate(): array
    {
        $usableHeight = self::PAGE_HEIGHT - (2 * self::MARGIN);
        $linesPerPage = max(1, (int) floor($usableHeight / self::LINE_HEIGHT));

        return array_chunk($this->lines === [] ? [''] : $this->lines, $linesPerPage);
    }

    private function escape(string $text): string
    {
        // Strip anything outside Latin-1 (the standard base-14 fonts only cover that range) rather
        // than emit bytes that would render as garbage — safer than a hard failure for this use case.
        $text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function pageContentStream(array $pageLines): string
    {
        $y = self::PAGE_HEIGHT - self::MARGIN;
        $stream = "BT\n/F1 " . self::FONT_SIZE . " Tf\n" . self::LINE_HEIGHT . " TL\n"
            . self::MARGIN . ' ' . $y . " Td\n";

        foreach ($pageLines as $i => $line) {
            if ($i > 0) {
                $stream .= "T*\n";
            }
            $stream .= '(' . $this->escape($line) . ") Tj\n";
        }

        $stream .= "ET\n";

        return $stream;
    }

    /** Builds the full PDF byte string. */
    private function build(): string
    {
        $pages = $this->paginate();
        $pageCount = count($pages);

        // Object numbering: 1 = Catalog, 2 = Pages, 3 = Font, then alternating
        // Page/Contents objects starting at 4.
        $objects = [];

        $kidsRefs = [];
        $objNum   = 4;
        $pageObjNums = [];
        foreach ($pages as $i => $pageLines) {
            $pageObjNums[$i] = $objNum;
            $kidsRefs[]      = $objNum . ' 0 R';
            $objNum += 2; // page object + its content stream object
        }

        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [" . implode(' ', $kidsRefs) . "] /Count {$pageCount} >>";
        $objects[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";

        foreach ($pages as $i => $pageLines) {
            $pageObj    = $pageObjNums[$i];
            $contentObj = $pageObj + 1;
            $stream     = $this->pageContentStream($pageLines);

            $objects[$pageObj] = "<< /Type /Page /Parent 2 0 R "
                . '/MediaBox [0 0 ' . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . '] '
                . '/Resources << /Font << /F1 3 0 R >> >> '
                . "/Contents {$contentObj} 0 R >>";

            $objects[$contentObj] = "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}endstream";
        }

        ksort($objects);

        $pdf    = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= "{$num} 0 obj\n{$body}\nendobj\n";
        }

        $xrefStart = strlen($pdf);
        $maxObj    = max(array_keys($objects));

        $pdf .= "xref\n0 " . ($maxObj + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($n = 1; $n <= $maxObj; $n++) {
            $pdf .= isset($offsets[$n])
                ? str_pad((string) $offsets[$n], 10, '0', STR_PAD_LEFT) . " 00000 n \n"
                : "0000000000 00000 f \n";
        }

        $pdf .= "trailer\n<< /Size " . ($maxObj + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefStart}\n%%EOF";

        return $pdf;
    }

    public function save(string $path): string
    {
        file_put_contents($path, $this->build());

        return $path;
    }

    public function download(string $filename): void
    {
        $body = $this->build();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($body));
        echo $body;
    }
}
