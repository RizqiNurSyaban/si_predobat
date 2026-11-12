<?php
// cetak-pengadaan.php - Cetak Surat Permintaan Pengadaan Obat (PDF View)
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/auth.php';

require_auth();

$db = getDBConnection();

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$order = null;
$items = [];

if ($orderId) {
    $stmt = $db->prepare("SELECT * FROM procurement_orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if ($order) {
        $stmtItems = $db->prepare("
            SELECT poi.*, m.name, m.code, m.dosage_form 
            FROM procurement_order_items poi 
            JOIN medicines m ON poi.medicine_id = m.id 
            WHERE poi.order_id = ?
        ");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll();
    }
}

// Fallback to active recommendation draft if no specific ID provided
if (!$order) {
    $order = [
        'order_code' => 'SPPO-202611-001',
        'order_date' => '2026-11-05',
        'target_period' => 'Desember 2026',
        'supplier_name' => 'PBF Medika Jaya',
        'total_items' => 6,
        'total_units' => 9515,
        'total_cost' => 48730000,
        'notes' => 'Prioritaskan Paracetamol, stok kritis menjelang musim hujan.'
    ];

    $items = [
        ['code' => 'OBT-0001', 'name' => 'Paracetamol 500 mg', 'dosage_form' => 'Tablet', 'current_stock' => 1240, 'predicted_demand' => 5420, 'ordered_qty' => 4180, 'unit_price' => 450, 'subtotal' => 1881000, 'priority' => 'Tinggi'],
        ['code' => 'OBT-0005', 'name' => 'Omeprazole 20 mg', 'dosage_form' => 'Kapsul', 'current_stock' => 410, 'predicted_demand' => 1960, 'ordered_qty' => 1550, 'unit_price' => 1800, 'subtotal' => 2790000, 'priority' => 'Tinggi'],
        ['code' => 'OBT-0010', 'name' => 'Oralit (ORS)', 'dosage_form' => 'Sachet', 'current_stock' => 340, 'predicted_demand' => 1380, 'ordered_qty' => 1040, 'unit_price' => 1100, 'subtotal' => 1144000, 'priority' => 'Tinggi'],
        ['code' => 'OBT-0002', 'name' => 'Amoxicillin 500 mg', 'dosage_form' => 'Kapsul', 'current_stock' => 860, 'predicted_demand' => 2750, 'ordered_qty' => 1890, 'unit_price' => 1250, 'subtotal' => 2362500, 'priority' => 'Sedang'],
        ['code' => 'OBT-0007', 'name' => 'Simvastatin 20 mg', 'dosage_form' => 'Tablet', 'current_stock' => 520, 'predicted_demand' => 1210, 'ordered_qty' => 690, 'unit_price' => 1400, 'subtotal' => 966000, 'priority' => 'Sedang'],
        ['code' => 'OBT-0008', 'name' => 'Salbutamol Inhaler', 'dosage_form' => 'Unit', 'current_stock' => 95, 'predicted_demand' => 260, 'ordered_qty' => 165, 'unit_price' => 85000, 'subtotal' => 14025000, 'priority' => 'Sedang'],
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permintaan Pengadaan Obat - <?= htmlspecialchars($order['order_code']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 font-sans text-slate-800">
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-lg border border-slate-200 print:shadow-none print:border-none print:p-0">
        
        <!-- Print Toolbar -->
        <div class="no-print flex items-center justify-between pb-6 mb-6 border-b border-slate-200">
            <a href="pengadaan.php" class="text-sm font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
                &larr; Kembali ke Rekomendasi Pengadaan
            </a>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="px-5 py-2.5 bg-[#00a884] hover:bg-[#009272] text-white text-sm font-bold rounded-xl shadow flex items-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    <span>Cetak / Simpan PDF</span>
                </button>
            </div>
        </div>

        <!-- Official Letterhead (KOP SURAT) -->
        <div class="flex items-center gap-6 pb-4 border-b-2 border-slate-800 text-center">
            <div class="w-16 h-16 rounded-xl bg-[#00a884] text-white flex items-center justify-center font-bold text-2xl flex-shrink-0">
                +
            </div>
            <div class="flex-1 text-center">
                <h1 class="text-xl font-bold uppercase tracking-wider text-slate-900">Pemerintah Daerah · RSUD Sehat Sentosa</h1>
                <h2 class="text-lg font-bold text-teal-800">INSTALASI FARMASI & LOGISTIK MEDIK</h2>
                <p class="text-xs text-slate-500 mt-1">Jl. Kesehatan No. 45, Kota Sentosa · Telp: (021) 7890-1234 · Email: farmasi@rsud-sehat.id</p>
            </div>
        </div>

        <!-- Document Details -->
        <div class="my-6">
            <h3 class="text-center font-extrabold text-base uppercase underline tracking-wide text-slate-900">
                Surat Permintaan Pengadaan Obat (SPPO)
            </h3>
            <p class="text-center text-xs text-slate-500 font-mono mt-0.5">Nomor: <?= htmlspecialchars($order['order_code']) ?></p>

            <div class="grid grid-cols-2 gap-4 mt-6 text-xs">
                <div>
                    <p><span class="text-slate-500 font-medium">Tanggal Pengajuan:</span> <strong><?= format_date_id($order['order_date'], true) ?></strong></p>
                    <p class="mt-1"><span class="text-slate-500 font-medium">Target Periode Kebutuhan:</span> <strong><?= htmlspecialchars($order['target_period']) ?></strong></p>
                </div>
                <div class="text-right">
                    <p><span class="text-slate-500 font-medium">PBF Rekomendasi:</span> <strong><?= htmlspecialchars($order['supplier_name']) ?></strong></p>
                    <p class="mt-1"><span class="text-slate-500 font-medium">Status Dokumen:</span> <strong class="text-teal-700">Draf Pengadaan (Prediktif)</strong></p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="my-6">
            <table class="w-full text-left text-xs border border-slate-200">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200 font-bold text-slate-700">
                        <th class="p-2 border-r border-slate-200 text-center w-8">No</th>
                        <th class="p-2 border-r border-slate-200">Kode</th>
                        <th class="p-2 border-r border-slate-200">Nama Obat</th>
                        <th class="p-2 border-r border-slate-200">Bentuk</th>
                        <th class="p-2 border-r border-slate-200 text-right">Stok</th>
                        <th class="p-2 border-r border-slate-200 text-right">Prediksi</th>
                        <th class="p-2 border-r border-slate-200 text-right">Jml Pesan</th>
                        <th class="p-2 border-r border-slate-200 text-right">Harga Satuan</th>
                        <th class="p-2 text-right">Estimasi Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php 
                    $no = 1; 
                    $totalQty = 0; 
                    $totalRp = 0;
                    foreach ($items as $item): 
                        $qty = $item['ordered_qty'];
                        $sub = $item['subtotal'] ?? ($qty * $item['unit_price']);
                        $totalQty += $qty;
                        $totalRp += $sub;
                    ?>
                    <tr>
                        <td class="p-2 border-r border-slate-200 text-center"><?= $no++ ?></td>
                        <td class="p-2 border-r border-slate-200 font-mono"><?= htmlspecialchars($item['code']) ?></td>
                        <td class="p-2 border-r border-slate-200 font-bold"><?= htmlspecialchars($item['name']) ?></td>
                        <td class="p-2 border-r border-slate-200"><?= htmlspecialchars($item['dosage_form'] ?? 'Tablet') ?></td>
                        <td class="p-2 border-r border-slate-200 text-right"><?= number_format($item['current_stock'], 0, ',', '.') ?></td>
                        <td class="p-2 border-r border-slate-200 text-right"><?= number_format($item['predicted_demand'], 0, ',', '.') ?></td>
                        <td class="p-2 border-r border-slate-200 text-right font-bold text-teal-800">+<?= number_format($qty, 0, ',', '.') ?></td>
                        <td class="p-2 border-r border-slate-200 text-right"><?= format_rupiah($item['unit_price']) ?></td>
                        <td class="p-2 text-right font-semibold"><?= format_rupiah($sub) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold border-t-2 border-slate-300">
                        <td colspan="6" class="p-2.5 text-right border-r border-slate-200">TOTAL UNIT DIPESAN & ANGGARAN:</td>
                        <td class="p-2.5 text-right border-r border-slate-200 text-teal-800 font-extrabold"><?= number_format($totalQty, 0, ',', '.') ?></td>
                        <td class="p-2.5 border-r border-slate-200"></td>
                        <td class="p-2.5 text-right text-slate-900 font-extrabold text-sm"><?= format_rupiah($totalRp) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes -->
        <div class="my-4 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700">
            <p><strong>Catatan Pengadaan:</strong> <?= htmlspecialchars($order['notes']) ?></p>
            <p class="mt-1 text-[11px] text-slate-500">
                * Rekomendasi dihitung otomatis oleh modul prediktif SI-PREDOBAT berdasarkan riwayat konsumsi, estimasi lonjakan musiman penyakit, dan standar buffer stock Kemenkes RI.
            </p>
        </div>

        <!-- Signatures (Tanda Tangan Resmi) -->
        <div class="grid grid-cols-2 gap-8 mt-12 text-xs text-center">
            <div>
                <p class="text-slate-500">Diajukan oleh:</p>
                <p class="font-bold text-slate-800 mt-1">Apoteker Penanggung Jawab</p>
                <div class="h-20 flex items-center justify-center">
                    <span class="text-teal-700 font-serif italic text-base">Apt. Anisa Fitri, S.Farm.</span>
                </div>
                <p class="font-bold text-slate-900 underline">Apt. Anisa Fitri, S.Farm.</p>
                <p class="text-slate-500 text-[11px]">SIPA: 19920815/SIPA/2021</p>
            </div>
            <div>
                <p class="text-slate-500">Mengetahui & Menyetujui:</p>
                <p class="font-bold text-slate-800 mt-1">Kepala Instalasi Farmasi</p>
                <div class="h-20 flex items-center justify-center">
                    <span class="text-teal-700 font-serif italic text-base">dr. Hendra Kusuma, Sp.PK</span>
                </div>
                <p class="font-bold text-slate-900 underline">dr. Hendra Kusuma, Sp.PK</p>
                <p class="text-slate-500 text-[11px]">NIP: 19800512 200501 1 003</p>
            </div>
        </div>

    </div>
</body>
</html>

