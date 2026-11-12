<?php
// pengaturan.php - Pengaturan SI-PREDOBAT
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/auth.php';

require_auth();

$db = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_settings') {
        $settings = $_POST['settings'] ?? [];
        $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($settings as $k => $v) {
            $stmt->execute([trim($v), $k]);
        }
        set_flash('success', 'Pengaturan sistem berhasil disimpan.');
        redirect('pengaturan.php');
    }

    if ($action === 'reset_database') {
        require_once __DIR__ . '/database/seed.php';
        runMigrationAndSeed(true);
        set_flash('success', 'Basis data berhasil direset dan diinisialisasi ulang dengan data standar!');
        redirect('pengaturan.php');
    }
}

// Fetch all settings
$rawSettings = $db->query("SELECT * FROM system_settings")->fetchAll();
$settings = [];
foreach ($rawSettings as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

$pageTitle = 'Pengaturan - SI-PREDOBAT';
$headerTitle = 'Pengaturan';
$headerSubtitle = 'Konfigurasi instansi farmasi, parameter algoritma prediksi, dan pemeliharaan sistem';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8 space-y-6">

        <form method="POST" action="pengaturan.php" class="space-y-6">
            <input type="hidden" name="action" value="save_settings">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Section 1: Identitas Instansi -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4 text-xs">
                    <div class="pb-3 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-800">Identitas Fasilitas Kesehatan</h3>
                        <p class="text-slate-500 mt-0.5">Informasi instansi yang ditampilkan pada surat pengadaan & laporan</p>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Rumah Sakit / Instansi</label>
                        <input type="text" name="settings[hospital_name]" value="<?= htmlspecialchars($settings['hospital_name'] ?? 'RSUD Sehat Sentosa') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Unit Pengelola</label>
                        <input type="text" name="settings[pharmacy_unit]" value="<?= htmlspecialchars($settings['pharmacy_unit'] ?? 'Instalasi Farmasi RSUD Sehat Sentosa') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Versi Aplikasi</label>
                        <input type="text" readonly value="<?= htmlspecialchars($settings['app_version'] ?? '2.4.0-Enterprise') ?>" class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-500 cursor-not-allowed">
                    </div>
                </div>

                <!-- Section 2: Parameter Algoritma Prediksi -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4 text-xs">
                    <div class="pb-3 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-800">Parameter Model Prediksi & Buffer Stock</h3>
                        <p class="text-slate-500 mt-0.5">Pengaturan standar perhitungan persediaan pengadaan obat</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Default Lead Time (hari)</label>
                            <input type="number" name="settings[lead_time_default]" value="<?= htmlspecialchars($settings['lead_time_default'] ?? '7') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Safety Stock / Buffer (%)</label>
                            <input type="number" name="settings[safety_stock_pct]" value="<?= htmlspecialchars($settings['safety_stock_pct'] ?? '20') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Batas Kritis (Sisa Hari)</label>
                            <input type="number" name="settings[critical_threshold_days]" value="<?= htmlspecialchars($settings['critical_threshold_days'] ?? '15') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Smoothing Alpha (SES)</label>
                            <input type="text" name="settings[smoothing_alpha]" value="<?= htmlspecialchars($settings['smoothing_alpha'] ?? '0.4') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Peringatan Kedaluwarsa Dini (Hari)</label>
                        <input type="number" name="settings[expiry_warning_days]" value="<?= htmlspecialchars($settings['expiry_warning_days'] ?? '90') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <button type="button" onclick="confirmReset()" class="px-4 py-2.5 rounded-xl border border-rose-200 text-rose-700 bg-rose-50 hover:bg-rose-100 font-semibold text-xs transition-colors">
                    Reset & Inisialisasi Ulang Data Standar
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#00a884] hover:bg-[#009272] text-white font-semibold text-xs shadow-md shadow-teal-900/10 hover:shadow-lg transition-all">
                    Simpan Pengaturan
                </button>
            </div>
        </form>

    </main>
</div>

<script>
function confirmReset() {
    if (confirm('Perhatian: Aksi ini akan mengembalikan seluruh data obat, riwayat pemakaian, dan pengguna ke status awal standar. Lanjutkan?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'pengaturan.php';
        form.innerHTML = '<input type="hidden" name="action" value="reset_database">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

