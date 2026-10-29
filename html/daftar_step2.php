<?php
require_once 'config/db.php';
ini_set("session.cookie_lifetime", 2592000);
ini_set("session.gc_maxlifetime", 2592000);
ini_set("session.cookie_lifetime", 2592000);
ini_set("session.gc_maxlifetime", 2592000);
session_set_cookie_params([
    "lifetime" => 2592000,
    "path" => "/",
    "domain" => ".dirasah.my.id",
    "secure" => true,
    "httponly" => true,
    "samesite" => "Lax"
]);
session_start();

// Cache-buster gambar
$v = time();
$fav_v = file_exists('assets/putih.png') ? filemtime('assets/putih.png') : time();

$role_key = isset($_GET['role']) ? htmlspecialchars($_GET['role']) : ($_SESSION['reg_role'] ?? 'pelajar');

$role_titles = [
    'pelajar'  => 'pelajar',
    'peneliti' => 'peneliti',
    'guru'     => 'guru & pendidik',
    'umum'     => 'pengguna umum'
];
$role_name = $role_titles[$role_key] ?? 'pelajar';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $terms    = isset($_POST['terms']);

    if (!$terms) {
        $error = 'Anda harus menyetujui Ketentuan Layanan dan Kebijakan Privasi.';
    } elseif (empty($email) || empty($username) || empty($password)) {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Kata sandi harus terdiri dari setidaknya 8 karakter.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email ini sudah terdaftar. Silakan masuk.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            try {
                $insert = $pdo->prepare("INSERT INTO users (nama_lengkap, email, role, password) VALUES (?, ?, ?, ?)");
                $insert->execute([$username, $email, $role_key, $hashed_password]);

                setcookie('dirasah_remember_email', $email, time() + (86400 * 30), "/");

                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['nama']    = $username;
                $_SESSION['role']    = $role_key;

                unset($_SESSION['reg_birth'], $_SESSION['reg_role']);

                header('Location: dashboard.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal mendaftar: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Daftar Akun - Dirasah.id</title>
    <!-- Favicon Tab Browser (putih.png) -->
    <link rel="icon" type="image/png" href="assets/putih.png?v=<?= $fav_v; ?>">
    <link rel="shortcut icon" type="image/png" href="assets/putih.png?v=<?= $fav_v; ?>">
    <link rel="apple-touch-icon" href="assets/putih.png?v=<?= $fav_v; ?>">

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body, input, textarea, button, select, p, span, h1, h2, h3, h4, h5, h6, a, div {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-weight: 400 !important;
        }
        .fa, .fas, .far, .fal, .fad, .fab, .fa-solid, .fa-regular, .fa-brands {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
        }
        a, button {
            transition: none !important;
        }
    </style>
<link rel="manifest" href="/manifest.json">
<script>
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker.register("/sw.js");
  });
}
</script>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Dirasah">
<link rel="apple-touch-icon" href="/assets/img/icon-192.png">

<meta name="author" content="Shahzada Rakhshaa Aprihansyah">
<meta name="creator" content="Shahzada Rakhshaa Aprihansyah">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Dirasah",
  "url": "https://dirasah.my.id",
  "creator": {
    "@type": "Person",
    "name": "Shahzada Rakhshaa Aprihansyah",
    "url": "https://github.com/srakss255-prog"
  }
}
</script>
</head>
<body class="bg-white text-slate-900 min-h-screen lg:h-screen overflow-x-hidden overflow-y-auto lg:overflow-hidden flex flex-col antialiased selection:bg-blue-100 selection:text-blue-900 font-normal">

    <!-- Header / Navbar -->
    <header class="w-full bg-white border-b border-slate-200 shrink-0 z-40 font-normal">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 h-16 flex items-center justify-between gap-2 sm:gap-4">
            
            <div class="flex items-center gap-1.5 sm:gap-4 flex-1 min-w-0">
                <div id="jelajahi-dropdown" class="relative group shrink-0">
                    <button class="flex items-center gap-1 text-xs sm:text-base font-normal text-[#1865f2] py-2 cursor-pointer">
                        <span>Jelajahi</span>
                        <i class="fa-solid fa-caret-down text-[10px] sm:text-xs"></i>
                    </button>
                    <div class="absolute left-0 top-full mt-1 w-52 bg-white border border-slate-200 rounded-md shadow-lg py-2 hidden group-hover:block z-50">
                        <a href="quran.php" class="block px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-50">Al-Qur'an Digital</a>
                        <a href="hadits.php" class="block px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-50">Kutubut Tis'ah &amp; Hadits</a>
                        <a href="kiblat.php" class="block px-4 py-2 text-sm font-normal text-slate-700 hover:bg-slate-50">Kompas Kiblat Web</a>
                    </div>
                </div>

                <button 
                    id="search-btn-trigger" 
                    onclick="openSearchBar()" 
                    class="flex items-center gap-1.5 px-2.5 py-1.5 border border-slate-300 rounded-md text-[#1865f2] bg-white text-xs sm:text-sm font-normal cursor-pointer shrink-0"
                >
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    <span class="hidden xs:inline">Cari</span>
                </button>

                <div id="search-active-container" class="hidden items-center gap-2 flex-1 max-w-md">
                    <button 
                        onclick="closeSearchBar()" 
                        class="text-[#1865f2] text-base w-7 h-7 flex items-center justify-center shrink-0 cursor-pointer" 
                        title="Tutup Pencarian"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <div class="relative w-full">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                        <input 
                            id="search-input" 
                            type="text" 
                            placeholder="Cari surat, hadits, atau topik..."
                            class="w-full pl-8 pr-3 py-1.5 text-xs sm:text-sm bg-white border-2 border-[#1865f2] rounded-md outline-none text-slate-900 font-normal"
                        >
                    </div>
                </div>
            </div>

            <div id="brand-logo" class="flex items-center justify-center shrink-0 px-1">
                <a href="index.php" class="flex items-center gap-2 sm:gap-2.5">
                    <div class="w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center text-[#182335]">
                        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                            <path d="M52 8C48 9.5 45 13.5 45 18C45 23.5 49.5 28 55 28C57.5 28 59.8 27.1 61.5 25.5C59.5 29.5 55 32 50 32C42.8 32 37 26.2 37 19C37 13.2 40.8 8.3 46 6.5C47.8 5.8 50 6.5 52 8Z" fill="#182335"/>
                            <polygon points="62,13 63.5,16 66.5,16.5 64.3,18.5 64.9,21.5 62,20 59.1,21.5 59.7,18.5 57.5,16.5 60.5,16" fill="#182335"/>
                            <path d="M14 41L11 68L22 75" stroke="#182335" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M86 41L89 68L78 75" stroke="#182335" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17 37L15 65L25 72" stroke="#182335" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M83 37L85 65L75 72" stroke="#182335" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M50 42C36 30 22 30 19 33V63C22 60 36 60 50 72C64 60 78 60 81 63V33C78 30 64 30 50 42Z" stroke="#182335" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M50 42V72" stroke="#182335" stroke-width="5" stroke-linecap="round"/>
                            <path d="M43 73.5C43 78 57 78 57 73.5" stroke="#182335" stroke-width="4.5" stroke-linecap="round"/>
                            <path d="M10 74C25 68 38 68 47 74" stroke="#182335" stroke-width="4.5" stroke-linecap="round"/>
                            <path d="M90 74C75 68 62 68 53 74" stroke="#182335" stroke-width="4.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="text-lg sm:text-[1.45rem] font-normal tracking-[-0.015em] text-[#182335]">
                        Dirasah.id
                    </span>
                </a>
            </div>

            <div class="flex items-center justify-end gap-2 sm:gap-6 flex-1 shrink-0 font-normal">
                <a href="login.php" class="text-xs sm:text-base font-normal text-[#1865f2]">
                    Masuk
                </a>
                <a href="index.php" class="bg-[#1865f2] text-white px-3 sm:px-6 py-1.5 sm:py-2 rounded-md text-xs sm:text-base font-normal shadow-sm">
                    Daftar
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-8 py-5 sm:py-8 lg:py-0 flex-1 flex items-center justify-center font-normal">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-14 w-full items-center justify-items-center">
            
            <!-- Kolom Kiri: Slider 3 Gambar Berputar Otomatis Identik dengan Index -->
            <div class="w-full max-w-lg lg:max-w-none lg:col-span-7 relative h-[360px] sm:h-[480px] lg:h-[600px] rounded-md overflow-hidden shadow-md bg-slate-950 mx-auto font-normal">
                
                <!-- Slide 1: pik1.png -->
                <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-100 flex flex-col justify-start p-5 sm:p-10 lg:p-12 text-white font-normal">
                    <img src="assets/pik1.png?v=<?= $v; ?>" alt="Slide 1" class="absolute inset-0 w-full h-full object-cover object-center z-0">
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/95 via-slate-950/45 to-transparent z-10"></div>
                    <div class="relative z-20 space-y-2 sm:space-y-3 max-w-xl font-normal">
                        <h1 class="text-lg sm:text-3xl lg:text-[2.65rem] leading-[1.22] text-white font-normal">
                            Ubah pencarian literasi Islam menjadi pemahaman nyata.
                        </h1>
                        <p class="text-xs sm:text-sm lg:text-base text-slate-200 leading-relaxed font-normal">
                            Dirasah.id membantu siapa saja mendalami studi Islam melalui korpus Al-Qur'an, jalur sanad hadits, dan literatur fikih dalam satu platform terpadu.
                        </p>
                    </div>
                </div>

                <!-- Slide 2: pik2.png -->
                <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0 pointer-events-none flex flex-col justify-start p-5 sm:p-10 lg:p-12 text-white font-normal">
                    <img src="assets/pik2.png?v=<?= $v; ?>" alt="Slide 2" class="absolute inset-0 w-full h-full object-cover object-center z-0">
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/95 via-slate-950/45 to-transparent z-10"></div>
                    <div class="relative z-20 space-y-2 sm:space-y-3 max-w-xl font-normal">
                        <h1 class="text-lg sm:text-3xl lg:text-[2.65rem] leading-[1.22] text-white font-normal">
                            Eksplorasi sanad hadits dan tafsir lebih terstruktur.
                        </h1>
                        <p class="text-xs sm:text-sm lg:text-base text-slate-200 leading-relaxed font-normal">
                            Telusuri jalur transmisi riwayat periwayatan dengan pohon sanad visual dan perbandingan matan kitab induk.
                        </p>
                    </div>
                </div>

                <!-- Slide 3: pik3.png -->
                <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0 pointer-events-none flex flex-col justify-start p-5 sm:p-10 lg:p-12 text-white font-normal">
                    <img src="assets/pik3.png?v=<?= $v; ?>" alt="Slide 3" class="absolute inset-0 w-full h-full object-cover object-center z-0">
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/95 via-slate-950/45 to-transparent z-10"></div>
                    <div class="relative z-20 space-y-2 sm:space-y-3 max-w-xl font-normal">
                        <h1 class="text-lg sm:text-3xl lg:text-[2.65rem] leading-[1.22] text-white font-normal">
                            Ruang belajar interaktif untuk semua kalangan.
                        </h1>
                        <p class="text-xs sm:text-sm lg:text-base text-slate-200 leading-relaxed font-normal">
                            Dari pelajar, pengkaji, hingga pendidik, temukan kurikulum kajian Islam yang kredibel dan aplikatif.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Kolom Kanan: Formulir Registrasi -->
            <div class="w-full max-w-lg lg:max-w-md lg:col-span-5 flex flex-col justify-center space-y-3 pb-8 lg:pb-0 mx-auto font-normal">
                
                <div>
                    <a href="index.php" class="inline-flex items-center gap-1.5 text-xs text-[#1865f2] mb-1 font-normal">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        <span>Kembali</span>
                    </a>
                    <h2 class="text-xl sm:text-2xl font-normal text-slate-900 tracking-normal">
                        Daftar sebagai <?= htmlspecialchars($role_name); ?> hari ini!
                    </h2>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="p-2.5 text-xs bg-red-50 text-red-600 rounded-md border border-red-200 font-normal">
                        <?= $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-3 font-normal" id="form-register">
                    
                    <!-- Kotak Persetujuan Kebijakan -->
                    <div class="p-3 bg-white border border-slate-200 rounded-md text-xs text-slate-600 flex items-start gap-2.5 font-normal">
                        <input 
                            type="checkbox" 
                            name="terms" 
                            id="terms" 
                            required
                            onchange="toggleSubmitBtn()"
                            class="mt-0.5 w-4 h-4 rounded border-slate-300 text-[#1865f2] focus:ring-0 accent-[#1865f2] cursor-pointer shrink-0"
                        >
                        <label for="terms" class="cursor-pointer leading-relaxed select-none">
                            Dengan mencentang kotak ini, saya setuju dengan <a href="#" class="text-[#1865f2]">Ketentuan Layanan Dirasah.id</a> dan <a href="#" class="text-[#1865f2]">Kebijakan Privasi</a>.
                        </label>
                    </div>

                    <!-- Input Email -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs font-normal">
                            <label class="text-slate-800" for="email">Apa email Anda?</label>
                            <span class="text-slate-400 text-[11px]">wajib</span>
                        </div>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            placeholder="Alamat email aktif untuk menerima informasi akun Dirasah.id."
                            class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-slate-200 rounded-md outline-none focus:border-[#1865f2] text-slate-900 font-normal"
                        >
                    </div>

                    <!-- Input Nama Pengguna -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs font-normal">
                            <label class="text-slate-800" for="username">Pilih nama pengguna</label>
                            <span class="text-slate-400 text-[11px]">wajib</span>
                        </div>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            placeholder="Gunakan huruf dan angka saja. Demi keamanan, jangan gunakan nama asli Anda."
                            class="w-full px-3.5 py-2 text-xs sm:text-sm bg-white border border-slate-200 rounded-md outline-none focus:border-[#1865f2] text-slate-900 font-normal"
                        >
                    </div>

                    <!-- Input Kata Sandi dengan Posisi Ikon Mata Tepat di Tengah Vertikal -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs font-normal">
                            <label class="text-slate-800" for="password">Kata sandi</label>
                            <span class="text-slate-400 text-[11px]">wajib</span>
                        </div>
                        <div class="relative w-full">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                minlength="8"
                                placeholder="Harus terdiri dari setidaknya 8 karakter."
                                class="w-full pl-3.5 pr-11 py-2 text-xs sm:text-sm bg-white border border-slate-200 rounded-md outline-none focus:border-[#1865f2] text-slate-900 font-normal"
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword('password', 'eye-btn')" 
                                class="absolute right-0 top-0 bottom-0 px-3.5 my-auto h-full flex items-center justify-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none"
                                title="Tampilkan / Sembunyikan Sandi"
                            >
                                <i id="eye-btn" class="fa-regular fa-eye text-sm leading-none"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Daftar -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            id="btn-submit"
                            disabled
                            class="w-full py-2.5 rounded-md text-xs sm:text-sm font-normal bg-slate-200 text-slate-400 cursor-not-allowed shadow-none"
                        >
                            Daftar
                        </button>
                    </div>
                </form>

                <!-- Footer Form -->
                <div class="pt-1 text-xs text-slate-600 text-left font-normal">
                    Sudah punya akun Dirasah.id? 
                    <a href="login.php" class="text-[#1865f2] font-normal ml-1">
                        Masuk
                    </a>
                </div>

            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full bg-white border-t border-slate-200 py-3.5 text-center text-xs text-slate-500 shrink-0 mt-auto font-normal">
        <p>&copy; 2026 Dirasah.id &mdash; Platform Solusi Interkoneksi Studi Islam</p>
    </footer>

    <script>
        // Toggle Tombol Submit Berdasarkan Checkbox
        function toggleSubmitBtn() {
            const termsCheckbox = document.getElementById('terms');
            const submitBtn = document.getElementById('btn-submit');

            if (termsCheckbox.checked) {
                submitBtn.disabled = false;
                submitBtn.className = "w-full py-2.5 rounded-md text-xs sm:text-sm font-normal bg-[#1865f2] text-white cursor-pointer shadow-sm";
            } else {
                submitBtn.disabled = true;
                submitBtn.className = "w-full py-2.5 rounded-md text-xs sm:text-sm font-normal bg-slate-200 text-slate-400 cursor-not-allowed shadow-none";
            }
        }

        // Slider Otomatis 3 Slide Identik index.php
        const slides = document.querySelectorAll('.hero-slide');
        let currentSlide = 0;

        function showNextSlide() {
            if (slides.length <= 1) return;
            slides[currentSlide].classList.remove('opacity-100');
            slides[currentSlide].classList.add('opacity-0', 'pointer-events-none');

            currentSlide = (currentSlide + 1) % slides.length;

            slides[currentSlide].classList.remove('opacity-0', 'pointer-events-none');
            slides[currentSlide].classList.add('opacity-100');
        }

        if (slides.length > 1) {
            setInterval(showNextSlide, 4500);
        }

        // Toggle Sandi
        function togglePassword(inputId, iconId) {
            const field = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!field || !icon) return;
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'fa-regular fa-eye-slash text-sm leading-none';
            } else {
                field.type = 'password';
                icon.className = 'fa-regular fa-eye text-sm leading-none';
            }
        }

        const jelajahiDropdown = document.getElementById('jelajahi-dropdown');
        const searchBtnTrigger = document.getElementById('search-btn-trigger');
        const searchActiveContainer = document.getElementById('search-active-container');
        const searchInput = document.getElementById('search-input');

        function openSearchBar() {
            if (jelajahiDropdown) jelajahiDropdown.classList.add('hidden');
            if (searchBtnTrigger) searchBtnTrigger.classList.add('hidden');
            if (searchActiveContainer) {
                searchActiveContainer.classList.remove('hidden');
                searchActiveContainer.classList.add('flex');
            }
            if (searchInput) searchInput.focus();
        }

        function closeSearchBar() {
            if (searchActiveContainer) {
                searchActiveContainer.classList.remove('flex');
                searchActiveContainer.classList.add('hidden');
            }
            if (jelajahiDropdown) jelajahiDropdown.classList.remove('hidden');
            if (searchBtnTrigger) searchBtnTrigger.classList.remove('hidden');
            if (searchInput) searchInput.value = '';
        }
    </script>
</body>
</html>