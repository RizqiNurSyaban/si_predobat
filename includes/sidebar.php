<?php
// includes/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
$user = current_user() ?? [
    'name' => 'Apt. Anisa Fitri',
    'role' => 'Apoteker Penanggung Jawab',
    'avatar_initials' => 'AF'
];
?>
<!-- Sidebar Component -->
<aside class="w-64 flex-shrink-0 bg-[#082f32] text-slate-300 flex flex-col justify-between select-none border-r border-[#0e3b3e]">
    <!-- Top Branding & Navigation -->
    <div class="p-5 flex flex-col h-full overflow-y-auto">
        <!-- Brand Logo -->
        <a href="<?= base_url('index.php') ?>" class="flex items-center gap-3 mb-8 px-1">
            <div class="w-10 h-10 rounded-xl bg-[#00a884] flex items-center justify-center text-white shadow-md shadow-emerald-900/40">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
            </div>
            <div>
                <h1 class="text-white font-bold text-lg tracking-tight flex items-center gap-1.5 leading-tight">
                    SI-PREDOBAT
                </h1>
                <p class="text-xs text-teal-200/70 font-medium leading-none mt-0.5">Prediksi Kebutuhan Obat</p>
            </div>
        </a>

        <!-- Menu Utama -->
        <div class="mb-6">
            <p class="px-3 text-[11px] font-bold text-teal-300/50 uppercase tracking-wider mb-2">
                Menu Utama
            </p>
            <nav class="space-y-1">
                <!-- Dashboard -->
                <a href="<?= base_url('index.php') ?>" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?= ($currentPage === 'index.php') ? 'bg-[#00a884] text-white shadow-md shadow-emerald-950/30' : 'text-slate-300 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                        <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Prediksi Kebutuhan -->
                <a href="<?= base_url('prediksi.php') ?>" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?= ($currentPage === 'prediksi.php') ? 'bg-[#00a884] text-white shadow-md shadow-emerald-950/30' : 'text-slate-300 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                    <span>Prediksi Kebutuhan</span>
                </a>

                <!-- Data Stok Obat -->
                <a href="<?= base_url('stok.php') ?>" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?= ($currentPage === 'stok.php') ? 'bg-[#00a884] text-white shadow-md shadow-emerald-950/30' : 'text-slate-300 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21 16-9 5-9-5"></path>
                        <path d="m21 12-9 5-9-5"></path>
                        <path d="m3 8 9-5 9 5-9 5Z"></path>
                    </svg>
                    <span>Data Stok Obat</span>
                </a>

                <!-- Rekomendasi Pengadaan -->
                <a href="<?= base_url('pengadaan.php') ?>" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?= ($currentPage === 'pengadaan.php') ? 'bg-[#00a884] text-white shadow-md shadow-emerald-950/30' : 'text-slate-300 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="21" r="1"></circle>
                        <circle cx="19" cy="21" r="1"></circle>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                    </svg>
                    <span>Rekomendasi Pengadaan</span>
                </a>

                <!-- Laporan -->
                <a href="<?= base_url('laporan.php') ?>" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?= ($currentPage === 'laporan.php') ? 'bg-[#00a884] text-white shadow-md shadow-emerald-950/30' : 'text-slate-300 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>Laporan</span>
                </a>
            </nav>
        </div>

        <!-- Administrasi -->
        <div>
            <p class="px-3 text-[11px] font-bold text-teal-300/50 uppercase tracking-wider mb-2">
                Administrasi
            </p>
            <nav class="space-y-1">
                <!-- Manajemen Pengguna -->
                <a href="<?= base_url('pengguna.php') ?>" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?= ($currentPage === 'pengguna.php') ? 'bg-[#00a884] text-white shadow-md shadow-emerald-950/30' : 'text-slate-300 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>Manajemen Pengguna</span>
                </a>

                <!-- Pengaturan -->
                <a href="<?= base_url('pengaturan.php') ?>" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?= ($currentPage === 'pengaturan.php') ? 'bg-[#00a884] text-white shadow-md shadow-emerald-950/30' : 'text-slate-300 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span>Pengaturan</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Bottom User Profile Pill (as in screenshots) -->
    <div class="p-3 border-t border-[#0e3b3e]/60 bg-[#07282b]/80">
        <div class="flex items-center justify-between gap-2 p-1.5 rounded-xl hover:bg-white/5 transition-colors group">
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <div class="w-9 h-9 rounded-xl bg-teal-500/20 text-teal-300 font-bold flex items-center justify-center border border-teal-500/30 flex-shrink-0 text-xs">
                    <?= htmlspecialchars($user['avatar_initials'] ?? 'AF') ?>
                </div>
                <div class="min-w-0 flex-1 overflow-hidden" title="<?= htmlspecialchars($user['name'] ?? '') ?> (<?= htmlspecialchars($user['role'] ?? '') ?>)">
                    <p class="text-xs font-semibold text-white truncate leading-tight"><?= htmlspecialchars($user['name'] ?? 'Apt. Anisa Fitri') ?></p>
                    <p class="text-[11px] text-teal-300/80 truncate leading-tight mt-0.5"><?= htmlspecialchars($user['role'] ?? 'Apoteker Penanggung Jawab') ?></p>
                </div>
            </div>
            <a href="<?= base_url('logout.php') ?>" title="Keluar / Logout" class="flex-shrink-0 text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-500/10 transition-colors">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </a>
        </div>
    </div>
</aside>

