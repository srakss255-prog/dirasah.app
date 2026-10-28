docker exec -i dirasah_db mariadb -udirasah_user -ppassword_db_aman dirasah_db << 'EOF'
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE IF NOT EXISTS `ai_study_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `ai_answer_summary` text NOT NULL,
  `matched_references` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `daily_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preferred_time` time DEFAULT '05:00:00',
  `category_focus` varchar(50) DEFAULT 'hadits',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `learning_contents` (
  `id` int(11) NOT NULL,
  `content_type` enum('quran','hadits','kitab') NOT NULL,
  `book_title` varchar(150) NOT NULL,
  `chapter_or_surah` varchar(150) NOT NULL,
  `number_reference` varchar(50) DEFAULT NULL,
  `arabic_text` text NOT NULL,
  `translation_id` text NOT NULL,
  `explanation_tafsir` text DEFAULT NULL,
  `topic_tags` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `learning_contents` (`id`, `content_type`, `book_title`, `chapter_or_surah`, `number_reference`, `arabic_text`, `translation_id`, `explanation_tafsir`, `topic_tags`) VALUES
(1, 'hadits', 'Shahih Bukhari', 'Kitab Niat', 'No. 1', 'إِنَّمَا الأَعْمَالُ بِالنِّيَّاتِ', 'Sesungguhnya setiap amalan tergantung pada niatnya...', 'Pondasi utama dalam setiap ibadah dan thalabul ilmi (menuntut ilmu).', 'niat, dasar ibadah'),
(2, 'quran', 'Al-Mujadilah', 'Surah ke-58', 'Ayat 11', 'يَرْفَعِ اللَّهُ الَّذِينَ آمَنُوا مِنكُمْ وَالَّذِينَ أُوتُوا الْعِلْمَ دَرَجَاتٍ', 'Allah akan meninggikan orang-orang yang beriman di antaramu dan orang-orang yang diberi ilmu pengetahuan beberapa derajat...', 'Dorongan dan keutamaan menuntut ilmu dalam Islam.', 'pendidikan, keutamaan ilmu')
ON DUPLICATE KEY UPDATE `id`=`id`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `facebook_id` varchar(100) DEFAULT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `role` enum('pelajar','peneliti','guru','umum') NOT NULL DEFAULT 'umum',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_lahir` date DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `no_wa` varchar(20) DEFAULT NULL,
  `zona_waktu` varchar(10) DEFAULT 'WIB',
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `google_id`, `facebook_id`, `nama_lengkap`, `email`, `foto`, `phone_number`, `role`, `avatar`, `created_at`, `tanggal_lahir`, `lokasi`, `no_wa`, `zona_waktu`, `password`) VALUES
(6, '101492372497759363223', NULL, 'fauziya abadi', 'fauziyaabadidud@gmail.com', NULL, NULL, 'umum', 'https://lh3.googleusercontent.com/a/ACg8ocJM74HfqK2AzzzGj_61OxaarHcwNomU27AZ5R_9gJ-3wUXW7g=s96-c', '2026-10-16 14:04:18', NULL, 'Tangerang Selatan, Banten (-6.3082, 106.7048)', NULL, 'WIB', ''),
(7, '100619802802014810872', NULL, 'Nia Aprihansah', 'nia.aprihansah@gmail.com', NULL, NULL, 'umum', 'https://lh3.googleusercontent.com/a/ACg8ocJbeECOy-o-3MYPybxrW2ftc_lVYT7X-TFBXrChRlJk2gjPNKP9Tw=s96-c', '2026-10-17 14:59:43', NULL, NULL, NULL, 'WIB', ''),
(8, '113275953782982279376', NULL, 'Surya Aprihansah', 'aprihansah88@gmail.com', NULL, NULL, 'umum', 'https://lh3.googleusercontent.com/a/ACg8ocIMXCjkiaLkSbPBHeBPw3voT7Eyy7AE1_4Avbonx34eTYNyVRk=s96-c', '2026-10-17 15:01:04', NULL, NULL, NULL, 'WIB', ''),
(9, '103709783817142291358', NULL, 'Jojo Jojok', 'jojobgscoc01@gmail.com', NULL, NULL, 'umum', 'https://lh3.googleusercontent.com/a/ACg8ocJyMD5MjhUCddTl1KNsZqFWkpqovhTxQLU5N_kLnCiPpRewig=s96-c', '2026-10-19 07:41:55', NULL, NULL, NULL, 'WIB', ''),
(10, '106302688790547317837', NULL, 'Mari Kita Mengaji', '80azizz@gmail.com', NULL, NULL, 'umum', 'https://lh3.googleusercontent.com/a/ACg8ocKaHaocBmR47FGN5QzceXDqz02OLCg4-D4AJse7x9ttYFSkpOE=s96-c', '2026-10-19 09:00:05', NULL, NULL, NULL, 'WIB', ''),
(11, '116056028549754862290', NULL, 'Davit Ronaldo', 'ronaldodavit23@gmail.com', NULL, NULL, 'umum', 'https://lh3.googleusercontent.com/a/ACg8ocI53RpExp1vM1ViyNw6zUI50wn7YL36nSrdbnd7Ge9sTJx_SWFD=s96-c', '2026-10-19 09:06:44', NULL, NULL, NULL, 'WIB', ''),
(12, '106700300779072882158', NULL, 'Risa Fauziah', 'fauziah.ica222@gmail.com', NULL, NULL, 'umum', 'https://lh3.googleusercontent.com/a/ACg8ocKFFp4mSneleLIGNgsXnGZZQPanv02xOtgoaauqh1u1iLRMM-Zd=s96-c', '2026-10-19 09:06:55', NULL, NULL, NULL, 'WIB', ''),
(14, '111207903994886256360', NULL, 'Muhammad Bennov Al-Fath', 'mbennovalfath@gmail.com', NULL, NULL, 'umum', 'https://lh3.googleusercontent.com/a/ACg8ocIdynNIb1h7ndSLLLjG-OpoBCOOjvgrO1JHQmEZVHVx4o7VpmN6=s96-c', '2026-10-21 13:48:19', NULL, NULL, NULL, 'WIB', ''),
(15, '109717622571109734027', NULL, 's rakss', 'srakss255@gmail.com', NULL, NULL, 'umum', 'https://lh3.googleusercontent.com/a/ACg8ocKyRZcVbJNwbJsMoobgat6_a8eoUGetuAU-CV3a5RMvN9Ks7kM=s96-c', '2026-10-21 13:49:48', NULL, NULL, NULL, 'WIB', '')
ON DUPLICATE KEY UPDATE `id`=`id`;

ALTER TABLE `ai_study_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `daily_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `learning_contents`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`);

ALTER TABLE `ai_study_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `daily_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `learning_contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

ALTER TABLE `ai_study_logs`
  ADD CONSTRAINT `ai_study_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `daily_subscriptions`
  ADD CONSTRAINT `daily_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

COMMIT;

