# Electricity Usage Monitor 🔌

Aplikasi monitoring penggunaan listrik token pribadi untuk membantu tracking konsumsi listrik harian, proyeksi bulanan, dan manajemen pembelian token listrik dengan AI Assistant yang memberikan analisa personal.

![Laravel](https://img.shields.io/badge/Laravel-11.x-red?logo=laravel)
![Livewire](https://img.shields.io/badge/Livewire-3.x-blue?logo=livewire)
![PHP](https://img.shields.io/badge/PHP-8.3-purple?logo=php)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-teal?logo=tailwindcss)

## 🌟 Fitur Utama

### 🤖 **AI Assistant Personal**
- **Personal Greeting**: Sapaan khusus untuk Bintang & Ayu
- **Analisa Cerdas**: Memberikan insight tentang pola penggunaan listrik
- **Proyeksi ke Gajian**: Perhitungan sisa kWh sampai tanggal 10 (tanggal gajian)
- **Rekomendasi**: Saran apakah perlu beli token sebelum gajian

### 📊 **Dashboard Monitoring Real-time**
- **Status Penggunaan**: Indikator visual (HEMAT/STANDAR/BOROS) berdasarkan konsumsi harian
- **Live Usage Indicator**: 
  - HEMAT (<7 kWh/hari) - Hijau
  - STANDAR (7-10 kWh/hari) - Kuning  
  - BOROS (>10 kWh/hari) - Merah
- **Statistik Lengkap**: 
  - Sisa listrik (kWh) terkini
  - Penggunaan rata-rata harian
  - Pembelian terakhir
  - kWh terpakai dari pembelian terakhir
  - Estimasi sisa hari

### 💰 **Manajemen Pembelian Token**
- **Auto-formatting Input**: Format Rupiah dengan pemisah ribuan
- **Auto-calculation**: Otomatis hitung kWh berdasarkan input harga
- **Purchase History**: Riwayat lengkap pembelian token
- **Price Tracking**: Monitor perubahan tarif listrik

### 📝 **Smart kWh Checking**
- **Modal Konfirmasi Otomatis**: Pop-up otomatis saat buka aplikasi
- **3 Opsi Respon**: 
  - Ya, Benar (simpan nilai sama)
  - Tidak, Sudah Berubah (input nilai baru)
  - Tanya Lagi Nanti (skip untuk session ini)
- **Check History**: Log lengkap pengecekan meter

### 📈 **Proyeksi & Analisa Advanced**
- **Proyeksi Bulanan**: Estimasi penggunaan dan biaya bulanan
- **Analisa Bulanan**: Prediksi kebutuhan bulan depan
- **Frekuensi Pembelian**: Berapa kali perlu beli token per bulan
- **Warning System**: Notifikasi jika perlu beli token sebelum gajian
- **Proyeksi Sisa Bulanan**: Estimasi sisa kWh di akhir bulan

### 📱 **Modern UI/UX**
- **Responsive Design**: Sempurna di desktop, tablet, dan mobile
- **Real-time Updates**: Fungsi refresh data langsung
- **Interactive Cards**: Efek hover dan transisi halus
- **Color-coded Status**: Indikator visual berbeda untuk setiap level
- **Gradient Backgrounds**: Desain modern dengan gradasi warna

## 🛠️ Technology Stack

- **Backend**: Laravel 11.x dengan PHP 8.3
- **Frontend**: Livewire 3.x untuk komponen reaktif
- **Styling**: TailwindCSS untuk desain modern
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Breeze
- **Build Tools**: Vite untuk kompilasi asset
- **Deployment**: GitHub Actions dengan CI/CD otomatis

## 📋 Requirements

- PHP 8.3 atau lebih tinggi
- Composer
- Node.js 18+ dan NPM
- MySQL 8.0+ atau PostgreSQL
- Web server (Nginx/Apache)

## ⚡ Quick Start

### 1. Clone Repository
```bash
git clone https://github.com/bintangtobing/electricity-usage.git
cd electricity-usage
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database di .env
DB_DATABASE=electricity_usage
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate

# Optional: Seed sample data
php artisan db:seed
```

### 5. Build Assets & Start Server
```bash
# Build frontend assets
npm run build

# Start development server
php artisan serve
```

Akses aplikasi di `http://localhost:8000`

## 🗂️ Project Structure

```
electricity-usage/
├── app/
│   ├── Livewire/              # Livewire components
│   │   ├── ElectricityDashboard.php
│   │   ├── ElectricityPurchaseForm.php
│   │   ├── ElectricityUsageCheckForm.php
│   │   ├── ElectricityHistory.php
│   │   └── KwhConfirmationModal.php
│   └── Models/                # Eloquent models
│       ├── ElectricityPurchase.php
│       └── ElectricityUsageCheck.php
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/               # Sample data seeders
├── resources/
│   ├── views/
│   │   ├── livewire/          # Livewire blade templates
│   │   └── layouts/           # Application layouts
│   ├── css/                   # Stylesheets
│   └── js/                    # JavaScript files
└── routes/
    └── web.php                # Web routes
```

## 📊 Cara Penggunaan

### 1. **Login/Register**
Daftar akun baru atau masuk dengan akun existing

### 2. **Input Pembelian Token**
- Klik tombol "Input Pembelian"
- Masukkan nomor meter
- Input harga pembelian (otomatis format Rupiah)
- kWh akan dihitung otomatis

### 3. **Cek Sisa kWh**
- Klik tombol "Cek Sisa kWh" atau
- Jawab modal konfirmasi yang muncul otomatis
- Update sisa kWh terkini

### 4. **Monitor Dashboard**
- Pantau penggunaan harian
- Lihat proyeksi bulanan
- Cek estimasi sampai tanggal gajian

### 5. **Ikuti Rekomendasi AI**
- Baca analisa dari AI Assistant
- Perhatikan warning jika perlu beli token

## 🔧 Konfigurasi

### Tanggal Gajian
Default tanggal gajian adalah tanggal 10. Untuk mengubah, edit di:
```php
// app/Livewire/ElectricityDashboard.php
// Line 162-168
if ($currentDay <= 10) {
    $targetDate = Carbon::create($now->year, $now->month, 10);
    // Ubah angka 10 sesuai tanggal gajian
}
```

### Threshold Penggunaan
Untuk mengubah kategori HEMAT/STANDAR/BOROS:
```php
// app/Livewire/ElectricityDashboard.php
// Line 144-154
if ($dailyUsage < 7) {           // HEMAT
    // ...
} elseif ($dailyUsage <= 10) {   // STANDAR
    // ...
} else {                          // BOROS
    // ...
}
```

## 🚀 Deployment

Aplikasi include GitHub Actions untuk deployment otomatis:

1. **Setup secrets** di GitHub repository:
   - `SERVER_HOST`: IP address VPS
   - `SERVER_PASSWORD`: SSH password

2. **Push ke main branch** untuk trigger deployment

3. **Server requirements**:
   - Ubuntu server dengan PHP 8.3, Nginx, MySQL
   - SSH access configured
   - Web directory: `/var/www/electricity-usage/`

## 🤝 Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

Project ini open-source dengan [MIT license](LICENSE).

## 👨‍💻 Developer

**Bintang Tobing**
- GitHub: [@bintangtobing](https://github.com/bintangtobing)
- LinkedIn: [Bintang Tobing](https://linkedin.com/in/bintangtobing)

Dikembangkan dengan ❤️ untuk kebutuhan monitoring listrik di Kos Bali.

## 🆘 Support

Jika project ini membantu, berikan ⭐ di GitHub!

Untuk support, silakan buka issue di GitHub repository.

---

*Built with ❤️ using Laravel, Livewire, and TailwindCSS*