<?php
// database/seed.php
require_once __DIR__ . '/../config/database.php';

function runMigrationAndSeed(bool $forceReset = false) {
    $db = getDBConnection();
    
    // Check if tables exist
    $check = $db->query("SHOW TABLES LIKE 'medicines'");
    $tablesExist = $check->rowCount() > 0;

    if ($forceReset || !$tablesExist) {
        $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
        $db->exec($schemaSql);
    }

    // Check if data is already seeded
    $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount > 0 && !$forceReset) {
        return; // Already seeded
    }

    // Disable foreign key checks for clean seed
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $db->exec("TRUNCATE TABLE procurement_order_items;");
    $db->exec("TRUNCATE TABLE procurement_orders;");
    $db->exec("TRUNCATE TABLE monthly_usage;");
    $db->exec("TRUNCATE TABLE medicine_batches;");
    $db->exec("TRUNCATE TABLE medicines;");
    $db->exec("TRUNCATE TABLE categories;");
    $db->exec("TRUNCATE TABLE users;");
    $db->exec("TRUNCATE TABLE system_settings;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 1. Seed Users
    $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
    $users = [
        [
            'name' => 'Apt. Anisa Fitri, S.Farm.',
            'username' => 'anisa.fitri',
            'email' => 'anisa.fitri@rsud-sehat.id',
            'password' => $passwordHash,
            'role' => 'Apoteker Penanggung Jawab',
            'avatar_initials' => 'AF',
            'is_active' => 1
        ],
        [
            'name' => 'Budi Santoso, A.Md.Farm.',
            'username' => 'budi.santoso',
            'email' => 'budi.santoso@rsud-sehat.id',
            'password' => $passwordHash,
            'role' => 'Asisten Apoteker',
            'avatar_initials' => 'BS',
            'is_active' => 1
        ],
        [
            'name' => 'dr. Hendra Kusuma, Sp.PK',
            'username' => 'dr.hendra',
            'email' => 'dr.hendra@rsud-sehat.id',
            'password' => $passwordHash,
            'role' => 'Kepala Instalasi Farmasi',
            'avatar_initials' => 'HK',
            'is_active' => 1
        ]
    ];
    $stmtUser = $db->prepare("INSERT INTO users (name, username, email, password, role, avatar_initials, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($users as $u) {
        $stmtUser->execute(array_values($u));
    }

    // 2. Seed Categories
    $categories = [
        ['CAT-01', 'Analgesik & Antipiretik', 92, 'teal'],
        ['CAT-02', 'Antibiotik', 74, 'blue'],
        ['CAT-03', 'Antidiabetes', 61, 'amber'],
        ['CAT-04', 'Antihipertensi', 48, 'purple'],
        ['CAT-05', 'Saluran Cerna', 35, 'rose'],
        ['CAT-06', 'Antihistamin', 80, 'sky'],
        ['CAT-07', 'Antikolesterol', 70, 'indigo'],
        ['CAT-08', 'Pernapasan', 65, 'teal'],
        ['CAT-09', 'Suplemen & Vitamin', 88, 'emerald'],
        ['CAT-10', 'Rehidrasi / Cairan', 50, 'cyan'],
    ];
    $stmtCat = $db->prepare("INSERT INTO categories (code, name, target_stock_pct, color_class) VALUES (?, ?, ?, ?)");
    $catMap = [];
    foreach ($categories as $cat) {
        $stmtCat->execute($cat);
        $catMap[$cat[1]] = $db->lastInsertId();
    }

    // 3. Seed Medicines
    $medicines = [
        [
            'code' => 'OBT-0001',
            'name' => 'Paracetamol 500 mg',
            'category_name' => 'Analgesik & Antipiretik',
            'dosage_form' => 'Tablet',
            'unit_price' => 450,
            'current_stock' => 1240,
            'minimum_stock' => 1500,
            'safety_stock' => 500,
            'lead_time_days' => 7,
            'nearest_expiry' => '2027-03-15',
            'status' => 'Kritis',
            'predicted_demand_next' => 5420,
            'recommended_order_qty' => 4180,
            'priority' => 'Tinggi',
            'order_before' => '2026-11-20'
        ],
        [
            'code' => 'OBT-0002',
            'name' => 'Amoxicillin 500 mg',
            'category_name' => 'Antibiotik',
            'dosage_form' => 'Kapsul',
            'unit_price' => 1250,
            'current_stock' => 860,
            'minimum_stock' => 1000,
            'safety_stock' => 300,
            'lead_time_days' => 5,
            'nearest_expiry' => '2027-08-20',
            'status' => 'Waspada',
            'predicted_demand_next' => 2750,
            'recommended_order_qty' => 1890,
            'priority' => 'Sedang',
            'order_before' => '2026-11-28'
        ],
        [
            'code' => 'OBT-0003',
            'name' => 'Metformin 500 mg',
            'category_name' => 'Antidiabetes',
            'dosage_form' => 'Tablet',
            'unit_price' => 600,
            'current_stock' => 1520,
            'minimum_stock' => 1000,
            'safety_stock' => 400,
            'lead_time_days' => 7,
            'nearest_expiry' => '2028-01-10',
            'status' => 'Aman',
            'predicted_demand_next' => 1490,
            'recommended_order_qty' => 0,
            'priority' => 'Rendah',
            'order_before' => null
        ],
        [
            'code' => 'OBT-0004',
            'name' => 'Amlodipine 5 mg',
            'category_name' => 'Antihipertensi',
            'dosage_form' => 'Tablet',
            'unit_price' => 800,
            'current_stock' => 1380,
            'minimum_stock' => 900,
            'safety_stock' => 350,
            'lead_time_days' => 7,
            'nearest_expiry' => '2027-10-25',
            'status' => 'Aman',
            'predicted_demand_next' => 1350,
            'recommended_order_qty' => 0,
            'priority' => 'Rendah',
            'order_before' => null
        ],
        [
            'code' => 'OBT-0005',
            'name' => 'Omeprazole 20 mg',
            'category_name' => 'Saluran Cerna',
            'dosage_form' => 'Kapsul',
            'unit_price' => 1800,
            'current_stock' => 410,
            'minimum_stock' => 800,
            'safety_stock' => 250,
            'lead_time_days' => 7,
            'nearest_expiry' => '2026-12-15', // Near expiry warning FEFO
            'status' => 'Kritis',
            'predicted_demand_next' => 1960,
            'recommended_order_qty' => 1550,
            'priority' => 'Tinggi',
            'order_before' => '2026-11-22'
        ],
        [
            'code' => 'OBT-0006',
            'name' => 'Cetirizine 10 mg',
            'category_name' => 'Antihistamin',
            'dosage_form' => 'Tablet',
            'unit_price' => 950,
            'current_stock' => 730,
            'minimum_stock' => 600,
            'safety_stock' => 200,
            'lead_time_days' => 5,
            'nearest_expiry' => '2027-06-30',
            'status' => 'Aman',
            'predicted_demand_next' => 710,
            'recommended_order_qty' => 0,
            'priority' => 'Rendah',
            'order_before' => null
        ],
        [
            'code' => 'OBT-0007',
            'name' => 'Simvastatin 20 mg',
            'category_name' => 'Antikolesterol',
            'dosage_form' => 'Tablet',
            'unit_price' => 1400,
            'current_stock' => 520,
            'minimum_stock' => 650,
            'safety_stock' => 200,
            'lead_time_days' => 6,
            'nearest_expiry' => '2027-02-18',
            'status' => 'Waspada',
            'predicted_demand_next' => 1210,
            'recommended_order_qty' => 690,
            'priority' => 'Sedang',
            'order_before' => '2026-11-30'
        ],
        [
            'code' => 'OBT-0008',
            'name' => 'Salbutamol Inhaler',
            'category_name' => 'Pernapasan',
            'dosage_form' => 'Unit',
            'unit_price' => 85000,
            'current_stock' => 95,
            'minimum_stock' => 150,
            'safety_stock' => 50,
            'lead_time_days' => 10,
            'nearest_expiry' => '2027-09-12',
            'status' => 'Waspada',
            'predicted_demand_next' => 260,
            'recommended_order_qty' => 165,
            'priority' => 'Sedang',
            'order_before' => '2026-11-30'
        ],
        [
            'code' => 'OBT-0009',
            'name' => 'Vitamin C 500 mg',
            'category_name' => 'Suplemen & Vitamin',
            'dosage_form' => 'Tablet',
            'unit_price' => 500,
            'current_stock' => 2100,
            'minimum_stock' => 1200,
            'safety_stock' => 400,
            'lead_time_days' => 4,
            'nearest_expiry' => '2028-05-20',
            'status' => 'Aman',
            'predicted_demand_next' => 1900,
            'recommended_order_qty' => 0,
            'priority' => 'Rendah',
            'order_before' => null
        ],
        [
            'code' => 'OBT-0010',
            'name' => 'Oralit (ORS)',
            'category_name' => 'Rehidrasi / Cairan',
            'dosage_form' => 'Sachet',
            'unit_price' => 1100,
            'current_stock' => 340,
            'minimum_stock' => 700,
            'safety_stock' => 250,
            'lead_time_days' => 5,
            'nearest_expiry' => '2027-01-15',
            'status' => 'Kritis',
            'predicted_demand_next' => 1380,
            'recommended_order_qty' => 1040,
            'priority' => 'Tinggi',
            'order_before' => '2026-11-22'
        ],
        // Additional medicines for realistic clinical catalog
        [
            'code' => 'OBT-0011',
            'name' => 'Ciprofloxacin 500 mg',
            'category_name' => 'Antibiotik',
            'dosage_form' => 'Tablet',
            'unit_price' => 1750,
            'current_stock' => 450,
            'minimum_stock' => 600,
            'safety_stock' => 150,
            'lead_time_days' => 7,
            'nearest_expiry' => '2027-07-14',
            'status' => 'Waspada',
            'predicted_demand_next' => 920,
            'recommended_order_qty' => 470,
            'priority' => 'Sedang',
            'order_before' => '2026-11-25'
        ],
        [
            'code' => 'OBT-0012',
            'name' => 'Antasida Doen',
            'category_name' => 'Saluran Cerna',
            'dosage_form' => 'Tablet',
            'unit_price' => 350,
            'current_stock' => 380,
            'minimum_stock' => 1100,
            'safety_stock' => 300,
            'lead_time_days' => 5,
            'nearest_expiry' => '2026-12-28', // Near expiry
            'status' => 'Kritis',
            'predicted_demand_next' => 1800,
            'recommended_order_qty' => 1420,
            'priority' => 'Tinggi',
            'order_before' => '2026-11-18'
        ],
        [
            'code' => 'OBT-0013',
            'name' => 'Asam Mefenamat 500 mg',
            'category_name' => 'Analgesik & Antipiretik',
            'dosage_form' => 'Kaplet',
            'unit_price' => 650,
            'current_stock' => 1100,
            'minimum_stock' => 1000,
            'safety_stock' => 300,
            'lead_time_days' => 6,
            'nearest_expiry' => '2027-11-10',
            'status' => 'Aman',
            'predicted_demand_next' => 1250,
            'recommended_order_qty' => 150,
            'priority' => 'Rendah',
            'order_before' => null
        ],
        [
            'code' => 'OBT-0014',
            'name' => 'Glimepiride 2 mg',
            'category_name' => 'Antidiabetes',
            'dosage_form' => 'Tablet',
            'unit_price' => 1100,
            'current_stock' => 620,
            'minimum_stock' => 800,
            'safety_stock' => 200,
            'lead_time_days' => 7,
            'nearest_expiry' => '2027-04-18',
            'status' => 'Waspada',
            'predicted_demand_next' => 950,
            'recommended_order_qty' => 330,
            'priority' => 'Sedang',
            'order_before' => '2026-11-26'
        ],
        [
            'code' => 'OBT-0015',
            'name' => 'Candesartan 8 mg',
            'category_name' => 'Antihipertensi',
            'dosage_form' => 'Tablet',
            'unit_price' => 3200,
            'current_stock' => 490,
            'minimum_stock' => 750,
            'safety_stock' => 200,
            'lead_time_days' => 8,
            'nearest_expiry' => '2027-05-15',
            'status' => 'Waspada',
            'predicted_demand_next' => 880,
            'recommended_order_qty' => 390,
            'priority' => 'Sedang',
            'order_before' => '2026-11-24'
        ]
    ];

    $stmtMed = $db->prepare("INSERT INTO medicines 
        (code, name, category_id, dosage_form, unit_price, current_stock, minimum_stock, safety_stock, lead_time_days, nearest_expiry, status, predicted_demand_next, recommended_order_qty, priority, order_before)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $medMap = [];
    foreach ($medicines as $m) {
        $catId = $catMap[$m['category_name']] ?? 1;
        $stmtMed->execute([
            $m['code'],
            $m['name'],
            $catId,
            $m['dosage_form'],
            $m['unit_price'],
            $m['current_stock'],
            $m['minimum_stock'],
            $m['safety_stock'],
            $m['lead_time_days'],
            $m['nearest_expiry'],
            $m['status'],
            $m['predicted_demand_next'],
            $m['recommended_order_qty'],
            $m['priority'],
            $m['order_before']
        ]);
        $medMap[$m['code']] = $db->lastInsertId();
    }

    // 4. Seed Batches (including near-expiry for warning)
    $batches = [
        [$medMap['OBT-0005'], 'OM-2211', 300, '2026-12-15', '2024-12-15', 'PBF Medika Jaya', 'Hampir Kedaluwarsa'],
        [$medMap['OBT-0005'], 'OM-2212', 110, '2027-06-20', '2025-06-20', 'PBF Medika Jaya', 'Aktif'],
        [$medMap['OBT-0001'], 'PC-1082', 1240, '2027-03-15', '2025-03-15', 'Kimia Farma Trading', 'Aktif'],
        [$medMap['OBT-0002'], 'AM-9011', 860, '2027-08-20', '2025-08-20', 'PBF Medika Jaya', 'Aktif'],
        [$medMap['OBT-0010'], 'OR-4412', 340, '2027-01-15', '2025-01-15', 'PT Anugerah Pharmindo', 'Aktif'],
        [$medMap['OBT-0012'], 'AD-1109', 380, '2026-12-28', '2024-12-28', 'Kimia Farma Trading', 'Hampir Kedaluwarsa'],
    ];
    $stmtBatch = $db->prepare("INSERT INTO medicine_batches (medicine_id, batch_number, stock_qty, expiry_date, received_date, supplier, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($batches as $b) {
        $stmtBatch->execute($b);
    }

    // 5. Seed Monthly Usage (Historical data: May 2026 - Oct 2026)
    // For Paracetamol matching Screenshot 4:
    // Mei: 4100, Jun: 4300, Jul: 4150, Agu: 4600, Sep: 4800, Okt: 4980
    $monthsData = [
        ['year' => 2026, 'month' => 5, 'label' => 'Mei 2026'],
        ['year' => 2026, 'month' => 6, 'label' => 'Jun 2026'],
        ['year' => 2026, 'month' => 7, 'label' => 'Jul 2026'],
        ['year' => 2026, 'month' => 8, 'label' => 'Agu 2026'],
        ['year' => 2026, 'month' => 9, 'label' => 'Sep 2026'],
        ['year' => 2026, 'month' => 10, 'label' => 'Okt 2026'],
    ];

    $usageMatrix = [
        'OBT-0001' => [4100, 4300, 4150, 4600, 4800, 4980], // Paracetamol
        'OBT-0002' => [2100, 2250, 2050, 2400, 2550, 2680], // Amoxicillin
        'OBT-0003' => [1350, 1400, 1380, 1420, 1460, 1490], // Metformin
        'OBT-0004' => [1200, 1250, 1220, 1280, 1310, 1340], // Amlodipine
        'OBT-0005' => [1500, 1620, 1580, 1750, 1820, 1910], // Omeprazole
        'OBT-0006' => [620, 650, 610, 670, 690, 710],       // Cetirizine
        'OBT-0007' => [980, 1040, 1010, 1100, 1150, 1190],  // Simvastatin
        'OBT-0008' => [190, 210, 185, 230, 245, 255],       // Salbutamol
        'OBT-0009' => [1600, 1700, 1650, 1800, 1850, 1900], // Vit C
        'OBT-0010' => [1050, 1120, 1080, 1200, 1260, 1340], // Oralit
        'OBT-0011' => [750, 800, 780, 850, 890, 910],       // Cipro
        'OBT-0012' => [1400, 1500, 1450, 1600, 1700, 1780], // Antasida
        'OBT-0013' => [1020, 1080, 1050, 1150, 1200, 1240], // Asam Mefenamat
        'OBT-0014' => [780, 820, 800, 870, 910, 940],       // Glimepiride
        'OBT-0015' => [720, 760, 740, 810, 850, 875],       // Candesartan
    ];

    $stmtUsage = $db->prepare("INSERT INTO monthly_usage (medicine_id, year, month, period_label, actual_usage, visits_count, disease_season_flag) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($usageMatrix as $code => $values) {
        $medId = $medMap[$code] ?? null;
        if (!$medId) continue;
        foreach ($values as $idx => $actual) {
            $mMeta = $monthsData[$idx];
            $visits = 1200 + ($idx * 80) + rand(-30, 40);
            $seasonFlag = ($mMeta['month'] >= 9) ? 1 : 0; // Rainy/flu season starting September/October
            $stmtUsage->execute([
                $medId,
                $mMeta['year'],
                $mMeta['month'],
                $mMeta['label'],
                $actual,
                $visits,
                $seasonFlag
            ]);
        }
    }

    // 6. Seed Procurement Order (Draft order matching screenshot 3)
    // 6 items selected: Paracetamol, Omeprazole, Oralit, Amoxicillin, Simvastatin, Salbutamol
    // Total items: 6, Total units: 9.515, Estimasi: Rp 48.730.000, Pemasok: PBF Medika Jaya
    $poStmt = $db->prepare("INSERT INTO procurement_orders 
        (order_code, order_date, target_period, supplier_name, total_items, total_units, total_cost, notes, status, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $poStmt->execute([
        'PO-202611-001',
        '2026-11-05',
        'Des 2026',
        'PBF Medika Jaya',
        6,
        9515,
        48730000,
        'Prioritaskan Paracetamol, stok kritis menjelang musim hujan.',
        'Diajukan',
        1
    ]);
    $orderId = $db->lastInsertId();

    $poItems = [
        [$orderId, $medMap['OBT-0001'], 1240, 5420, 4180, 450, 1881000, 'Tinggi', '2026-11-20'],
        [$orderId, $medMap['OBT-0005'], 410, 1960, 1550, 1800, 2790000, 'Tinggi', '2026-11-22'],
        [$orderId, $medMap['OBT-0010'], 340, 1380, 1040, 1100, 1144000, 'Tinggi', '2026-11-22'],
        [$orderId, $medMap['OBT-0002'], 860, 2750, 1890, 1250, 2362500, 'Sedang', '2026-11-28'],
        [$orderId, $medMap['OBT-0007'], 520, 1210, 690, 1400, 966000, 'Sedang', '2026-11-30'],
        [$orderId, $medMap['OBT-0008'], 95, 260, 165, 85000, 14025000, 'Sedang', '2026-11-30'],
    ];
    $stmtPoItem = $db->prepare("INSERT INTO procurement_order_items 
        (order_id, medicine_id, current_stock, predicted_demand, ordered_qty, unit_price, subtotal, priority, order_before_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($poItems as $poi) {
        $stmtPoItem->execute($poi);
    }

    // 7. Seed System Settings
    $settings = [
        ['hospital_name', 'RSUD Sehat Sentosa', 'Nama Instansi / Rumah Sakit'],
        ['pharmacy_unit', 'Instalasi Farmasi RSUD Sehat Sentosa', 'Nama Unit Farmasi'],
        ['lead_time_default', '7', 'Default Lead Time pengadaan (hari)'],
        ['safety_stock_pct', '20', 'Persentase Buffer / Safety Stock (%)'],
        ['critical_threshold_days', '15', 'Batas hari sisa stok untuk status Kritis'],
        ['warning_threshold_days', '30', 'Batas hari sisa stok untuk status Waspada'],
        ['expiry_warning_days', '90', 'Peringatan dini masa kedaluwarsa (hari)'],
        ['default_prediction_method', 'moving_average_seasonal', 'Metode prediksi default'],
        ['smoothing_alpha', '0.4', 'Parameter alpha untuk Single Exponential Smoothing (0.1 - 0.9)'],
        ['app_version', '2.4.0-Enterprise', 'Versi SI-PREDOBAT'],
    ];
    $stmtSet = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
    foreach ($settings as $s) {
        $stmtSet->execute($s);
    }
}

