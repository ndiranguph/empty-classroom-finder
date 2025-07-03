<?php
$filePath = __DIR__ . '/templates/schedule_template.xlsx';

if (file_exists($filePath)) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="schedule_template.xlsx"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
} else {
    echo "Template not found.";
}
