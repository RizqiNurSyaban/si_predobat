<?php
// laporan.php - Laporan & Analisis Farmasi
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/auth.php';

require_auth();

$db = getDBConnection();

$tab = $_GET['tab'] ?? 'prediksi';

// Fetch reports data
$medicines = $db->query("
    SELECT m.*, c.name as category_name 
    FROM medicines m 
    JOIN categories c ON m.category_id = c.id 
    ORDER BY m.status DESC, m.name ASC
")->fetchAll();

$batches = $db->query("
    SELECT b.*, m.name as medicine_name, m.code as medicine_code, m.dosage_form 
    FROM medicine_batches b 
    JOIN medicines m ON b.medicine_id = m.id 
    ORDER BY b.expiry_date ASC
")->fetchAll();

// Handle Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Laporan_Farmasi_' . $tab . '_' . date('Ymd') . '.csv');
    $out = fopen('php://output', 'w');
    if ($tab === 'prediksi') {
        fputcsv($out, ['Kode', 'Nama Obat', 'Kategori', 'Sediaan', 'Stok Saat Ini', 'Prediksi Des 2026', 'Rekomendasi Pesan', 'Prioritas']);
        foreach ($medicines as $m) {
            fputcsv($out, [$m['code'], $m['name'], $m['category_name'], $m['dosage_form'], $m['current_stock'], $m['predicted_demand_next'], $m['recommended_order_qty'], $m['priority']]);
        }
    } else {
        fputcsv($out, ['Kode', 'Nama Obat', 'No. Batch', 'Stok Batch', 'Tanggal Kedaluwarsa', 'Status', 'Pemasok']);
        foreach ($batches as $b) {
            fputcsv($out, [$b['medicine_code'], $b['medicine_name'], $b['batch_number'], $b['stock_qty'], $b['expiry_date'], $b['status'], $b['supplier']]);
        }
    }
    fclose($out);
    exit;
}

$pageTitle = 'Laporan & Analisis - SI-PREDOBAT';
$headerTitle = 'Laporan & Analisis';
$headerSubtitle = 'Rekapitulasi proyeksi kebutuhan, evaluasi persediaan, dan monitoring kedaluwarsa';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8 space-y-6">

        <!-- Top Navigation Tabs & Export -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="<?= base_url('laporan.php?tab=prediksi') ?>" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all <?= ($tab === 'prediksi') ? 'bg-[#00a884] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                    Laporan Prediksi & Pengadaan
                </a>
                <a href="<?= base_url('laporan.php?tab=kedaluwarsa') ?>" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all <?= ($tab === 'kedaluwarsa') ? 'bg-[#00a884] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                    Monitoring Kedaluwarsa (FEFO)
                </a>
            </div>

            <div class="flex items-center gap-2">
                <a href="<?= base_url('laporan.php?tab=' . $tab . '&export=csv') ?>" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-white bg-white/60 transition-colors">
                    <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span>Ekspor CSV</span>
                </a>
                <button onclick="window.print()" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-white bg-white/60 transition-colors">
                    <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    <span>Cetak Laporan</span>
                </button>
            </div>
        </div>

        <?php if ($tab === 'prediksi'): ?>
        <!-- Tab 1: Laporan Prediksi -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-6">
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-800">Rekapitulasi Proyeksi Kebutuhan & Rekomendasi Pengadaan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Analisis kebutuhan periode Desember 2026 berdasarkan pola konsumsi dan tren epidemiologi</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Nama Obat</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4 text-right">Stok Riil</th>
                            <th class="py-3 px-4 text-right">Prediksi Kebutuhan</th>
                            <th class="py-3 px-4 text-right">Rekomendasi Pesan</th>
                            <th class="py-3 px-4">Status Stok</th>
                            <th class="py-3 px-4">Prioritas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($medicines as $m): ?>
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3 px-4 font-mono font-medium text-slate-600"><?= htmlspecialchars($m['code']) ?></td>
                            <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($m['name']) ?></td>
                            <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($m['category_name']) ?></td>
                            <td class="py-3 px-4 text-right font-semibold"><?= number_format($m['current_stock'], 0, ',', '.') ?></td>
                            <td class="py-3 px-4 text-right font-semibold text-slate-700"><?= number_format($m['predicted_demand_next'], 0, ',', '.') ?></td>
                            <td class="py-3 px-4 text-right font-bold text-teal-700">
                                <?= ($m['recommended_order_qty'] > 0) ? '+' . number_format($m['recommended_order_qty'], 0, ',', '.') : '-' ?>
                            </td>
                            <td class="py-3 px-4"><?= get_status_badge($m['status']) ?></td>
                            <td class="py-3 px-4"><?= get_priority_badge($m['priority']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php else: ?>
        <!-- Tab 2: Laporan Kedaluwarsa & FEFO -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden p-6">
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-800">Laporan Masa Kedaluwarsa & Pengendalian FEFO</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar lot/batch obat terdaftar berurutan dari kedaluwarsa terdekat (First Expired First Out)</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Nama Obat</th>
                            <th class="py-3 px-4">No. Batch</th>
                            <th class="py-3 px-4 text-right">Stok Batch</th>
                            <th class="py-3 px-4">Tanggal Masuk</th>
                            <th class="py-3 px-4">Tanggal Kedaluwarsa</th>
                            <th class="py-3 px-4">Status FEFO</th>
                            <th class="py-3 px-4">Pemasok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($batches as $b): 
                            $isUrgent = ($b['status'] === 'Hampir Kedaluwarsa');
                        ?>
                        <tr class="hover:bg-slate-50/70 <?= $isUrgent ? 'bg-amber-50/30' : '' ?>">
                            <td class="py-3 px-4 font-mono font-medium text-slate-600"><?= htmlspecialchars($b['medicine_code']) ?></td>
                            <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($b['medicine_name']) ?></td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-700"><?= htmlspecialchars($b['batch_number']) ?></td>
                            <td class="py-3 px-4 text-right font-bold"><?= number_format($b['stock_qty'], 0, ',', '.') ?> <?= htmlspecialchars($b['dosage_form']) ?></td>
                            <td class="py-3 px-4 text-slate-500"><?= format_date_id($b['received_date']) ?></td>
                            <td class="py-3 px-4 font-bold <?= $isUrgent ? 'text-amber-700' : 'text-slate-700' ?>">
                                <?= format_date_id($b['expiry_date']) ?>
                            </td>
                            <td class="py-3 px-4">
                                <?php if ($isUrgent): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        Prioritas Pakai (FEFO)
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Aman
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($b['supplier']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

