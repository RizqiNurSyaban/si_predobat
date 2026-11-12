<?php
// includes/topbar.php
$headerTitle = $headerTitle ?? 'Dashboard';
$headerSubtitle = $headerSubtitle ?? 'Rabu, 5 November 2026 · Ringkasan kondisi stok dan prediksi';

// Count notifications
$db = getDBConnection();
$critCount = $db->query("SELECT COUNT(*) FROM medicines WHERE status = 'Kritis'")->fetchColumn();
$expCount = $db->query("SELECT COUNT(*) FROM medicine_batches WHERE status = 'Hampir Kedaluwarsa'")->fetchColumn();
$totalAlerts = $critCount + $expCount;
?>
<header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between flex-shrink-0">
    <!-- Left: Page Title & Subtitle -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight"><?= htmlspecialchars($headerTitle) ?></h2>
        <p class="text-xs text-slate-500 font-medium mt-0.5"><?= htmlspecialchars($headerSubtitle) ?></p>
    </div>

    <!-- Right: Search Bar & Notification -->
    <div class="flex items-center gap-4">
        <!-- Global Search -->
        <form action="<?= base_url('stok.php') ?>" method="GET" class="relative w-80">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
            <input type="text" 
                   name="q" 
                   placeholder="Cari obat, kode, atau laporan..." 
                   class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200/90 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all">
        </form>

        <!-- Notification Bell with Dropdown -->
        <div class="relative" id="notifDropdownContainer">
            <button type="button" 
                    id="notifBtn" 
                    onclick="toggleNotifDropdown()" 
                    class="relative p-2.5 rounded-xl border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <?php if ($totalAlerts > 0): ?>
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"></span>
                <?php endif; ?>
            </button>

            <!-- Notification Popover -->
            <div id="notifMenu" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-800">Notifikasi & Peringatan</h3>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-700">
                        <?= $totalAlerts ?> Perlu Tindakan
                    </span>
                </div>
                <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                    <a href="<?= base_url('stok.php?filter=kritis') ?>" class="block p-3.5 hover:bg-slate-50 transition-colors">
                        <div class="flex items-start gap-3">
                            <span class="p-2 rounded-lg bg-rose-100 text-rose-600 mt-0.5">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </span>
                            <div>
                                <p class="text-xs font-semibold text-slate-800">17 Obat Berisiko Kekurangan Stok</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">Paracetamol, Omeprazole, Oralit membutuhkan restock segera.</p>
                            </div>
                        </div>
                    </a>
                    <a href="<?= base_url('pengadaan.php') ?>" class="block p-3.5 hover:bg-slate-50 transition-colors">
                        <div class="flex items-start gap-3">
                            <span class="p-2 rounded-lg bg-amber-100 text-amber-600 mt-0.5">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </span>
                            <div>
                                <p class="text-xs font-semibold text-slate-800">Omeprazole 20mg Mendekati Kedaluwarsa</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">Batch OM-2211 jatuh tempo Des 2026. Terapkan FEFO.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="p-2.5 bg-slate-50 text-center border-t border-slate-100">
                    <a href="<?= base_url('pengadaan.php') ?>" class="text-xs text-teal-600 hover:text-teal-700 font-semibold">
                        Buka Rekomendasi Pengadaan &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function toggleNotifDropdown() {
    const menu = document.getElementById('notifMenu');
    menu.classList.toggle('hidden');
}
window.addEventListener('click', function(e) {
    const container = document.getElementById('notifDropdownContainer');
    if (container && !container.contains(e.target)) {
        document.getElementById('notifMenu').classList.add('hidden');
    }
});
</script>

