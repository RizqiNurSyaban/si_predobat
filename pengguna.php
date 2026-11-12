<?php
// pengguna.php - Manajemen Pengguna
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/auth.php';

require_auth();

$db = getDBConnection();

// Handle User Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'Asisten Apoteker');
        $password = trim($_POST['password'] ?? 'password123');

        $words = explode(' ', $name);
        $initials = '';
        foreach ($words as $w) {
            $initials .= strtoupper(substr($w, 0, 1));
            if (strlen($initials) >= 2) break;
        }

        try {
            $stmt = $db->prepare("INSERT INTO users (name, username, email, password, role, avatar_initials, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$name, $username, $email, password_hash($password, PASSWORD_BCRYPT), $role, $initials]);
            set_flash('success', "Pengguna {$name} berhasil ditambahkan!");
        } catch (Exception $e) {
            set_flash('error', "Gagal menambah pengguna: " . $e->getMessage());
        }
        redirect('pengguna.php');
    }

    if ($action === 'toggle_status') {
        $id = (int)$_POST['id'];
        $status = (int)$_POST['status'];
        $newStatus = $status ? 0 : 1;
        $stmt = $db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        set_flash('success', "Status pengguna berhasil diperbarui.");
        redirect('pengguna.php');
    }
}

$users = $db->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();

$pageTitle = 'Manajemen Pengguna - SI-PREDOBAT';
$headerTitle = 'Manajemen Pengguna';
$headerSubtitle = 'Kelola staf farmasi, hak akses, dan autentikasi akun';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8 space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">Daftar Pengguna Sistem</h3>
                <p class="text-xs text-slate-500 mt-0.5">Staf instalasi farmasi yang memiliki akses ke SI-PREDOBAT</p>
            </div>
            <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#00a884] hover:bg-[#009272] text-white text-xs font-semibold shadow-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Tambah Pengguna</span>
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3.5 px-4">Pengguna</th>
                            <th class="py-3.5 px-4">Username</th>
                            <th class="py-3.5 px-4">Role / Jabatan</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Tanggal Registrasi</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 font-bold flex items-center justify-center border border-teal-200">
                                        <?= htmlspecialchars($u['avatar_initials']) ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800"><?= htmlspecialchars($u['name']) ?></p>
                                        <p class="text-[11px] text-slate-500"><?= htmlspecialchars($u['email']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600"><?= htmlspecialchars($u['username']) ?></td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700"><?= htmlspecialchars($u['role']) ?></td>
                            <td class="py-3.5 px-4">
                                <?php if ($u['is_active']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500"><?= format_date_id($u['created_at']) ?></td>
                            <td class="py-3.5 px-4 text-right">
                                <?php if ($u['id'] != current_user()['id']): ?>
                                <form method="POST" action="pengguna.php" class="inline">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="status" value="<?= $u['is_active'] ?>">
                                    <button type="submit" class="text-xs font-semibold <?= $u['is_active'] ? 'text-rose-600 hover:underline' : 'text-emerald-600 hover:underline' ?>">
                                        <?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-slate-400 italic">Akun Anda</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- Modal: Tambah Pengguna -->
<div id="addUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Tambah Pengguna Baru</h3>
            <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form method="POST" action="pengguna.php" class="space-y-4 mt-4 text-xs">
            <input type="hidden" name="action" value="add">

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap & Gelar</label>
                <input type="text" name="name" required placeholder="Contoh: Rina Novita, S.Farm., Apt." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Username</label>
                    <input type="text" name="username" required placeholder="rina.novita" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Role</label>
                    <select name="role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                        <option value="Apoteker Penanggung Jawab">Apoteker Penanggung Jawab</option>
                        <option value="Asisten Apoteker">Asisten Apoteker</option>
                        <option value="Kepala Instalasi Farmasi">Kepala Instalasi Farmasi</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Email</label>
                <input type="email" name="email" required placeholder="rina.novita@rsud-sehat.id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Kata Sandi</label>
                <input type="password" name="password" required value="password123" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#00a884] hover:bg-[#009272] text-white font-semibold shadow-sm">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

