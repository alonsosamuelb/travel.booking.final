<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$summary = admin_reservation_report_summary();
$reports = admin_past_trip_reports(['page' => 1, 'search' => '', 'per_page' => 500]);

function admin_pdf_safe_text(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $text);
    return preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';
}

function admin_pdf_content_stream(array $lines, int $pageNumber, int $pageCount): string
{
    $content = "BT\n/F1 11 Tf\n50 790 Td\n14 TL\n";
    $content .= '(' . admin_pdf_safe_text('Travel Booking System - Informe global de reservas') . ") Tj\n";
    $content .= 'T* ';
    $content .= '(' . admin_pdf_safe_text('Administrador: ' . (string) ($GLOBALS['adminUser']['full_name'] ?? 'Admin')) . ") Tj\n";
    $content .= 'T* ';
    $content .= '(' . admin_pdf_safe_text('Generado: ' . date('d/m/Y H:i')) . ") Tj\n";
    $content .= 'T* ';
    $content .= '(' . admin_pdf_safe_text('Pagina ' . $pageNumber . ' de ' . $pageCount) . ") Tj\n";
    $content .= "T*\nT*\n";

    foreach ($lines as $line) {
        $content .= '(' . admin_pdf_safe_text($line) . ") Tj\nT*\n";
    }

    $content .= "ET";
    return $content;
}

function admin_build_simple_pdf(array $pages): string
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

        $content = admin_pdf_content_stream($pageLines, $index + 1, count($pages));

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

$adminUser = current_user();
$lines = [];
$lines[] = 'Resumen general';
$lines[] = 'Reservas pasadas: ' . (int) $summary['past_reservations_total'];
$lines[] = 'Viajeros transportados: ' . (int) $summary['travelled_passengers_total'];
$lines[] = 'Viajes completados: ' . (int) $summary['successful_trips_total'];
$lines[] = 'Reservas canceladas: ' . (int) $summary['cancelled_reservations_total'];
$lines[] = 'Tasa de exito: ' . (int) $summary['success_rate'] . '%';
$lines[] = 'Importe completado: ' . format_money((float) ($summary['collected_revenue_total'] ?? 0));
$lines[] = ' ';
$lines[] = 'Detalle por viaje';

foreach ($reports['items'] as $index => $item) {
    $travelledPassengers = (int) $item['travelled_passengers'];
    $cancelledReservations = (int) $item['cancelled_reservations'];
    $reportLabel = $travelledPassengers > 0 ? 'Completado con exito' : ($cancelledReservations > 0 ? 'Sin viaje efectivo' : 'Sin reservas');

    $lines[] = ($index + 1) . '. ' . (string) $item['name'];
    $lines[] = '   Ruta: ' . (string) $item['origin'] . ' -> ' . (string) $item['destination'];
    $lines[] = '   Fecha: ' . format_datetime((string) $item['departure_at']);
    $lines[] = '   Viajeros: ' . $travelledPassengers . ' | Canceladas: ' . $cancelledReservations . ' | Importe: ' . format_money((float) ($item['collected_amount'] ?? 0)) . ' | Estado: ' . $reportLabel;
    $lines[] = ' ';
}

$pageSize = 34;
$pages = array_chunk($lines, $pageSize);

if ($pages === []) {
    $pages = [['No hay datos historicos disponibles.']];
}

$pdf = admin_build_simple_pdf($pages);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="admin-informe-reservas.pdf"');
header('Content-Length: ' . strlen($pdf));

echo $pdf;
exit;
