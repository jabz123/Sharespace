-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 08, 2026 at 09:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sharedspace`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(500) NOT NULL,
  `excerpt` text NOT NULL,
  `content` longtext NOT NULL,
  `author_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `trust_score` tinyint(4) NOT NULL DEFAULT 80,
  `has_media` tinyint(1) NOT NULL DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `is_premium_only` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `excerpt`, `content`, `author_id`, `category_id`, `trust_score`, `has_media`, `image_path`, `is_premium_only`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'OpenAI Announces GPT-5 With Multimodal Reasoning Capabilities', 'The latest flagship model from OpenAI promises a significant leap in reasoning, code generation, and real-time visual understanding.', 'OpenAI has officially unveiled GPT-5, its most capable large language model to date. The new model introduces what the company calls \"chain-of-thought fusion\" — a technique that allows the model to reason across text, images, and structured data simultaneously.\n\nIn benchmark tests, GPT-5 outperformed its predecessor on the MMLU reasoning suite by 14 percentage points and achieved near-human performance on the MedQA dataset used for medical licensing examinations.\n\nChief Technology Officer Mira Murati described the release as \"a step change rather than an incremental improvement.\" The model is immediately available to ChatGPT Plus subscribers and through the OpenAI API.\n\nSafety researchers say GPT-5 underwent six months of red-teaming and alignment training before release. An independent audit found no evidence of deceptive behaviour during testing.', 1, 1, 80, 0, NULL, 0, '2025-01-08 09:00:00', '2026-03-07 19:48:14', '2026-03-07 19:48:14'),
(2, 'EU\'s AI Act Enforcement Begins: What Tech Companies Must Do Now', 'With the first wave of AI Act obligations now in force, companies operating in Europe face strict transparency and risk classification requirements.', 'The European Union\'s landmark Artificial Intelligence Act has entered its first enforcement phase, requiring companies deploying AI systems in Europe to classify their tools by risk level and implement corresponding compliance measures.\n\nHigh-risk AI systems — including those used in hiring, credit scoring, and biometric identification — must now undergo mandatory conformity assessments and register in a new EU-wide AI database.\n\nThe European AI Office confirmed that penalties for non-compliance can reach €35 million or 7% of global annual turnover, whichever is higher.\n\nSeveral major US technology firms including Microsoft, Google, and Meta have established dedicated EU AI compliance teams in Brussels.', 1, 1, 80, 0, NULL, 0, '2025-01-20 08:00:00', '2026-03-07 19:48:14', '2026-03-07 19:48:14'),
(3, 'Are Foldable Phones Finally Ready for the Mainstream?', 'With improved durability and falling prices, foldable smartphones are attracting a wider audience — but concerns about longevity remain.', 'Foldable smartphones have been available since Samsung introduced the Galaxy Fold in 2019, but the category has long struggled to break into mainstream adoption due to high prices and fragile displays.\n\nSamsung\'s latest Galaxy Z Fold 6 starts at $1,099 — down from $1,799 at launch four years ago — and independent durability testing rated the hinge mechanism for over 200,000 folds before showing degradation.\n\nIDC data shows foldable smartphone shipments grew 52% year-over-year in 2024, reaching 28 million units globally.\n\nCritics note that crease visibility, display brightness in direct sunlight, and software optimisation remain areas where foldables lag behind conventional flagship devices.', 1, 1, 80, 0, NULL, 0, '2025-03-01 10:00:00', '2026-03-07 19:48:14', '2026-03-07 19:48:14'),
(4, 'Breakthrough Cancer Immunotherapy Shows 94% Remission Rate in Trial', 'A personalised mRNA vaccine combined with checkpoint inhibitors has produced remarkable results in a Phase 2 melanoma trial.', 'Researchers at Memorial Sloan Kettering Cancer Center have published results from a Phase 2 clinical trial showing a 94% remission rate in patients with advanced melanoma treated with a combination of a personalised mRNA cancer vaccine and the checkpoint inhibitor pembrolizumab.\n\nThe trial enrolled 157 participants with stage III or IV melanoma who had not previously responded to standard treatment. After 18 months of follow-up, 94% showed no evidence of disease.\n\nThe personalised vaccine is manufactured individually for each patient based on genomic sequencing of their tumour. The FDA has granted Breakthrough Therapy designation.\n\nHealth economists estimate the process currently costs approximately $100,000 per patient, though mass production techniques could reduce this substantially.', 3, 2, 80, 0, NULL, 0, '2025-01-09 08:30:00', '2026-03-07 19:48:14', '2026-03-07 19:48:14'),
(5, 'Antarctic Ice Sheet Loss Accelerating Faster Than Models Predicted', 'New satellite data reveals ice mass loss from the West Antarctic Ice Sheet has increased by 75% over the past decade.', 'A study published in Nature Geoscience has found that the West Antarctic Ice Sheet is losing mass at a rate 75% higher than measurements taken a decade ago, significantly exceeding predictions of the most pessimistic climate models.\n\nUsing data from the ESA\'s CryoSat-2 satellite and NASA\'s GRACE-FO mission, researchers calculated annual ice mass loss of 212 gigatons in 2024 — up from 121 gigatons in 2015.\n\nThe acceleration is primarily attributed to warm circumpolar deep water intruding beneath the Thwaites and Pine Island glaciers.\n\nIf current trends continue, the researchers project sea level contributions from West Antarctica alone could reach 0.5 metres by 2100.', 3, 2, 80, 0, NULL, 0, '2025-02-14 08:00:00', '2026-03-07 19:48:14', '2026-03-07 19:48:14'),
(6, 'New Study Links Ultra-Processed Food to 32 Adverse Health Outcomes', 'A meta-analysis of 45 studies covering over 10 million participants finds strong associations between UPF intake and cardiovascular disease and depression.', 'A landmark meta-analysis published in the British Medical Journal has found consistent associations between high consumption of ultra-processed foods and 32 negative health outcomes, including a 50% increased risk of cardiovascular disease-related death and a 48% higher risk of depression.\n\nThe analysis synthesised data from 45 pooled studies covering more than 10 million participants across North America, Europe, and Australia.\n\nThe study\'s authors acknowledge important limitations: most underlying research is observational, making it impossible to establish causation from association alone.\n\nDespite these caveats, the researchers argue the breadth of associations provides a strong basis for public health recommendations.', 2, 2, 80, 0, NULL, 0, '2025-02-05 10:00:00', '2026-03-07 19:48:14', '2026-03-07 19:48:14'),
(7, 'Federal Reserve Holds Rates Steady as Inflation Proves Stubborn', 'The FOMC voted unanimously to maintain the federal funds rate at 4.25-4.5%, citing persistent services inflation and a resilient labour market.', 'The Federal Open Market Committee voted unanimously to hold the federal funds rate in the target range of 4.25% to 4.5%, pausing what had been an extended easing cycle.\n\nIn the statement accompanying the decision, the Committee noted that inflation remains somewhat elevated, with core services inflation running at approximately 3.6%.\n\nChair Jerome Powell indicated that the Committee would need to see \"several more months of good data\" before resuming rate cuts.\n\nEconomists at JPMorgan and Goldman Sachs both revised their Fed rate forecasts, now projecting only one 25-basis-point cut in 2025.', 1, 4, 80, 0, NULL, 0, '2025-01-30 08:30:00', '2026-03-07 19:48:14', '2026-03-07 19:48:14'),
(8, 'The Housing Crisis Is Getting Worse in Every Major City', 'A structural shortage of homes, restrictive planning laws, and rising construction costs are keeping housing unaffordable across the developed world.', 'Housing affordability has deteriorated to historically extreme levels across major cities, with the median house price now representing more than 10 times the median household income in cities including London, Sydney, Vancouver, and San Francisco.\n\nRestrictive zoning laws in most major cities prohibit high-density housing in large swaths of land close to employment centres, limiting supply precisely where demand is greatest.\n\nConstruction cost inflation has made new development increasingly marginal for housebuilders, rising approximately 40% in real terms since 2020.\n\nNew Zealand\'s experience following zoning reform in 2021 is frequently cited as evidence that supply-side reform can work, with building consents increasing 40% and rent growth decelerating.', 1, 4, 80, 0, NULL, 0, '2025-02-25 09:30:00', '2026-03-07 19:48:14', '2026-03-07 19:48:14'),
(9, 'Six Nations 2025: Ireland\'s Dominance Continues With Grand Slam', 'Ireland secured a third Grand Slam in eight years with a comprehensive 31-18 victory over England at Twickenham.', 'Ireland completed a third Six Nations Grand Slam since 2018 with a dominant 31-18 victory over England at Twickenham, confirming their status as the world\'s top-ranked rugby union team under head coach Andy Farrell.\n\nIreland\'s victory was built on a clinical first half in which they scored three tries through Hugo Keenan, James Lowe, and Caelan Doris, taking a 24-6 lead into the break.\n\nFly-half Jack Crowley delivered a composed performance, landing five conversions and a penalty.\n\nFrance, despite finishing third, ended the tournament with the most tries scored and will be regarded as Ireland\'s most credible challenger heading into the 2027 World Cup cycle.', 2, 5, 80, 0, NULL, 0, '2025-03-16 18:00:00', '2026-03-07 19:48:14', '2026-03-07 19:48:14'),
(10, 'The Science of Sports Nutrition: What the Evidence Actually Says', 'From protein timing to creatine and cold plunges, we separate well-supported sports science from marketing hype.', 'Sports nutrition is a field where rigorous science, commercial interests, and social media trends collide in ways that make it difficult for athletes to know what works.\n\nProtein consumption is the area with the strongest evidence base. A 2022 meta-analysis found that protein supplementation significantly increases muscle mass and strength gains from resistance training, with a plateau effect around 1.6g per kilogram of body weight daily.\n\nCreatine monohydrate has one of the strongest evidence profiles of any legal performance supplement, with hundreds of studies supporting its effectiveness for short-duration, high-intensity exercise.\n\nCold water immersion is effective at reducing delayed onset muscle soreness but may blunt long-term hypertrophy adaptations by suppressing the inflammatory signalling that drives muscle growth.', 2, 5, 80, 0, NULL, 0, '2025-04-03 09:30:00', '2026-03-07 19:48:14', '2026-03-09 00:20:59'),
(11, 'NHS Approves Ozempic for Cardiovascular Risk Reduction', 'NICE has approved semaglutide for patients with established cardiovascular disease and BMI over 27, regardless of whether they have type 2 diabetes.', 'NICE has approved semaglutide for use in NHS patients with established cardiovascular disease and a BMI of 27 or above, regardless of whether they have a diabetes diagnosis.\n\nThe decision follows publication of the SELECT trial, which found that semaglutide reduced the risk of major adverse cardiovascular events by 20% compared to placebo over an average follow-up of 33 months.\n\nThe approval represents the first time any weight management medication has been approved on the basis of cardiovascular outcomes rather than weight loss alone.\n\nNHS England estimates approximately 340,000 patients would initially qualify, though supply constraints will require a phased rollout over at least two years.', 2, 6, 80, 0, NULL, 0, '2025-01-25 08:30:00', '2026-03-07 19:48:14', '2026-03-07 19:48:14'),
(12, 'Sleep Science Update: Trackers, Chronotypes, and Caffeine', 'Wearable sleep trackers are less accurate than manufacturers claim, and caffeine timing matters more than quantity.', 'Sleep trackers manufactured by Garmin, Apple, Fitbit, and Oura have made consumer-grade sleep staging a mainstream feature. However, a 2024 validation study found that commercial wearables misclassify sleep stages in 30-40% of epochs.\n\nChronotype research has moved beyond the simple morning/evening dichotomy. Genome-wide association studies have identified over 350 genetic variants associated with chronotype, suggesting it is a complex continuous trait.\n\nCaffeine\'s half-life of 5-6 hours means a 200mg dose at 2pm leaves 50mg active at midnight. A 2024 randomised trial found that morning-only caffeine consumption improved sleep quality scores by an average of 14% compared to afternoon consumption of the same total dose.', 2, 6, 80, 0, NULL, 0, '2025-03-03 10:00:00', '2026-03-07 19:48:14', '2026-03-07 19:48:14'),
(15, 'Sports test', 'Testing55', 'Sports insights and test', 5, 5, 85, 0, 'uploads/articles/1772912066_sports-tools_53876-138077.avif', 0, '2026-03-08 03:34:26', '2026-03-08 03:34:26', '2026-03-08 06:50:54'),
(16, 'How to manage your time between sports and studies or work', 'Sports are essential for holistic development, offering profound physical, mental, and social benefits, including improved fitness, discipline, and teamwork. Beyond personal health, they foster resilience and bridge cultural divides. A strong essay highlights these advantages while emphasizing how sports shape character, teach leadership, and promote a balanced, healthy life. ', '1', 5, 5, 80, 0, 'uploads/articles/1772919112_Youth-soccer-indiana.jpg', 0, '2026-03-08 05:31:52', '2026-03-08 05:31:52', '2026-03-08 23:56:58'),
(17, 'Health Sports', 'Maintain healthy through sports', 'Sports are very essential for every human life which keeps them fit and fine and physical strength. It has great importance in each stage of life. It also improves the personality of people. Sports keep our all organs alert and our hearts become stronger by regularly playing some kind of sports. sports has always given priority from old ages and nowadays it has become more fascinating. Due to the physical activity blood pressure also remains healthy, and blood vessels remain clean. Sugar level also reduces and cholesterol comes down by daily activity. Different people have different interests in sports but the action is the same in all sports. Sports are becoming big channels to make more capital/money day by day and the number of people is also increasing. By playing sports even at a young age you can also be better and free from some diseases. By playing sports lung function also improves and becomes healthy because more oxygen is supplied. Sports also improves bone strength even in old age.', 1, 6, 80, 0, NULL, 0, '2026-03-08 21:15:25', '2026-03-08 21:15:25', '2026-03-08 21:15:25');

-- --------------------------------------------------------

--
-- Table structure for table `article_flags`
--

CREATE TABLE `article_flags` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL DEFAULT 'inappropriate',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `admin_user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `admin_user_id`, `created_at`) VALUES
(1, 'Technology', 'Tech news and innovations', NULL, '2026-03-07 19:48:14'),
(2, 'Science', 'Scientific discoveries', NULL, '2026-03-07 19:48:14'),
(3, 'Politics', 'Political news and analysis', NULL, '2026-03-07 19:48:14'),
(4, 'Economy', 'Business and economic news', NULL, '2026-03-07 19:48:14'),
(5, 'Sports', 'Sports news and results', NULL, '2026-03-07 19:48:14'),
(6, 'Health', 'Health and medical news', NULL, '2026-03-07 19:48:14');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `article_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 4, 'Really interesting — I wonder how long before this starts affecting white-collar jobs at scale.', '2025-01-08 11:30:00'),
(2, 1, 2, 'The safety section is reassuring but I\'d like to see more detail on the red-teaming methodology.', '2025-01-08 14:00:00'),
(3, 4, 4, 'As someone who lost a family member to melanoma this gives me real hope. When might this reach the NHS?', '2025-01-09 12:00:00'),
(4, 4, 3, 'The $100k cost per patient is the most significant barrier. Even with Breakthrough Therapy designation the regulatory path takes years.', '2025-01-10 08:30:00'),
(5, 9, 4, 'Ireland have been extraordinary to watch this tournament. Crowley stepping into Sexton\'s boots seamlessly is remarkable.', '2025-03-17 09:00:00'),
(6, 11, 2, 'The cardiovascular outcomes data from SELECT is genuinely impressive. Good to see approvals based on endpoints beyond weight loss.', '2025-01-26 10:00:00'),
(8, 17, 6, 'aa', '2026-03-08 23:10:29'),
(9, 17, 5, 'adsasdadsdsa', '2026-03-09 01:07:06');

-- --------------------------------------------------------

--
-- Table structure for table `saved_articles`
--

CREATE TABLE `saved_articles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `saved_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_feedback`
--

CREATE TABLE `site_feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT '',
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `content` text NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar_url` text DEFAULT NULL,
  `role` enum('free','premium','category_admin','system_admin','ai_trainer') NOT NULL DEFAULT 'free',
  `is_premium` tinyint(1) NOT NULL DEFAULT 0,
  `is_suspended` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `full_name`, `bio`, `avatar_url`, `role`, `is_premium`, `is_suspended`, `created_at`, `updated_at`) VALUES
(1, 'alex.morgan@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'Alex Morgan', 'Technology and business journalist covering emerging markets.', NULL, 'free', 0, 0, '2024-03-01 08:00:00', '2026-03-07 19:48:14'),
(2, 'priya.sharma@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'Priya Sharma', 'Freelance writer covering health, wellness, and nutrition.', NULL, 'free', 0, 0, '2024-03-03 09:00:00', '2026-03-07 19:48:14'),
(3, 'lucas.ford@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'Lucas Ford', 'Independent journalist covering science and the environment.', NULL, 'free', 0, 0, '2024-03-05 10:00:00', '2026-03-07 19:48:14'),
(4, 'reader@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHej3sEpCV4rMb5TbKhxjjXAOn99Pjvg8e', 'Jamie Lee', 'Avid reader interested in science and technology news.', NULL, 'free', 0, 0, '2024-04-01 08:00:00', '2026-03-07 19:48:14'),
(5, 'jamestan@example.com', '$2y$10$WThGM0YqN16DvgYvmx897uJCSFTz4XU6EPE46toW08.OsPVccgAGS', 'James Tan', NULL, NULL, 'premium', 0, 0, '2026-03-07 20:12:01', '2026-03-07 20:31:26'),
(6, 'freeuser@example.com', '$2y$10$QfvDlA1hZSAzQOB.I2//h./zJKzz/6kiYkyiqFs6LZtZX0V8w6LFi', 'freeuser', NULL, NULL, 'free', 0, 0, '2026-03-08 22:44:25', '2026-03-08 22:44:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_author` (`author_id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_published` (`published_at`);

--
-- Indexes for table `article_flags`
--
ALTER TABLE `article_flags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_flag` (`article_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `admin_user_id` (`admin_user_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_article` (`article_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `saved_articles`
--
ALTER TABLE `saved_articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_saved` (`user_id`,`article_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_article` (`article_id`);

--
-- Indexes for table `site_feedback`
--
ALTER TABLE `site_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `article_flags`
--
ALTER TABLE `article_flags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `saved_articles`
--
ALTER TABLE `saved_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_feedback`
--
ALTER TABLE `site_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `article_flags`
--
ALTER TABLE `article_flags`
  ADD CONSTRAINT `article_flags_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_flags_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_articles`
--
ALTER TABLE `saved_articles`
  ADD CONSTRAINT `saved_articles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_articles_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `site_feedback`
--
ALTER TABLE `site_feedback`
  ADD CONSTRAINT `site_feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
