# Fitur Notifikasi Lonceng - Sistem DUMAS

## 📋 Deskripsi
Fitur notifikasi lonceng memungkinkan user untuk menerima pemberitahuan secara real-time ketika pengaduan mereka mendapat balasan dari admin/petugas.

## 🎯 Fitur Utama

### 1. **Tombol Lonceng Notifikasi**
- Terletak di navbar sebelah kiri tombol LOGOUT
- Menampilkan badge merah dengan jumlah notifikasi yang belum dibaca
- Animasi pulse pada badge untuk menarik perhatian
- Warna: Border emas (#FFD700) dengan efek hover

### 2. **Dropdown Notifikasi**
- Menampilkan 5 balasan terbaru
- Setiap notifikasi menampilkan:
  - Icon chat (bulat emas)
  - Judul laporan (maksimal 40 karakter)
  - Isi balasan (maksimal 60 karakter)
  - Timestamp balasan
- Link "Lihat Semua Laporan" di bagian bawah

### 3. **Penghitung Otomatis**
- Badge merah menampilkan jumlah balasan yang belum dibaca
- Counter diupdate otomatis saat user membuka halaman
- Status `is_read` untuk tracking notifikasi yang sudah dibaca

## 🗄️ Database Requirements

### Kolom Tambahan di `tabel_laporan`:

```sql
-- Kolom is_read (TINYINT)
is_read TINYINT(1) DEFAULT 0
-- 0 = belum dibaca
-- 1 = sudah dibaca

-- Kolom tanggal_balasan (DATETIME)
tanggal_balasan DATETIME NULL
-- Menyimpan waktu kapan balasan diberikan
```

### Cara Install:
```bash
# Import file SQL ke database
mysql -u root -p nama_database < database/add_notification_columns.sql
```

Atau jalankan manual di phpMyAdmin:
1. Buka file `database/add_notification_columns.sql`
2. Copy isi file
3. Paste di tab SQL phpMyAdmin
4. Klik "Go" untuk execute

## 🎨 Skema Warna

Menggunakan skema warna Bootstrap yang konsisten dengan navbar:
- **White** - Border tombol lonceng, text
- **#0d6efd** (Bootstrap Primary) - Header dropdown, icon background, text highlight
- **#dc3545** (Bootstrap Danger) - Badge notifikasi
- **#0d47a1** (Dark Blue) - Border badge

## 📱 Responsive Design

- Desktop: Dropdown lebar 380-400px
- Mobile: Dropdown lebar 320-350px
- Tombol notifikasi: 45x45px (bulat sempurna)

## 🔔 Cara Kerja

### Untuk User:
1. Login ke sistem
2. Tombol lonceng muncul di navbar
3. Jika ada balasan baru, badge merah akan muncul
4. Klik tombol lonceng untuk melihat notifikasi
5. Klik notifikasi untuk melihat detail laporan

### Untuk Admin:
Ketika admin memberikan balasan pada pengaduan:
1. Pastikan kolom `balasan` diisi
2. Set `tanggal_balasan` = NOW()
3. Set `is_read` = 0 (belum dibaca)
4. User akan otomatis menerima notifikasi

## 🔗 Integrasi

### File yang Dimodifikasi:
- `bar/navbar.php` - Tombol notifikasi dan dropdown

### Query Database:
```php
// Get unread count
SELECT COUNT(*) as total FROM tabel_laporan
WHERE user_id = ? AND status IN ('Diproses', 'Selesai')
AND balasan IS NOT NULL AND balasan != ''
AND is_read = 0

// Get recent notifications
SELECT id_laporan, judul_laporan, balasan, status, tanggal_balasan
FROM tabel_laporan
WHERE user_id = ? AND status IN ('Diproses', 'Selesai')
AND balasan IS NOT NULL AND balasan != ''
ORDER BY tanggal_balasan DESC LIMIT 5
```

## 🎯 TODO untuk Pengembangan Lebih Lanjut

### Fitur yang Bisa Ditambahkan:
1. **Mark as Read** - Tombol untuk menandai notifikasi sudah dibaca
2. **Real-time Update** - Menggunakan AJAX/WebSocket untuk update otomatis
3. **Notifikasi Push** - Browser notification API
4. **Filter Status** - Filter notifikasi berdasarkan status (Diproses/Selesai)
5. **Delete Notification** - Hapus notifikasi tertentu
6. **Sound Alert** - Suara ketika ada notifikasi baru
7. **Email Notification** - Kirim email ketika ada balasan

## 📝 Catatan Penting

1. **Session Requirement**: User harus login (`$_SESSION['user_id']` tersedia)
2. **Database Connection**: Pastikan `$db` connection tersedia di navbar
3. **Bootstrap 5**: Menggunakan Bootstrap 5 dropdown component
4. **Icons**: Menggunakan Bootstrap Icons (bi-bell-fill, bi-chat-left-text-fill, dll)

## 🐛 Troubleshooting

### Badge tidak muncul?
- Pastikan ada balasan yang belum dibaca di database
- Cek kolom `is_read` = 0
- Cek kolom `balasan` tidak NULL atau empty

### Dropdown tidak buka?
- Pastikan Bootstrap 5 JS sudah dimuat
- Cek console browser untuk error JavaScript
- Pastikan attribute `data-bs-toggle="dropdown"` ada

### Query error?
- Pastikan kolom `is_read` dan `tanggal_balasan` sudah ditambahkan
- Jalankan SQL script `add_notification_columns.sql`

## 👨‍💻 Developer Notes

Fitur ini menggunakan:
- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5.x
- Bootstrap Icons
- Prepared Statements (untuk security)

---

**Created with**: Claude Code
**Version**: 1.0
**Last Updated**: 2025
