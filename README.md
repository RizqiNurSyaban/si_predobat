# SI-PREDOBAT (Sistem Informasi Prediksi Kebutuhan Obat)
### Instalasi Farmasi RSUD Sehat Sentosa

Aplikasi web desktop modern untuk manajemen persediaan dan **prediksi kebutuhan obat farmasi rumah sakit**, dikembangkan menggunakan **PHP 8.3 Native**, **Tailwind CSS**, dan **MySQL**.

---

## 🚀 Fitur Utama & Kesesuaian Desain

1. **Autentikasi & Login (`login.php`)**
   - Tampilan split-screen dengan banner gradient deep-teal (`#082f32`) dan ring dekoratif.
   - Kartu metrik: *92,4% Akurasi model*, *10 Kategori obat terpantau*, *30 hari Cakupan peringatan dini*.
   - Tombol pengisian cepat (One-Click Demo Login) untuk akun Apoteker PJ dan Asisten Farmasi.

2. **Dashboard Eksekutif Farmasi (`index.php`)**
   - 4 Kartu Status Utama: Total Item Terpantau (1.284), Prediksi Kebutuhan Bulan Berjalan (5.180 unit), Obat Berisiko Kekurangan (17 item), dan Obat Mendekati Kedaluwarsa (9 item).
   - Grafik interaktif *Tren Pemakaian & Prediksi Kebutuhan* dengan pemisah visual *Periode Prediksi* (garis solid aktual vs garis putus-putus prediksi).
   - Indikator Ketersediaan per Kategori (Analgesik, Antibiotik, Antidiabetes, Antihipertensi, Saluran Cerna).
   - Tabel *Peringatan Stok Prioritas* (Paracetamol, Amoxicillin, Metformin, Omeprazole, Salbutamol) beserta status Kritis/Waspada/Aman.

3. **Prediksi Kebutuhan Obat Berbasis AI & Statistik (`prediksi.php`)**
   - **Parameter Prediksi**:
     - Pemilihan obat dan kategori otomatis.
     - 4 Metode Prediksi Teruji:
       1. *Moving Average + Tren Musiman*
       2. *Single Exponential Smoothing (SES)*
       3. *Regresi Linear (Linear Trend Forecasting)*
       4. *Metode Konsumsi Farmasi Standar Kemenkes RI* ($A = [B + C + D] - E$)
     - Faktor penyesuaian: Lonjakan musim penyakit, tren kunjungan rawat jalan/inap, program kesehatan.
     - Horizon proyeksi fleksibel (1, 3, hingga 6 bulan ke depan).
   - **Hasil & Evaluasi Model**:
     - Proyeksi volume per bulan dengan **Rentang Kepercayaan 95%** ($Lower – Upper Bound$).
     - Evaluasi akurasi menggunakan metrik **MAPE (Mean Absolute Percentage Error)**.
     - Rekomendasi kuantitas pengadaan cerdas dan batas tanggal pemesanan (*Order Before*).
     - Fitur Ekspor Data Prediksi ke format CSV/Excel.

4. **Data Stok & Pengendalian Persediaan (`stok.php`)**
   - Filter cepat berbasis status: *Semua (1.284)*, *Aman (1.102)*, *Waspada (165)*, *Kritis (17)*.
   - Pencarian instan dan penyaringan menurut kategori obat dan sediaan.
   - CRUD Obat lengkap (Tambah Obat, Ubah Data, Detail Batch & Lot, Hapus).
   - Modul Impor Excel/CSV untuk pembaharuan massal persediaan.

5. **Rekomendasi Pengadaan Cerdas (`pengadaan.php`)**
   - Daftar rekomendasi pemesanan otomatis dengan checkbox interaktif.
   - Kalkulasi real-time untuk total unit dipesan, estimasi anggaran (Rp), dan persentase penurunan risiko kekosongan stok.
   - Deteksi dini masa kedaluwarsa dengan peringatan **FEFO (First Expired, First Out)** (misal batch OM-2211 Omeprazole).
   - Pemilihan Pedagang Besar Farmasi (PBF) utama dan catatan khusus pengadaan.
   - Form pengajuan PO dan riwayat pengadaan.

6. **Cetak Surat Permintaan Pengadaan Obat / PDF (`cetak-pengadaan.php`)**
   - Format resmi KOP SURAT RSUD Sehat Sentosa.
   - Lengkap dengan rincian kebutuhan per item, satuan, harga, dan total anggaran.
   - Kolom tanda tangan resmi Apoteker Penanggung Jawab dan Kepala Instalasi Farmasi.

7. **Laporan & Analisis (`laporan.php`)**
   - Laporan rekapitulasi prediksi dan rencana pengadaan bulanan.
   - Laporan monitoring kedaluwarsa dan pengendalian FEFO.
   - Fitur Cetak Laporan dan Ekspor CSV.

8. **Manajemen Pengguna & Pengaturan Sistem (`pengguna.php` & `pengaturan.php`)**
   - Manajemen staf farmasi dan penugasan role (Apoteker PJ, Asisten Apoteker, Kepala Instalasi).
   - Konfigurasi parameter prediktif (Lead Time pengadaan, Buffer/Safety Stock %, Batas Hari Kritis, Smoothing Factor $\alpha$).
   - Fitur pemulihan/reset data standar.

---

## 🔑 Akun Pengguna Demo

| Nama Pengguna | Role | Email / Username | Kata Sandi |
| :--- | :--- | :--- | :--- |
| **Apt. Anisa Fitri, S.Farm.** | Apoteker Penanggung Jawab | `anisa.fitri@rsud-sehat.id` / `anisa.fitri` | `password123` |
| **Budi Santoso, A.Md.Farm.** | Asisten Apoteker | `budi.santoso@rsud-sehat.id` / `budi.santoso` | `password123` |
| **dr. Hendra Kusuma, Sp.PK** | Kepala Instalasi Farmasi | `dr.hendra@rsud-sehat.id` / `dr.hendra` | `password123` |

---

## 🛠️ Cara Menjalankan

### Melalui Laragon (Otomatis):
1. Pastikan Apache & MySQL di Laragon telah aktif.
2. Buka browser dan akses:
   ```
   http://localhost/si_predobat/
   ```
   *(Atau `http://si_predobat.test/` jika VirtualHost Laragon aktif)*.

### Melalui PHP Built-in Server:
Jika ingin menjalankan secara standalone di port lain:
```powershell
cd c:\laragon\www\si_predobat
& "C:\laragon\bin\php\php-8.3.36-1-nts-Win32-vs16-x64\php.exe" -S localhost:8080
```
Buka browser di `http://localhost:8080/`.

---

© 2026 SI-PREDOBAT · Instalasi Farmasi RSUD Sehat Sentosa

