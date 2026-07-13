/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    // Sebagian kelas ditentukan di PHP, bukan di Blade -- misalnya warna
    // indikator (bg-green-500 / bg-red-500) di ElectricityDashboard. Tanpa
    // baris ini kelas tersebut ikut terbuang saat build dan indikatornya
    // kehilangan warna.
    "./app/**/*.php",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}