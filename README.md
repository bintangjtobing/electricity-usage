# Electricity Usage Monitor

A modern web application built with Laravel and Livewire for monitoring residential electricity usage, tracking purchases, and providing detailed analytics with projections.

![Laravel](https://img.shields.io/badge/Laravel-10.x-red?logo=laravel)
![Livewire](https://img.shields.io/badge/Livewire-3.x-blue?logo=livewire)
![PHP](https://img.shields.io/badge/PHP-8.3-purple?logo=php)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-teal?logo=tailwindcss)

## 🚀 Features

### 📊 **Real-time Dashboard Analytics**
- **Live Usage Indicator**: HEMAT (<7 kWh/day), STANDAR (=7 kWh/day), BOROS (>7 kWh/day)
- **Last Purchase Tracking**: Date and days since last electricity token purchase
- **Remaining kWh**: Real-time display of electricity balance
- **Daily Average Usage**: Calculated based on historical data
- **Usage Percentage**: Shows how much electricity has been consumed from last purchase

### 💰 **Smart Purchase Management**
- **Auto-formatting Input**: Rupiah currency format with thousands separator
- **Auto-calculation**: Automatically calculates kWh based on price input
- **Purchase History**: Complete record of all electricity token purchases
- **Price per Unit Tracking**: Monitors electricity tariff changes

### 📈 **Advanced Projections**
- **Monthly Usage Projection**: Estimated monthly electricity consumption
- **Monthly Cost Estimation**: Projected monthly electricity expenses
- **Token Purchase Frequency**: How often you need to buy electricity tokens
- **Next Month Estimation**: Predictive analytics for future usage
- **Remaining Days**: Estimated days until next token purchase needed

### 🔍 **Usage Monitoring**
- **kWh Remaining Checks**: Track current electricity balance
- **Usage Pattern Analysis**: Historical consumption patterns
- **Check History**: Complete log of all electricity meter readings

### 📱 **Modern UI/UX**
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile
- **Real-time Updates**: Live refresh functionality
- **Quick Action Buttons**: Shortcut buttons for common actions
- **Interactive Cards**: Hover effects and smooth transitions
- **Color-coded Status**: Visual indicators for different usage levels

## 🛠️ Technology Stack

- **Backend**: Laravel 10.x with PHP 8.3
- **Frontend**: Livewire 3.x for reactive components
- **Styling**: TailwindCSS for modern UI design
- **Database**: MySQL with optimized migrations
- **Build Tools**: Vite for asset compilation
- **Deployment**: GitHub Actions with automated CI/CD

## 📋 Requirements

- PHP 8.3 or higher
- Composer
- Node.js 18+ and NPM
- MySQL 8.0+
- Web server (Nginx/Apache)

## ⚡ Quick Start

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/electricity-usage.git
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

# Configure database in .env file
DB_DATABASE=elec_usage
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed sample data
php artisan db:seed --class=ElectricityDataSeeder
```

### 5. Build Assets & Start Server
```bash
# Build frontend assets
npm run build

# Start development server
php artisan serve
```

Visit `http://localhost:8000` to access the application.

## 🗂️ Project Structure

```
electricity-usage/
├── app/
│   ├── Livewire/              # Livewire components
│   │   ├── ElectricityDashboard.php
│   │   ├── ElectricityPurchaseForm.php
│   │   ├── ElectricityUsageCheckForm.php
│   │   └── ElectricityHistory.php
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

## 📊 Usage Analytics Explained

### Usage Categories
- **HEMAT** (Green): Daily usage below 7 kWh - Efficient electricity consumption
- **STANDAR** (Yellow): Daily usage around 7 kWh - Normal household consumption  
- **BOROS** (Red): Daily usage above 7 kWh - High electricity consumption

### Calculations
- **Daily Average**: Total kWh used ÷ Days since last purchase
- **Monthly Projection**: Daily average × 30 days
- **Token Frequency**: 30 days ÷ (kWh per purchase ÷ daily average)
- **Remaining Days**: Current kWh balance ÷ daily average

## 🚀 Deployment

The application includes automated deployment via GitHub Actions:

1. **Set up secrets** in your GitHub repository:
   - `SERVER_HOST`: Your VPS IP address
   - `SERVER_PASSWORD`: SSH password for your server

2. **Push to main branch** to trigger automatic deployment

3. **Server requirements**:
   - Ubuntu server with PHP 8.3, Nginx, MySQL
   - SSH access configured
   - Web directory: `/var/www/electricity-usage/`

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 👨‍💻 Author

**Bintang Tobing**
- GitHub: [@bintangtobing](https://github.com/bintangtobing)
- LinkedIn: [Bintang Tobing](https://linkedin.com/in/bintangtobing)

## 🆘 Support

If you find this project helpful, please give it a ⭐ on GitHub!

For support, please open an issue in the GitHub repository.

---

*Built with ❤️ using Laravel, Livewire, and TailwindCSS*