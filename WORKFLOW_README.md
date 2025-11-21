# 📋 Sistem Workflow Multi-Aktor

## Gambaran Umum

Sistem ini mengimplementasikan workflow pengaduan dengan 3 aktor utama:
1. **Ditresnarkoba** (Aktor Pertama/Utama)
2. **Ditsamapta** (Aktor Kedua)
3. **Ditbinmas** (Aktor Kedua)

## 🔄 Alur Workflow

### Tahap 1: Laporan Masuk
```
Status: BARU
- Laporan dibuat oleh masyarakat
- Masuk ke dashboard Ditresnarkoba
```

### Tahap 2: Diproses Ditresnarkoba
```
Status: DIPROSES_DITRESNARKOBA
- Ditresnarkoba mengubah status menjadi "Diproses Ditresnarkoba"
- Sistem otomatis mengirim notifikasi ke Ditsamapta DAN Ditbinmas
- Kedua unit (Ditsamapta & Ditbinmas) dapat melihat notifikasi
```

### Tahap 3: Pemrosesan oleh Ditsamapta DAN/ATAU Ditbinmas
```
Status: DIPROSES_DITSAMAPTA atau DIPROSES_DITBINMAS (atau KEDUANYA)
- Setelah Ditresnarkoba set status "Diproses Ditresnarkoba"
- KEDUA unit (Ditsamapta & Ditbinmas) dapat melihat dan memproses laporan yang sama
- Ditsamapta dapat mengubah status → "Diproses Ditsamapta"
- Ditbinmas dapat mengubah status → "Diproses Ditbinmas"
- Kedua unit dapat bekerja secara paralel pada laporan yang sama
- Timeline akan mencatat semua perubahan status dari kedua unit
- PENTING: Setiap unit HARUS melalui tahap "diproses" sebelum bisa "selesai"
```

### Tahap 4: Penyelesaian
```
Ditsamapta menyelesaikan:
- Harus sudah berstatus "Diproses Ditsamapta" terlebih dahulu
- Baru bisa ubah ke → Status: SELESAI_DITSAMAPTA

Ditbinmas menyelesaikan:
- Harus sudah berstatus "Diproses Ditbinmas" terlebih dahulu
- Baru bisa ubah ke → Status: SELESAI_DITBINMAS

Ditresnarkoba menyelesaikan langsung:
- Status: SELESAI (tanpa perlu unit lain)

CATATAN: Tidak bisa langsung "selesai" tanpa melalui tahap "diproses" terlebih dahulu
```

## 📊 Timeline Tracking

Setiap perubahan status dicatat dalam kolom `timeline_json` dengan informasi:
- Status dari
- Status ke
- Diproses oleh (unit mana)
- Nama petugas
- Tanggapan/catatan
- Timestamp perubahan

### Contoh Timeline (Parallel Processing):
```
1. [BARU] Laporan Dibuat - 09 Nov 2025, 10:00
2. [DIPROSES] Diproses Ditresnarkoba - AKBP Aziz - 09 Nov 2025, 11:00
   Tanggapan: "Kami akan tindaklanjuti laporan ini"
3. [DIPROSES] Diproses Ditsamapta - Aiptu Rahmat - 09 Nov 2025, 12:00
   Tanggapan: "Sedang dilakukan penyelidikan"
4. [DIPROSES] Diproses Ditbinmas - Bripka Andi - 09 Nov 2025, 13:00
   Tanggapan: "Koordinasi dengan pihak terkait"
5. [SELESAI] Selesai Ditsamapta - Aiptu Rahmat - 10 Nov 2025, 09:00
   Tanggapan: "Kasus telah ditangani dari sisi Ditsamapta"
6. [SELESAI] Selesai Ditbinmas - Bripka Andi - 10 Nov 2025, 14:00
   Tanggapan: "Pembinaan masyarakat selesai dilakukan"
```

**Catatan:** Satu laporan bisa memiliki timeline dari KEDUA unit (Ditsamapta dan Ditbinmas)

## 🗄️ Setup Database

### 1. Jalankan SQL untuk update tabel_laporan:
```bash
mysql -u root -p ditresnarkoba < database/update_tabel_laporan_simple.sql
```

**ATAU** jalankan manual di phpMyAdmin:
```sql
ALTER TABLE `tabel_laporan`
ADD COLUMN `sedang_diproses_oleh` enum('','ditresnarkoba','ditsamapta','ditbinmas') DEFAULT '',
ADD COLUMN `timeline_json` TEXT DEFAULT NULL,
ADD COLUMN `is_notif_ditsamapta` tinyint(1) DEFAULT 0,
ADD COLUMN `is_notif_ditbinmas` tinyint(1) DEFAULT 0;
```

### 2. Kolom Baru di `tabel_laporan`:

**HANYA 4 KOLOM BARU!** Tidak ada tabel baru!

```sql
- sedang_diproses_oleh: enum('','ditresnarkoba','ditsamapta','ditbinmas')
  → Tracking unit mana yang sedang handle laporan

- timeline_json: TEXT
  → Menyimpan SEMUA history perubahan status dalam format JSON

- is_notif_ditsamapta: tinyint(1) [0 atau 1]
  → Flag notifikasi untuk Ditsamapta

- is_notif_ditbinmas: tinyint(1) [0 atau 1]
  → Flag notifikasi untuk Ditbinmas
```

### 3. Contoh Data `timeline_json`:
```json
[
  {
    "timestamp": "2025-11-19 10:00:00",
    "status_dari": "baru",
    "status_ke": "diproses_ditresnarkoba",
    "diproses_oleh": "ditresnarkoba",
    "nama_petugas": "AKBP Aziz",
    "tanggapan": "Kami akan tindaklanjuti"
  },
  {
    "timestamp": "2025-11-19 12:00:00",
    "status_dari": "diproses_ditresnarkoba",
    "status_ke": "diproses_ditsamapta",
    "diproses_oleh": "ditsamapta",
    "nama_petugas": "Aiptu Rahmat",
    "tanggapan": "Sedang dilakukan penyelidikan"
  }
]
```

Status laporan yang tersedia:
```sql
- baru
- diproses_ditresnarkoba
- diproses_ditsamapta
- diproses_ditbinmas
- selesai_ditsamapta
- selesai_ditbinmas
- selesai
- ditolak
```

## 🔔 Sistem Notifikasi

### File: `get_notifikasi_laporan.php`

Endpoint untuk mendapatkan notifikasi laporan yang perlu ditindaklanjuti.

**Request:**
```
GET get_notifikasi_laporan.php
```

**Response (untuk Ditsamapta):**
```json
{
  "success": true,
  "count": 3,
  "laporan": [
    {
      "id_laporan": 15,
      "judul_laporan": "Peredaran narkoba di Medan",
      "lokasi": "Jl. Sei Blumai",
      "tanggal_lapor": "2025-11-09 10:00:00",
      "status_laporan": "diproses_ditresnarkoba",
      "tanggal_mulai_diproses": "2025-11-09 11:00:00"
    }
  ]
}
```

## 🎨 UI Features

### Detail Pengaduan Page
1. **Timeline Visual**
   - Icon badge dengan warna sesuai status
   - Garis waktu vertikal
   - Badge unit yang memproses (Ditresnarkoba/Ditsamapta/Ditbinmas)
   - Tanggapan dari setiap petugas

2. **Update Status Form**
   - Dropdown status dinamis berdasarkan role user
   - Validasi: Ditsamapta/Ditbinmas hanya bisa mengambil jika sudah "diproses_ditresnarkoba"
   - Konfirmasi sebelum submit

3. **Color Scheme**
   - **#1E40AF** - Primary (Ditresnarkoba, Ditsamapta)
   - **#FFD700** - Secondary (Warning, Baru)
   - **#28a745** - Success (Selesai)
   - **#dc3545** - Danger (Ditolak)

## 🔐 Hak Akses

### Ditresnarkoba
- Melihat semua laporan
- Mengubah status dari: baru → diproses_ditresnarkoba → selesai/ditolak
- Memulai workflow

### Ditsamapta
- Melihat laporan dengan status >= diproses_ditresnarkoba
- Memproses laporan yang sudah di-broadcast Ditresnarkoba
- Mengubah status: diproses_ditsamapta → selesai_ditsamapta
- **DAPAT** memproses laporan yang sama dengan Ditbinmas secara paralel
- **HARUS** melalui tahap "diproses" sebelum bisa "selesai"

### Ditbinmas
- Melihat laporan dengan status >= diproses_ditresnarkoba
- Memproses laporan yang sudah di-broadcast Ditresnarkoba
- Mengubah status: diproses_ditbinmas → selesai_ditbinmas
- **DAPAT** memproses laporan yang sama dengan Ditsamapta secara paralel
- **HARUS** melalui tahap "diproses" sebelum bisa "selesai"

## 🚀 Cara Penggunaan

### 1. Login sebagai Ditresnarkoba
```
1. Lihat laporan baru
2. Klik "Detail"
3. Ubah status → "Diproses Ditresnarkoba"
4. Isi tanggapan
5. Submit
   → Notifikasi dikirim ke Ditsamapta & Ditbinmas
```

### 2. Login sebagai Ditsamapta/Ditbinmas
```
1. Cek notifikasi (akan muncul badge)
2. Lihat laporan yang di-broadcast
3. Klik "Detail"
4. Lihat timeline untuk melihat progress dari unit lain
5. Ubah status → "Diproses Ditsamapta/Ditbinmas"
6. Isi tanggapan
7. Submit
   → Status berubah, kedua unit dapat bekerja paralel pada laporan yang sama
```

### 3. Menyelesaikan Laporan
```
PENTING: Harus sudah status "Diproses" dulu!

Untuk Ditsamapta:
1. Buka detail laporan yang sudah "Diproses Ditsamapta"
2. Ubah status → "Selesai Ditsamapta"
3. Isi tanggapan akhir
4. Submit

Untuk Ditbinmas:
1. Buka detail laporan yang sudah "Diproses Ditbinmas"
2. Ubah status → "Selesai Ditbinmas"
3. Isi tanggapan akhir
4. Submit

CATATAN: Tidak bisa langsung "selesai" tanpa melalui "diproses" terlebih dahulu
```

## 📝 Notes

- **Parallel Processing**: KEDUA unit (Ditsamapta & Ditbinmas) dapat memproses laporan yang sama secara bersamaan
- **Timeline**: Semua perubahan status dari semua unit tercatat lengkap dengan timestamp dan petugas dalam format JSON
- **Wajib Diproses**: Tidak bisa langsung "selesai", harus melalui tahap "diproses" terlebih dahulu
- **Notifikasi**: Dikirim ke kedua unit ketika Ditresnarkoba mulai memproses
- **Warna Solid**: Tidak ada gradient, semua menggunakan solid color (#1E40AF dan #FFD700)

## 🐛 Troubleshooting

### Notifikasi tidak muncul
- Pastikan kolom `is_notif_ditsamapta` dan `is_notif_ditbinmas` sudah ada di `tabel_laporan`
- Check role user di session
- Pastikan status laporan sudah "diproses_ditresnarkoba"

### Timeline tidak tampil
- Pastikan kolom `timeline_json` ada di `tabel_laporan`
- Check decode JSON di detail_pengaduan.php
- Pastikan data JSON valid

### Status tidak bisa diubah
- Pastikan user punya role yang sesuai
- Check validasi di dropdown status
- Pastikan database connection aktif

## 📞 Support

Untuk pertanyaan atau issue, silakan hubungi tim development.

---
**Generated with ❤️ by Claude Code**
