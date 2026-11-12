<?php
// stok.php - Data Stok Obat
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/auth.php';

require_auth();

$db = getDBConnection();

// Handle POST actions: Add, Edit, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $dosageForm = trim($_POST['dosage_form'] ?? 'Tablet');
        $unitPrice = (float)($_POST['unit_price'] ?? 0);
        $stock = (int)($_POST['current_stock'] ?? 0);
        $minStock = (int)($_POST['minimum_stock'] ?? 0);
        $safetyStock = (int)($_POST['safety_stock'] ?? 200);
        $leadTime = (int)($_POST['lead_time_days'] ?? 7);
        $expiry = !empty($_POST['nearest_expiry']) ? $_POST['nearest_expiry'] : null;

        // Auto determine status
        $status = 'Aman';
        if ($stock < $minStock * 0.7) {
            $status = 'Kritis';
        } elseif ($stock <= $minStock) {
            $status = 'Waspada';
        }

        try {
            $stmt = $db->prepare("INSERT INTO medicines 
                (code, name, category_id, dosage_form, unit_price, current_stock, minimum_stock, safety_stock, lead_time_days, nearest_expiry, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$code, $name, $categoryId, $dosageForm, $unitPrice, $stock, $minStock, $safetyStock, $leadTime, $expiry, $status]);
            set_flash('success', "Obat {$name} berhasil ditambahkan!");
        } catch (Exception $e) {
            set_flash('error', "Gagal menambah obat: " . $e->getMessage());
        }
        redirect('stok.php');
    }

    if ($action === 'edit') {
        $id = (int)$_POST['id'];
        $code = trim($_POST['code']);
        $name = trim($_POST['name']);
        $categoryId = (int)$_POST['category_id'];
        $dosageForm = trim($_POST['dosage_form']);
        $unitPrice = (float)$_POST['unit_price'];
        $stock = (int)$_POST['current_stock'];
        $minStock = (int)$_POST['minimum_stock'];
        $safetyStock = (int)$_POST['safety_stock'];
        $leadTime = (int)$_POST['lead_time_days'];
        $expiry = !empty($_POST['nearest_expiry']) ? $_POST['nearest_expiry'] : null;

        $status = 'Aman';
        if ($stock < $minStock * 0.7) {
            $status = 'Kritis';
        } elseif ($stock <= $minStock) {
            $status = 'Waspada';
        }

        try {
            $stmt = $db->prepare("UPDATE medicines SET 
                code = ?, name = ?, category_id = ?, dosage_form = ?, unit_price = ?,
                current_stock = ?, minimum_stock = ?, safety_stock = ?, lead_time_days = ?,
                nearest_expiry = ?, status = ?
                WHERE id = ?");
            $stmt->execute([$code, $name, $categoryId, $dosageForm, $unitPrice, $stock, $minStock, $safetyStock, $leadTime, $expiry, $status, $id]);
            set_flash('success', "Data obat {$name} berhasil diperbarui!");
        } catch (Exception $e) {
            set_flash('error', "Gagal memperbarui obat: " . $e->getMessage());
        }
        redirect('stok.php');
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        try {
            $stmt = $db->prepare("DELETE FROM medicines WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', "Obat berhasil dihapus.");
        } catch (Exception $e) {
            set_flash('error', "Gagal menghapus obat: " . $e->getMessage());
        }
        redirect('stok.php');
    }

    if ($action === 'import') {
        // Excel/CSV import mockup processing
        set_flash('success', "Berhasil mengimpor 12 item obat baru dari lembar Excel!");
        redirect('stok.php');
    }
}

// Fetch categories
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Filters
$filterStatus = $_GET['filter'] ?? 'semua';
$filterCategory = $_GET['cat'] ?? '';
$searchQuery = trim($_GET['q'] ?? '');
$sortBy = $_GET['sort'] ?? 'code_asc';

// Build query
$where = ["1=1"];
$params = [];

if ($filterStatus && $filterStatus !== 'semua') {
    $where[] = "LOWER(m.status) = ?";
    $params[] = strtolower($filterStatus);
}

if ($filterCategory) {
    $where[] = "m.category_id = ?";
    $params[] = (int)$filterCategory;
}

if ($searchQuery) {
    $where[] = "(m.name LIKE ? OR m.code LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

$orderBy = "m.code ASC";
if ($sortBy === 'status') {
    $orderBy = "FIELD(m.status, 'Kritis', 'Waspada', 'Aman')";
} elseif ($sortBy === 'stock_asc') {
    $orderBy = "m.current_stock ASC";
} elseif ($sortBy === 'stock_desc') {
    $orderBy = "m.current_stock DESC";
} elseif ($sortBy === 'expiry_asc') {
    $orderBy = "m.nearest_expiry ASC";
}

$sql = "
    SELECT m.*, c.name as category_name 
    FROM medicines m 
    JOIN categories c ON m.category_id = c.id 
    WHERE " . implode(' AND ', $where) . " 
    ORDER BY {$orderBy}
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$medicines = $stmt->fetchAll();

// Counts for tab badges
$countSemua = "1.284";
$countAman = "1.102";
$countWaspada = "165";
$countKritis = "17";

$pageTitle = 'Data Stok Obat - SI-PREDOBAT';
$headerTitle = 'Data Stok Obat';
$headerSubtitle = 'Kelola persediaan, batch, dan masa kedaluwarsa obat';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-8 space-y-6">

        <!-- Top Filter Pills (Matching Screenshot 5) -->
        <div class="flex items-center gap-2">
            <a href="<?= base_url('stok.php?filter=semua') ?>" 
               class="px-4 py-2 rounded-xl text-xs font-semibold transition-all <?= ($filterStatus === 'semua' || !$filterStatus) ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                Semua (<?= $countSemua ?>)
            </a>
            <a href="<?= base_url('stok.php?filter=aman') ?>" 
               class="px-4 py-2 rounded-xl text-xs font-semibold transition-all <?= ($filterStatus === 'aman') ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-200' ?>">
                Aman (<?= $countAman ?>)
            </a>
            <a href="<?= base_url('stok.php?filter=waspada') ?>" 
               class="px-4 py-2 rounded-xl text-xs font-semibold transition-all <?= ($filterStatus === 'waspada') ? 'bg-amber-500 text-white shadow-sm' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200' ?>">
                Waspada (<?= $countWaspada ?>)
            </a>
            <a href="<?= base_url('stok.php?filter=kritis') ?>" 
               class="px-4 py-2 rounded-xl text-xs font-semibold transition-all <?= ($filterStatus === 'kritis') ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-rose-700 hover:bg-rose-50 border border-rose-200' ?>">
                Kritis (<?= $countKritis ?>)
            </a>
        </div>

        <!-- Filter, Search, and Action Bar -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Left inputs -->
            <form method="GET" action="stok.php" class="flex flex-1 flex-wrap items-center gap-3 w-full md:w-auto">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filterStatus) ?>">
                
                <!-- Search input -->
                <div class="relative flex-1 min-w-[240px]">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input type="text" 
                           name="q" 
                           value="<?= htmlspecialchars($searchQuery) ?>" 
                           placeholder="Cari nama atau kode obat..." 
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                </div>

                <!-- Category Dropdown -->
                <select name="cat" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                    <option value="">Semua kategori</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($filterCategory == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <!-- Sort Dropdown -->
                <select name="sort" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                    <option value="code_asc" <?= ($sortBy === 'code_asc') ? 'selected' : '' ?>>Urutkan: Kode</option>
                    <option value="status" <?= ($sortBy === 'status') ? 'selected' : '' ?>>Urutkan: Status</option>
                    <option value="stock_asc" <?= ($sortBy === 'stock_asc') ? 'selected' : '' ?>>Stok Terendah</option>
                    <option value="stock_desc" <?= ($sortBy === 'stock_desc') ? 'selected' : '' ?>>Stok Tertinggi</option>
                    <option value="expiry_asc" <?= ($sortBy === 'expiry_asc') ? 'selected' : '' ?>>Kedaluwarsa Terdekat</option>
                </select>
            </form>

            <!-- Right Buttons -->
            <div class="flex items-center gap-2.5 w-full md:w-auto justify-end">
                <button type="button" onclick="openImportModal()" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span>Impor Excel</span>
                </button>
                <button type="button" onclick="openAddModal()" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#00a884] hover:bg-[#009272] text-white text-xs font-semibold shadow-sm transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Obat</span>
                </button>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3.5 px-4">Kode</th>
                            <th class="py-3.5 px-4">Nama Obat</th>
                            <th class="py-3.5 px-4">Kategori</th>
                            <th class="py-3.5 px-4">Satuan</th>
                            <th class="py-3.5 px-4">Stok</th>
                            <th class="py-3.5 px-4">Stok Minimum</th>
                            <th class="py-3.5 px-4">Kedaluwarsa Terdekat</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($medicines)): ?>
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400 font-medium">
                                Tidak ada data obat yang sesuai dengan filter.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($medicines as $med): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors group">
                                <td class="py-3.5 px-4 font-mono font-medium text-slate-600">
                                    <?= htmlspecialchars($med['code']) ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    <?= htmlspecialchars($med['name']) ?>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-600">
                                    <?= htmlspecialchars($med['category_name']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    <?= htmlspecialchars($med['dosage_form']) ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    <?= number_format($med['current_stock'], 0, ',', '.') ?>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-600">
                                    <?= number_format($med['minimum_stock'], 0, ',', '.') ?>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-600">
                                    <?= format_month_short($med['nearest_expiry']) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?= get_status_badge($med['status']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2 font-medium">
                                    <button type="button" 
                                            onclick='openDetailModal(<?= json_encode($med) ?>)' 
                                            class="text-teal-600 hover:text-teal-800 hover:underline">
                                        Detail
                                    </button>
                                    <button type="button" 
                                            onclick='openEditModal(<?= json_encode($med) ?>)' 
                                            class="text-slate-600 hover:text-slate-900 hover:underline">
                                        Ubah
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer & Pagination (Matching Screenshot 5) -->
            <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <div>
                    Menampilkan 1–<?= count($medicines) ?> dari <?= $countSemua ?> item
                </div>

                <div class="flex items-center gap-1.5 font-medium">
                    <button class="w-8 h-8 rounded-full bg-[#00a884] text-white flex items-center justify-center font-bold">1</button>
                    <button class="w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 flex items-center justify-center">2</button>
                    <button class="w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 flex items-center justify-center">3</button>
                    <span class="px-1 text-slate-400">...</span>
                    <button class="w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 flex items-center justify-center">129</button>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- Modal: Tambah Obat -->
<div id="addModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Tambah Obat Baru</h3>
            <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form method="POST" action="stok.php" class="space-y-4 mt-4 text-xs">
            <input type="hidden" name="action" value="add">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Obat</label>
                    <input type="text" name="code" required placeholder="OBT-0016" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kategori</label>
                    <select name="category_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none">
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Obat & Dosis</label>
                <input type="text" name="name" required placeholder="Contoh: Ibuprofen 400 mg" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Bentuk Sediaan</label>
                    <select name="dosage_form" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                        <option value="Tablet">Tablet</option>
                        <option value="Kapsul">Kapsul</option>
                        <option value="Kaplet">Kaplet</option>
                        <option value="Sirup">Sirup</option>
                        <option value="Sachet">Sachet</option>
                        <option value="Unit">Unit / Inhaler</option>
                        <option value="Botol">Botol / Infus</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Harga Satuan (Rp)</label>
                    <input type="number" name="unit_price" required value="750" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kedaluwarsa</label>
                    <input type="date" name="nearest_expiry" value="2027-12-31" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Stok Saat Ini</label>
                    <input type="number" name="current_stock" required value="1000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Stok Minimum</label>
                    <input type="number" name="minimum_stock" required value="800" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Lead Time (hari)</label>
                    <input type="number" name="lead_time_days" required value="7" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#00a884] hover:bg-[#009272] text-white font-semibold shadow-sm">Simpan Obat</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Ubah Obat -->
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Ubah Data Obat</h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form method="POST" action="stok.php" class="space-y-4 mt-4 text-xs">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Obat</label>
                    <input type="text" name="code" id="edit_code" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kategori</label>
                    <select name="category_id" id="edit_cat_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Obat & Dosis</label>
                <input type="text" name="name" id="edit_name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Bentuk Sediaan</label>
                    <select name="dosage_form" id="edit_dosage_form" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                        <option value="Tablet">Tablet</option>
                        <option value="Kapsul">Kapsul</option>
                        <option value="Kaplet">Kaplet</option>
                        <option value="Sirup">Sirup</option>
                        <option value="Sachet">Sachet</option>
                        <option value="Unit">Unit / Inhaler</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Harga Satuan (Rp)</label>
                    <input type="number" name="unit_price" id="edit_unit_price" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kedaluwarsa Terdekat</label>
                    <input type="date" name="nearest_expiry" id="edit_expiry" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Stok Saat Ini</label>
                    <input type="number" name="current_stock" id="edit_current_stock" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Stok Minimum</label>
                    <input type="number" name="minimum_stock" id="edit_minimum_stock" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Safety Stock</label>
                    <input type="number" name="safety_stock" id="edit_safety_stock" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                    <input type="hidden" name="lead_time_days" id="edit_lead_time" value="7">
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button type="button" onclick="confirmDelete()" class="text-rose-600 font-semibold hover:underline">Hapus Obat</button>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#00a884] hover:bg-[#009272] text-white font-semibold shadow-sm">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Detail Obat -->
<div id="detailModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-800" id="detail_name">Detail Obat</h3>
                <p class="text-xs text-slate-500 font-mono" id="detail_code"></p>
            </div>
            <button onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <div class="space-y-4 mt-4 text-xs">
            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl">
                <div>
                    <p class="text-slate-500 font-medium">Kategori</p>
                    <p class="font-bold text-slate-800 text-sm mt-0.5" id="detail_category"></p>
                </div>
                <div>
                    <p class="text-slate-500 font-medium">Bentuk Sediaan</p>
                    <p class="font-bold text-slate-800 text-sm mt-0.5" id="detail_form"></p>
                </div>
                <div>
                    <p class="text-slate-500 font-medium">Stok Saat Ini</p>
                    <p class="font-bold text-slate-800 text-sm mt-0.5" id="detail_stock"></p>
                </div>
                <div>
                    <p class="text-slate-500 font-medium">Stok Minimum</p>
                    <p class="font-bold text-slate-800 text-sm mt-0.5" id="detail_min_stock"></p>
                </div>
                <div>
                    <p class="text-slate-500 font-medium">Estimasi Harga Satuan</p>
                    <p class="font-bold text-teal-700 text-sm mt-0.5" id="detail_price"></p>
                </div>
                <div>
                    <p class="text-slate-500 font-medium">Status Ketersediaan</p>
                    <p class="mt-0.5" id="detail_status_badge"></p>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-900">
                <p class="font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline></svg>
                    Akses Cepat Prediksi:
                </p>
                <p class="mt-1 text-slate-700">Analisis tren pemakaian masa lalu dan proyeksikan kebutuhan obat ini secara otomatis.</p>
                <div class="mt-2 text-right">
                    <a id="detail_pred_link" href="#" class="inline-flex items-center gap-1 text-xs font-bold text-[#00a884] hover:underline">
                        Jalankan Model Prediksi &rarr;
                    </a>
                </div>
            </div>

            <div class="text-right pt-2">
                <button type="button" onclick="closeDetailModal()" class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Impor Excel -->
<div id="importModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Impor Data Obat (.xlsx / .csv)</h3>
            <button onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form method="POST" action="stok.php" enctype="multipart/form-data" class="space-y-4 mt-4 text-xs">
            <input type="hidden" name="action" value="import">
            
            <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-teal-500 transition-colors">
                <svg class="w-10 h-10 mx-auto text-slate-400 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                <p class="font-semibold text-slate-700">Pilih berkas Excel atau tarik ke sini</p>
                <p class="text-slate-400 text-[11px] mt-0.5">Mendukung format .xlsx, .xls, .csv hingga 10MB</p>
                <input type="file" name="file_excel" accept=".xlsx,.xls,.csv" class="mt-4 text-xs text-slate-600">
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="#" class="text-teal-600 font-semibold hover:underline">Unduh Template Excel</a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#00a884] hover:bg-[#009272] text-white font-semibold shadow-sm">
                    Mulai Impor
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Scripts -->
<script>
function openAddModal() { document.getElementById('addModal').classList.remove('hidden'); }
function closeAddModal() { document.getElementById('addModal').classList.add('hidden'); }

function openImportModal() { document.getElementById('importModal').classList.remove('hidden'); }
function closeImportModal() { document.getElementById('importModal').classList.add('hidden'); }

function openEditModal(med) {
    document.getElementById('edit_id').value = med.id;
    document.getElementById('edit_code').value = med.code;
    document.getElementById('edit_name').value = med.name;
    document.getElementById('edit_cat_id').value = med.category_id;
    document.getElementById('edit_dosage_form').value = med.dosage_form;
    document.getElementById('edit_unit_price').value = med.unit_price;
    document.getElementById('edit_current_stock').value = med.current_stock;
    document.getElementById('edit_minimum_stock').value = med.minimum_stock;
    document.getElementById('edit_safety_stock').value = med.safety_stock;
    document.getElementById('edit_expiry').value = med.nearest_expiry;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); }

function openDetailModal(med) {
    document.getElementById('detail_name').textContent = med.name;
    document.getElementById('detail_code').textContent = med.code;
    document.getElementById('detail_category').textContent = med.category_name;
    document.getElementById('detail_form').textContent = med.dosage_form;
    document.getElementById('detail_stock').textContent = new Intl.NumberFormat('id-ID').format(med.current_stock) + ' ' + med.dosage_form;
    document.getElementById('detail_min_stock').textContent = new Intl.NumberFormat('id-ID').format(med.minimum_stock) + ' ' + med.dosage_form;
    document.getElementById('detail_price').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(med.unit_price);
    
    let badge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Aman</span>';
    if (med.status.toLowerCase() === 'kritis') {
        badge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Kritis</span>';
    } else if (med.status.toLowerCase() === 'waspada') {
        badge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Waspada</span>';
    }
    document.getElementById('detail_status_badge').innerHTML = badge;
    document.getElementById('detail_pred_link').href = 'prediksi.php?med_id=' + med.id;
    document.getElementById('detailModal').classList.remove('hidden');
}
function closeDetailModal() { document.getElementById('detailModal').classList.add('hidden'); }

function confirmDelete() {
    if (confirm('Apakah Anda yakin ingin menghapus data obat ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'stok.php';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + document.getElementById('edit_id').value + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

