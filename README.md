<p align="center">
  <h1 align="center">🌿 Aurea — Mental Health & Habit Growth Companion</h1>
  <p align="center">
    <strong>Aplikasi Pelacak Kesehatan Mental, Habit Tracker Gamified (Habit Growth Tree), dan AI Companion Berbasis Laravel 13 & Livewire 3.</strong>
  </p>
  <p align="center">
    <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3"></a>
    <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13"></a>
    <a href="https://livewire.laravel.com"><img src="https://img.shields.io/badge/Livewire-3.x-4E5BA6?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire 3"></a>
    <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/Tailwind_CSS-3.4-38BDF8?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS"></a>
    <a href="https://ai.google.dev"><img src="https://img.shields.io/badge/Google_Gemini-AI-4285F4?style=for-the-badge&logo=googlegemini&logoColor=white" alt="Google Gemini AI"></a>
    <a href="https://midtrans.com"><img src="https://img.shields.io/badge/Payment-Midtrans-00A9E0?style=for-the-badge" alt="Midtrans Payment"></a>
  </p>
</p>

---

## 📌 Tentang Aurea

**Aurea** adalah platform web modern yang dirancang untuk membantu pengguna—khususnya remaja dan dewasa muda—dalam mengelola kesehatan mental, membangun kebiasaan positif (*habit tracking*), serta mendapatkan pendampingan emosional yang responsif dan aman melalui kecerdasan buatan (**AI Aurea**).

Dengan memadukan pendekatan **gamifikasi (Habit Growth Tree)**, **pencatatan jurnal berbasis AI**, dan **pelacakan suasana hati harian**, Aurea menciptakan ruang yang hangat, interaktif, dan bebas dari penghakiman untuk pertumbuhan pribadi pengguna.

---

## ✨ Fitur-Fitur Utama

### 🌳 1. Habit Growth Tree (Gamifikasi Kebiasaan)
- Visualisasi pertumbuhan pohon personal yang tumbuh berdasar kebiasaan (*habits*) positif yang diselesaikan harian.
- Sistem progres bertahap (*growth percentage* dan *growth stages*) yang mendorong konsistensi tanpa tekanan berlebih.

### 🤖 2. AI Aurea Companion
- Teman ngobrol AI berbasis **Google Gemini (Gemini 3.8 Flash & Fallback Chain)** yang empatik dan suportif.
- **Validasi Emosi & Refleksi**: Mendengarkan tanpa menghakimi dan memberikan panduan penanganan stres (*coping mechanism*).
- **Protokol Keselamatan**: Dilengkapi pendeteksi risiko diri (*self-harm / crisis detection*) untuk mengarahkan pengguna ke bantuan profesional atau orang dewasa terpercaya.
- **Resilient Model Architecture**: Memiliki mekanisme fallback otomatis antar model AI jika terjadi kendala kuota atau *rate-limiting*.

### 📊 3. Daily Mood & Check-in Tracking
- Pencatatan suasana hati harian (*MoodType*) beserta log *check-in*.
- Deteksi pola emosi (*Pattern Detection Service*) dan perhitungan skor kesejahteraan (*Wellbeing Scoring Service*).

### 📖 4. Smart Digital Journaling
- Wadah refleksi diri interaktif untuk menuangkan pikiran dan perasaan.
- **Ringkasan & Saran AI**: Otomatis menganalisis isi jurnal dan memberikan ringkasan serta saran praktis yang hangat.

### 📝 5. Personal Onboarding & Mental Profiling
- Kuesioner *onboarding* interaktif untuk memetakan kondisi mental awal dan preferensi pengguna.
- Pembuatan *Mental Profile* kustom untuk pengalaman yang disesuaikan (*personalized*).

### 💎 6. Freemium & Premium Subscription
- Pembatasan kuota harian prompt AI untuk pengguna gratis.
- Integrasi **Midtrans Payment Gateway** untuk aktivasi keanggotaan Premium tanpa batas kuota.

### 🔑 7. Autentikasi & Keamanan
- Autentikasi berbasis Laravel Breeze + Livewire.
- Dukungan **Social Login** (OAuth Google) menggunakan Laravel Socialite.

---

## 🛠️ Tech Stack & Arsitektur

| Kategori | Teknologi / Library |
| :--- | :--- |
| **Framework Backend** | PHP 8.3, Laravel 13 |
| **Frontend Stack** | Livewire 3, Livewire Volt, Alpine.js, Tailwind CSS |
| **Asset Bundler** | Vite |
| **Database & ORM** | MySQL / SQLite, Eloquent ORM |
| **Kecerdasan Buatan (AI)** | Google Gemini API (via HTTP Client & Custom Service) |
| **Payment Gateway** | Midtrans PHP SDK |
| **Autentikasi** | Laravel Breeze & Laravel Socialite |
| **Testing & Formatting** | Pest PHP, Laravel Pint |

---

## 📁 Struktur Proyek Utama

```text
aurea/
├── app/
│   ├── Enums/               # Enum MoodType dan tipe data khusus
│   ├── Exceptions/          # AiPromptLimitReachedException & kustom penanganan exception
│   ├── Http/
│   │   ├── Controllers/     # CheckInController, PremiumController, SubscriptionController, SocialiteController
│   │   └── Middleware/      # EnsureOnboardingCompleted middleware
│   ├── Livewire/            # Component Livewire (AiAurea, JournalPage, Dashboard, Settings)
│   ├── Models/              # User, Habit, Tree, Journal, CheckIn, ChatSession, Subscription, dll.
│   └── Services/            # ChatbotService, WellbeingScoringService, PatternDetectionService, HabitRecommendationService
├── database/
│   ├── migrations/          # Migrasi struktur basis data
│   └── seeders/             # Data awal untuk onboarding & testing
├── resources/
│   └── views/               # Blade views, Volt components, dan layout aplikasi
└── routes/
    ├── web.php              # Rute utama aplikasi
    └── auth.php             # Rute autentikasi
```

---

## 🚀 Panduan Instalasi & Jalankan Lokal

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek Aurea di lingkungan lokal Anda:

### 1. Prasyarat System
- **PHP** >= 8.3
- **Composer** >= 2.x
- **Node.js** >= 18.x & NPM
- **MySQL** / **SQLite** (dapat menggunakan Laragon, XAMPP, atau DB local)

### 2. Clone Repository
```bash
git clone https://github.com/username/aurea.git
cd aurea
```

### 3. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 4. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```

Buka file `.env` dan sesuaikan konfigurasi basis data & API Key:
```env
APP_NAME=Aurea
APP_URL=http://localhost:8000

# Konfigurasi Database (Contoh SQLite)
DB_CONNECTION=sqlite

# Kunci API Google Gemini (Wajib untuk fitur AI Aurea)
GEMINI_API_KEY=your_gemini_api_key_here

# Konfigurasi Payment Gateway Midtrans (Opsional untuk fitur Premium)
MIDTRANS_SERVER_KEY=your_midtrans_server_key
MIDTRANS_CLIENT_KEY=your_midtrans_client_key
MIDTRANS_IS_PRODUCTION=false
```

### 5. Generate Application Key & Database Migration
```bash
# Generate APP_KEY
php artisan key:generate

# Jalankan migrasi basis data beserta seeder
php artisan migrate --seed
```

### 6. Jalankan Server Lokal
Gunakan perintah berikut untuk menjalankan server Laravel dan Vite secara bersamaan:
```bash
composer run dev
```
atau secara terpisah:
```bash
# Terminal 1: Server Laravel
php artisan serve

# Terminal 2: Vite Dev Server
npm run dev
```

Buka browser Anda dan akses: `http://localhost:8000` (atau `http://127.0.0.1:8000`).

---

## 🧪 Pengujian & Code Styling

Proyek ini dilengkapi dengan skrip pengujian berbasis **Pest PHP** dan formatter **Laravel Pint**.

```bash
# Jalankan pengujian unit & fitur (Pest)
composer run test

# Jalankan pengemasan/format kode PHP otomatis (Laravel Pint)
vendor/bin/pint
```

---

## 📜 Lisensi

Proyek **Aurea** dirilis di bawah [MIT License](LICENSE).

---

<p align="center">
  Dibuat dengan 💙 oleh tim <strong>Aurea</strong> untuk mendukung kesejahteraan emosional pengguna.
</p>
