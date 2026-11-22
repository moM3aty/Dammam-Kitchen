-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 22, 2026 at 09:55 PM
-- Server version: 11.8.9-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u315614949_kitchen`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_values`
--

CREATE TABLE `about_values` (
  `id` int(11) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `about_values`
--

INSERT INTO `about_values` (`id`, `icon`, `title`, `description`, `sort_order`, `is_active`) VALUES
(1, '🪟', 'مطابخ ألمنيوم', 'تفصيل بتصاميم عصرية.', 1, 1),
(2, '🪵', 'مطابخ خشب', 'مودرن وكلاسيك حسب ذوقك.', 2, 1),
(3, '🪨', 'تغيير رخام', 'تفصيل وتركيب بقياس دقيق.', 3, 1),
(4, '🛠️', 'صيانة المطابخ', 'جميع أنواع المطابخ.', 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `name`, `sort_order`, `is_active`) VALUES
(1, 'الدمام', 1, 1),
(2, 'الخبر', 2, 1),
(3, 'الظهران', 3, 1),
(4, 'القطيف', 4, 1),
(5, 'الجبيل', 5, 1),
(6, 'الأحساء', 6, 1),
(7, 'سيهات', 7, 1),
(8, 'صفوى', 8, 1),
(9, 'رأس تنورة', 9, 1),
(10, 'الخفجي', 10, 1),
(11, 'بقيق', 11, 1),
(12, 'الشاطئ', 12, 1),
(13, 'الفيصلية', 13, 1),
(14, 'الفاخرية', 14, 1),
(15, 'الجلوية', 15, 1),
(16, 'الراكة', 16, 1),
(17, 'العزيزية', 17, 1),
(18, 'الحمراء', 18, 1),
(19, 'الروضة', 19, 1),
(20, 'الأمانة', 20, 1),
(21, 'الندى', 21, 1),
(22, 'الشعلة', 22, 1),
(23, 'طيبة', 23, 1),
(24, 'غرناطة', 24, 1),
(25, 'الريان', 25, 1),
(26, 'النزهة', 26, 1),
(27, 'بدر', 27, 1),
(28, 'النخيل', 28, 1),
(29, 'المنتزه', 29, 1),
(30, 'وجميع أحياء الدمام', 30, 1);

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question` varchar(500) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `sort_order`, `is_active`) VALUES
(1, 'هل تخدمون جميع أحياء الدمام والمنطقة الشرقية؟', 'مقرنا في الدمام ونخدم أحياءها والمدن المجاورة مثل الخبر والظهران والقطيف والجبيل والأحساء وغيرها. أخبرنا بموقعك لنؤكد لك موعد المعاينة.', 1, 1),
(2, 'ما الفرق بين مطبخ الألمنيوم ومطبخ الخشب؟', 'مطبخ الألمنيوم يتميز بتحمل الرطوبة وبشكله العصري، ومطبخ الخشب يمنح دفئاً وتنوعاً في الأشكال الكلاسيكية والمودرن. الاختيار يعتمد على ذوقك وميزانيتك ومكان المطبخ، ونوضح لك ذلك عند المعاينة.', 2, 1),
(3, 'هل يمكن تغيير الرخام فقط دون تغيير المطبخ؟', 'نعم، يمكن تغيير رخام المطبخ فقط مع الإبقاء على الدواليب إذا كانت بحالة جيدة. نقيس المكان ونوضح لك الخيارات والخامات المتاحة قبل الاتفاق.', 3, 1),
(4, 'ما أنواع المطابخ التي تقدمون لها الصيانة؟', 'نقدم الصيانة لجميع أنواع المطابخ، ألمنيوم وخشب وغيرها، وتشمل المفصلات والأبواب والأدراج والواجهات وغير ذلك بحسب المشكلة.', 4, 1),
(5, 'كيف يتم تحديد السعر؟', 'يعتمد السعر على نوع المطبخ والخامة والمقاسات والتشطيب والملحقات. نقدّم لك تصوراً واضحاً بعد القياس والاتفاق على التفاصيل.', 5, 1),
(6, 'هل يتم القياس في موقعي؟', 'نعم، نرتب معك زيارة لقياس المطبخ ومعاينته في موقعك حتى يكون التفصيل أو الرخام أو الصيانة على أساس صحيح.', 6, 1),
(7, 'كيف أتواصل معكم للحجز؟', 'اتصل أو راسلنا على واتساب على الرقم 0507023985، أو استخدم نموذج التواصل وسيفتح لك واتساب برسالة جاهزة.', 7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `type` enum('img','vid') NOT NULL DEFAULT 'img',
  `file_path` varchar(500) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `type`, `file_path`, `alt_text`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'img', 'images/photo_6050667996268662845_y.jpg', 'أعمال شبح المطابخ', 1, 1, '2026-11-22 19:43:26'),
(2, 'img', 'images/photo_6050667996268662846_y.jpg', 'أعمال شبح المطابخ', 2, 1, '2026-11-22 19:43:26'),
(3, 'img', 'images/photo_6050667996268662847_y.jpg', 'أعمال شبح المطابخ', 3, 1, '2026-11-22 19:43:26'),
(4, 'img', 'images/photo_6050667996268662848_y.jpg', 'أعمال شبح المطابخ', 4, 1, '2026-11-22 19:43:26'),
(5, 'img', 'images/photo_6050667996268662849_y.jpg', 'أعمال شبح المطابخ', 5, 1, '2026-11-22 19:43:26'),
(6, 'img', 'images/photo_6050667996268662850_y.jpg', 'أعمال شبح المطابخ', 6, 1, '2026-11-22 19:43:26'),
(7, 'img', 'images/photo_6050667996268662857_y.jpg', 'أعمال شبح المطابخ', 7, 1, '2026-11-22 19:43:26'),
(8, 'img', 'images/photo_6050667996268662858_y.jpg', 'أعمال شبح المطابخ', 8, 1, '2026-11-22 19:43:26'),
(9, 'img', 'images/photo_6050667996268662859_y.jpg', 'أعمال شبح المطابخ', 9, 1, '2026-11-22 19:43:26'),
(10, 'img', 'images/photo_6050667996268662860_y.jpg', 'أعمال شبح المطابخ', 10, 1, '2026-11-22 19:43:26'),
(11, 'img', 'images/photo_6050667996268662861_y.jpg', 'أعمال شبح المطابخ', 11, 1, '2026-11-22 19:43:26'),
(12, 'img', 'images/photo_6050667996268662862_y.jpg', 'أعمال شبح المطابخ', 12, 1, '2026-11-22 19:43:26'),
(13, 'img', 'images/photo_6050667996268662863_y.jpg', 'أعمال شبح المطابخ', 13, 1, '2026-11-22 19:43:26'),
(14, 'img', 'images/photo_6050667996268662864_y.jpg', 'أعمال شبح المطابخ', 14, 1, '2026-11-22 19:43:26'),
(15, 'img', 'images/photo_6050667996268662865_y.jpg', 'أعمال شبح المطابخ', 15, 1, '2026-11-22 19:43:26'),
(16, 'img', 'images/photo_6050667996268662866_y.jpg', 'أعمال شبح المطابخ', 16, 1, '2026-11-22 19:43:26'),
(17, 'img', 'images/photo_6050667996268662867_y.jpg', 'أعمال شبح المطابخ', 17, 1, '2026-11-22 19:43:26'),
(18, 'img', 'images/photo_6050667996268662868_y.jpg', 'أعمال شبح المطابخ', 18, 1, '2026-11-22 19:43:26'),
(19, 'vid', 'images/document_6050667995808670481.mp4', 'فيديو مطبخ', 19, 1, '2026-11-22 19:43:26'),
(20, 'vid', 'images/document_6050667995808670483.mp4', 'فيديو مطبخ', 20, 1, '2026-11-22 19:43:26'),
(21, 'vid', 'images/document_6050667995808670484.mp4', 'فيديو مطبخ', 21, 1, '2026-11-22 19:43:26'),
(22, 'vid', 'images/document_6050667995808670485.mp4', 'فيديو مطبخ', 22, 1, '2026-11-22 19:43:26'),
(23, 'vid', 'images/document_6050667995808670486.mp4', 'فيديو مطبخ', 23, 1, '2026-11-22 19:43:26'),
(24, 'vid', 'images/document_6050667995808670488.mp4', 'فيديو مطبخ', 24, 1, '2026-11-22 19:43:26'),
(25, 'vid', 'images/document_6050667995808670492.mp4', 'فيديو مطبخ', 25, 1, '2026-11-22 19:43:26'),
(26, 'vid', 'images/document_5766941219284329118.mp4', 'فيديو مطبخ', 26, 1, '2026-11-22 19:43:26'),
(27, 'vid', 'images/document_5766941219284329120.mp4', 'فيديو مطبخ', 27, 1, '2026-11-22 19:43:26'),
(28, 'vid', 'images/document_5766941219284329121.mp4', 'فيديو مطبخ', 28, 1, '2026-11-22 19:43:26'),
(29, 'vid', 'images/document_5766941219284329122.mp4', 'فيديو مطبخ', 29, 1, '2026-11-22 19:43:26'),
(30, 'vid', 'images/document_5766941219284329123.mp4', 'فيديو مطبخ', 30, 1, '2026-11-22 19:43:26'),
(31, 'vid', 'images/document_5766941219284329124.mp4', 'فيديو مطبخ', 31, 1, '2026-11-22 19:43:26'),
(32, 'vid', 'images/document_5766941219284329125.mp4', 'فيديو مطبخ', 32, 1, '2026-11-22 19:43:26'),
(33, 'vid', 'uploads/gallery/ec3ea4a388a906410cf0.mp4', '', 0, 1, '2026-11-22 20:51:00');

-- --------------------------------------------------------

--
-- Table structure for table `kitchen_types`
--

CREATE TABLE `kitchen_types` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `kitchen_types`
--

INSERT INTO `kitchen_types` (`id`, `name`, `sort_order`, `is_active`) VALUES
(1, 'مطبخ مودرن', 1, 1),
(2, 'مطبخ كلاسيك', 2, 1),
(3, 'مطبخ نيوكلاسيك', 3, 1),
(4, 'مطبخ حرف L', 4, 1),
(5, 'مطبخ حرف U', 5, 1),
(6, 'مطبخ جزيرة', 6, 1),
(7, 'مطبخ مفتوح', 7, 1),
(8, 'مطبخ صغير', 8, 1),
(9, 'مطبخ فيلا', 9, 1),
(10, 'مطبخ شقة', 10, 1),
(11, 'مطبخ استراحة', 11, 1),
(12, 'مطبخ خارجي', 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `username` varchar(120) DEFAULT NULL,
  `success` tinyint(1) DEFAULT NULL,
  `attempted_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `ip_address`, `username`, `success`, `attempted_at`) VALUES
(1, '197.54.110.233', 'admin', 1, '2026-11-22 19:43:43'),
(2, '197.54.110.233', 'admin', 1, '2026-11-22 19:53:59'),
(3, '2001:16a2:c0c3:d37a:2:1:ae6:3432', 'admin', 1, '2026-11-22 20:36:04');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `name`, `icon`, `description`, `sort_order`, `is_active`) VALUES
(1, 'الألمنيوم', '🪟', 'للمطابخ التي تحتاج مقاومة أكبر للرطوبة.', 1, 1),
(2, 'الخشب المصنّع', '🪵', 'خامات متنوعة لتصاميم مودرن وكلاسيك.', 2, 1),
(3, 'الخشب الطبيعي', '🌳', 'لمن يفضل مظهراً خشبياً أصيلاً.', 3, 1),
(4, 'الرخام', '🪨', 'أسطح للمطبخ بأشكال وألوان متعددة.', 4, 1),
(5, 'الجرانيت والكوارتز', '💎', 'خيارات عملية لأسطح العمل.', 5, 1),
(6, 'الملحقات', '🔩', 'مفصلات ومقابض وأدراج وسكك.', 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `city` varchar(120) DEFAULT NULL,
  `area` varchar(120) DEFAULT NULL,
  `service` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `keywords` varchar(500) DEFAULT NULL,
  `button_text` varchar(150) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `title`, `description`, `details`, `icon`, `features`, `keywords`, `button_text`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'تفصيل مطابخ ألمنيوم في الدمام', 'مطابخ الألمنيوم من الخيارات التي يفضلها كثيرون في مدن الساحل ذات الرطوبة، لأن الألمنيوم لا يتأثر بالماء والبخار كما يتأثر الخشب العادي. نفصّل لك مطبخ الألمنيوم على مقاس موقعك بالضبط، بهيكل وواجهات وأدراج وأبواب تناسب طريقة استخدامك، ويمكن اختيار ألوان وتشطيبات متنوعة بين المودرن والبسيط والفاخر، مع إضافة الزجاج في بعض الواجهات إن رغبت.', 'نناقش معك توزيع الدواليب العلوية والسفلية، وأماكن الفرن والموقد والمغسلة، وأدراج الأواني والمخزن، لتخرج مساحة المطبخ عملية ومرتبة.', '🪟', '[\"تفصيل على مقاس المطبخ\",\"هيكل وواجهات ألمنيوم\",\"ألوان وتشطيبات متنوعة\",\"أدراج ومفصلات وملحقات\",\"مناسب للأجواء الرطبة\",\"تركيب مرتب في الموقع\"]', 'مطابخ ألمنيوم الدمام, تفصيل مطابخ ألمنيوم, مطابخ ألمنيوم مودرن, اسعار مطابخ ألمنيوم', 'اطلب تفصيل مطبخ ألمنيوم', 1, 1, '2026-11-22 19:43:26'),
(2, 'تفصيل مطابخ خشب في الدمام', 'مطابخ الخشب تمنح المكان دفئاً وأناقة، وتتنوع بين المودرن والكلاسيك والنيوكلاسيك. نفصّلها بخامات خشبية مختلفة وتشطيبات بألوان هادئة أو لاكيه أو شكل خشبي طبيعي، حسب ذوقك وميزانيتك.', '', '🪵', '[\"مودرن وكلاسيك ونيوكلاسيك\",\"خامات خشبية متنوعة\",\"تشطيبات وألوان حسب الطلب\",\"توزيع عملي للأدراج والأرفف\"]', 'مطابخ خشب الدمام, تفصيل مطابخ خشب, مطابخ خشب مودرن', 'اطلب مطبخ خشب', 2, 1, '2026-11-22 19:43:26'),
(3, 'تغيير رخام المطابخ في الدمام', 'الرخام هو واجهة المطبخ الأولى، وتغييره يجدد المكان كله حتى مع بقاء الدواليب. نقيس الرخام بدقة ونفصّله حسب شكل المطبخ، مع فتحات الحوض والموقد والحواف المناسبة، ونركّبه بترتيب ونظافة. وتتوفر خيارات متعددة من الرخام والجرانيت والكوارتز بحسب المتاح.', '', '🪨', '[\"قياس دقيق للرخام\",\"فتحات حوض وموقد\",\"إزالة القديم وتركيب الجديد\",\"خيارات رخام وجرانيت وكوارتز\"]', 'تغيير رخام مطبخ, رخام مطابخ الدمام, تركيب رخام', 'اطلب تغيير الرخام', 3, 1, '2026-11-22 19:43:26'),
(4, 'صيانة جميع أنواع المطابخ في الدمام', 'سواء كان مطبخك ألمنيوم أو خشب أو من أي نوع آخر، نقدّم صيانة شاملة تعيد له عمله وشكله. نبدأ بفحص المشكلة وتحديد سببها، ثم نعالجها بقطع مناسبة وتركيب دقيق، فلا نعالج الظاهر فقط ونترك السبب.', 'وإذا كان المطبخ قديماً ويحتاج تغييراً جزئياً، فيمكن تبديل الواجهات والأبواب والأدراج أو الرخام فقط للحصول على شكل جديد دون إعادة تفصيل المطبخ بالكامل.', '🛠️', '[\"تغيير المفصلات والمقابض\",\"إصلاح وتعديل الأبواب\",\"إصلاح وتغيير الأدراج\",\"تبديل واجهات الدواليب\",\"معالجة الأجزاء المتضررة\",\"تجديد المطابخ القديمة\"]', 'صيانة مطابخ الدمام, صيانة مطابخ ألمنيوم, صيانة مطابخ خشب, تجديد مطابخ', 'اطلب صيانة المطبخ', 4, 1, '2026-11-22 19:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('about_lead', 'شبح المطابخ في الدمام متخصص في تفصيل المطابخ الألمنيوم والخشب وتغيير رخام المطابخ وصيانة جميع أنواع المطابخ، لمن يريد مطبخاً عملياً وأنيقاً مصنوعاً على مقاس مساحته، دون التنقل بين أكثر من فني ومورد.', '2026-11-22 19:43:26'),
('about_p1', 'المطبخ هو أكثر مكان تتكرر فيه الحركة في البيت، من الطبخ والغسيل والتخزين وتجمّع الأسرة، لذلك نهتم بأن تكون الأدراج والأبواب والأرفف والرخام في أماكنها الصحيحة وبمقاسات تناسب استخدامك. نبدأ بالقياس والمعاينة في موقعك، ثم نناقش معك الشكل والخامات والألوان، ونوضّح لك الخيارات بين مطابخ الألمنيوم ومطابخ الخشب وما يناسب كل منها.', '2026-11-22 19:43:26'),
('about_p2', 'وإذا كان لديك مطبخ قائم تريد إصلاحه أو تجديده، فنقدّم صيانة المطابخ بمختلف أنواعها، من تغيير المفصلات والأبواب والأدراج إلى تبديل الواجهات وتغيير الرخام، حتى يعود المطبخ جميلاً ومريحاً في الاستخدام. ونخدم الدمام وأحيائها وجميع مدن المنطقة الشرقية.', '2026-11-22 19:43:26'),
('geo_placename', 'الدمام - المنطقة الشرقية', '2026-11-22 19:43:26'),
('geo_region', 'SA-04', '2026-11-22 19:43:26'),
('hero_desc_1', 'شبح المطابخ في الدمام يقدّم لك تفصيل مطابخ ألمنيوم ومطابخ خشب بتصاميم مودرن وكلاسيك على مقاس مطبخك بالضبط، وتغيير رخام المطابخ بتفصيل دقيق وتركيب مرتب، وصيانة جميع أنواع المطابخ سواء كانت ألمنيوم أو خشب أو مطابخ قديمة تحتاج تجديداً.', '2026-11-22 19:43:26'),
('hero_desc_2', 'نخدم الدمام وأحيائها والخبر والظهران والقطيف والجبيل والأحساء وكل مدن المنطقة الشرقية، ونبدأ معك بقياس ومعاينة في موقعك لتحصل على مطبخ يناسب مساحتك واستخدامك وذوقك.', '2026-11-22 19:43:26'),
('hero_h1_sub', 'تفصيل مطابخ ألمنيوم وخشب، وتغيير رخام، وصيانة جميع أنواع المطابخ في الدمام', '2026-11-22 19:43:26'),
('hero_location', 'الدمام الشرقية', '2026-11-22 19:43:26'),
('hero_name_1', 'شبح', '2026-11-22 19:43:26'),
('hero_name_2', 'المطابخ', '2026-11-22 19:43:26'),
('hero_tag', '📍 الدمام · المنطقة الشرقية · المملكة العربية السعودية', '2026-11-22 19:43:26'),
('meta_description', 'شبح المطابخ في الدمام – المنطقة الشرقية: تفصيل مطابخ ألمنيوم وخشب بتصاميم مودرن وكلاسيك، تغيير رخام المطابخ، وصيانة جميع أنواع المطابخ. اتصل 0507023985', '2026-11-22 19:43:26'),
('meta_keywords', 'شبح المطابخ, شبح المطابخ الدمام, تفصيل مطابخ الدمام, تفصيل مطابخ في الدمام, تفصيل مطابخ المنطقة الشرقية, مطابخ ألمنيوم الدمام, تفصيل مطابخ ألمنيوم, مطابخ ألمنيوم مودرن, مطابخ ألمنيوم للرطوبة, اسعار مطابخ ألمنيوم في الدمام, مطابخ خشب الدمام, تفصيل مطابخ خشب, مطابخ خشب مودرن, مطابخ خشب كلاسيك, مطابخ خشب mdf, مطابخ خشب hdf, مطابخ خشب طبيعي, مطابخ نيوكلاسيك, مطابخ مودرن, مطابخ كلاسيك, مطابخ حديثة, مطابخ عصرية, مطابخ جاهزة, مطابخ على شكل حرف L, مطابخ على شكل حرف U, مطابخ جزيرة, مطابخ مفتوحة, مطابخ صغيرة, مطابخ فلل, مطابخ شقق, مطابخ استراحات, مطابخ خارجية, تغيير رخام المطابخ, تغيير رخام مطبخ الدمام, رخام مطابخ الدمام, تركيب رخام مطبخ, تفصيل رخام مطابخ, رخام جرانيت مطابخ, رخام كوارتز مطابخ, تبديل رخام المطبخ, صيانة مطابخ الدمام, صيانة جميع انواع المطابخ, صيانة مطابخ ألمنيوم, صيانة مطابخ خشب, تغيير مفصلات المطابخ, تغيير ابواب المطابخ, تغيير واجهات المطابخ, اصلاح ادراج المطابخ, تجديد المطابخ القديمة, ترميم المطابخ, نجار مطابخ الدمام, معلم مطابخ الدمام, فني مطابخ الدمام, افضل شركة تفصيل مطابخ في الدمام, تصميم مطابخ الدمام, معاينة مجانية, مطابخ الخبر, مطابخ الظهران, مطابخ القطيف, مطابخ الجبيل, مطابخ الاحساء, مطابخ سيهات, مطابخ صفوى, مطابخ رأس تنورة, مطابخ الخفجي, مطابخ بقيق, مطابخ حي الشاطئ, مطابخ حي الفيصلية, مطابخ حي الفاخرية, مطابخ حي الجلوية, مطابخ حي الراكة, مطابخ حي العزيزية, مطابخ حي الحمراء, مطابخ حي الروضة, مطابخ حي الأمانة, مطابخ حي الندى, مطابخ حي الشعلة, مطابخ حي طيبة, مطابخ حي غرناطة, مطابخ حي الريان, مطابخ حي النزهة, مطابخ حي بدر, مطابخ حي النخيل, مطابخ حي أحد, مطابخ حي المنتزه, مطابخ ضاحية الملك فهد, الدمام, المنطقة الشرقية, المملكة العربية السعودية', '2026-11-22 19:45:06'),
('meta_title', 'شبح المطابخ | تفصيل مطابخ ألمنيوم وخشب وتغيير رخام وصيانة مطابخ في الدمام والمنطقة الشرقية', '2026-11-22 19:43:26'),
('site_city', 'الدمام', '2026-11-22 19:43:26'),
('site_country', 'SA', '2026-11-22 19:43:26'),
('site_email', 'admin@dammamkitchen.online', '2026-11-22 19:43:26'),
('site_name', 'شبح المطابخ', '2026-11-22 19:43:26'),
('site_phone', '0507023985', '2026-11-22 19:43:26'),
('site_region', 'المنطقة الشرقية', '2026-11-22 19:43:26'),
('site_tagline', 'الدمام الشرقية', '2026-11-22 19:43:26'),
('site_whatsapp', '966507023985', '2026-11-22 19:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `steps`
--

CREATE TABLE `steps` (
  `id` int(11) NOT NULL,
  `step_number` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `steps`
--

INSERT INTO `steps` (`id`, `step_number`, `title`, `description`, `sort_order`, `is_active`) VALUES
(1, 1, 'تواصل معنا', 'اتصل أو راسلنا على واتساب وأخبرنا بنوع الخدمة وحيك في الدمام أو مدينتك.', 1, 1),
(2, 2, 'المعاينة والقياس', 'نزور موقعك ونقيس المطبخ ونسمع احتياجك وطريقة استخدامك له.', 2, 1),
(3, 3, 'التصميم والاتفاق', 'نقترح التوزيع والخامة والألوان، ونتفق معك على التفاصيل والتكلفة بوضوح.', 3, 1),
(4, 4, 'التصنيع والتجهيز', 'نجهز المطبخ أو الرخام أو القطع المطلوبة على القياس المتفق عليه.', 4, 1),
(5, 5, 'التركيب', 'نركّب في موقعك بترتيب وعناية بالمكان، ونضبط الأبواب والأدراج.', 5, 1),
(6, 6, 'التسليم والمتابعة', 'نراجع معك النتيجة النهائية، ونبقى على تواصل لأي ملاحظة.', 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `client_name` varchar(120) NOT NULL,
  `avatar_letter` varchar(5) DEFAULT NULL,
  `content` text NOT NULL,
  `rating` tinyint(4) DEFAULT 5,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `client_name`, `avatar_letter`, `content`, `rating`, `sort_order`, `is_active`) VALUES
(1, 'أبو فيصل', 'أ', 'فصّلنا مطبخ ألمنيوم لشقتنا، وكان القياس دقيقاً والتركيب مرتباً. شرحوا لنا الخيارات قبل أن نقرر.', 5, 1, 1),
(2, 'أم عبدالله', 'أ', 'غيّرنا رخام المطبخ فقط وتغيّر شكل المكان بالكامل. كان العمل نظيفاً وتركوا المكان مرتباً.', 5, 2, 1),
(3, 'محمد الدوسري', 'م', 'طلبت صيانة لمطبخ قديم، تغيّرت المفصلات والأدراج وصار الاستخدام أسهل. تعامل محترم وواضح.', 5, 3, 1),
(4, 'نورة العتيبي', 'ن', 'مطبخ الخشب كان بالتصميم الذي اخترناه، وساعدونا في توزيع الأدراج بشكل عملي.', 5, 4, 1),
(5, 'عبدالعزيز الشمري', 'ع', 'التواصل كان سريعاً ومرتباً من أول اتصال حتى انتهاء العمل، وكان الاتفاق واضحاً.', 5, 5, 1),
(6, 'سارة القحطاني', 'س', 'جددنا واجهات المطبخ دون تغيير كامل، وكانت النتيجة أنيقة وبتكلفة مناسبة لنا.', 5, 6, 1),
(7, 'خالد الحربي', 'خ', 'أعجبني اهتمامهم بالقياسات والتفاصيل الصغيرة، وركّبوا كل شيء في مكانه بعناية.', 5, 7, 1),
(8, 'هند الغامدي', 'ه', 'اخترنا مطبخاً بتصميم كلاسيك، وكانت الألوان والخامات مناسبة للبيت. أنصح بالتواصل معهم.', 5, 8, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(120) DEFAULT NULL,
  `role` enum('admin','editor') DEFAULT 'admin',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin@dammamkitchen.online', '$2y$10$JGXsQ.EQL3Ebfqc2p3NSUOP6/0CMT3Ozf6h/HnVgawEwpdllZTUFC', 'مدير الموقع', 'admin', '2026-11-22 20:36:04', '2026-11-22 19:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `why_us`
--

CREATE TABLE `why_us` (
  `id` int(11) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `why_us`
--

INSERT INTO `why_us` (`id`, `icon`, `title`, `description`, `sort_order`, `is_active`) VALUES
(1, '📐', 'تفصيل على المقاس', 'نقيس موقعك بدقة ونصمم المطبخ بحسب مساحته لا بمقاسات جاهزة.', 1, 1),
(2, '🧩', 'خدمات متكاملة', 'ألمنيوم وخشب ورخام وصيانة من جهة واحدة دون تعدد الفنيين.', 2, 1),
(3, '🎨', 'خيارات متنوعة', 'تصاميم مودرن وكلاسيك وألوان وتشطيبات تناسب ذوقك.', 3, 1),
(4, '🛠️', 'صيانة لكل الأنواع', 'نصلح مطابخ الألمنيوم والخشب وغيرها، ونجدد القديم منها.', 4, 1),
(5, '🤝', 'اتفاق واضح', 'نوضح التفاصيل والخامات والتكلفة قبل بدء العمل.', 5, 1),
(6, '📍', 'نخدم الشرقية', 'الدمام والخبر والظهران والقطيف والجبيل والأحساء وغيرها.', 6, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_values`
--
ALTER TABLE `about_values`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kitchen_types`
--
ALTER TABLE `kitchen_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip_address` (`ip_address`),
  ADD KEY `attempted_at` (`attempted_at`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `steps`
--
ALTER TABLE `steps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `why_us`
--
ALTER TABLE `why_us`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_values`
--
ALTER TABLE `about_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `kitchen_types`
--
ALTER TABLE `kitchen_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `steps`
--
ALTER TABLE `steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `why_us`
--
ALTER TABLE `why_us`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
