<?php
// config/helpers.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get dynamic base URL for assets and routing
 */
function base_url(string $path = ''): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Check script directory
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($scriptName));
    
    // Normalize path to root of project
    // If script is in a subdirectory (e.g. /api or /includes), trim it
    $parts = explode('/', trim($dir, '/'));
    if (!empty($parts) && $parts[0] === 'si_predobat') {
        $base = '/si_predobat';
    } else {
        $base = ($dir === '/' || $dir === '.') ? '' : $dir;
    }
    
    $cleanPath = ltrim($path, '/');
    return $cleanPath ? rtrim($base, '/') . '/' . $cleanPath : rtrim($base, '/') . '/';
}

function redirect(string $path): void {
    header("Location: " . base_url($path));
    exit;
}

function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function format_number($val, int $decimals = 0): string {
    if (!is_numeric($val)) return '0';
    return number_format((float)$val, $decimals, ',', '.');
}

function format_rupiah($val): string {
    if (!is_numeric($val)) return 'Rp 0';
    return 'Rp ' . number_format((float)$val, 0, ',', '.');
}

function format_date_id(?string $dateStr, bool $withDay = false): string {
    if (!$dateStr) return '-';
    $time = strtotime($dateStr);
    if (!$time) return $dateStr;

    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $dayName = $days[date('w', $time)];
    $day = date('j', $time);
    $month = $months[(int)date('n', $time)];
    $year = date('Y', $time);

    if ($withDay) {
        return "{$dayName}, {$day} {$month} {$year}";
    }
    return "{$day} {$month} {$year}";
}

function format_month_short(?string $dateStr): string {
    if (!$dateStr) return '-';
    $time = strtotime($dateStr);
    if (!$time) return $dateStr;
    $shortMonths = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    return $shortMonths[(int)date('n', $time)] . ' ' . date('Y', $time);
}

function get_status_badge(string $status): string {
    $st = strtolower(trim($status));
    if ($st === 'kritis') {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">Kritis</span>';
    } elseif ($st === 'waspada') {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">Waspada</span>';
    } elseif ($st === 'aman') {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Aman</span>';
    }
    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">' . htmlspecialchars($status) . '</span>';
}

function get_priority_badge(string $priority): string {
    $pr = strtolower(trim($priority));
    if ($pr === 'tinggi') {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">Tinggi</span>';
    } elseif ($pr === 'sedang') {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">Sedang</span>';
    } elseif ($pr === 'rendah') {
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Rendah</span>';
    }
    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">' . htmlspecialchars($priority) . '</span>';
}

