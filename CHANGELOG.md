# Changelog

Semua perubahan penting pada project "Electricity Usage Monitor" akan didokumentasikan dalam file ini.

Format berdasarkan [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
dan project ini menggunakan [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - 2025-07-26

### 🎉 Added
- **AI Assistant Personal**: Fitur AI yang memberikan sapaan personal untuk Bintang dengan analisa penggunaan listrik
- **Proyeksi ke Tanggal Gajian**: Perhitungan otomatis sisa kWh sampai tanggal 10 (tanggal gajian) dengan warning jika perlu beli token
- **Smart kWh Confirmation Modal**: Modal konfirmasi otomatis yang muncul saat user membuka aplikasi untuk update sisa kWh
- **Opsi Respon Modal**: 3 pilihan respon (Ya/Tidak/Tanya Nanti) dengan input field untuk nilai baru
- **Session Storage**: Fitur "Tanya Lagi Nanti" yang skip modal untuk session tersebut

### 🔧 Fixed
- **Perhitungan Daily Average**: Perbaikan logika perhitungan penggunaan harian yang sebelumnya salah
- **kWh Used Calculation**: Fix perhitungan total kWh terpakai dengan mempertimbangkan multiple purchases
- **Days Since Purchase**: Koreksi perhitungan hari sejak pembelian terakhir (sekarang menghitung ke hari ini, bukan ke check date)
- **Historical Usage**: Implementasi perhitungan yang lebih akurat berdasarkan usage checks history

### 📝 Changed
- **README.md**: Update dengan deskripsi lengkap dalam Bahasa Indonesia, fitur-fitur baru, dan cara penggunaan
- **Dashboard Layout**: Penambahan section AI Assistant di bagian atas dashboard
- **Usage Indicator**: Update threshold menjadi <7 (Hemat), 7-10 (Standar), >10 (Boros)

## [1.0.0] - 2025-07-26

### 🎉 Added
- **Modal Konfirmasi kWh**: Implementasi modal untuk konfirmasi sisa listrik
- **Auto-show Logic**: Modal muncul otomatis setiap kali user akses website
- **Refresh Dashboard**: Event listener untuk refresh dashboard setelah save data

### 🔧 Fixed
- **Calculation Logic**: Perbaikan berbagai bug di perhitungan dashboard
- **Usage Pattern Analysis**: Implementasi analisa yang lebih akurat

## [0.3.0] - 2025-07-24

### 🚀 Added
- **GitHub Actions**: Setup CI/CD dengan GitHub Actions untuk automated deployment
- **Workflow Configuration**: Konfigurasi deployment ke VPS

### 🗑️ Removed
- **Manual Deploy**: Hapus script deployment manual

## [0.2.0] - 2025-07-24

### 🎉 Initial Release
- **Dashboard Monitoring**: Dashboard dengan statistik real-time
- **Purchase Management**: Form input pembelian token listrik
- **Usage Checking**: Form untuk cek sisa kWh
- **History Tracking**: Riwayat pembelian dan pengecekan
- **Projections**: Proyeksi bulanan dan estimasi penggunaan
- **Responsive Design**: UI modern dengan Tailwind CSS
- **Authentication**: Sistem login dengan Laravel Breeze

### 🛠️ Technical
- Laravel 11.x framework
- Livewire 3.x untuk komponen reaktif
- MySQL database
- Tailwind CSS untuk styling
- Vite untuk build tools

---

## Catatan Developer

Project ini dikembangkan untuk kebutuhan personal monitoring listrik token di Kos Bali. Fitur-fitur dirancang khusus untuk memudahkan tracking penggunaan listrik dan planning pembelian token, terutama disesuaikan dengan tanggal gajian.

### Rencana Pengembangan
- [ ] Export data ke Excel/PDF
- [ ] Grafik visualisasi penggunaan
- [ ] Notifikasi WhatsApp/Telegram
- [ ] Multi-user support
- [ ] API untuk integrasi dengan smart meter
- [ ] Dark mode
- [ ] PWA support untuk mobile

### Known Issues
- Modal konfirmasi belum memiliki opsi untuk disable permanen
- Belum ada validasi untuk input kWh negatif
- Perlu optimasi query untuk data history yang banyak

---

Untuk detail lengkap setiap perubahan, lihat [commit history](https://github.com/bintangtobing/electricity-usage/commits/main).
