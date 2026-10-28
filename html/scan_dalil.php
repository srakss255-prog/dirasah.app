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

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];$nama_user = $_SESSION['nama'] ?? 'Pengguna Dirasah';$foto_user = null;

try {
    $qUser =$pdo->prepare("SELECT * FROM users WHERE id = ?");
    $qUser->execute([$user_id]);
    $curUser =$qUser->fetch(PDO::FETCH_ASSOC);
    if ($curUser) {$nama_user = !empty($curUser['nama']) ?$curUser['nama'] : (!empty($curUser['username']) ?$curUser['username'] : $nama_user);$foto_user = !empty($curUser['foto']) ? trim($curUser['foto']) : null;
    }
} catch (Exception $e) {$foto_user = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Dalil &amp; Hadits - Dirasah.id</title>
    <link rel="preconnect" href="[https://fonts.googleapis.com](https://fonts.googleapis.com)">
    <link rel="preconnect" href="[https://fonts.gstatic.com](https://fonts.gstatic.com)" crossorigin>
    <link href="[https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@600;700&family=Amiri&display=swap](https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@600;700&family=Amiri&display=swap)" rel="stylesheet">
    <script src="[https://cdn.tailwindcss.com](https://cdn.tailwindcss.com)"></script>
    <link href="[https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css](https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css)" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .arabic-text { font-family: 'Amiri', serif; }
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
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased min-h-screen flex">

    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/40 z-40 hidden lg:hidden"></div>

    <!-- Sidebar Bilah Sisi -->
    <aside id="main-sidebar" class="fixed top-0 bottom-0 left-0 z-50 bg-white border-r border-slate-200 flex flex-col w-64 -translate-x-full lg:translate-x-0 transition-transform">
        <div class="h-20 px-3 border-b border-slate-200 flex items-center justify-between shrink-0">
            <a href="dashboard.php" class="flex items-center gap-3 p-1">
                <div class="w-12 h-12 flex items-center justify-center shrink-0">
                    <img src="assets/dirasah.png" alt="Dirasah.id" class="w-12 h-12 object-contain">
                </div>
                <span class="text-xl font-bold tracking-tight text-[#182335]">Dirasah.id</span>
            </a>
            <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-400 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
            <div>
                <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Utama</span>
                <nav class="space-y-1">
                    <a href="dashboard.php" class="flex items-center gap-3 px-2 py-2 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50">
                        <img src="assets/home.png" alt="" class="w-6 h-6 object-contain">
                        <span>Dashboard</span>
                    </a>
                </nav>
            </div>

            <div>
                <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Khazanah Studi</span>
                <nav class="space-y-1">
                    <a href="quran.php" class="flex items-center gap-3 px-2 py-2 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50">
                        <img src="assets/quran.png" alt="" class="w-7 h-auto object-contain">
                        <span>Al-Qur'an Digital</span>
                    </a>
                    <a href="hadits.php" class="flex items-center gap-3 px-2 py-2 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50">
                        <img src="assets/hadis.png" alt="" class="w-7 h-auto object-contain">
                        <span>Kutubut Tis'ah &amp; Hadits</span>
                    </a>
                    <!-- Menu Scan Dalil Aktif -->
                    <a href="scan_dalil.php" class="flex items-center gap-3 px-2 py-2 rounded-xl text-sm font-semibold text-[#1865f2] bg-blue-50/60">
                        <div class="w-7 h-7 rounded-lg bg-blue-100 text-[#1865f2] flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-shield-halved text-xs"></i>
                        </div>
                        <span>Cek Keaslian Dalil</span>
                    </a>
                    <a href="kitab.php" class="flex items-center gap-3 px-2 py-2 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50">
                        <img src="assets/kitab.png" alt="" class="w-7 h-auto object-contain">
                        <span>Kitab &amp; Syarah</span>
                    </a>
                </nav>
            </div>
        </div>
    </aside>

    <!-- Konten Area -->
    <div class="flex-1 flex flex-col min-w-0 lg:ml-64">
        <header class="w-full bg-white border-b border-slate-200 h-16 px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30">
            <button type="button" onclick="toggleSidebar()" class="lg:hidden text-slate-700 p-1.5">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="text-xs font-semibold text-slate-600">Verifikasi Dalil &amp; Takhrij Hadits</div>
            <a href="profil.php" class="flex items-center gap-2">
                <?php if (!empty($foto_user)): ?>
                    <img src="<?= htmlspecialchars($foto_user); ?>" alt="" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                <?php else: ?>
                    <div class="w-8 h-8 rounded-full bg-[#1865f2] text-white flex items-center justify-center text-xs font-bold">
                        <?= strtoupper(substr($nama_user, 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </a>
        </header>

        <main class="flex-1 p-4 sm:p-8 max-w-4xl mx-auto w-full space-y-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Uji Keaslian Hadits &amp; Dalil</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Unggah foto kutipan buku/broadcast WhatsApp atau tempel teks dalil untuk mendeteksi derajat keshahihan sanadnya.</p>
            </div>

            <!-- Formulir Input Scan -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-4">
                <!-- Dropzone Foto -->
                <div class="border-2 border-dashed border-slate-200 hover:border-[#1865f2] rounded-xl p-5 text-center cursor-pointer transition relative bg-slate-50/50" onclick="document.getElementById('input-image').click()">
                    <input type="file" id="input-image" accept="image/*" class="hidden" onchange="previewImage(event)">
                    
                    <div id="upload-placeholder" class="space-y-2">
                        <i class="fa-solid fa-camera text-2xl text-slate-400"></i>
                        <p class="text-xs text-slate-600 font-medium">Klik untuk foto langsung / pilih screenshot teks dalil</p>
                        <p class="text-[11px] text-slate-400">Format JPG, PNG, atau WebP</p>
                    </div>

                    <div id="preview-box" class="hidden relative inline-block">
                        <img id="img-preview" src="" class="max-h-48 rounded-lg border border-slate-200 shadow-sm mx-auto">
                        <button type="button" onclick="removeImage(event)" class="absolute -top-2 -right-2 bg-rose-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow-md">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Input Teks -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Atau Ketik / Tempel Bunyi Dalil:</label>
                    <textarea id="input-text" rows="3" placeholder="Contoh: 'Tuntutlah ilmu walau ke negeri Cina' atau lafaz matan hadits..." class="w-full text-xs sm:text-sm p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-[#1865f2] focus:bg-white text-slate-800"></textarea>
                </div>

                <button type="button" id="btn-submit" onclick="prosesScanDalil()" class="w-full py-2.5 bg-[#1865f2] hover:bg-blue-600 text-white text-xs sm:text-sm font-semibold rounded-xl flex items-center justify-center gap-2 cursor-pointer transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Periksa Keaslian Dalil</span>
                </button>
            </div>

            <!-- Hasil Analisis -->
            <div id="result-box" class="hidden bg-white border border-slate-200 rounded-2xl p-6 space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Hasil Takhrij</span>
                        <h2 id="res-title" class="text-base sm:text-lg font-bold text-slate-900 mt-0.5">-</h2>
                    </div>
                    <span id="res-badge" class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 uppercase">
                        -
                    </span>
                </div>

                <div class="space-y-1">
                    <span class="text-xs text-slate-400 font-medium">Rujukan Kitab / Sumber:</span>
                    <p id="res-source" class="text-xs sm:text-sm font-semibold text-slate-800">-</p>
                </div>

                <div id="res-arab-container" class="space-y-1 text-right">
                    <p id="res-arab" class="arabic-text text-2xl text-slate-900 leading-relaxed" dir="rtl">-</p>
                </div>

                <div class="space-y-1">
                    <span class="text-xs text-slate-400 font-medium">Terjemahan Matan:</span>
                    <p id="res-trans" class="text-xs sm:text-sm text-slate-700 leading-relaxed">-</p>
                </div>

                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">Telaah Derajat &amp; Sanad:</span>
                    <p id="res-analysis" class="text-xs text-slate-700 leading-relaxed font-normal">-</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        let base64Image = null;

        function previewImage(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(evt) {
                base64Image = evt.target.result;
                document.getElementById('img-preview').src = base64Image;
                document.getElementById('preview-box').classList.remove('hidden');
                document.getElementById('upload-placeholder').classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }

        function removeImage(e) {
            e.stopPropagation();
            base64Image = null;
            document.getElementById('input-image').value = '';
            document.getElementById('preview-box').classList.add('hidden');
            document.getElementById('upload-placeholder').classList.remove('hidden');
        }

        async function prosesScanDalil() {
            const textVal = document.getElementById('input-text').value.trim();
            const btn = document.getElementById('btn-submit');
            const resultBox = document.getElementById('result-box');

            if (!textVal && !base64Image) {
                alert("Silakan masukkan teks dalil atau unggah gambar terlebih dahulu.");
                return;
            }

            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Memeriksa sanad & rujukan kitab...`;

            try {
                const res = await fetch('api_scan_dalil.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        text: textVal,
                        image_base64: base64Image
                    })
                });

                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    const d = json.data;
                    document.getElementById('res-title').textContent = d.judul_dalil || 'Kutipan Dalil';
                    document.getElementById('res-source').textContent = d.rujukan || 'Tidak ditemukan dalam rujukan induk.';
                    document.getElementById('res-trans').textContent = d.terjemah || '-';
                    document.getElementById('res-analysis').textContent = d.analisis_sanad || '-';

                    if (d.matan_arab) {
                        document.getElementById('res-arab').textContent = d.matan_arab;
                        document.getElementById('res-arab-container').classList.remove('hidden');
                    } else {
                        document.getElementById('res-arab-container').classList.add('hidden');
                    }

                    const badge = document.getElementById('res-badge');
                    badge.textContent = d.status_dalil;
                    
                    const st = (d.status_dalil || '').toLowerCase();
                    if (st.includes('shahih') || st.includes('qur\'an')) {
                        badge.className = "px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 uppercase";
                    } else if (st.includes('hasan')) {
                        badge.className = "px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 uppercase";
                    } else if (st.includes('dhaif') || st.includes('lemah')) {
                        badge.className = "px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 uppercase";
                    } else {
                        badge.className = "px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 uppercase";
                    }

                    resultBox.classList.remove('hidden');
                    resultBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    alert(json.message || "Gagal memproses verifikasi dalil.");
                }
            } catch (err) {
                alert("Terjadi kesalahan jaringan atau server.");
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-solid fa-magnifying-glass"></i> <span>Periksa Keaslian Dalil</span>`;
            }
        }

        function toggleSidebar() {
            document.getElementById('main-sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }
    </script>
</body>
</html>