<?php
// prediksi.php - Prediksi Kebutuhan Obat
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/services/PredictionService.php';

require_auth();

$db = getDBConnection();

// Get medicines for dropdown
$medicines = $db->query("
    SELECT m.*, c.name as category_name 
    FROM medicines m 
    JOIN categories c ON m.category_id = c.id 
    ORDER BY m.code ASC
")->fetchAll();

// Selected parameters
$selectedMedId = isset($_GET['med_id']) ? (int)$_GET['med_id'] : ($medicines[0]['id'] ?? 1);
$selectedMethod = $_GET['method'] ?? 'moving_average_seasonal';
$selectedHorizon = isset($_GET['horizon']) ? (int)$_GET['horizon'] : 3;
$factorSeason = isset($_GET['factor_season']) ? (bool)$_GET['factor_season'] : true;
$factorVisits = isset($_GET['factor_visits']) ? (bool)$_GET['factor_visits'] : true;
$factorProgram = isset($_GET['factor_program']) ? (bool)$_GET['factor_program'] : false;

// Find current medicine
$currentMed = null;
foreach ($medicines as $m) {
    if ($m['id'] == $selectedMedId) {
        $currentMed = $m;
        break;
    }
}
if (!$currentMed && !empty($medicines)) {
    $currentMed = $medicines[0];
    $selectedMedId = $currentMed['id'];
}

// Fetch historical monthly usage for this medicine
$stmtHist = $db->prepare("
    SELECT * FROM monthly_usage 
    WHERE medicine_id = ? 
    ORDER BY year ASC, month ASC
");
$stmtHist->execute([$selectedMedId]);
$histRecords = $stmtHist->fetchAll();

$historicalValues = [];
$historicalLabels = [];
foreach ($histRecords as $hr) {
    $historicalValues[] = (int)$hr['actual_usage'];
    $shortLabel = explode(' ', $hr['period_label'])[0] ?? $hr['period_label'];
    $historicalLabels[] = $shortLabel;
}

// Fallback if empty
if (empty($historicalValues)) {
    $historicalValues = [4100, 4300, 4150, 4600, 4800, 4980];
    $historicalLabels = ['Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'];
}

// Run prediction service
$predResult = PredictionService::predict(
    $historicalValues,
    $selectedMethod,
    $selectedHorizon,
    $factorSeason,
    $factorVisits,
    $factorProgram
);

// Target month prediction (Des 2026 in mockup)
$targetPred = $predResult['detailed_monthly'][0]['predicted'] ?? 5420;
$recomOrder = max(0, $targetPred - (int)$currentMed['current_stock']);
$orderBeforeStr = $currentMed['order_before'] ? date('j M', strtotime($currentMed['order_before'])) : '20 Nov';

// Handle Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Prediksi_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $currentMed['name']) . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Periode', 'Prediksi (Unit)', 'Batas Bawah 95%', 'Batas Atas 95%', 'Perubahan']);
    foreach ($predResult['detailed_monthly'] as $row) {
        fputcsv($out, [$row['period'], $row['predicted'], $row['lower_bound'], $row['upper_bound'], $row['pct_change_formatted']]);
    }
    fclose($out);
    exit;
}

$pageTitle = 'Prediksi Kebutuhan Obat - SI-PREDOBAT';
$headerTitle = 'Prediksi Kebutuhan Obat';
$headerSubtitle = 'Analisis dan proyeksi kebutuhan obat berbasis data historis';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Panel: Parameter Prediksi (Col 4) -->
            <div class="lg:col-span-4 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm h-fit">
                <div class="mb-6">
                    <h3 class="text-base font-bold text-slate-800">Parameter Prediksi</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Atur data dan model yang digunakan</p>
                </div>

                <form method="GET" action="prediksi.php" id="predictionForm" class="space-y-4 text-xs">
                    <!-- 1. Nama Obat Dropdown -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Nama obat</label>
                        <select name="med_id" id="medSelect" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 font-medium focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:outline-none">
                            <?php foreach ($medicines as $med): ?>
                            <option value="<?= $med['id'] ?>" <?= ($med['id'] == $selectedMedId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($med['name']) ?> (<?= htmlspecialchars($med['code']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 2. Kategori -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Kategori</label>
                        <input type="text" readonly value="<?= htmlspecialchars($currentMed['category_name']) ?>" class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-600 font-medium cursor-not-allowed">
                    </div>

                    <!-- 3. Metode Prediksi -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Metode prediksi</label>
                        <select name="method" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 font-medium focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:outline-none">
                            <option value="moving_average_seasonal" <?= ($selectedMethod === 'moving_average_seasonal') ? 'selected' : '' ?>>
                                Moving Average + Tren Musiman
                            </option>
                            <option value="exponential_smoothing" <?= ($selectedMethod === 'exponential_smoothing') ? 'selected' : '' ?>>
                                Single Exponential Smoothing (SES)
                            </option>
                            <option value="linear_regression" <?= ($selectedMethod === 'linear_regression') ? 'selected' : '' ?>>
                                Regresi Linear (Trend Line)
                            </option>
                            <option value="kemenkes_consumption" <?= ($selectedMethod === 'kemenkes_consumption') ? 'selected' : '' ?>>
                                Metode Konsumsi (Standar Kemenkes RI)
                            </option>
                        </select>
                    </div>

                    <!-- 4. Rentang Data Historis -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Rentang data historis</label>
                        <select name="range" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 font-medium focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:outline-none">
                            <option value="6">Mei 2026 - Okt 2026 (6 bulan)</option>
                            <option value="3">Agu 2026 - Okt 2026 (3 bulan)</option>
                            <option value="12">Nov 2025 - Okt 2026 (12 bulan)</option>
                        </select>
                    </div>

                    <!-- 5. Horizon Prediksi -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Horizon prediksi</label>
                        <select name="horizon" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 font-medium focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:outline-none">
                            <option value="1" <?= ($selectedHorizon == 1) ? 'selected' : '' ?>>1 bulan ke depan</option>
                            <option value="3" <?= ($selectedHorizon == 3) ? 'selected' : '' ?>>3 bulan ke depan</option>
                            <option value="6" <?= ($selectedHorizon == 6) ? 'selected' : '' ?>>6 bulan ke depan</option>
                        </select>
                    </div>

                    <!-- 6. Faktor Tambahan Toggles (Pills) -->
                    <div class="pt-2">
                        <label class="block font-semibold text-slate-700 mb-2">Faktor tambahan</label>
                        <div class="flex flex-wrap gap-2">
                            <label class="cursor-pointer">
                                <input type="checkbox" name="factor_season" value="1" <?= $factorSeason ? 'checked' : '' ?> class="sr-only peer">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border transition-all peer-checked:bg-teal-50 peer-checked:text-teal-700 peer-checked:border-teal-400 bg-slate-50 text-slate-500 border-slate-200">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Musim penyakit
                                </span>
                            </label>

                            <label class="cursor-pointer">
                                <input type="checkbox" name="factor_visits" value="1" <?= $factorVisits ? 'checked' : '' ?> class="sr-only peer">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border transition-all peer-checked:bg-teal-50 peer-checked:text-teal-700 peer-checked:border-teal-400 bg-slate-50 text-slate-500 border-slate-200">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Jumlah kunjungan
                                </span>
                            </label>

                            <label class="cursor-pointer">
                                <input type="checkbox" name="factor_program" value="1" <?= $factorProgram ? 'checked' : '' ?> class="sr-only peer">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border transition-all peer-checked:bg-teal-50 peer-checked:text-teal-700 peer-checked:border-teal-400 bg-slate-50 text-slate-500 border-slate-200">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Program kesehatan
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Button: Jalankan Prediksi -->
                    <div class="pt-4">
                        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#00a884] hover:bg-[#009272] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-md shadow-teal-900/10 hover:shadow-lg transition-all duration-200">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                            </svg>
                            <span>Jalankan Prediksi</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Panel: Hasil Prediksi (Col 8) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Top Chart Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">
                                Hasil Prediksi · <?= htmlspecialchars($currentMed['name']) ?>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Unit kebutuhan per bulan · diperbarui <?= date('j M Y, H:i') ?> WIB
                            </p>
                        </div>
                        <a href="prediksi.php?med_id=<?= $selectedMedId ?>&method=<?= $selectedMethod ?>&export=csv" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            <span>Ekspor</span>
                        </a>
                    </div>

                    <!-- Custom Chart Legend -->
                    <div class="flex items-center gap-6 text-xs font-medium text-slate-600 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#2563eb]"></span>
                            <span>Aktual</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#93c5fd]"></span>
                            <span>Data model</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#00a884]"></span>
                            <span>Prediksi</span>
                        </div>
                    </div>

                    <!-- Chart Container -->
                    <div class="relative h-64 w-full">
                        <canvas id="predictionChart"></canvas>
                    </div>
                </div>

                <!-- 3 Metric Cards Under Chart (Matching Screenshot 4) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- 1. Prediksi Kebutuhan Des 2026 -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
                        <p class="text-xs text-slate-500 font-medium">Prediksi kebutuhan <?= htmlspecialchars($predResult['detailed_monthly'][0]['period'] ?? 'Des 2026') ?></p>
                        <h4 class="text-2xl font-bold text-slate-800 tracking-tight mt-1">
                            <?= number_format($targetPred, 0, ',', '.') ?> <span class="text-xs font-normal text-slate-500">unit</span>
                        </h4>
                        <p class="text-[11px] font-semibold text-emerald-600 mt-2 flex items-center gap-1">
                            ▲ <?= $predResult['detailed_monthly'][0]['pct_change_formatted'] ?? '+4,6%' ?> dari bulan sebelumnya
                        </p>
                    </div>

                    <!-- 2. Akurasi Model (MAPE) -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Akurasi model (MAPE)</p>
                            <h4 class="text-2xl font-bold text-slate-800 tracking-tight mt-1">
                                <?= number_format($predResult['accuracy_pct'], 1, ',', '.') ?>%
                            </h4>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 mt-3 overflow-hidden">
                            <div class="bg-[#00a884] h-2 rounded-full transition-all duration-500" style="width: <?= $predResult['accuracy_pct'] ?>%"></div>
                        </div>
                    </div>

                    <!-- 3. Rekomendasi Sistem (Alert card) -->
                    <div class="bg-amber-50/70 border border-amber-200/70 rounded-2xl p-5 flex flex-col justify-between">
                        <div>
                            <p class="text-xs font-bold text-amber-900">Rekomendasi sistem</p>
                            <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                                Stok saat ini <span class="font-bold"><?= number_format($currentMed['current_stock'], 0, ',', '.') ?> unit</span>. 
                                Tambah pengadaan minimal <span class="font-bold"><?= number_format($recomOrder, 0, ',', '.') ?> unit</span> sebelum <?= $orderBeforeStr ?>.
                            </p>
                        </div>
                        <div class="mt-2 text-right">
                            <a href="<?= base_url('pengadaan.php') ?>" class="text-[11px] font-bold text-teal-700 hover:text-teal-800">Buat PO &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Rincian Prediksi Bulanan Table (Matching Screenshot 4) -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                    <h3 class="text-base font-bold text-slate-800 mb-4">Rincian Prediksi Bulanan</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="py-3 px-4">Periode</th>
                                    <th class="py-3 px-4">Prediksi (Unit)</th>
                                    <th class="py-3 px-4">Rentang Kepercayaan 95%</th>
                                    <th class="py-3 px-4 text-right">Perubahan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($predResult['detailed_monthly'] as $mRow): ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4 font-semibold text-slate-800">
                                        <?= htmlspecialchars($mRow['period']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-slate-700">
                                        <?= number_format($mRow['predicted'], 0, ',', '.') ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-slate-500">
                                        <?= htmlspecialchars($mRow['ci_formatted']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-semibold <?= ($mRow['pct_change'] >= 0) ? 'text-emerald-600' : 'text-rose-600' ?>">
                                        <?= htmlspecialchars($mRow['pct_change_formatted']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </main>
</div>

<!-- Chart Script for Prediction -->
<script>
let currentPredictionChart = null;

function renderPredictionChart(histLabels, histData, fitModelData, predVals, predLabels) {
    const ctx = document.getElementById('predictionChart').getContext('2d');
    const allLabels = [...histLabels, ...predLabels.map(f => f.split(' ')[0])];

    const actualSeries = [...histData];
    for (let i = 0; i < predVals.length; i++) actualSeries.push(null);

    const fitModelSeries = [...fitModelData];
    for (let i = 0; i < predVals.length; i++) fitModelSeries.push(null);

    const predSeries = [];
    for (let i = 0; i < histData.length - 1; i++) predSeries.push(null);
    predSeries.push(histData[histData.length - 1]);
    for (let i = 0; i < predVals.length; i++) predSeries.push(predVals[i]);

    if (currentPredictionChart) {
        currentPredictionChart.destroy();
    }

    currentPredictionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: allLabels,
            datasets: [
                {
                    label: 'Aktual',
                    data: actualSeries,
                    borderColor: '#2563eb',
                    backgroundColor: '#2563eb',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    tension: 0.35,
                    spanGaps: false
                },
                {
                    label: 'Data model',
                    data: fitModelSeries,
                    borderColor: '#93c5fd',
                    borderDash: [3, 3],
                    borderWidth: 1.5,
                    pointRadius: 0,
                    tension: 0.35,
                    spanGaps: false
                },
                {
                    label: 'Prediksi',
                    data: predSeries,
                    borderColor: '#00a884',
                    backgroundColor: '#00a884',
                    borderWidth: 2.5,
                    borderDash: [5, 5],
                    pointRadius: 4,
                    tension: 0.35,
                    spanGaps: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + new Intl.NumberFormat('id-ID').format(context.parsed.y) + ' unit';
                        }
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        font: { size: 10 },
                        color: '#94a3b8',
                        callback: function(val) {
                            return new Intl.NumberFormat('id-ID').format(val);
                        }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: { font: { size: 10 }, color: '#64748b' },
                    grid: { display: false }
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const histLabels = <?= json_encode($historicalLabels) ?>;
    const histData = <?= json_encode($historicalValues) ?>;
    const fitModelData = <?= json_encode($predResult['fit_model']) ?>;
    const predVals = <?= json_encode($predResult['predictions']) ?>;
    const predLabels = <?= json_encode(array_column($predResult['detailed_monthly'], 'period')) ?>;

    renderPredictionChart(histLabels, histData, fitModelData, predVals, predLabels);
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

