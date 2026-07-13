import './bootstrap';

import Chart from 'chart.js/auto';

// Dashboard membuat grafik lewat window.Chart. Sebelumnya Chart.js dimuat dari
// CDN di dalam blade; sekarang ikut dibundel supaya halaman tidak bergantung
// pada server luar.
window.Chart = Chart;
