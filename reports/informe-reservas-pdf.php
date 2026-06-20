<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$user = current_user();
$reservations = my_reservations((int) $user['id']);

function pdf_safe_text(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $text);
    return preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';
}

function pdf_content_stream(array $lines, int $pageNumber, int $pageCount): string
{
    $content = "BT\n/F1 11 Tf\n50 790 Td\n14 TL\n";
    $content .= '(' . pdf_safe_text('Travel Booking System - Informe de reservas') . ") Tj\n";
    $content .= 'T* ';
    $content .= '(' . pdf_safe_text('Usuario: ' . (string) ($GLOBALS['user']['full_name'] ?? 'Usuario')) . ") Tj\n";
    $content .= 'T* ';
    $content .= '(' . pdf_safe_text('Generado: ' . date('d/m/Y H:i')) . ") Tj\n";
    $content .= 'T* ';
    $content .= '(' . pdf_safe_text('Pagina ' . $pageNumber . ' de ' . $pageCount) . ") Tj\n";
    $content .= "T*\nT*\n";

    foreach ($lines as $line) {
        $content .= '(' . pdf_safe_text($line) . ") Tj\nT*\n";
    }

    $content .= "ET";
    return $content;
}

function build_simple_pdf(array $pages): string
{
    $fontObject = 3 + (count($pages) * 2);
    $objects = [
        1 => '<< /Type /Catalog /Pages 2 0 R >>',
    ];

    $pageRefs = [];

    foreach ($pages as $index => $pageLines) {
        $pageObject = 3 + ($index * 2);
        $contentObject = $pageObject + 1;
        $pageRefs[] = $pageObject . ' 0 R';

        $content = pdf_content_stream($pageLines, $index + 1, count($pages));

        $objects[$pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 ' . $fontObject . ' 0 R >> >> /Contents ' . $contentObject . ' 0 R >>';
        $objects[$contentObject] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
    }

    $objects[2] = '<< /Type /Pages /Count ' . count($pages) . ' /Kids [ ' . implode(' ', $pageRefs) . ' ] >>';
    $objects[$fontObject] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

    ksort($objects);

    $pdf = "%PDF-1.4\n";
    $offsets = [];

    foreach ($objects as $number => $object) {
        $offsets[$number] = strlen($pdf);
        $pdf .= $number . " 0 obj\n" . $object . "\nendobj\n";
    }

    $maxObject = max(array_keys($objects));
    $xrefOffset = strlen($pdf);

    $pdf .= "xref\n0 " . ($maxObject + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";

    for ($i = 1; $i <= $maxObject; $i++) {
        $offset = $offsets[$i] ?? 0;
        $pdf .= sprintf('%010d 00000 n ', $offset) . "\n";
    }

    $pdf .= "trailer\n<< /Size " . ($maxObject + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

    return $pdf;
}

$lines = [];
$lines[] = 'Resumen';
$lines[] = 'Total de reservas: ' . count($reservations);
$lines[] = 'Importe acumulado: ' . format_money(array_sum(array_map(static fn (array $reservation): float => (float) ($reservation['total_price'] ?? 0), $reservations)));
$lines[] = ' ';
$lines[] = 'Detalle';

if ($reservations === []) {
    $lines[] = 'No hay reservas registradas.';
} else {
    foreach ($reservations as $index => $reservation) {
        $lines[] = ($index + 1) . '. ' . (string) $reservation['trip_name'];
        $lines[] = '   Ruta: ' . (string) $reservation['origin'] . ' -> ' . (string) $reservation['destination'];
        $lines[] = '   Fecha: ' . format_datetime((string) $reservation['departure_at']);
        $lines[] = '   Plazas: ' . (int) $reservation['seats_reserved'] . ' | Importe: ' . format_money((float) ($reservation['total_price'] ?? 0)) . ' | Pago: ' . payment_status_label((string) ($reservation['payment_status'] ?? 'pending')) . ' | Estado: ' . reservation_status_label((string) $reservation['status']);

        if (!empty($reservation['notes'])) {
            $lines[] = '   Nota: ' . trim((string) $reservation['notes']);
        }

        $lines[] = ' ';
    }
}

$pageSize = 34;
$pages = array_chunk($lines, $pageSize);

if ($pages === []) {
    $pages = [['No hay contenido disponible.']];
}

$pdf = build_simple_pdf($pages);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="informe-reservas.pdf"');
header('Content-Length: ' . strlen($pdf));

echo $pdf;
exit;
