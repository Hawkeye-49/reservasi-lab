# SiResLab - Sistem Reservasi Laboratorium Komputer

**Sistem Reservasi Laboratorium Komputer** adalah aplikasi web yang memudahkan dosen untuk melakukan reservasi ruangan laboratorium, dan admin untuk mengelola seluruh proses reservasi.

---

## Fitur Utama

### Admin

- **Dashboard** - Monitoring reservasi dan statistik
- **Manajemen Dosen** - Tambah, edit, hapus data dosen
- **Manajemen Mata Kuliah** - Kelola data mata kuliah
- **Manajemen Kelas** - Data kelas dan semester
- **Manajemen Ruangan** - Data ruangan lab beserta fasilitas
- **Verifikasi Reservasi** - Terima/tolak permintaan reservasi
- **Laporan Reservasi** - Melihat dan Cetak laporan reservasi per periode

### Dosen

- **Dashboard** - Ringkasan reservasi pribadi
- **Reservasi Ruangan** - Buat permintaan reservasi baru
- **Lihat Jadwal** - Melihat jadwal ruangan lab
- **Riwayat Reservasi** - History permintaan reservasi
- **Update Profil** - Perbarui data diri

---

## Persyaratan Sistem

### Recommended:

- PHP 8.0+
- MySQL 8.0+
- Laragon atau XAMPP (untuk development)

### Perangkat yang Diperlukan:

- Windows/Linux/macOS
- RAM minimal 2GB
- Koneksi internet stabil

---

## Instalasi

### **Opsi 1: Instalasi di XAMPP** (Windows/Linux/Mac)

#### Langkah 1: Download & Extract Project

1. **Download** file ZIP project ini
2. **Extract** ke folder:
   ```
   C:\xampp\htdocs\reservasi-lab  (Windows)
   /opt/lampp/htdocs/reservasi-lab  (Linux)
   /Applications/XAMPP/htdocs/reservasi-lab  (Mac)
   ```
3. Buat folder jika belum ada

#### Langkah 2: Jalankan XAMPP

**Windows:**

1. Buka **XAMPP Control Panel**
2. Klik **Start** untuk Apache
3. Klik **Start** untuk MySQL
4. Pastikan kedua layanan berjalan (hijau)

**Linux:**

```bash
sudo /opt/lampp/manager-linux.run
# atau via terminal:
sudo /opt/lampp/lampp start
```

**Mac:**

```bash
sudo /Applications/XAMPP/xamppfiles/bin/apachectl start
```

#### Langkah 3: Buat & Import Database

1. Buka **phpMyAdmin**: http://localhost/phpmyadmin
2. Login dengan:
   - Username: `root`
   - Password: _(kosong)_

3. **Buat Database Baru:**
   - Klik tab **Databases**
   - Isi nama database: `reservasi_lab`
   - Character set: `utf8mb4_unicode_ci`
   - Klik **Create**

4. **Import File SQL:**
   - Pilih database `reservasi_lab`
   - Klik tab **Import**
   - Klik **Choose File** → Pilih `database.sql` yang ada di folder project yang sudah diekstrak
   - Klik **Import**

5. Tunggu hingga proses selesai

---

### **Opsi 2: Instalasi di Laragon** (Windows)

#### Langkah 1: Download & Extract Project

1. **Download** file ZIP project
2. **Extract** ke folder:
   ```
   C:\laragon\www\reservasi-lab
   ```
   _(Sesuaikan path instalasi Laragon Anda)_

#### Langkah 2: Jalankan Laragon

1. Buka **Laragon**
2. Klik **Start All** untuk memulai Apache & MySQL
3. Status akan menunjukkan "Running"

#### Langkah 3: Buat & Import Database

1. Klik **Database** di laragon, lalu **Open**

2. Klik **Laragon.MySQL** di sidebar kiri, lalu klik menu **File** -> **Run SQL file...**
   - Pilih database `database.sql` yang ada di folder project yang sudah diekstrak
   - Klik **Yes**

---

### **Konfigurasi Database**

Database `reservasi_lab` akan otomatis membuat tabel berikut:

| Tabel        | Deskripsi                        |
| ------------ | -------------------------------- |
| `admin`      | Data administrator system        |
| `dosen`      | Data dosen dan akun login        |
| `matakuliah` | Data mata kuliah                 |
| `kelas`      | Data kelas                       |
| `ruangan`    | Data ruangan laboratorium        |
| `slot_waktu` | Jadwal time slot untuk reservasi |
| `reservasi`  | Data reservasi ruangan           |

---

## Cara Menjalankan

### Via XAMPP

```
http://localhost/reservasi-lab/
```

Jika XAMPP installed di path berbeda, sesuaikan URL:

```
http://localhost/[folder-name]/
```

### Via Laragon

```
http://localhost/reservasi-lab/
```

---

## Panduan Penggunaan

### Login Admin

#### Akses Login Admin

1. Buka aplikasi di browser: `http://localhost/reservasi-lab/`
2. Klik tombol **Admin** di navbar halaman utama
3. URL: `http://localhost/reservasi-lab/admin/login.php`

#### Pertama Kali Setup (Registrasi Admin)

Jika belum ada akun admin, lakukan registrasi:

1. Di halaman login admin, klik **Register Admin** di bagian bawah
2. Isi form (contoh):
   - **Nama**: `Admin` (minimal 5 karakter)
   - **Email**: `admin@lab.id`
   - **Username**: `admin`
   - **Password**: `admin123` (minimal 6 karakter)
   - **Konfirmasi Password**: Sama dengan Password
3. Klik **Buat Akun Admin**
4. Setelah berhasil, login dengan username & password yang tadi didaftarkan
