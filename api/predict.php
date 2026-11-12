<?php
// api/predict.php - Endpoint AJAX untuk Prediksi Real-Time
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../services/PredictionService.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$medId = (int)($_GET['med_id'] ?? 1);
$method = $_GET['method'] ?? 'moving_average_seasonal';
$horizon = (int)($_GET['horizon'] ?? 3);
$factorSeason = filter_var($_GET['factor_season'] ?? true, FILTER_VALIDATE_BOOLEAN);
$factorVisits = filter_var($_GET['factor_visits'] ?? true, FILTER_VALIDATE_BOOLEAN);
$factorProgram = filter_var($_GET['factor_program'] ?? false, FILTER_VALIDATE_BOOLEAN);

$db = getDBConnection();

// Get medicine info
$stmt = $db->prepare("SELECT m.*, c.name as category_name FROM medicines m JOIN categories c ON m.category_id = c.id WHERE m.id = ?");
$stmt->execute([$medId]);
$medicine = $stmt->fetch();

if (!$medicine) {
    echo json_encode(['success' => false, 'message' => 'Obat tidak ditemukan']);
    exit;
}

// Get historical usage
$stmtHist = $db->prepare("SELECT * FROM monthly_usage WHERE medicine_id = ? ORDER BY year ASC, month ASC");
$stmtHist->execute([$medId]);
$histRecords = $stmtHist->fetchAll();

$historicalValues = [];
$historicalLabels = [];
foreach ($histRecords as $hr) {
    $historicalValues[] = (int)$hr['actual_usage'];
    $historicalLabels[] = explode(' ', $hr['period_label'])[0] ?? $hr['period_label'];
}

if (empty($historicalValues)) {
    $historicalValues = [4100, 4300, 4150, 4600, 4800, 4980];
    $historicalLabels = ['Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'];
}

$result = PredictionService::predict(
    $historicalValues,
    $method,
    $horizon,
    $factorSeason,
    $factorVisits,
    $factorProgram
);

$result['medicine'] = $medicine;
$result['historical_labels'] = $historicalLabels;
$result['order_before_formatted'] = $medicine['order_before'] ? date('j M', strtotime($medicine['order_before'])) : '20 Nov';

echo json_encode($result);

