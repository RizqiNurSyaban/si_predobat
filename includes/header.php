<?php
// includes/header.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth.php';

// Auto seed check
runAutoSeedIfEmpty();

function runAutoSeedIfEmpty() {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        $db = getDBConnection();
        $res = $db->query("SHOW TABLES LIKE 'medicines'");
        if ($res->rowCount() === 0) {
            require_once __DIR__ . '/../database/seed.php';
            runMigrationAndSeed();
        }
    } catch (Exception $e) {
        // Ignored
    }
}

// Current logged in user
$currentUser = current_user();
if (!$currentUser && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    require_auth();
}

$pageTitle = $pageTitle ?? 'SI-PREDOBAT - Sistem Informasi Prediksi Kebutuhan Obat';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                            950: '#042f2e',
                        },
                        sidebar: {
                            bg: '#082f32',
                            card: '#0f3a3d',
                            hover: '#0c3538',
                            active: '#00a884',
                            text: '#94a3b8',
                            activeText: '#ffffff'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="h-full flex overflow-hidden text-slate-800 antialiased selection:bg-teal-500 selection:text-white">

