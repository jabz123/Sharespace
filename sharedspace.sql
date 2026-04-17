-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 18, 2026 at 07:01 AM
-- Server version: 8.0.45-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

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
  `id` int NOT NULL,
  `title` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_id` int NOT NULL,
  `category_id` int NOT NULL,
  `trust_score` tinyint NOT NULL DEFAULT '80',
  `has_media` tinyint(1) NOT NULL DEFAULT '0',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_premium_only` tinyint(1) NOT NULL DEFAULT '0',
  `published_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `excerpt`, `content`, `author_id`, `category_id`, `trust_score`, `has_media`, `image_path`, `is_premium_only`, `published_at`, `created_at`, `updated_at`, `status`) VALUES
(119, 'The Rise of Artificial Intelligence', 'Artificial intelligence is transforming modern industries.', 'AI technologies are revolutionizing automation, healthcare, finance, and many other sectors. Experts predict continued rapid growth in AI capabilities.', 65, 1, 88, 1, 'uploads/articles/1773625205_tech4.jpg', 0, '2026-03-16 01:26:47', '2026-03-16 01:26:47', '2026-03-16 01:40:05', 'published'),
(120, 'Smart Technology in Everyday Life', 'Smart devices are becoming part of everyday living.', 'From smart homes to wearable technology, connected devices are improving convenience and efficiency across the world.', 65, 1, 84, 1, 'uploads/articles/1773624331_tech2.jpg', 0, '2026-03-16 01:26:47', '2026-03-16 01:26:47', '2026-03-16 01:39:52', 'published'),
(121, 'Digital Transformation Across Industries', 'Businesses worldwide are embracing digital innovation.', 'Companies are investing heavily in cloud computing, artificial intelligence, and big data to remain competitive in the digital economy.', 65, 1, 86, 1, 'uploads/articles/1773625277_tech5.jpg', 0, '2026-03-16 01:26:47', '2026-03-16 01:26:47', '2026-03-16 01:41:17', 'published'),
(122, 'Human and Machine Collaboration', 'AI systems are increasingly working alongside humans.', 'Modern AI solutions are designed to support professionals by analyzing large data sets and improving decision-making processes.', 65, 1, 87, 1, 'uploads/articles/1773625287_tech6.jpg', 0, '2026-03-16 01:26:47', '2026-03-16 01:26:47', '2026-03-16 01:41:27', 'published'),
(123, 'Advancements in Robotics', 'Robotics continues to evolve across industries.', 'New robotic technologies are enabling automation in manufacturing, healthcare, and logistics, increasing productivity worldwide.', 65, 1, 89, 1, 'uploads/articles/1773625243_tech3.jpg', 0, '2026-03-16 01:26:47', '2026-03-16 01:26:47', '2026-03-16 01:40:43', 'published'),
(124, 'The Future of Connected Devices', 'IoT devices continue expanding globally.', 'The Internet of Things is connecting billions of devices, transforming cities, homes, and industries.', 65, 1, 83, 1, 'uploads/articles/1773625349_download (1).jpg', 0, '2026-03-16 01:26:47', '2026-03-16 01:26:47', '2026-03-16 01:42:29', 'published'),
(125, 'Next Generation AI Systems', 'Machine learning breakthroughs drive new innovation.', 'Advanced neural networks and AI models are enabling smarter applications and new technological possibilities.', 65, 1, 90, 1, 'uploads/articles/1773625231_tech1.jpg', 0, '2026-03-16 01:26:47', '2026-03-16 01:26:47', '2026-03-16 01:40:31', 'published'),
(126, 'Breakthroughs in Renewable Energy Storage', 'Scientists are developing advanced battery technologies capable of storing renewable energy more efficiently.', 'Renewable energy sources such as solar and wind power are becoming increasingly important in the global transition toward sustainable energy. However, storing energy produced during peak production hours has always been a challenge. Recent scientific research focuses on developing next-generation battery technologies that can store significantly more energy while maintaining long-term stability. Researchers are experimenting with materials such as lithium-sulfur and solid-state batteries that promise improved safety, longer life cycles, and greater efficiency. These developments could revolutionize how energy is stored and distributed, making renewable energy more reliable for everyday use and reducing dependence on fossil fuels worldwide.', 65, 2, 90, 0, 'uploads/articles/1773625837_images.webp', 0, '2026-03-16 01:45:14', '2026-03-16 01:45:14', '2026-03-16 01:50:37', 'published'),
(127, 'Exploring the Mysteries of Deep-Sea Ecosystems', 'Marine scientists continue to discover previously unknown species living in extreme ocean environments.', 'The deep ocean remains one of the least explored environments on Earth. Scientists using advanced submersibles and remotely operated vehicles are uncovering ecosystems thriving thousands of meters below the ocean surface. These ecosystems survive under extreme pressure, darkness, and limited food sources. Researchers have discovered unique organisms that rely on chemical energy rather than sunlight, providing new insights into how life adapts to extreme conditions. Understanding these ecosystems not only expands our knowledge of biodiversity but may also contribute to discoveries in medicine, biotechnology, and environmental science. As exploration technology improves, scientists expect many more groundbreaking discoveries from the ocean depths.', 65, 2, 88, 0, 'uploads/articles/1773625925_images (1).webp', 0, '2026-03-16 01:45:14', '2026-03-16 01:45:14', '2026-03-16 01:52:05', 'published'),
(128, 'The Science Behind Climate Change Modeling', 'Researchers rely on advanced climate models to predict long-term environmental changes.', 'Climate scientists use complex computer simulations to understand and predict the Earth’s changing climate system. These models analyze vast amounts of data including ocean temperatures, atmospheric conditions, greenhouse gas concentrations, and land surface interactions. By simulating how these variables interact over time, scientists can forecast potential environmental scenarios decades into the future. Although climate models are not perfect, they are essential tools for policymakers, researchers, and environmental organizations working to mitigate climate risks. Continuous improvements in computing power and satellite monitoring allow these models to become increasingly accurate, helping humanity better prepare for environmental challenges.', 65, 2, 91, 0, 'uploads/articles/1773625935_download (7).jpg', 0, '2026-03-16 01:45:14', '2026-03-16 01:45:14', '2026-03-16 01:52:15', 'published'),
(129, 'Advancements in Genetic Research and CRISPR Technology', 'CRISPR gene-editing technology is transforming modern biomedical research.', 'Genetic research has advanced dramatically with the development of CRISPR-Cas9 gene editing technology. Scientists can now modify DNA sequences with remarkable precision, enabling researchers to study genetic diseases and develop potential treatments. This technology allows scientists to identify harmful genetic mutations and potentially correct them before they cause disease. While the technology holds enormous promise, it also raises ethical considerations about how far genetic modifications should be allowed to go. Researchers and regulators continue to debate how to balance scientific innovation with responsible use of powerful genetic tools.', 65, 2, 89, 0, 'uploads/articles/1773625947_download (8).jpg', 0, '2026-03-16 01:45:14', '2026-03-16 01:45:14', '2026-03-16 01:52:27', 'published'),
(130, 'Understanding the Physics of Black Holes', 'Black holes remain one of the most fascinating phenomena in modern astrophysics.', 'Black holes are regions of space where gravity is so strong that nothing, not even light, can escape. Scientists study these cosmic objects to better understand the laws of physics and the structure of the universe. Recent discoveries using gravitational wave detectors and space telescopes have allowed researchers to observe black hole mergers and other extreme cosmic events. These observations provide valuable insights into how galaxies evolve and how matter behaves under extreme gravitational forces. Ongoing research continues to challenge existing theories and pushes the boundaries of our understanding of the universe.', 65, 2, 92, 0, 'uploads/articles/1773625796_download (5).jpg', 0, '2026-03-16 01:45:14', '2026-03-16 01:45:14', '2026-03-16 01:49:56', 'published'),
(131, 'The Role of Artificial Intelligence in Scientific Discovery', 'Artificial intelligence is becoming a powerful tool for researchers across many scientific fields.', 'Artificial intelligence is rapidly transforming the scientific research process. Machine learning algorithms can analyze massive datasets far faster than traditional methods, helping scientists identify patterns that would otherwise remain hidden. In fields such as astronomy, biology, and chemistry, AI systems are assisting researchers in identifying new molecules, detecting exoplanets, and modeling complex biological processes. By combining human expertise with advanced computational techniques, scientists are accelerating discoveries that could have taken decades using traditional approaches.', 65, 2, 87, 0, NULL, 0, '2026-03-16 01:45:14', '2026-03-16 01:45:14', '2026-03-16 01:52:54', 'published'),
(132, 'Space Exploration and the Search for Habitable Planets', 'Astronomers continue searching for planets capable of supporting life beyond our solar system.', 'The search for habitable planets has become one of the most exciting areas of modern astronomy. Using advanced telescopes and space missions, scientists are identifying planets orbiting distant stars that may contain conditions suitable for life. Researchers analyze factors such as planetary atmosphere composition, temperature, and distance from their host stars to determine habitability. While the discovery of extraterrestrial life remains uncertain, ongoing exploration provides valuable insights into planetary formation and the diversity of worlds within our universe.', 65, 2, 90, 0, 'uploads/articles/1773625982_download (7).jpg', 0, '2026-03-16 01:45:14', '2026-03-16 01:45:14', '2026-03-16 01:53:02', 'published'),
(133, 'Nanotechnology and the Future of Medicine', 'Nanotechnology is opening new possibilities in medical treatments and drug delivery systems.', 'Nanotechnology involves manipulating materials at the molecular and atomic scale. In medicine, researchers are exploring how nanoscale particles can deliver drugs directly to targeted areas of the body, reducing side effects and improving treatment effectiveness. Scientists are also investigating nanomaterials for use in diagnostics, imaging, and regenerative medicine. These technologies could dramatically change how diseases are detected and treated in the future. Although many applications are still in the experimental stage, early results suggest enormous potential for improving healthcare outcomes.', 65, 2, 86, 0, 'uploads/articles/1773626016_images.png', 0, '2026-03-16 01:45:14', '2026-03-16 01:45:14', '2026-03-16 01:53:36', 'published'),
(134, 'The Importance of Biodiversity in Ecosystem Stability', 'Scientists emphasize that biodiversity plays a critical role in maintaining ecological balance.', 'Biodiversity refers to the wide variety of life forms found on Earth, including plants, animals, and microorganisms. Healthy ecosystems rely on biodiversity to maintain stability, productivity, and resilience against environmental changes. When biodiversity declines due to habitat destruction, pollution, or climate change, ecosystems become more vulnerable to collapse. Scientists study biodiversity patterns to understand how ecosystems function and how conservation efforts can protect natural environments. Protecting biodiversity is essential not only for preserving wildlife but also for ensuring long-term environmental sustainability and human well-being.', 65, 2, 88, 0, NULL, 0, '2026-03-16 01:45:14', '2026-03-16 01:45:14', '2026-03-16 01:45:14', 'published'),
(135, 'The Evolving Landscape of Modern Democracy', 'Political systems around the world are adapting to new societal expectations and technological change.', 'Democracy continues to evolve as societies face rapid technological, economic, and social transformations. Governments must balance the need for transparency, accountability, and public participation while maintaining efficient governance. Digital platforms have enabled citizens to engage more actively in political discussions, but they have also introduced new challenges such as misinformation and political polarization. Researchers and policymakers are examining how democratic institutions can adapt to modern communication technologies while maintaining trust and legitimacy among citizens. Strengthening democratic participation and improving political literacy remain key priorities for many governments.', 65, 3, 85, 0, 'uploads/articles/1773626184_pol1.jpg', 0, '2026-03-16 01:55:45', '2026-03-16 01:55:45', '2026-03-16 01:56:24', 'published'),
(136, 'Global Cooperation in an Increasingly Complex World', 'International relations are becoming more interconnected as countries address global challenges together.', 'Modern politics increasingly requires cooperation across national borders. Issues such as climate change, economic stability, cybersecurity, and public health demand coordinated responses among nations. International organizations and diplomatic partnerships play a critical role in facilitating dialogue and resolving conflicts. While geopolitical tensions sometimes complicate cooperation, many governments recognize that long-term stability depends on collaborative approaches to shared challenges. Strengthening diplomatic relationships and international institutions will remain an essential part of global political strategy in the coming decades.', 65, 3, 87, 0, 'uploads/articles/1773626229_pol2.jpg', 0, '2026-03-16 01:55:45', '2026-03-16 01:55:45', '2026-03-16 01:57:09', 'published'),
(137, 'The Role of Youth Participation in Politics', 'Younger generations are becoming more engaged in political processes and civic discussions.', 'Youth participation in politics has grown significantly in recent years. Young voters are increasingly active in elections, social movements, and policy discussions. Digital platforms have enabled younger generations to organize campaigns, share information, and advocate for social change more effectively than ever before. Political analysts note that issues such as climate change, education, employment opportunities, and social equality strongly influence youth engagement. Encouraging constructive political participation among younger citizens can strengthen democratic institutions and ensure that future policies reflect the priorities of upcoming generations.', 65, 3, 86, 0, NULL, 0, '2026-03-16 01:55:45', '2026-03-16 01:55:45', '2026-03-16 01:55:45', 'published'),
(138, 'Balancing National Security and Civil Liberties', 'Governments continue to debate how to protect citizens while preserving individual freedoms.', 'National security remains a central responsibility of governments worldwide. However, maintaining security often involves complex decisions that affect civil liberties, privacy rights, and freedom of expression. Policymakers must carefully balance these priorities to ensure that protective measures do not undermine fundamental democratic values. Advances in surveillance technology and digital monitoring have intensified debates about privacy protections and the appropriate limits of government authority. Transparent legal frameworks and public oversight are crucial for maintaining trust while addressing security concerns.', 65, 3, 88, 0, 'uploads/articles/1773626249_pol3.jpg', 0, '2026-03-16 01:55:45', '2026-03-16 01:55:45', '2026-03-16 01:57:29', 'published'),
(139, 'Economic Policy and Political Decision Making', 'Economic strategies often play a central role in shaping political priorities and public policy.', 'Economic policy is one of the most influential aspects of modern governance. Decisions related to taxation, public spending, infrastructure investment, and labor markets have long-term effects on national development and social welfare. Political leaders must weigh competing priorities when designing economic policies that promote growth while addressing inequality. Global economic interdependence also means that domestic economic decisions can have international consequences. As economic challenges evolve, governments continue to explore innovative approaches to sustain growth and stability.', 65, 3, 84, 0, 'uploads/articles/1773626267_pol4.jpg', 0, '2026-03-16 01:55:45', '2026-03-16 01:55:45', '2026-03-16 01:57:47', 'published'),
(140, 'Global Economic Outlook in a Changing World', 'Economists analyze the current trends shaping the global economy.', 'The global economy continues to evolve as nations adapt to new technological developments, shifting trade relationships, and changing consumer behaviors. Governments and businesses are closely monitoring inflation rates, employment levels, and supply chain stability to better understand future economic directions. Experts emphasize the importance of sustainable economic policies that encourage innovation, support small businesses, and promote inclusive growth. As economic systems become more interconnected, international cooperation and sound financial planning remain essential for maintaining long-term stability and prosperity.', 65, 4, 88, 0, 'uploads/articles/1773626524_eco.webp', 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:02:04', 'published'),
(141, 'The Rise of Digital Economies', 'Digital transformation is redefining how modern economies function.', 'Digital technologies are increasingly influencing the structure and performance of global economies. From e-commerce platforms to digital payment systems, businesses are rapidly adopting new tools to improve efficiency and expand their reach. Governments are also exploring digital infrastructure investments to support economic development and improve access to financial services. While the digital economy creates new opportunities, it also introduces challenges such as cybersecurity risks, regulatory complexities, and workforce reskilling. Addressing these challenges will be crucial for ensuring sustainable economic growth in the digital age.', 65, 4, 87, 0, 'uploads/articles/1773626564_eco2.jpg', 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:02:44', 'published'),
(142, 'Small Businesses and Economic Growth', 'Entrepreneurship continues to play a vital role in economic development.', 'Small and medium-sized enterprises remain the backbone of many national economies. These businesses contribute significantly to job creation, innovation, and local economic activity. Governments around the world are implementing policies aimed at supporting entrepreneurs through access to financing, simplified regulations, and digital transformation programs. As global markets become more competitive, empowering small businesses with technology and financial resources will remain a key driver of economic resilience and long-term development.', 65, 4, 86, 0, 'uploads/articles/1773626621_download (1).png', 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:03:41', 'published'),
(143, 'Sustainable Economic Development', 'Economic growth must balance financial progress with environmental responsibility.', 'Sustainable development has become a major focus in economic policy discussions worldwide. Policymakers are increasingly considering how economic strategies can promote growth while protecting natural resources and minimizing environmental impact. Investments in renewable energy, green infrastructure, and sustainable manufacturing are gaining momentum as countries seek to reduce carbon emissions and transition to more environmentally friendly economic models. Achieving sustainable economic growth requires collaboration between governments, industries, and communities to ensure that development benefits both current and future generations.', 65, 4, 89, 0, NULL, 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:01:01', 'published'),
(144, 'The Impact of Global Trade on Local Economies', 'International trade continues to influence domestic economic performance.', 'Global trade networks allow countries to exchange goods, services, and resources more efficiently than ever before. Trade agreements and international partnerships often shape economic opportunities for businesses and workers alike. While globalization has expanded markets and increased economic growth in many regions, it has also created challenges related to economic inequality and industrial competitiveness. Policymakers must carefully balance trade policies to protect domestic industries while encouraging global economic cooperation and innovation.', 65, 4, 85, 0, 'uploads/articles/1773626596_download.png', 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:03:16', 'published'),
(145, 'The Importance of Discipline in Professional Sports', 'Athletes rely on discipline and dedication to achieve peak performance.', 'Professional sports require far more than natural talent. Athletes must maintain rigorous training schedules, balanced nutrition, and mental resilience to compete at the highest levels. Coaches and sports scientists work closely with athletes to optimize training techniques and prevent injuries. Modern sports training increasingly incorporates data analytics, biomechanics, and psychological preparation to enhance performance. Discipline, teamwork, and perseverance remain the foundation of success in competitive sports across the world.', 65, 5, 90, 0, 'uploads/articles/1773626656_adobestock_278427683.jpeg', 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:04:16', 'published'),
(146, 'The Evolution of Sports Science', 'Scientific advancements are transforming athlete training and recovery.', 'Sports science has become an essential component of modern athletic performance. Researchers study how physiology, nutrition, biomechanics, and psychology influence athletic success. Advanced technologies now allow trainers to monitor athletes’ physical conditions in real time, enabling more personalized training programs. Innovations in recovery techniques, including cryotherapy and advanced rehabilitation methods, help athletes recover faster and reduce injury risks. As sports science continues to evolve, athletes are achieving performance levels once thought impossible.', 65, 5, 89, 0, 'uploads/articles/1773626684_images (5).jpg', 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:04:44', 'published'),
(147, 'The Global Popularity of Football', 'Football remains one of the most widely followed sports worldwide.', 'Football has grown into a global cultural phenomenon, bringing together millions of fans across continents. International competitions and domestic leagues generate significant economic and social impact. The sport promotes teamwork, physical fitness, and international cooperation through global tournaments. Youth development programs also play a critical role in nurturing future talent and encouraging participation at the grassroots level. As football continues to grow in popularity, its influence on global sports culture remains unmatched.', 65, 5, 88, 0, 'uploads/articles/1773626704_download (9).jpg', 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:05:04', 'published'),
(148, 'Sports and Community Development', 'Sporting activities often strengthen community engagement and unity.', 'Local sports programs and community leagues contribute significantly to social development. By encouraging participation in physical activities, sports promote healthier lifestyles and provide opportunities for social interaction. Communities often rally around local teams, creating a sense of shared identity and pride. Sports initiatives also help develop leadership skills, discipline, and teamwork among young participants. As a result, sports programs continue to play an important role in fostering stronger and more connected communities.', 65, 5, 87, 0, NULL, 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:01:01', 'published'),
(149, 'Technology in Modern Sports', 'Technology is reshaping how sports are played, analyzed, and experienced.', 'Technological innovations are transforming nearly every aspect of modern sports. High-speed cameras, data analytics, and wearable sensors provide coaches with detailed insights into athlete performance. Refereeing technologies such as video assistant systems improve fairness and accuracy in competitive matches. Fans also benefit from enhanced viewing experiences through digital broadcasting, interactive statistics, and immersive media platforms. As technology continues to advance, the relationship between sports and innovation will only become stronger.', 65, 5, 86, 0, NULL, 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:01:01', 'published'),
(150, 'The Importance of Preventive Healthcare', 'Preventive healthcare strategies can significantly improve long-term well-being.', 'Preventive healthcare focuses on maintaining health and preventing diseases before they occur. Regular medical check-ups, balanced diets, physical activity, and vaccination programs all play important roles in reducing the risk of chronic illnesses. Healthcare professionals emphasize that early detection and healthy lifestyle choices can dramatically improve quality of life. Governments and health organizations continue to promote public awareness campaigns encouraging individuals to take proactive steps toward maintaining their health.', 65, 6, 91, 0, 'uploads/articles/1773626733_download (10).jpg', 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:05:33', 'published'),
(151, 'Mental Health Awareness in Modern Society', 'Mental health has become an increasingly important public health topic.', 'Mental health awareness has gained significant attention in recent years as societies recognize the importance of emotional and psychological well-being. Stress, anxiety, and depression affect millions of people globally, often influenced by work pressures, social expectations, and lifestyle factors. Healthcare professionals emphasize the importance of open conversations, access to support systems, and early intervention. Promoting mental health education helps reduce stigma and encourages individuals to seek assistance when needed.', 65, 6, 90, 0, NULL, 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:01:01', 'published'),
(152, 'Nutrition and Healthy Living', 'Balanced nutrition plays a crucial role in maintaining overall health.', 'A healthy diet provides the essential nutrients required for the body to function effectively. Medical experts recommend balanced meals that include fruits, vegetables, whole grains, and lean proteins. Proper nutrition supports immune function, improves energy levels, and reduces the risk of chronic diseases such as diabetes and heart conditions. Public health campaigns often encourage healthier eating habits as part of broader strategies to improve population health outcomes.', 65, 6, 89, 0, 'uploads/articles/1773626782_download (12).jpg', 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:06:22', 'published'),
(153, 'The Benefits of Regular Physical Activity', 'Exercise is one of the most effective ways to maintain physical health.', 'Regular physical activity helps strengthen the cardiovascular system, improve muscle strength, and enhance overall well-being. Health professionals recommend at least moderate exercise several times a week to maintain optimal health. Activities such as walking, cycling, and sports participation can significantly improve both physical and mental health. Exercise also reduces stress and improves sleep quality, making it an essential component of a balanced lifestyle.', 65, 6, 88, 0, NULL, 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 02:01:01', 'published'),
(154, 'Advances in Modern Medical Research', 'Scientific research continues to drive improvements in healthcare treatments.', 'Medical research plays a vital role in developing new treatments and improving patient care. Scientists and healthcare professionals collaborate to study diseases, test new therapies, and evaluate medical technologies. Advances in areas such as biotechnology, pharmaceuticals, and diagnostic tools have improved survival rates and treatment outcomes for many conditions. Continued investment in research and innovation remains essential for addressing future health challenges and improving global healthcare systems.', 65, 6, 90, 0, 'uploads/articles/1773626750_download (11).jpg', 0, '2026-03-16 02:01:01', '2026-03-16 02:01:01', '2026-03-16 18:39:07', 'published'),
(155, 'ccccccccccaaaarrr', 'dsadasadsasdsa', 'saddsadasadsadsdsa', 16, 1, 80, 0, 'uploads/articles/1773691418_tech1.jpg', 0, '2026-03-16 20:03:38', '2026-03-16 20:03:38', '2026-03-16 20:03:38', 'published'),
(156, 'test11555', 'tsats', 'safafafs5sas5asassaas', 16, 14, 80, 0, NULL, 0, '2026-03-17 16:40:12', '2026-03-17 16:39:07', '2026-03-17 16:40:12', 'published');

-- --------------------------------------------------------

--
-- Table structure for table `article_flags`
--

CREATE TABLE `article_flags` (
  `id` int NOT NULL,
  `article_id` int NOT NULL,
  `user_id` int NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inappropriate',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `article_views`
--

CREATE TABLE `article_views` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `article_id` int NOT NULL,
  `viewed_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `article_views`
--

INSERT INTO `article_views` (`id`, `user_id`, `article_id`, `viewed_at`) VALUES
(678, 65, 120, '2026-03-16 01:29:27'),
(679, 65, 119, '2026-03-16 01:36:20'),
(680, 65, 122, '2026-03-16 01:40:47'),
(681, 65, 121, '2026-03-16 01:41:01'),
(684, 65, 124, '2026-03-16 01:41:42'),
(688, 65, 126, '2026-03-16 01:45:30'),
(690, 65, 135, '2026-03-16 01:56:01'),
(691, 65, 136, '2026-03-16 01:56:59'),
(692, 65, 138, '2026-03-16 01:57:16'),
(693, 65, 139, '2026-03-16 01:57:40'),
(694, 65, 140, '2026-03-16 02:01:58'),
(695, 65, 141, '2026-03-16 02:02:37'),
(696, 65, 144, '2026-03-16 02:02:58'),
(697, 65, 142, '2026-03-16 02:03:22'),
(698, 65, 145, '2026-03-16 02:03:47'),
(699, 65, 146, '2026-03-16 02:04:21'),
(700, 65, 147, '2026-03-16 02:04:49'),
(701, 65, 150, '2026-03-16 02:05:27'),
(702, 65, 154, '2026-03-16 02:05:38'),
(705, 65, 152, '2026-03-16 02:06:06'),
(706, 20, 154, '2026-03-16 10:19:10'),
(707, 16, 155, '2026-03-16 20:03:43'),
(708, 16, 156, '2026-03-17 16:40:55');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `admin_user_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
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
(6, 'Health', 'Health and medical news', NULL, '2026-03-07 19:48:14'),
(14, 'Games', 'New upcoming games', NULL, '2026-03-17 08:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `article_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landing_features`
--

CREATE TABLE `landing_features` (
  `id` int NOT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `display_order` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `landing_features`
--

INSERT INTO `landing_features` (`id`, `icon_path`, `title`, `description`, `display_order`) VALUES
(1, 'public/icons/landingpage/factcheck.png', 'AI Fact-Checking', 'Every article is analysed by our advanced AI to verify claims and provide a confidence score before publication.', 1),
(2, 'public/icons/landingpage/publish.png', 'Real-Time Publishing', 'Share your verified news instantly with our streamlined publishing workflow and instant distribution.', 2),
(3, 'public/icons/landingpage/communitycomments.png', 'Community Comments', 'Readers and writers can comment on every article, fostering open discussion around verified news.', 3),
(4, 'public/icons/landingpage/trust.png', 'Trust Analytics', 'Track your credibility score over time and see how your articles perform in trust metrics.', 4),
(5, 'public/icons/landingpage/secure.png', 'Secure Accounts', 'Your account and data are protected with industry-standard security practices.', 5),
(6, 'public/icons/landingpage/multicategory.png', 'Multi-Category Support', 'Organise content across technology, politics, science, sports, and more with dedicated category management.', 6);

-- --------------------------------------------------------

--
-- Table structure for table `landing_pricing_features`
--

CREATE TABLE `landing_pricing_features` (
  `id` int NOT NULL,
  `plan_id` int NOT NULL,
  `feature_text` varchar(255) DEFAULT NULL,
  `is_included` tinyint(1) DEFAULT '1',
  `display_order` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `landing_pricing_features`
--

INSERT INTO `landing_pricing_features` (`id`, `plan_id`, `feature_text`, `is_included`, `display_order`) VALUES
(1, 1, 'Read all text-based articles', 1, 1),
(2, 1, 'Publish plain-text articles', 1, 2),
(3, 1, 'View AI trust scores', 1, 3),
(4, 1, 'Comment on articles', 1, 4),
(5, 1, 'Access to media content', 0, 5),
(6, 1, 'Save articles for later', 0, 6),
(7, 1, 'Ad-free experience', 0, 7),
(8, 2, 'Everything in Free', 1, 1),
(9, 2, 'Access to all categories', 1, 2),
(10, 2, 'Read articles with media', 1, 3),
(11, 2, 'Save articles for later', 1, 4),
(12, 2, 'Ad-free experience', 1, 5),
(13, 2, 'Priority AI analysis', 1, 6),
(14, 2, 'Priority support', 1, 7);

-- --------------------------------------------------------

--
-- Table structure for table `landing_pricing_plans`
--

CREATE TABLE `landing_pricing_plans` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` varchar(50) DEFAULT NULL,
  `price_suffix` varchar(50) DEFAULT NULL,
  `description` text,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `is_popular` tinyint(1) DEFAULT '0',
  `display_order` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `landing_pricing_plans`
--

INSERT INTO `landing_pricing_plans` (`id`, `name`, `price`, `price_suffix`, `description`, `button_text`, `button_link`, `is_popular`, `display_order`) VALUES
(1, 'Free', '$0', '/ forever', 'Perfect for casual readers and new writers', 'Get Started Free', '/register.php', 0, 1),
(2, 'Premium', '$12', '/ per month', 'For serious writers and engaged readers', 'Upgrade to Premium', '/register.php', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `landing_sections`
--

CREATE TABLE `landing_sections` (
  `id` int NOT NULL,
  `section_key` varchar(50) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `title_highlight` varchar(100) DEFAULT NULL,
  `subtitle` text,
  `badge` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `landing_sections`
--

INSERT INTO `landing_sections` (`id`, `section_key`, `title`, `title_highlight`, `subtitle`, `badge`) VALUES
(1, 'hero', 'Truth in Every', 'Headline', 'Join the platform where news is verified, trusted, and shared responsibly. Our AI analyses every article for accuracy before it reaches you.', 'AI-Powered Fact Checking');

-- --------------------------------------------------------

--
-- Table structure for table `landing_steps`
--

CREATE TABLE `landing_steps` (
  `id` int NOT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `step_number` varchar(5) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `display_order` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `landing_steps`
--

INSERT INTO `landing_steps` (`id`, `icon_path`, `step_number`, `title`, `description`, `display_order`) VALUES
(1, 'public/icons/landingpage/step1write.png', '01', 'Write Your Article', 'Create your news piece using our intuitive editor. Add sources, quotes, and supporting evidence.', 1),
(2, 'public/icons/landingpage/step2analysis.png', '02', 'AI Analysis', 'Our AI fact-checker analyses claims, cross-references sources, and generates a trust score.', 2),
(3, 'public/icons/landingpage/step3review.png', '03', 'Review & Refine', 'See detailed feedback on claims that need verification. Improve your article\'s credibility.', 3),
(4, 'public/icons/landingpage/step4publish.png', '04', 'Publish & Share', 'Publish your verified article with confidence. Readers see the trust score upfront.', 4);

-- --------------------------------------------------------

--
-- Table structure for table `saved_articles`
--

CREATE TABLE `saved_articles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `article_id` int NOT NULL,
  `saved_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_feedback`
--

CREATE TABLE `site_feedback` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `rating` tinyint NOT NULL DEFAULT '5',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sentiment_label` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sentiment_score` decimal(4,3) DEFAULT NULL,
  `sentiment_status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `avatar_url` text COLLATE utf8mb4_unicode_ci,
  `role` enum('free','premium','category_admin','system_admin','ai_trainer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'free',
  `is_premium` tinyint(1) NOT NULL DEFAULT '0',
  `is_suspended` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email_verified` tinyint(1) DEFAULT '0',
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_verification_email` datetime DEFAULT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `age_group` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `onboarding_completed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `full_name`, `bio`, `avatar_url`, `role`, `is_premium`, `is_suspended`, `created_at`, `updated_at`, `email_verified`, `verification_token`, `last_verification_email`, `reset_token`, `reset_expires`, `age_group`, `gender`, `onboarding_completed`) VALUES
(1, 'alex.morgan@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'Alex Morgan', 'test1', NULL, 'free', 0, 0, '2024-03-01 08:00:00', '2026-03-12 21:39:19', 1, NULL, NULL, NULL, NULL, '25-34', 'female', 1),
(2, 'priya.sharma@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'Priya Sharma', 'Freelance writer covering health, wellness, and nutrition.', NULL, 'free', 0, 0, '2024-03-03 09:00:00', '2026-03-07 19:48:14', 0, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(3, 'lucas.ford@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'Lucas Ford', 'uploadarticles', NULL, 'premium', 0, 0, '2024-03-05 10:00:00', '2026-03-13 16:49:38', 1, NULL, NULL, NULL, NULL, 'below12', 'male', 1),
(4, 'reader@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHej3sEpCV4rMb5TbKhxjjXAOn99Pjvg8e', 'Jamie Lee', 'Avid reader interested in science and technology news.', NULL, 'free', 0, 0, '2024-04-01 08:00:00', '2026-03-07 19:48:14', 0, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(5, 'jamestan@example.com', '$2y$10$WThGM0YqN16DvgYvmx897uJCSFTz4XU6EPE46toW08.OsPVccgAGS', 'James Tan', NULL, NULL, 'premium', 0, 0, '2026-03-07 20:12:01', '2026-03-10 09:14:47', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(6, 'freeuser@example.com', '$2y$10$QfvDlA1hZSAzQOB.I2//h./zJKzz/6kiYkyiqFs6LZtZX0V8w6LFi', 'freeuser', NULL, NULL, 'free', 0, 0, '2026-03-08 22:44:25', '2026-03-10 08:30:45', 1, 'NULL', NULL, NULL, NULL, NULL, NULL, 0),
(8, 'hee@gmail.com', '$2y$10$9RKBaoisTCS62zbcm4oxH.OPnM.OyvsaUwW1hhIWQ9QiiFx.pgKsa', 'Jabez Hee Ting Jia', NULL, NULL, 'free', 0, 0, '2026-03-09 02:56:12', '2026-03-09 02:56:12', 0, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(9, 'he@gmail.com', '$2y$10$H4Tmr3CPY8IW1R06It94WOpyFsNUaBh2NPW0Jdf5uEtPxf8/Aodr2', 'Jabez Hee', NULL, NULL, 'free', 0, 0, '2026-03-09 02:57:33', '2026-03-09 02:57:33', 0, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(15, 'keenangohganaesan@gmail.com', '$2y$10$9r8JycddPP5KqCuijTGoD.NNgUa9PXC2nYng1mnvS5fbmhOIWIQxu', 'Keenan Goh Ganaeson', NULL, NULL, 'system_admin', 0, 0, '2026-03-10 08:36:30', '2026-03-17 08:00:36', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(16, 'marcuskhong@hotmail.sg', '$2y$10$VsIBYe81Vx1iQ9fhgiiZmeAKJzDcr.Wf5HxDzDMk.2JwOKHyhOvea', 'marcus', 'Testing123', NULL, 'free', 0, 0, '2026-03-10 08:36:51', '2026-03-16 20:25:30', 1, NULL, NULL, NULL, NULL, '25-34', 'male', 1),
(20, 'jd432102003@gmail.com', '$2y$10$nrAjMRRu5qgTT9Og./CRVOuwMnKS3eywy97lcETXKLs/VfEVNrw5u', 'John Doe', 'No', NULL, 'system_admin', 0, 0, '2026-03-10 08:42:41', '2026-03-16 09:13:11', 1, NULL, NULL, NULL, NULL, '18-24', 'male', 1),
(21, 'abcee1900@gmail.com', '$2y$10$BEyBhTOi4SBYIyEE7/I67.eMRAYrlrMeB7gXvXKZ4pDboLp8ZZ3vS', 'Abc', 'Hi', NULL, 'premium', 0, 0, '2026-03-10 09:25:13', '2026-03-13 07:21:15', 1, NULL, NULL, NULL, NULL, '18-24', 'male', 1),
(22, 'chualiwen00@gmail.com', '$2y$10$3VQcxlbFyzREWhhF2OwRnusEatI3tyjS/AH7/cRpKS/z8wWFfOP.6', 'liwen', ':)', NULL, 'premium', 0, 0, '2026-03-11 09:39:27', '2026-03-15 08:55:24', 1, NULL, NULL, NULL, NULL, '18-24', 'female', 1),
(24, '12belowuser1@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 12Below 1', 'Student exploring news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:43:20', 1, NULL, NULL, NULL, NULL, 'below12', 'male', 1),
(25, '12belowuser2@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 12Below 2', 'Student exploring news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:43:20', 1, NULL, NULL, NULL, NULL, 'below12', 'female', 1),
(26, '12belowuser3@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 12Below 3', 'Student exploring news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:43:20', 1, NULL, NULL, NULL, NULL, 'below12', 'male', 1),
(27, '12belowuser4@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 12Below 4', 'Student exploring news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:43:20', 1, NULL, NULL, NULL, NULL, 'below12', 'female', 1),
(28, '13to17user1@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 13to17 1', 'Teen reader interested in trends', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:38:21', 1, NULL, NULL, NULL, NULL, '13-17', 'male', 1),
(29, '13to17user2@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 13to17 2', 'Teen reader interested in trends', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:38:21', 1, NULL, NULL, NULL, NULL, '13-17', 'female', 1),
(30, '13to17user3@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 13to17 3', 'Teen reader interested in trends', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:38:29', 1, NULL, NULL, NULL, NULL, '13-17', 'male', 1),
(31, '13to17user4@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 13to17 4', 'Teen reader interested in trends', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:38:29', 1, NULL, NULL, NULL, NULL, '13-17', 'female', 1),
(32, '13to17user5@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 13to17 5', 'Teen reader interested in trends', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:38:34', 1, NULL, NULL, NULL, NULL, '13-17', 'male', 1),
(33, '13to17user6@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 13to17 6', 'Teen reader interested in trends', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:38:34', 1, NULL, NULL, NULL, NULL, '13-17', 'female', 1),
(34, '18to24user1@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 18to24 1', 'University student reading news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:38:40', 1, NULL, NULL, NULL, NULL, '18-24', 'male', 1),
(35, '18to24user2@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 18to24 2', 'University student reading news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:38:40', 1, NULL, NULL, NULL, NULL, '18-24', 'female', 1),
(36, '18to24user3@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 18to24 3', 'University student reading news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:38:50', 1, NULL, NULL, NULL, NULL, '18-24', 'male', 1),
(37, '18to24user4@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 18to24 4', 'University student reading news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:38:50', 1, NULL, NULL, NULL, NULL, '18-24', 'female', 1),
(38, '18to24user5@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 18to24 5', 'University student reading news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:38:56', 1, NULL, NULL, NULL, NULL, '18-24', 'male', 1),
(39, '18to24user6@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 18to24 6', 'University student reading news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:38:56', 1, NULL, NULL, NULL, NULL, '18-24', 'female', 1),
(40, '18to24user7@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 18to24 7', 'University student reading news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:04', 1, NULL, NULL, NULL, NULL, '18-24', 'male', 1),
(41, '18to24user8@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 18to24 8', 'University student reading news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:04', 1, NULL, NULL, NULL, NULL, '18-24', 'female', 1),
(42, '18to24user9@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 18to24 9', 'University student reading news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:12', 1, NULL, NULL, NULL, NULL, '18-24', 'male', 1),
(43, '18to24user10@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 18to24 10', 'University student reading news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:12', 1, NULL, NULL, NULL, NULL, '18-24', 'female', 1),
(44, '25to34user1@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 25to34 1', 'Young professional reading insights', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:18', 1, NULL, NULL, NULL, NULL, '25-34', 'male', 1),
(45, '25to34user2@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 25to34 2', 'Young professional reading insights', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:18', 1, NULL, NULL, NULL, NULL, '25-34', 'female', 1),
(46, '25to34user3@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 25to34 3', 'Young professional reading insights', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:24', 1, NULL, NULL, NULL, NULL, '25-34', 'male', 1),
(47, '25to34user4@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 25to34 4', 'Young professional reading insights', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:24', 1, NULL, NULL, NULL, NULL, '25-34', 'female', 1),
(48, '25to34user5@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 25to34 5', 'Young professional reading insights', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:31', 1, NULL, NULL, NULL, NULL, '25-34', 'male', 1),
(49, '25to34user6@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 25to34 6', 'Young professional reading insights', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:31', 1, NULL, NULL, NULL, NULL, '25-34', 'female', 1),
(50, '25to34user7@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 25to34 7', 'Young professional reading insights', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:42', 1, NULL, NULL, NULL, NULL, '25-34', 'male', 1),
(51, '25to34user8@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 25to34 8', 'Young professional reading insights', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:42', 1, NULL, NULL, NULL, NULL, '25-34', 'female', 1),
(52, '25to34user9@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 25to34 9', 'Young professional reading insights', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:47', 1, NULL, NULL, NULL, NULL, '25-34', 'male', 1),
(53, '25to34user10@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 25to34 10', 'Young professional reading insights', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:47', 1, NULL, NULL, NULL, NULL, '25-34', 'female', 1),
(54, '35to44user1@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 35to44 1', 'Experienced reader following global news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:55', 1, NULL, NULL, NULL, NULL, '35-44', 'male', 1),
(55, '35to44user2@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 35to44 2', 'Experienced reader following global news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:39:55', 1, NULL, NULL, NULL, NULL, '35-44', 'female', 1),
(56, '35to44user3@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 35to44 3', 'Experienced reader following global news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:40:02', 1, NULL, NULL, NULL, NULL, '35-44', 'male', 1),
(57, '35to44user4@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 35to44 4', 'Experienced reader following global news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:40:02', 1, NULL, NULL, NULL, NULL, '35-44', 'female', 1),
(58, '35to44user5@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 35to44 5', 'Experienced reader following global news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:40:09', 1, NULL, NULL, NULL, NULL, '35-44', 'male', 1),
(59, '35to44user6@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User 35to44 6', 'Experienced reader following global news', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:40:09', 1, NULL, NULL, NULL, NULL, '35-44', 'female', 1),
(60, 'above45user1@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User Above45 1', 'Reader keeping up with world events', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:40:17', 1, NULL, NULL, NULL, NULL, '45+', 'male', 1),
(61, 'above45user2@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User Above45 2', 'Reader keeping up with world events', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:40:17', 1, NULL, NULL, NULL, NULL, '45+', 'female', 1),
(62, 'above45user3@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User Above45 3', 'Reader keeping up with world events', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:40:30', 1, NULL, NULL, NULL, NULL, '45+', 'male', 1),
(63, 'above45user4@example.com', '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'User Above45 4', 'Reader keeping up with world events', NULL, 'free', 0, 0, '2026-03-13 16:17:39', '2026-03-13 16:40:30', 1, NULL, NULL, NULL, NULL, '45+', 'female', 1),
(65, 'jabezhee@gmail.com', '$2y$10$ncEfJ.yzGLh3FJQvJp/F6OI5x3JXDTrqsLUw9kdiQqIt11hT.Tv7O', 'jabezhee', 'HIHI ', NULL, 'premium', 1, 0, '2026-03-14 06:46:13', '2026-03-15 23:23:22', 1, NULL, NULL, NULL, NULL, '18-24', 'male', 1),
(66, 'keenanganaesan@yahoo.com', '$2y$10$HWwIw4tfBSrJaPBzmavDueLQI9qt28tZ8UBakSqmoRvv2VPQCCbBm', 'Keenan', NULL, NULL, 'free', 0, 0, '2026-03-17 08:39:41', '2026-03-17 08:39:41', 0, '571bfb846c4228a06253d9a23034ce881f22ab86720a3bebd75ac2eddf7f70ea', NULL, NULL, NULL, NULL, NULL, 0),
(67, 'smoothic2003@gmail.com', '$2y$10$F/d8lp7hbaciTJ8CSQnRTOUbaCYxK8LMgfnyrgsITy0TRqWbXCmr2', 'Mamacita', 'Hi, im a female user', NULL, 'free', 0, 0, '2026-03-18 06:01:17', '2026-03-18 06:04:24', 1, NULL, NULL, NULL, NULL, '13-17', 'female', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_interests`
--

CREATE TABLE `user_interests` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `category_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_interests`
--

INSERT INTO `user_interests` (`id`, `user_id`, `category_id`, `created_at`) VALUES
(1, 16, 4, '2026-03-12 15:35:24'),
(2, 16, 6, '2026-03-12 15:35:24'),
(3, 16, 3, '2026-03-12 15:35:24'),
(4, 1, 4, '2026-03-12 21:39:19'),
(5, 1, 2, '2026-03-12 21:39:19'),
(6, 1, 5, '2026-03-12 21:39:19'),
(10, 21, 4, '2026-03-13 07:21:15'),
(11, 21, 5, '2026-03-13 07:21:15'),
(12, 21, 1, '2026-03-13 07:21:15'),
(13, 20, 4, '2026-03-13 07:25:29'),
(14, 20, 6, '2026-03-13 07:25:29'),
(15, 20, 3, '2026-03-13 07:25:29'),
(19, 3, 4, '2026-03-13 16:47:01'),
(20, 3, 6, '2026-03-13 16:47:01'),
(21, 3, 3, '2026-03-13 16:47:01'),
(22, 24, 3, '2026-03-13 19:07:50'),
(23, 24, 5, '2026-03-13 19:07:50'),
(24, 24, 2, '2026-03-13 19:07:50'),
(25, 25, 3, '2026-03-13 19:07:50'),
(26, 25, 4, '2026-03-13 19:07:50'),
(27, 25, 2, '2026-03-13 19:07:50'),
(28, 26, 5, '2026-03-13 19:07:50'),
(29, 26, 3, '2026-03-13 19:07:50'),
(30, 26, 1, '2026-03-13 19:07:50'),
(31, 27, 2, '2026-03-13 19:07:50'),
(32, 27, 3, '2026-03-13 19:07:50'),
(33, 27, 4, '2026-03-13 19:07:50'),
(34, 28, 3, '2026-03-13 19:07:50'),
(35, 28, 4, '2026-03-13 19:07:50'),
(36, 28, 5, '2026-03-13 19:07:50'),
(37, 29, 2, '2026-03-13 19:07:50'),
(38, 29, 3, '2026-03-13 19:07:50'),
(39, 29, 6, '2026-03-13 19:07:50'),
(40, 30, 3, '2026-03-13 19:07:50'),
(41, 30, 1, '2026-03-13 19:07:50'),
(42, 30, 5, '2026-03-13 19:07:50'),
(43, 31, 4, '2026-03-13 19:07:50'),
(44, 31, 3, '2026-03-13 19:07:50'),
(45, 31, 2, '2026-03-13 19:07:50'),
(46, 32, 5, '2026-03-13 19:07:50'),
(47, 32, 3, '2026-03-13 19:07:50'),
(48, 32, 6, '2026-03-13 19:07:50'),
(49, 33, 2, '2026-03-13 19:07:50'),
(50, 33, 4, '2026-03-13 19:07:50'),
(51, 33, 3, '2026-03-13 19:07:50'),
(52, 34, 3, '2026-03-13 19:07:50'),
(53, 34, 6, '2026-03-13 19:07:50'),
(54, 34, 5, '2026-03-13 19:07:50'),
(55, 35, 2, '2026-03-13 19:07:50'),
(56, 35, 6, '2026-03-13 19:07:50'),
(57, 35, 3, '2026-03-13 19:07:50'),
(58, 36, 3, '2026-03-13 19:07:50'),
(59, 36, 5, '2026-03-13 19:07:50'),
(60, 36, 1, '2026-03-13 19:07:50'),
(61, 37, 4, '2026-03-13 19:07:50'),
(62, 37, 3, '2026-03-13 19:07:50'),
(63, 37, 2, '2026-03-13 19:07:50'),
(64, 38, 6, '2026-03-13 19:07:50'),
(65, 38, 3, '2026-03-13 19:07:50'),
(66, 38, 5, '2026-03-13 19:07:50'),
(67, 39, 3, '2026-03-13 19:07:50'),
(68, 39, 2, '2026-03-13 19:07:50'),
(69, 39, 4, '2026-03-13 19:07:50'),
(70, 40, 5, '2026-03-13 19:07:50'),
(71, 40, 3, '2026-03-13 19:07:50'),
(72, 40, 6, '2026-03-13 19:07:50'),
(73, 41, 3, '2026-03-13 19:07:50'),
(74, 41, 4, '2026-03-13 19:07:50'),
(75, 41, 2, '2026-03-13 19:07:50'),
(76, 42, 6, '2026-03-13 19:07:50'),
(77, 42, 3, '2026-03-13 19:07:50'),
(78, 42, 5, '2026-03-13 19:07:50'),
(79, 43, 3, '2026-03-13 19:07:50'),
(80, 43, 2, '2026-03-13 19:07:50'),
(81, 43, 6, '2026-03-13 19:07:50'),
(82, 44, 6, '2026-03-13 19:07:50'),
(83, 44, 3, '2026-03-13 19:07:50'),
(84, 44, 5, '2026-03-13 19:07:50'),
(85, 45, 2, '2026-03-13 19:07:50'),
(86, 45, 6, '2026-03-13 19:07:50'),
(87, 45, 3, '2026-03-13 19:07:50'),
(88, 46, 3, '2026-03-13 19:07:50'),
(89, 46, 1, '2026-03-13 19:07:50'),
(90, 46, 5, '2026-03-13 19:07:50'),
(91, 47, 6, '2026-03-13 19:07:50'),
(92, 47, 3, '2026-03-13 19:07:50'),
(93, 47, 2, '2026-03-13 19:07:50'),
(94, 48, 5, '2026-03-13 19:07:50'),
(95, 48, 3, '2026-03-13 19:07:50'),
(96, 48, 6, '2026-03-13 19:07:50'),
(97, 49, 3, '2026-03-13 19:07:50'),
(98, 49, 4, '2026-03-13 19:07:50'),
(99, 49, 6, '2026-03-13 19:07:50'),
(100, 50, 6, '2026-03-13 19:07:50'),
(101, 50, 3, '2026-03-13 19:07:50'),
(102, 50, 5, '2026-03-13 19:07:50'),
(103, 51, 3, '2026-03-13 19:07:50'),
(104, 51, 2, '2026-03-13 19:07:50'),
(105, 51, 6, '2026-03-13 19:07:50'),
(106, 52, 5, '2026-03-13 19:07:50'),
(107, 52, 3, '2026-03-13 19:07:50'),
(108, 52, 6, '2026-03-13 19:07:50'),
(109, 53, 3, '2026-03-13 19:07:50'),
(110, 53, 4, '2026-03-13 19:07:50'),
(111, 53, 6, '2026-03-13 19:07:50'),
(112, 54, 1, '2026-03-13 19:07:50'),
(113, 54, 6, '2026-03-13 19:07:50'),
(114, 54, 3, '2026-03-13 19:07:50'),
(115, 55, 2, '2026-03-13 19:07:50'),
(116, 55, 6, '2026-03-13 19:07:50'),
(117, 55, 3, '2026-03-13 19:07:50'),
(118, 56, 3, '2026-03-13 19:07:50'),
(119, 56, 5, '2026-03-13 19:07:50'),
(120, 56, 6, '2026-03-13 19:07:50'),
(121, 57, 6, '2026-03-13 19:07:50'),
(122, 57, 3, '2026-03-13 19:07:50'),
(123, 57, 2, '2026-03-13 19:07:50'),
(124, 58, 5, '2026-03-13 19:07:50'),
(125, 58, 3, '2026-03-13 19:07:50'),
(126, 58, 1, '2026-03-13 19:07:50'),
(127, 59, 3, '2026-03-13 19:07:50'),
(128, 59, 6, '2026-03-13 19:07:50'),
(129, 59, 4, '2026-03-13 19:07:50'),
(130, 60, 1, '2026-03-13 19:07:50'),
(131, 60, 6, '2026-03-13 19:07:50'),
(132, 60, 3, '2026-03-13 19:07:50'),
(133, 61, 2, '2026-03-13 19:07:50'),
(134, 61, 6, '2026-03-13 19:07:50'),
(135, 61, 3, '2026-03-13 19:07:50'),
(136, 62, 6, '2026-03-13 19:07:50'),
(137, 62, 3, '2026-03-13 19:07:50'),
(138, 62, 5, '2026-03-13 19:07:50'),
(139, 63, 3, '2026-03-13 19:07:50'),
(140, 63, 6, '2026-03-13 19:07:50'),
(141, 63, 2, '2026-03-13 19:07:50'),
(145, 22, 4, '2026-03-14 06:14:00'),
(146, 22, 6, '2026-03-14 06:14:00'),
(147, 22, 1, '2026-03-14 06:14:00'),
(148, 65, 4, '2026-03-14 06:53:48'),
(149, 65, 6, '2026-03-14 06:53:48'),
(150, 65, 3, '2026-03-14 06:53:48'),
(151, 67, 4, '2026-03-18 06:04:24'),
(152, 67, 14, '2026-03-18 06:04:24'),
(153, 67, 6, '2026-03-18 06:04:24');

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
-- Indexes for table `article_views`
--
ALTER TABLE `article_views`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_article` (`user_id`,`article_id`),
  ADD KEY `idx_article` (`article_id`),
  ADD KEY `idx_user` (`user_id`);

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
-- Indexes for table `landing_features`
--
ALTER TABLE `landing_features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `landing_pricing_features`
--
ALTER TABLE `landing_pricing_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `landing_pricing_plans`
--
ALTER TABLE `landing_pricing_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `landing_sections`
--
ALTER TABLE `landing_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_key` (`section_key`);

--
-- Indexes for table `landing_steps`
--
ALTER TABLE `landing_steps`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `user_interests`
--
ALTER TABLE `user_interests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_category` (`user_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `article_flags`
--
ALTER TABLE `article_flags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `article_views`
--
ALTER TABLE `article_views`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=709;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `landing_features`
--
ALTER TABLE `landing_features`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `landing_pricing_features`
--
ALTER TABLE `landing_pricing_features`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `landing_pricing_plans`
--
ALTER TABLE `landing_pricing_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `landing_sections`
--
ALTER TABLE `landing_sections`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `landing_steps`
--
ALTER TABLE `landing_steps`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `saved_articles`
--
ALTER TABLE `saved_articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_feedback`
--
ALTER TABLE `site_feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `user_interests`
--
ALTER TABLE `user_interests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

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
-- Constraints for table `article_views`
--
ALTER TABLE `article_views`
  ADD CONSTRAINT `article_views_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_views_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `landing_pricing_features`
--
ALTER TABLE `landing_pricing_features`
  ADD CONSTRAINT `landing_pricing_features_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `landing_pricing_plans` (`id`) ON DELETE CASCADE;

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

--
-- Constraints for table `user_interests`
--
ALTER TABLE `user_interests`
  ADD CONSTRAINT `user_interests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_interests_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- AI trainer role seed and tables
--
INSERT INTO `users`
  (`email`, `password`, `full_name`, `bio`, `role`, `is_premium`, `is_suspended`, `created_at`, `updated_at`, `email_verified`, `onboarding_completed`)
SELECT
  'ai.trainer@example.com',
  '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu',
  'AI Trainer',
  'Model trainer account for reviewing AI trust analysis.',
  'ai_trainer',
  0,
  0,
  NOW(),
  NOW(),
  1,
  1
WHERE NOT EXISTS (
  SELECT 1 FROM `users` WHERE `email` = 'ai.trainer@example.com'
);

CREATE TABLE IF NOT EXISTS `ai_trainer_analyses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article_id` int NOT NULL,
  `trust_score` tinyint NOT NULL DEFAULT '80',
  `factual_accuracy` tinyint NOT NULL DEFAULT '80',
  `source_quality` tinyint NOT NULL DEFAULT '80',
  `bias_detection` tinyint NOT NULL DEFAULT '80',
  `analysed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ai_trainer_article` (`article_id`),
  KEY `idx_ai_trainer_trust_score` (`trust_score`),
  KEY `idx_ai_trainer_analysed_at` (`analysed_at`),
  CONSTRAINT `fk_ai_trainer_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_trainer_calibration_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ai_trainer_setting` (`setting_key`),
  KEY `idx_ai_trainer_setting_updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `ai_trainer_calibration_settings` (`setting_key`, `setting_value`) VALUES
('publishing_threshold', '60'),
('factual_accuracy_weight', '60'),
('source_quality_weight', '60'),
('bias_detection_weight', '60'),
('strict_mode', '0');

INSERT IGNORE INTO `ai_trainer_analyses`
  (`article_id`, `trust_score`, `factual_accuracy`, `source_quality`, `bias_detection`, `analysed_at`)
SELECT
  `id`,
  `trust_score`,
  LEAST(100, `trust_score` + 4),
  GREATEST(0, `trust_score` - 2),
  GREATEST(0, `trust_score` - 6),
  NOW()
FROM `articles`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
