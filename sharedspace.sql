-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 15, 2026 at 08:40 PM
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
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
(16, 'How to manage your time between sports and studies or work', 'Sports are essential for holistic development, offering profound physical, mental, and social benefits, including improved fitness, discipline, and teamwork. Beyond personal health, they foster resilience and bridge cultural divides. A strong essay highlights these advantages while emphasizing how sports shape character, teach leadership, and promote a balanced, healthy life. ', '1', 5, 5, 80, 0, 'uploads/articles/1772919112_Youth-soccer-indiana.jpg', 0, '2026-03-08 05:31:52', '2026-03-08 05:31:52', '2026-03-10 09:13:11'),
(37, 'The Rise of Ambient Computing: Technology That Disappears', 'Ambient computing is reshaping how we interact with technology by making devices fade into the background. Instead of relying on screens and manual input, systems anticipate user needs through voice, sensors, and AI-driven personalization. While this promises convenience and accessibility, challenges like privacy, interoperability, and trust remain critical', 'For years, our relationship with technology has been defined by screens—smartphones, laptops, and tablets have dominated how we work, communicate, and entertain ourselves. Yet a new paradigm is emerging: ambient computing, a vision where technology blends seamlessly into our surroundings and operates quietly in the background. In this model, devices are less about demanding attention and more about anticipating needs, creating an environment where technology feels invisible yet indispensable.\r\n\r\nAmbient computing refers to systems that respond naturally to human presence and behavior without requiring deliberate input. Smart speakers, for example, can anticipate daily routines, while thermostats learn and adjust to personal preferences. Augmented reality glasses may overlay useful information without distracting the user. The essence of ambient computing lies in reducing friction—allowing people to interact with technology through voice, gestures, or even passive presence rather than constant tapping and swiping.\r\n\r\nThe potential benefits are significant. By adapting to individual routines, ambient systems create a sense of personalization that makes technology feel more like a companion than a tool. For individuals with disabilities, this shift offers new pathways to independence, minimizing the need for physical interaction. At its best, ambient computing promises a world where technology is accessible, intuitive, and supportive of human needs.\r\n\r\nHowever, this vision comes with challenges. Devices that constantly listen, watch, or sense inevitably raise concerns about surveillance and data ownership. With multiple companies building their own ecosystems, interoperability becomes a pressing issue—users want their devices to work together seamlessly, not in isolated silos. Trust and transparency are equally critical; invisible systems must explain their decisions clearly to avoid leaving users feeling manipulated or powerless.\r\n\r\nLooking ahead, the ultimate goal of ambient computing is for technology to “disappear”—not in the literal sense, but in how seamlessly it integrates into everyday life. Imagine walking into a room where lighting, temperature, and digital tools adjust instantly to your mood and tasks, without you lifting a finger. That is the promise of a world where computing is everywhere, yet nowhere, quietly shaping experiences while fading into the background.', 20, 1, 80, 0, NULL, 0, '2026-03-10 10:07:23', '2026-03-10 10:07:23', '2026-03-10 10:07:23'),
(57, 'Rising Stars Shine in Unexpected Victor', 'The Riverdale Raptors upset the Eastbrook Titans 92–88, led by rookie Marcus Lee’s clutch three-pointer. The win boosts their playoff hopes and builds momentum ahead of their next game against the Greenfield Hawks', 'In a thrilling display of teamwork and resilience, the Riverdale Raptors stunned fans last night with a 92–88 victory over the heavily favored Eastbrook Titans. The game, held at the packed Eastbrook Arena, showcased the Raptors’ ability to adapt under pressure and seize opportunities when it mattered most.\r\n\r\nThe Titans entered the matchup riding a six-game winning streak, but the Raptors’ young roster proved they were more than capable of disrupting expectations. Rookie guard Marcus Lee led the charge with 24 points, including a clutch three-pointer in the final minute that sealed the win. His performance drew comparisons to seasoned veterans, highlighting his potential as a future cornerstone of the franchise.\r\nCoach Sandra Morales praised her team’s defensive intensity:\r\n\"We knew we couldn’t outmuscle them, but we could outthink them. Every possession mattered, and our players executed beautifully.\"\r\n\r\nFans erupted in celebration, with many calling this the Raptors’ most memorable win of the season. The victory not only boosts their playoff hopes but also signals a shift in momentum for a team once considered an underdog.\r\n\r\nLooking ahead, the Raptors face the Greenfield Hawks next week—a matchup that could further solidify their reputation as the league’s most surprising contenders.', 21, 5, 80, 0, 'uploads/articles/1773301139_Basketball stock.webp', 0, '2026-03-12 07:38:59', '2026-03-12 07:38:59', '2026-03-12 07:38:59'),
(58, 'Singapore’s Economy Gains Momentum in 2026', 'Singapore’s economy is forecast to grow 3.6% in 2026, supported by manufacturing and trade, with MTI raising its official outlook to 2%–4%. Risks include global trade tensions and inflation, but the overall outlook remains positive', 'Singapore’s economy is showing stronger momentum in 2026 than previously expected. According to the Monetary Authority of Singapore’s March survey of professional forecasters, GDP growth is projected at 3.6%, a notable upgrade from the earlier forecast of 2.3%. The Ministry of Trade and Industry has also raised its official forecast range to 2%–4%, reflecting resilience across key sectors. Manufacturing, particularly electronics and precision engineering, is driving much of this improvement, while wholesale and retail trade are benefiting from regional demand recovery. Services such as tourism and finance continue to provide steady contributions.\r\n\r\nHowever, risks remain. Global trade tensions, especially potential tariff increases from the US, could weigh on exports. Inflationary pressures may affect consumer spending, and external uncertainties like interest rate shifts and geopolitical developments could challenge stability. Despite these risks, the overall outlook suggests Singapore is well-positioned for steady growth in 2026, with opportunities for businesses and households alike.\r\n\r\nSingapore’s economy is entering 2026 with renewed strength and optimism. The Monetary Authority of Singapore’s March survey of professional forecasters projects GDP growth at 3.6%, a sharp upgrade from the earlier estimate of 2.3%. This reflects stronger-than-expected performance in manufacturing, wholesale trade, and retail sectors, alongside steady contributions from services such as tourism and finance. The Ministry of Trade and Industry (MTI) has also raised its official forecast range to 2%–4%, signaling confidence in the nation’s resilience.\r\n\r\nManufacturing is a key driver, with electronics and precision engineering rebounding thanks to global demand for semiconductors and advanced components. Wholesale and retail trade are benefiting from regional recovery in consumer spending and supply chain stability. Meanwhile, services such as tourism continue to recover as international travel normalizes, and financial services remain a pillar of stability.\r\nDespite the upbeat outlook, challenges remain. Global trade tensions—particularly the possibility of higher US tariffs—could dampen export growth. Inflationary pressures may erode household purchasing power, while external uncertainties such as interest rate shifts and geopolitical risks could affect investor confidence. Policymakers are expected to balance growth momentum with these external risks, ensuring stability through targeted fiscal and monetary measures.\r\n\r\nFor businesses, the stronger growth outlook presents opportunities in manufacturing, trade, and services, especially for firms positioned to leverage regional demand. Households may benefit from stable employment and wage growth, though rising costs could temper consumer sentiment. Overall, Singapore’s economy appears well-positioned to navigate 2026 with resilience, adaptability, and continued competitiveness.', 21, 4, 80, 0, 'uploads/articles/1773301800_InCorp-What-Actually-Makes-the-Singapore-Economy-Work-meta-banner.jpg', 0, '2026-03-12 07:50:00', '2026-03-12 07:50:00', '2026-03-12 07:50:00'),
(59, 'ComfortDelGro to invest over $200m in driving education; new CCK driving centre to use AI in tests', 'ComfortDelGro will invest over $200 million over 30 years to advance driving education in Singapore.', 'SINGAPORE – ComfortDelGro (CDG) will be investing more than $200 million over 30 years to develop driving education in Singapore, which includes using technology instead of human instructors at a new driving centre to be built in Choa Chu Kang.\r\n\r\nCDG said on March 11 that it intends to invest in new training facilities, technologies and operations in a bid to meet the demand for school-based training and address the declining number of private driving instructors.\r\n\r\nAbout a fifth of its investment, or $38 million, was put up in a bid for a plot of land in Lorong Bistari, near Kranji Camp III, which will replace Bukit Batok Driving Centre (BBDC) by 2030.\r\n\r\nSpanning 24,890 sq m, the Choa Chu Kang site was launched for public tender on Oct 8, 2025, receiving two bids when it closed on Feb 26.\r\n\r\nThe other bidder was BBDC, which offered slightly over $25 million.\r\n\r\nSpanning multiple storeys, with a gross floor area measuring 72,500 sq m, the driving school will make use of various technological tools to educate would-be drivers.\r\n\r\nThe plan is for it to be opened in phases.', 3, 1, 80, 0, 'uploads/articles/1773421128_tech1.jpg', 0, '2026-03-13 16:58:48', '2026-03-13 16:58:48', '2026-03-13 16:58:48'),
(60, 'Software firm Atlassian to cut 1,600 jobs in AI pivot', 'Atlassian has seen a steep sell-off in 2026, losing more than 50 per cent of its market value through regular trading on March 11.', 'BENGALURU – Atlassian on March 11 said it would lay off around 10 per cent of its workforce, or 1,600 employees, to push into artificial intelligence and enterprise sales.\r\n\r\nShares of the enterprise software company rose nearly 2 per cent in extended trading after Atlassian said it plans to “rebalance” its resources to focus on the “future of teamwork in the AI era”.\r\n\r\nThe company said the majority of impacted employees are in North America, amounting to 40 per cent, followed by 30 per cent in Australia and 16 per cent in India.\r\n\r\n“Our approach is not ‘AI replaces people’. But it would be disingenuous to pretend AI doesn’t change the mix of skills we need or the number of roles required in certain areas. It does,” chief executive Mike Cannon-Brookes said in a memo to employees.\r\n\r\nThe move comes as investors increasingly scrutinise software firms amid fears that advances in AI could disrupt traditional software business models, though some analysts say the sector-wide sell-off may be an overreaction.\r\n\r\nTop executives at the World Economic Forum’s annual meeting in January said that while jobs would disappear, new ones would spring up, with two telling Reuters that AI would be used as an excuse by companies that were already planning layoffs.\r\n\r\nAtlassian has seen a steep sell-off in 2026, losing more than 50 per cent of its market value through regular trading on March 11. It derives a majority of its revenue from its collaboration tools, including Jira software for planning and project management and Confluence for content creation.', 3, 1, 80, 0, 'uploads/articles/1773421181_tech2.jpg', 0, '2026-03-13 16:59:41', '2026-03-13 16:59:41', '2026-03-13 16:59:41'),
(61, 'US firm Quantinuum’s new R&D centre in S’pore to spur growth of quantum computing applications', '(From left) Quantinuum Singapore country leader Marvin Lee, Quantinuum president Rajeeb Hazra and Minister for Digital Development and Information Josephine Teo, at the centre launch on March 11.', 'SINGAPORE - A new R&D and operations centre set up in one-north by US-based firm Quantinuum will boost Singapore’s efforts in developing algorithms and applications for quantum computers.\r\n\r\nThe centre’s opening on March 11 marks the first step of Quantinuum’s expansion into Singapore, which will complement the firm’s plans to deploy its Helios quantum computer here later in 2026.\r\n\r\nThe centre serves as a base where the firm’s staff, researchers and local companies will come together to co-develop commercially relevant quantum solutions. The firm previously told The Straits Times it is looking to hire an undisclosed number of engineers, software specialists and researchers to support its customers here.\r\n\r\nThe development of commercially ready solutions can benefit the finance, logistics and pharmaceutical sectors in Singapore and globally, said Minister for Digital Development and Information Josephine Teo. She was speaking during the centre’s opening event on March 11.\r\n\r\n“At the same time, we can grow our capabilities in hardware and components across the quantum computing value chain,” said Mrs Teo, adding that Singapore’s strengths in semiconductors, advanced packaging and precision manufacturing can also be adapted to support quantum developments.\r\n\r\nUnlike traditional computers that store information as either zeroes or ones, quantum computers use quantum bits (or qubits) to represent and store information in a complex mix of zeroes and ones simultaneously. A quantum computer becomes exponentially more powerful as the number of qubits grows, allowing it to examine countless possibilities to pinpoint a probable solution in hours – a traditional computer would take thousands of years to do the same.\r\n\r\nHence, quantum computers have been tipped to lead to new discoveries in fields such as medicine, materials and more sophisticated artificial intelligence.', 3, 1, 80, 0, 'uploads/articles/1773421309_tech3.jpg', 0, '2026-03-13 17:01:37', '2026-03-13 17:01:37', '2026-03-13 17:01:49'),
(62, 'One-person firms and ‘raising lobsters’: China hunts for new ways to create jobs in AI era', 'The model of solo entrepreneurship is gaining popularity in China, mirroring a global trend, as AI tools grow more capable and lower the costs of starting a business, such as through the use of open-source AI agents like OpenClaw.', 'BEIJING – Chinese lawmakers are championing more support for people to start businesses using artificial intelligence, a move that comes as the widening application of the technology stokes anxieties about jobs.\r\n\r\nOne-person companies, or OPCs, emerged as a talking point at China’s top parliamentary meetings in Beijing in 2026, where delegates called for more resources and better legal frameworks to boost the growing model of AI-enabled entrepreneurship.\r\n\r\nThe term refers to entire businesses run by single founders without any employees, who instead outsource tasks to AI agents that can work 24/7 and with little supervision.\r\n\r\nFor example, app creators are reportedly using these agents to find ideas and write code, while e-commerce vendors tap them to manage online stores.\r\n\r\nThe model of solo entrepreneurship is gaining popularity in China, mirroring a global trend, as AI tools grow more capable and lower the costs of starting a business, such as through the use of open-source AI agents like OpenClaw.\r\n\r\nUsage of the popular OpenClaw has been termed “raising lobsters” in China, a reference to its lobster-shaped logo.\r\n\r\n“Since last year, one-person companies have been popping up in Hangzhou,” said Ms Luo Weihong, a delegate to China’s National People’s Congress (NPC), citing growing clusters of young entrepreneurs in the tech hub in eastern Zhejiang province, which is home to big names in Chinese tech such as Alibaba and DeepSeek.', 3, 1, 80, 0, 'uploads/articles/1773421345_tech4.jpg', 0, '2026-03-13 17:02:25', '2026-03-13 17:02:25', '2026-03-13 17:02:25'),
(63, '‘Lesson’ for Johor: Data centres should not be sited too near homes, says assemblyman after outcry', 'A cement truck exiting the ZData and NTT data centre construction site near Taman Nusa Bayu, Iskandar Puteri, in Malaysia\'s Johor state on Feb 23.', 'ISKANDAR PUTERI, Johor – A Johor assemblyman has conceded that the recent public uproar over a data centre built too close to homes should serve as a “lesson” for the state government.\r\n\r\nThe state government should have consulted residents before embarking on data centre projects, Kota Iskandar assemblyman Pandak Ahmad admitted at a March 11 press conference held with Beijing-based data centre operator ZData.\r\n\r\nIn 2024, ZData, which operates in Malaysia under the subsidiary Computility Technology, and Japan-based NTT Data Group purchased a 43.5ha plot near the Taman Nusa Bayu residential neighbourhood in Iskandar Puteri to build their respective data centre campuses.\r\n\r\nDevelopers began clearing land early in 2025 for construction, which included the levelling of a hill that residents once cherished. In nearby Taman Nusantara Prima, construction hoardings start just outside residents’ backyards.\r\n\r\nMr Ramli Paiman, who lives in Taman Nusantara Prima, said residents had to put up with dust clouds from the nearby construction site that “will fall from the sky into their homes”.\r\n\r\nDuring heavy rain, flash floods occur on the roads surrounding the residential estate, the 65-year-old retiree told The Straits Times.\r\n\r\nResidents also believe that the cracks that have appeared in their homes are caused by the vibrations from piling works, he said. And as a result of the land clearing, wild animals like boars and snakes are seen more regularly in the neighbourhood.', 3, 1, 80, 0, NULL, 0, '2026-03-13 17:03:30', '2026-03-13 17:03:30', '2026-03-13 17:03:30'),
(64, 'Ig Nobel prizes moving to Europe because US is ‘unsafe’ to visit', 'Previous winners of the Ig Nobel Prize include scientists who painted zebra stripes on cows to fend off flies', 'PARIS – The tongue-in-cheek Ig Nobel awards will be held in Europe for the first time in 2026 because the United States has become “unsafe” for international prize-winners to visit, the organisers have announced.\r\n\r\nThe awards, which celebrate the sillier side of science, have held raucous ceremonies that see the winners showered with paper aeroplanes at universities in Massachusetts since 1991.\r\n\r\nLike the Nobels they satirise, Ig Nobel laureates hail from all over the world. However, international academics have reported problems travelling to the US since President Donald Trump’s second term began in early 2025.\r\n\r\n“During the past year, it has become unsafe for our guests to visit the country,” Ig Nobel founder Marc Abrahams said in a statement on March 9.\r\n\r\n“We cannot, in good conscience, ask the new winners or the international journalists who cover the event to travel to the USA this year,” he said.\r\n\r\nThe 36th edition of the Ig Nobels will be held in the Swiss city of Zurich on Sept 3, the organisation said.\r\n\r\nThe University of Zurich and ETH Domain will host the ceremony, which gives prizes to achievements “that first make people laugh, then make them think”.', 3, 2, 80, 0, 'uploads/articles/1773421626_science1.webp', 0, '2026-03-13 17:07:06', '2026-03-13 17:07:06', '2026-03-13 17:07:06'),
(65, 'Dino Disco to kick off Science Centre’s after-hours programmes', 'The silent disco will be presented in collaboration with Wild Pearl Studios, who have worked with the Natural History Museum in Britain on similar events.', 'SINGAPORE – Visitors to Science Centre Singapore can soon enjoy extended-hours programmes tied to special occasions such as Halloween, school holidays and festive periods.\r\n\r\nTitled Sundown Science, the new venture reimagines how audiences can engage with science beyond conventional day visits, Ms Tham Mun See, chief executive of the Science Centre board, said on March 10 in response to queries from The Straits Times.\r\n\r\n“This initiative reflects the centre’s commitment to innovation in science communication, recognising that different formats and timings can unlock new audiences while providing existing guests fresh reasons to return,” she said.\r\n\r\nThe first after-hours event is a silent disco on March 21 to mark the closure of Science Centre Singapore’s dinosaur exhibition.\r\n\r\nThe Dinosaurs | Extinctions | Us exhibition, featuring the 40m-long Patagotitan – the largest dinosaur known – will end on March 23 after close to six months.\r\n\r\nUnlike a traditional disco, in a silent disco people dance to music using wireless headphones instead of having music blaring through a speaker system.\r\n\r\nMs Tham says the Dino Disco is “an exciting standalone event and a crucial pilot for future programming”, adding that it would allow the centre to test the “audience appetite for science-meets-culture experiences”.', 3, 2, 80, 0, 'uploads/articles/1773421661_science2.webp', 0, '2026-03-13 17:07:41', '2026-03-13 17:07:41', '2026-03-13 17:07:41'),
(66, 'Catch firefighting and boxing robots in action at Science Centre’s robotics festival in April', 'Science Centre Singapore hosts RoboFest 2026 from April 9-12, showcasing robotics and AI through interactive exhibits and live demonstrations across four themed zones.', 'SINGAPORE – Robots fighting fires, assisting seniors in walking, and even sparring with one another – these are some of the sights that will greet visitors to the Science Centre Singapore’s first robotics and artificial intelligence festival this April.\r\n\r\nRoboFest 2026: Meet Tomorrow, Today is a four-day festival that will run from April 9 to 12 with interactive experiences and live demonstrations across four zones.\r\n\r\nThe first zone, dubbed The Playground, will feature live demonstrations including a robot boxing showdown and a robot dog firefighting challenge, as well as a comedy performance entitled My Colleague Is A Robot Dog.\r\n\r\n“Visitors will also get a chance to build robots, engage in AI prompt-crafting through a multi-player game and determine if an image or video is real or AI-generated,” the Science Centre said in a release.\r\n\r\nThe second zone, Robotics In Real Life, features a curated gallery of applied robotics and AI, centred on real-world problem-solving.\r\n\r\nThe National University of Singapore will showcase seven projects. These include a palm-sized robot which replicates the elegant swimming motions of an octopus, to demonstrate advanced soft robotics technology where machines are made of flexible materials that allow them to mimic biological movements.\r\n\r\nOther NUS projects include an assistive robot with a flexible robotic arm that can support seniors with walking and fall prevention, as well as a household robot assistant that people can instruct to perform tasks such as throwing away rubbish.', 3, 2, 80, 0, 'uploads/articles/1773421699_science3.webp', 0, '2026-03-13 17:08:19', '2026-03-13 17:08:19', '2026-03-13 17:08:19'),
(67, 'Singapore revises healthcare AI guidelines, attains WHO top rating for medical device regulation', 'Singapore updated AI healthcare guidelines, using regulatory sandboxes to evaluate AI solutions in real settings, ensuring safety and innovation.', 'SINGAPORE – Singapore has revised its healthcare AI guidelines to enable more innovations to augment the healthcare workforce and new drugs to reach patients faster, Minister for Health Ong Ye Kung said on March 10.\r\n\r\nThe Health Sciences Authority (HSA) has also become the first national regulatory authority to attain the World Health Organization’s (WHO) highest level of medical device regulation, he said. The recognition enables HSA to serve as a global reference for other regulators worldwide.\r\n\r\nSpeaking at the opening of the International Medical Device Regulators Forum at NTUC Centre, Mr Ong said the Ministry of Health (MOH) and HSA co-developed and launched the revised healthcare AI framework that addresses developments in artificial intelligence, such as generative AI, to better support innovation, while ensuring safety and quality.\r\n\r\nIn his speech, Mr Ong said regulatory sandboxes will facilitate the evaluation of AI solutions in real-world healthcare settings, ensuring that AI tools are built using quality, real-life data.\r\n\r\nHe added that HSA has not received any registration application for AI-developed drugs, but it would welcome such applications in the near future.\r\n\r\nAI is revolutionising the drug development process, as simulated laboratory data is being used to replace traditional early-phase clinical trials, which are costly and time-consuming.\r\n\r\nHSA “will take a technology-neutral approach to regulation, applying the same rigour to AI-developed drugs as it does to conventional drugs”, Mr Ong said.', 3, 2, 80, 0, 'uploads/articles/1773421727_science4.webp', 0, '2026-03-13 17:08:47', '2026-03-13 17:08:47', '2026-03-13 17:08:47'),
(68, 'Singapore named in second US unfair trade practices probe; MTI to engage US Trade Rep’s office', 'Singapore is among 60 economies named in a second US trade investigation.', 'SINGAPORE – The Ministry of Trade and Industry (MTI) said on March 13 that it will engage the Office of the United States Trade Representative (USTR) over a second unfair trade practices probe the American agency has initiated.\r\n\r\nUSTR said on March 12 that it has started investigations into 60 economies, including Singapore, on the importation of goods produced with forced labour.\r\n\r\nOther than Singapore, the likes of Australia, Canada, Japan and South Korea are also being investigated.\r\n\r\nAlso subject to the probe are Singapore’s ASEAN neighbours Cambodia, Indonesia, Malaysia, the Philippines, Thailand and Vietnam.\r\n\r\nAccording to USTR’s announcement, the 60 economies were identified on the basis that “none has adopted or effectively enforced a forced labour import prohibition to date”.\r\n\r\n“For too long, American workers and firms have been forced to compete against foreign producers who may have an artificial cost advantage gained from the scourge of forced labour,” said US Trade Representative ​Jamieson Greer in a statement on March 12.\r\n\r\n“These investigations will determine whether foreign governments have taken sufficient steps to prohibit the importation of goods produced with forced labour, and how the failure to eradicate these abhorrent practices impacts US workers and businesses.”', 3, 3, 80, 0, 'uploads/articles/1773422668_politics1.webp', 0, '2026-03-13 17:24:28', '2026-03-13 17:24:28', '2026-03-13 17:24:28'),
(69, 'Brazil’s jailed ex-president Jair Bolsonaro hospitalised in ICU with pneumonia', 'Jair Bolsonaro, 70, was hospitalised on March 13 with bronchopneumonia, fever, chills, and low oxygen levels.', 'SAO PAULO - Brazil’s imprisoned former president Jair Bolsonaro was hospitalised in an intensive care unit on March 13 after being diagnosed with bronchopneumonia, according to a medical note from the DF Star hospital.\r\n\r\nBolsonaro, 70, was taken to the hospital early on March 13 with a high fever, chills and a drop in oxygen saturation, the note said, adding that he is being treated with antibiotics.\r\n\r\nThe former president, who governed from 2019 to 2022, has a history of hospitalisations and surgeries related to a stabbing during a 2018 campaign event.\r\n\r\nHe had been hospitalised in January for a series of exams after falling and hitting his head.\r\n\r\nIn December, he underwent medical procedures to treat a hernia and persistent hiccups.\r\n\r\nBolsonaro is serving a 27-year sentence for plotting a coup after losing the 2022 presidential election to leftist current President Luiz Inacio Lula da Silva. REUTERS', 3, 3, 80, 0, 'uploads/articles/1773422774_politics2.webp', 0, '2026-03-13 17:26:14', '2026-03-13 17:26:14', '2026-03-13 17:26:14'),
(70, 'Party withdraws from Greenland’s governing coalition, weakening united front against Trump', 'The departure of Greenland\'s Siumut party from the coalition means that Greenlandic Foreign Minister Vivian Motzfeldt - who has played a key role in diplomatic talks with the US - will leave her post, said reports.', 'COPENHAGEN - Greenland’s Siumut party has withdrawn from the coalition government, the prime minister said on March 13, weakening efforts to present a united front against US President Donald Trump’s campaign to take control of the Arctic island.\r\n\r\nThe departure follows Siumut chair Aleqa Hammond’s warning that the party would leave after two Greenlandic ministers announced candidacies for Denmark’s March 24 parliamentary election without prior leave.\r\n\r\nGreenland’s Prime Minister Jens-Frederik Nielsen expressed disappointment but said his government would carry on, emphasising the importance of governance during heightened global scrutiny.\r\n\r\n“I think it’s terribly bad timing and I’m very frustrated and disappointed that it’s happening at a time when we should be standing together,” he told reporters.\r\n\r\n“Anything that might look like division in our country is grist to the mill for foreigners and we should avoid that at all cost,” he said.\r\n\r\nThe broad coalition had been a cornerstone of Mr Nielsen’s strategy to respond to what he has termed Greenland’s most serious time in recent history.\r\n\r\nSiumut’s departure means that Greenlandic Foreign Minister Vivian Motzfeldt, who has played a key role in diplomatic talks with the United States, is leaving her post, according to broadcaster KNR.', 3, 3, 80, 0, 'uploads/articles/1773422973_politics3.webp', 0, '2026-03-13 17:29:33', '2026-03-13 17:29:33', '2026-03-13 17:29:33'),
(71, 'Will China’s new ethnic unity law hasten the erosion of minority cultures?', 'China passed a law promoting ethnic unity via \"interaction, exchange and integration,\" legally backing policies such as Mandarin education for minorities.', 'BEIJING – China’s passage of a landmark national law on ethnic minority relations puts a legal stamp on President Xi Jinping’s agenda to assimilate minorities into a national identity that has accelerated in recent years.\r\n\r\nIt gives legal backing to existing practices, such as a requirement for pre-schoolers to learn Mandarin, and for students to be proficient in the language at the end of nine years of compulsory education, at age 15.', 3, 3, 80, 0, 'uploads/articles/1773423034_politics4.webp', 0, '2026-03-13 17:30:34', '2026-03-13 17:30:34', '2026-03-13 17:30:34'),
(72, 'Slowing economy, tricky external ties: China has plate full as annual Two Sessions close', 'China\'s Two Sessions concluded with the approval of its 15th Five-Year Plan, prioritising technological innovation and industrial modernisation from 2026 to 2030.', 'BEIJING – The annual meetings of China’s legislature and top political advisory body, collectively known as the Two Sessions, concluded in Beijing on March 12, cementing a policy blueprint for the world’s second-largest economy to bet big on technological innovation and industrial modernisation over the next five years.\r\n\r\nAfter eight days of deliberation, more than 2,700 Chinese lawmakers approved the 135-page 15th Five-Year Plan, which sets the nation’s development trajectory until 2030.\r\n\r\nThe document also outlines the hefty agenda that policymakers across the country face as they work to plug domestic gaps while navigating a fracturing global order.\r\n\r\nAt the closing session of the National People’s Congress (NPC) on March 12, Mr Zhao Leji, chairman of the Parliament’s standing committee, spoke to rapturous applause as he called on legislators to turn China’s “grand vision” into a “beautiful reality”.\r\n\r\n“In accordance with the arrangements made at this meeting, we must carry out all tasks in a solid and effective manner, remain steadfast in managing our own affairs well (and) strive to achieve a good start for the 15th Five-Year Plan,” he said.\r\n\r\nThe NPC also approved the central government’s annual work report, which sets out China’s goals for 2026, by a near-unanimous majority of 2,759 votes. One lawmaker voted no, while two others abstained.\r\n\r\nThe spotlight at the 2026 Two Sessions remained on China’s headline gross domestic product (GDP) growth target, which was trimmed to a range of 4.5 per cent to 5 per cent – its lowest level since 1991.', 3, 3, 80, 0, 'uploads/articles/1773423069_politics5.webp', 0, '2026-03-13 17:31:09', '2026-03-13 17:31:09', '2026-03-13 17:31:09'),
(73, 'Revised data shows Japan economy grew on strong investment', 'The revision comes as Prime Minister Sanae Takaichi faces pressure to boost the economy.', 'TOKYO - The Japanese economy grew more than initially thought in the fourth quarter of 2025 on strong corporate investment, revised data showed on March 10.\r\n\r\nThe world’s fourth-largest economy grew 0.3 per cent in the three months to December, slightly up from the preliminary figure of 0.1 per cent, according to the Cabinet Office.\r\n\r\nOn an annualised basis, GDP grew 1.3 per cent, up from an initially reported increase of 0.2 per cent.\r\n\r\nGrowth in private consumption, and private residential and corporate investments, contributed to the expansion, according to the cabinet office data.\r\n\r\nThe revision comes as Prime Minister Sanae Takaichi faces pressure to boost the economy.\r\n\r\nMs Takaichi became Japan’s first woman prime minister last October and called snap elections for Feb 8.\r\n\r\nThe vote saw her Liberal Democratic Party (LDP) win a historic two-thirds majority in the lower house.', 3, 4, 80, 0, 'uploads/articles/1773423699_econ1.webp', 0, '2026-03-13 17:41:39', '2026-03-13 17:41:39', '2026-03-13 17:41:39'),
(74, 'UK seeks to limit economic hit from Iran as borrowing costs jump', 'LONDON, March 9 - Britain wants major economies to agree to release emergency oil reserves as the escalating Iranian crisis sends energy prices soaring and increases the risk of higher inflation, finance minister Rachel Reeves said on Monday.  British borrowing costs have soared since the conflict erupted more than a week ago, and by more than those of other European countries and the U.S., as investors fear that surging oil and gas prices will stoke already stubborn inflation and require the government to borrow more.  Prime Minister Keir Starmer warned that a prolonged crisis would hurt the economy, on a day when short-dated British government bond prices were at one point on track for their sharpest daily fall since the market crisis that brought down his Conservative predecessor, Liz Truss.  \"The longer this goes on, the more likely the potential for an impact on our economy,\" Starmer said.  Interest rate futures on Monday suggested investors no longer expect the Bank of England to cut rates this year. Bond prices had recouped most of their losses by the end of the day as oil prices fell from an earlier peak.  QUESTION MARK OVER HOUSEHOLD ENERGY SUBSIDIES  The jump in energy prices threatens to force the government to intervene to cushion the economic blow, a potentially huge challenge as it has limited room to increase spending and is widely unpopular.', 'LONDON, March 9 - Britain wants major economies to agree to release emergency oil reserves as the escalating Iranian crisis sends energy prices soaring and increases the risk of higher inflation, finance minister Rachel Reeves said on Monday.\r\n\r\nBritish borrowing costs have soared since the conflict erupted more than a week ago, and by more than those of other European countries and the U.S., as investors fear that surging oil and gas prices will stoke already stubborn inflation and require the government to borrow more.\r\n\r\nPrime Minister Keir Starmer warned that a prolonged crisis would hurt the economy, on a day when short-dated British government bond prices were at one point on track for their sharpest daily fall since the market crisis that brought down his Conservative predecessor, Liz Truss.\r\n\r\n\"The longer this goes on, the more likely the potential for an impact on our economy,\" Starmer said.\r\n\r\nInterest rate futures on Monday suggested investors no longer expect the Bank of England to cut rates this year. Bond prices had recouped most of their losses by the end of the day as oil prices fell from an earlier peak.\r\n\r\nQUESTION MARK OVER HOUSEHOLD ENERGY SUBSIDIES\r\n\r\nThe jump in energy prices threatens to force the government to intervene to cushion the economic blow, a potentially huge challenge as it has limited room to increase spending and is widely unpopular.', 3, 4, 80, 0, 'uploads/articles/1773423731_econ2.webp', 0, '2026-03-13 17:42:11', '2026-03-13 17:42:11', '2026-03-13 17:42:11');
INSERT INTO `articles` (`id`, `title`, `excerpt`, `content`, `author_id`, `category_id`, `trust_score`, `has_media`, `image_path`, `is_premium_only`, `published_at`, `created_at`, `updated_at`) VALUES
(75, 'Raising solar deployment target also has ‘economic and geopolitical’ component: Energy expert', 'Solar is currently the main source of renewable energy that can be harnessed domestically.', 'SINGAPORE - The move to raise the Republic’s solar deployment target from 2 gigawatt-peak (GWp) to 3 GWp by 2030 reflects the maturation of Singapore’s solar industry and could boost the nation’s resilience to energy import disruptions, experts said.\r\n\r\nDr Thomas Reindl, deputy chief executive at the Solar Energy Research Institute of Singapore (Seris), said that maximising solar deployment here not only helps to decarbonise the nation’s power sector, but also has an economic and geopolitical component.\r\n\r\nHe added: “For every kilowatt-hour generated locally, we don’t need to import fuel from overseas, and we also don’t need to ‘ask anyone’ or depend on contractual parties, such as for the importing of electricity.”\r\n\r\nDr Reindl was among the experts responding to The Straits Times’ queries on the significance of Singapore’s move to increase its solar deployment target, first announced by Prime Minister Lawrence Wong in his Budget 2026 speech on Feb 12.\r\n\r\nThe nation’s earlier solar deployment target, of reaching 2 GWp by 2030, was met in 2025.\r\n\r\nGWp measures the maximum power that solar panel systems in Singapore can produce together under standard test conditions. However, the average amount of electricity generated from solar energy at any given time is lower, for reasons that include electricity not being generated at night.\r\n\r\nSolar is currently the main source of renewable energy that can be harnessed domestically, although its potential remains small relative to the nation’s total energy needs.', 3, 4, 80, 0, 'uploads/articles/1773423835_econ3.webp', 0, '2026-03-13 17:43:55', '2026-03-13 17:43:55', '2026-03-13 17:43:55'),
(76, 'Putin to press on with war despite economic woes, Lithuania warns', 'The intelligence assesses that Russian President Vladimir Putin’s war goals remain unchanged despite the military and economic damage from the war.', 'VILNIUS – Russian President Vladimir Putin is determined to keep spending on strengthening Russia’s military despite a weakening economy, according to a Lithuanian intelligence assessment.\r\n\r\nThe country’s growing economic difficulties aren’t enough to force Mr Putin to end the war in Ukraine, where he’s likely still seeking a breakthrough on the battlefield, the Lithuanian National Threat Assessment report said.  \r\n\r\nThis determination remains a major source of concern to its neighbours as Moscow continues to add new military units on NATO’s borders even with the ongoing hostilities in Ukraine, it said.\r\n\r\nThe intelligence assesses that Mr Putin’s war goals remain unchanged despite the military and economic damage from the war and the Kremlin is ready to use repression and propaganda to respond to any signs of social or political instability.\r\n\r\nLow unemployment, rapid wage growth and high demand for labour in the defence sector “have so far directly cushioned the effect of the economic slowdown on society,” the report said. “It is unlikely that deteriorating living standards will pose a risk to the Kremlin’s political stability.”\r\n\r\nThe report offers sobering assessment of how much Mr Putin is ready to stretch his resources to achieve the military goals and contrasts with views from some western experts that economic cracks that are starting to emerge may force the Kremlin to negotiate.\r\n\r\nRussia has shown no desire to compromise and its goals remain unchanged – to seize more Ukrainian territory and to change the balance of power in Europe, according to the report.', 3, 4, 80, 0, NULL, 0, '2026-03-13 17:44:16', '2026-03-13 17:44:16', '2026-03-13 17:44:16'),
(77, 'IMF ready to help economies squeezed by Middle East oil shock', 'About 50 countries already rely on the fund to meet their balance of payment needs.', 'BANGKOK – The International Monetary Fund said it stands ready to assist countries facing balance of payment concerns amid heightened uncertainty from the Middle East conflict.\r\n\r\nIMF managing director Kristalina Georgieva said that she expects greater demand for the fund’s programmes, especially since foreign aid is also on the decline.\r\n\r\nAbout 50 countries already rely on the fund to meet their balance of payment needs, she said in an interview with Bloomberg Television’s Haslinda Amin in Bangkok.\r\n\r\n“We have some of our members that have significant balance of payment concerns already engaging with us,” Ms Georgieva said on March 6. “We are ready to act. We recognise our responsibility in this world of uncertainty to be an anchor of stability.”\r\n\r\nShe expressed concern for some Pacific Island countries that are among the most vulnerable to a disruption in global oil supplies.\r\n\r\nLow-income countries and those with high levels of debt could also come under pressure, she said.\r\n\r\nAccording to Ms Georgieva, a 10 per cent increase in energy prices lasting a year would raise inflation by 40 basis points and slow growth by up to 0.2 per cent.', 3, 4, 80, 0, NULL, 0, '2026-03-13 17:52:18', '2026-03-13 17:52:18', '2026-03-13 17:52:18'),
(78, 'Filipino wild card Miguel Tabuena proud to represent the Philippines, South-east Asia on LIV stage', 'Miguel Tabuena\'s early exposure to golf, influenced by his parents and Tiger Woods, fuelled his passion and led to success, including a silver medal at the 2010 Asian Games.', 'SINGAPORE – While most children fell asleep to shows like Barney And Friends or Sesame Street, Miguel Tabuena had a different nightly ritual growing up: Watching Tiger Woods’ 1997 Masters triumph on videotapes.\r\n\r\nHe eventually memorised the commentary after viewing the VHS playback repeatedly, an early sign of his love for golf.\r\n\r\nHis parents, Luigi and Lorna, who played the sport at a club level, introduced him to golf early. At one year and eight months old, Tabuena owned his first club, albeit a plastic one.\r\n\r\nThat was soon replaced by a more serious upgrade – his mother’s five-wood, cut down to about a foot long.\r\n\r\nThe club went everywhere with him and remained by his side even when he slept.\r\n\r\nRecalling his early days as a golfer, the 31-year-old said: “I was never forced to play, even when I got older, I enjoyed playing other sports. I also grew up playing basketball and football, but golf really hit me a different way for sure.”\r\n\r\nHis love for the sport endured and his potential grew over time.', 3, 5, 80, 0, 'uploads/articles/1773424654_sport2.webp', 0, '2026-03-13 17:56:28', '2026-03-13 17:56:28', '2026-03-13 17:57:34'),
(79, 'Buoyant Bryson DeChambeau takes three-shot lead after second round of LIV Singapore', 'Bryson DeChambeau leads LIV Singapore after a 65, putting him at 10-under 132, three shots ahead of the competition as he aims to fine-tune his game for the Masters.', 'SINGAPORE – The pieces of Bryson DeChambeau’s game are starting to fall into place with the Major season looming.\r\n\r\nJust weeks out from the April 9-12 Masters – the season’s first Major – the American is finding his form, as evident from his six-under 65 display at Sentosa Golf Club’s Serapong Course on March 13 that gave him a three-shot lead after the second round of LIV Singapore.\r\n\r\nHis 10-under 132 total score gives him a comfortable three-shot advantage over Belgian Thomas Detry (67), Louis Oosthuizen of South Africa (67), Spaniard Jon Rahm (68), England’s Lee Westwood (68) and Canadian Richard Lee (68) heading into the weekend.\r\n\r\nTwo-time US Open winner DeChambeau said: “I feel like there are times when I see the golf ball coming out of the window that I want, and it’s consistently doing that, and I feel so comfortable doing that every time, no matter the situation.\r\n\r\n“That’s when I get into that sphere – the zone, as people call it. I’ve seen glimpses of it this week. But it’s just not fully there yet and I hope to get even more into that bubble as time goes on.”\r\n\r\nThe 32-year-old is still trying to recapture the form that saw him shoot a final-round 58 en route to claiming the LIV Greenbrier event in 2023 and he believes he is on the right trajectory.\r\n\r\nHe finished joint-24th at the last event in Hong Kong, after tying for third in Adelaide and placing tied-17th at the season-opener in Riyadh.', 3, 5, 80, 0, 'uploads/articles/1773424705_sport1.webp', 0, '2026-03-13 17:58:25', '2026-03-13 17:58:25', '2026-03-13 17:58:25'),
(80, 'George Russell leads Mercedes one-two in China GP sprint qualifying', 'SHANGHAI – Championship pacesetter George Russell relished an “amazing feeling” as he took pole position on March 13 for the Formula One Chinese Grand Prix sprint race, leading a Mercedes one-two ahead of teammate Kimi Antonelli.', 'The 28-year-old Briton clocked 1min 31.520sec around the 5.451km Shanghai International Circuit, 0.289sec quicker than Antonelli, with world champion Lando Norris 0.621sec behind his fellow Englishman in third.\r\n\r\nAntonelli was investigated after the session for allegedly impeding Norris, but he was cleared and will retain second spot.\r\n\r\nFerrari’s Lewis Hamilton, winner of the China sprint in 2025, qualified fourth for the 19-lap race on March 14, 0.641sec slower than Russell.', 3, 5, 80, 0, 'uploads/articles/1773424731_sport3.webp', 0, '2026-03-13 17:58:51', '2026-03-13 17:58:51', '2026-03-13 17:58:51'),
(81, 'Arsenal and Manchester City resume title duel after woeful week for English clubs', 'Arsenal manager Mikel Arteta (left) and Manchester City boss Pep Guardiola both know that they are entering a vital stage.', 'LONDON – After a sobering week in Europe for English clubs, some bruised egos return to domestic issues this weekend with the English Premier League title race and relegation battles both reaching pivotal moments.\r\n\r\nThe league’s boast of being the continent’s best took a bit of a hammering over 48 hours this week, with none of the six clubs in Champions League last-16 action winning their matches.\r\n\r\nManchester City were one of the four teams to suffer defeat and they will need to dust themselves down quickly from a 3-0 loss at Real Madrid when they face West Ham United away on March 14. At kick-off they could find themselves 10 points behind league leaders Arsenal, although City will have played two fewer games.\r\n\r\nThe Gunners, who needed a last-minute Kai Havertz penalty to salvage a 1-1 draw away to Bayer Leverkusen on March 11, have the opportunity to really turn the screws on Pep Guardiola’s side as they host Everton earlier in the day.\r\n\r\n“It is a pleasure to be here. It is nice,” the City manager said of the title race on March 13.\r\n\r\n“We arrive in the last 10 games and everyone is playing for something. Now there is no second chances.\r\n\r\n“Tomorrow is important for the Premier League. We are used to these situations. All the time we are making decisions and I think a lot about what is best for the team. The league is the most difficult title. Every game is important. We will fight until the end.”', 3, 5, 80, 0, NULL, 0, '2026-03-13 17:59:07', '2026-03-13 17:59:07', '2026-03-13 17:59:07'),
(82, 'Australia edge out North Korea to reach Women’s Asian Cup semis', 'Sam Kerr of Australia celebrates after winning the Women’s Asian Cup quarter-final match against North Korea.', 'PERTH – In-form Alanna Kennedy and skipper Sam Kerr produced spectacular strikes as Australia overcame a talented North Korea side 2-1 to reach the semi-finals of the Women’s Asian Cup on March 13.\r\n\r\nKicking off the knockout phase, the Matildas were outplayed by their youthful opponents for large chunks of a tense match in front of 16,466 fans at Perth Rectangular Stadium.\r\n\r\nBut they took their chances, with Kennedy firing in her fifth tournament goal with a left-foot bullet from the edge of the box in the ninth minute.', 3, 5, 80, 0, NULL, 0, '2026-03-13 17:59:52', '2026-03-13 17:59:52', '2026-03-13 17:59:52'),
(83, '870 psychologists enrol in professional body as Singapore plans mandatory registration', 'About 870 psychologists have voluntarily registered with the Singapore Psychological Society ahead of mandatory registration, as demand for mental health services rises.', 'SINGAPORE – Ahead of the impending mandatory registration for psychologists in Singapore, at least 870 of them have voluntarily registered with the Singapore Psychological Society (SPS), offering an initial indication of the total number of psychologists practising here. \r\n\r\nThis number, which is up from around 700 in early 2024, is expected to grow further as SPS continues to process new applications to the register, said SPS president Adrian Toh. \r\n\r\nThere is a shortage of mental health professionals, such as psychologists, as the demand for mental health services here rises, but without a mandatory registration system, it is hard to pinpoint the exact number of qualified psychologists practising here.\r\n\r\nAs part of a broader national effort to strengthen the mental health infrastructure in Singapore to ensure timely support, the Government has said that it aims to increase the number of public-sector psychologists by about 40 per cent by 2030 or earlier, as well as offer a registration framework for these practitioners to enhance standards and safety.\r\n\r\nAt the March 5 debate on the Ministry of Health’s (MOH) budget, Senior Minister of State for Health Koh Poh Koon said the ministry will be registering psychologists in five sub-disciplines – clinical, educational, counselling, forensic psychology and clinical neuropsychology. The detailed schedule, requirements and road maps for the registration will be announced by early 2027, he added.\r\n\r\nThe five sub-disciplines were identified as these psychologists provide direct care involving higher-risk assessments and interventions across various sectors that warrant regulatory oversight.\r\n\r\nMOH said the registration requirements, which are being developed by an inter-agency committee, will be based on international standards, local and overseas regulatory frameworks, current SPS criteria for registered psychologists, and feedback from public- and private-sector psychologists.', 3, 6, 80, 0, 'uploads/articles/1773424946_health1.webp', 0, '2026-03-13 18:02:26', '2026-03-13 18:02:26', '2026-03-13 18:02:26'),
(84, 'WHO confirms 18 attacks on healthcare sites in Iran', 'A member of the Iranian medical staff seen in front of the destroyed Gandhi Hospital in Tehran, on March 7.', 'The World Health Organization on March 11 said it has verified 18 attacks on healthcare centres in Iran since the start of the US and Israel war on Iran on Feb 28, which has resulted in eight deaths among health workers.\r\n\r\n“These attacks not only cost lives but deprive communities of care when they need it most. Health workers, patients and health facilities must always be protected under international humanitarian law,” the WHO said in a statement.\r\n\r\nDuring the same period, 25 attacks on healthcare centres in Lebanon resulted in 16 deaths and 29 injuries, the agency said.\r\n\r\nThe conflict has triggered a large-scale population movement, the WHO added.\r\n\r\nIt estimates more than 100,000 people in Iran have relocated, and up to 700,000 people in Lebanon have been internally displaced, many sheltering in crowded buildings with scarce access to clean water, sanitation and hygiene.\r\n\r\nSuch conditions risk outbreaks of respiratory and diarrhoeal diseases, the WHO warned, particularly among women and children.', 3, 6, 80, 0, 'uploads/articles/1773424970_health2.webp', 0, '2026-03-13 18:02:50', '2026-03-13 18:02:50', '2026-03-13 18:02:50'),
(85, 'US sued by food stamp recipients over restrictions on sugary drinks, candy', 'The plaintiffs suing the US Department of Agriculture said they or family members rely on the restricted foods to manage health conditions like diabetes, or to obtain energy boosts.', 'WASHINGTON – Food stamp recipients sued the US Department of Agriculture on March 11 to undo Trump administration efforts to prevent them from using benefits to buy products such as sugary drinks, energy drinks and candy.\r\n\r\nIn a complaint filed in the Washington, DC federal court, five plaintiffs said the restrictions “destabilise food access” for participants in the Supplemental Nutrition Assistance Program in the 22 US states where the department has approved so-called “food restriction” waivers.\r\n\r\nUS Agriculture Secretary Brooke Rollins, and US Health and Human Services Secretary Robert F. Kennedy Jr have endorsed the waivers as part of the Make America Healthy Again (MAHA) movement.\r\n\r\nThe plaintiffs – who live in Colorado, Iowa, Nebraska, Tennessee and West Virginia – said they or family members rely on the restricted foods to manage health conditions such as diabetes and allergies, or to obtain energy boosts needed to conduct their daily lives.\r\n\r\nThey said the waivers cause confusion at the checkout line, and cause irreparable harm by forcing them to choose between spending cash on restricted items, or forgoing spending on basics such as rent and transportation.\r\n\r\nOne plaintiff, Ms Amanda Johnson of Knoxville, Tennessee, said letting her state’s waiver take effect would restrict her autistic 19-year-old daughter to only three “safe” foods and beverages – one of which is bottled water – because of a serious eating disorder.\r\n\r\nMs Johnson said her daughter’s other six safe foods, including M&M’s and Welch’s fruit punch, would be ruled out.', 3, 6, 80, 0, 'uploads/articles/1773424989_health3.webp', 0, '2026-03-13 18:03:09', '2026-03-13 18:03:09', '2026-03-13 18:03:09'),
(86, 'Is your psychologist actually qualified? How to check in Singapore', 'Clearing the air on what different types of psychologists do.', 'Synopsis: Every first Wednesday of the month, The Straits Times helps you make sense of health matters that affect you.\r\n\r\nThe Ministry of Health has announced that five key psychological subdisciplines will be registered under the Allied Health Professions Act, with details expected to be out in early 2027. This move aims to enhance the safety and public trust associated with psychological services and the profession as a whole.', 3, 6, 80, 0, 'uploads/articles/1773425038_health4.webp', 0, '2026-03-13 18:03:58', '2026-03-13 18:03:58', '2026-03-13 18:03:58'),
(87, 'New IP rider premiums to cost at least 30% less, with one insurer offering an 84% reduction', 'New riders to be sold will differ in their coverage, with some covering only up to public hospital stays and some including private hospital stays as well.', 'SINGAPORE – Private health insurers gearing up for the launch of new riders have revealed that policyholders who switch schemes come April 1 could see premium reductions of at least 30 per cent, with one insurer offering premium reductions of up to 84 per cent.\r\n\r\nAll seven insurers will be launching new Integrated Shield Plan (IP) rider products by April 1 in order to meet the Ministry of Health’s (MOH) new requirements.\r\n\r\nThe new requirements stipulate that new IP riders will no longer be allowed to cover the minimum deductibles set by MOH, meaning those with the new riders have to pay at least $1,500 before insurance coverage kicks in.\r\n\r\nIn addition, the co-payment cap will be doubled from the current $3,000 to $6,000, requiring policyholders to pay a larger portion of their bills.\r\n\r\nThe move by MOH aims to address rising insurance premiums and private healthcare costs by instilling discipline in healthcare consumption, particularly for minor episodes. This in turn will slow down the migration of private healthcare patients to the public sector, which already caters to 90 per cent of patients.', 3, 6, 80, 0, NULL, 0, '2026-03-13 18:04:14', '2026-03-13 18:04:14', '2026-03-13 18:04:14'),
(88, 'Parents in Japan to get Instagram notifications when teens repeatedly search for suicide content', 'To further protect children, Instagram will also soon introduce a feature that restricts access to posts about drugs and dangerous behaviour.', 'TOKYO – The Japanese arm of US-based Meta Platforms, which runs Instagram, said on March 10 that it would introduce a feature in Japan in 2026 to notify parents if children aged 13 to 17 repeatedly try to search for content related to suicide or self-harm on the photo-sharing app.\r\n\r\nTo further protect children, it will also soon introduce a feature that restricts access to posts about drugs and dangerous behaviour.\r\n\r\nFor users aged 13 to 17, who are allowed on Instagram under the app’s terms of use, the “Teen Accounts” feature, which limits certain functions, will notify parents via the app or by e-mail if children repeatedly try to search for suicide-related content. For this to work, parents must link their account to their child’s.\r\n\r\nWhile this feature is already available in the United States and Britain, it had not been introduced in Japan.\r\n\r\nInstagram will also soon introduce a feature to restrict teens up to age 17 from viewing posts containing drug-related content, extreme language such as threats and dangerous acts like shooting guns. The platform already limits the display of posts when they contain sexual imagery or relate to alcohol or tobacco.', 3, 6, 80, 0, NULL, 0, '2026-03-13 18:04:29', '2026-03-13 18:04:29', '2026-03-13 18:04:29'),
(89, 'Gaining competitive edge: How the right digital transformation partner can help drive business success', 'FUJIFILM Business Innovation supports organisations in transforming everyday operations by combining devices, cloud, services and AI-powered solutions', 'An earlier article explored FUJIFILM Business Innovation’s digital transformation (DX) support strategy, focusing on the fundamental infrastructure-development phase. It highlighted the importance of building a solid digital backbone – from securing an information entry point using multifunction printers to stabilising IT operations through managed services.\r\n\r\nTrue digital transformation, however, does not stop there. Once data has been accumulated and systems stabilised, companies must take the next step – shifting to offensive DX.\r\n\r\nThis article takes a closer look at the implementation of more advanced, strategic solutions. Continuing the discussion with FUJIFILM Business Innovation’s corporate vice president Shiro Kikuchi and general manager of the Marketing Business Solution Division Motoru Takizawa, the article examines concrete approaches to strengthening organisational competitiveness – from rethinking business processes and transforming work styles to designing hybrid workspaces that seamlessly integrate the analogue and the digital.', 3, 1, 80, 0, 'uploads/articles/1773425898_latest1.webp', 0, '2026-03-13 18:18:18', '2026-03-13 18:18:18', '2026-03-13 18:18:18'),
(90, 'Antarctic sea ice improves after four years of extreme lows, say US scientists', 'Antarctic sea ice rebounded in 2024, reaching 2.58 million sq km on Feb 26, closer to the average after four years of record lows.', 'PARIS - Antarctic sea ice coverage has likely rebounded this year, coming closer to its annual summer average after four years of extreme lows, US scientists said on March 9.\r\n\r\nThe area covered by Antarctic sea ice likely reached its annual minimum level at 2.58 million square kilometres on Feb 26, according to scientists at the National Snow and Ice Data Center (NSIDC) at the University of Colorado Boulder.\r\n\r\nEvery year, Antarctic sea ice reaches a minimum level during the southern hemisphere’s summer, so this is the point that scientists measure it for annual readings.\r\n\r\nThis year’s level ranks as the 16th smallest since satellite measurements began in 1979.\r\n\r\nThe 2026 minimum sea ice extent is closer to average than in the past four years, and 730,000 square kilometres above the record low set in February 2023, the scientists said.\r\n\r\nBut it was still 260,000 square kilometres below the 1981-2010 average.\r\n\r\n“Through most of the year, Antarctic sea ice was well below the daily average,” said Dr Ted Scambos, senior research scientist at the Cooperative Institute for Research in the Environmental Sciences (CIRES).', 3, 2, 80, 0, NULL, 0, '2026-03-13 18:27:54', '2026-03-13 18:27:54', '2026-03-13 18:27:54'),
(91, 'Trump administration estimates Iran war cost at over $14 billion in six days, source says', 'Black soot after reported black rain following a strike on fuel tanks, amid the US-Israeli conflict with Iran, in Tehran on March 10', 'WASHINGTON - Officials from US President Donald Trump’s administration estimated during a congressional briefing this week that the first six days of the war on Iran had cost the United States at least US$11.3 billion (S$14.4 billion), a source familiar with the matter said on March 11.\r\n\r\nThat figure, from a closed-door briefing for senators on March 10, did not include the entire cost of the war, but was provided to lawmakers as they have clamoured for more information about the conflict.\r\n\r\nSeveral congressional aides have said they expect the White House to soon submit a request to Congress for additional funding for the war.\r\n\r\nSome officials have said the request could be for US$50 billion, while others have said that estimate seems low.\r\n\r\nThe administration has not provided a public assessment of the cost of the conflict or a clear idea of its expected duration. Mr Trump said during a trip to Kentucky on March 11 that “we won” the war but that the US would stay in the fight to finish the job.\r\n\r\nThe US$11.3 billion figure was first reported on March 11 by The New York Times.', 3, 3, 80, 0, 'uploads/articles/1773426611_latest2.webp', 0, '2026-03-13 18:30:11', '2026-03-13 18:30:11', '2026-03-13 18:30:11'),
(92, 'Singapore retail sales drop 0.4% in January, partly because of later CNY timing', 'Looking ahead, economists said rising oil prices from the Iran war raises inflation risks, but it is too early to tell how much of an impact this will have on retail sales', 'SINGAPORE - Takings at the till dipped 0.4 per cent year on year in January, reversing the 2.5 per cent growth recorded in December.\r\n\r\nThe decline in retail sales was partly due to Chinese New Year falling in February in 2026, as opposed to January in the previous year. Despite this, analysts polled by Bloomberg had expected sales to rise by 2.8 per cent.\r\n\r\nLooking ahead, economists said rising oil prices from the Iran war raise inflation risks, but it is too early to tell how much of an impact this will have on retail sales.\r\n\r\nExcluding motor vehicles, parts and accessories, retail sales dropped 2.8 per cent year on year, compared with the 1.8 per cent growth in December 2025.\r\n\r\nHowever, on a seasonally adjusted basis, retail sales rose 6.1 per cent in January over the previous month, according to data from the Singapore Department of Statistics released on March 5.\r\n\r\nPerformance was mixed across the different retail industries.\r\n\r\nWearing apparel and footwear retailers recorded a year-on-year decline in sales of 12.9 per cent, mainly due to lower sales of bags and shoes.', 3, 4, 80, 0, 'uploads/articles/1773426657_latest3.webp', 0, '2026-03-13 18:30:57', '2026-03-13 18:30:57', '2026-03-13 18:30:57'),
(93, 'Carlos Alcaraz sets up Daniil Medvedev Indian Wells semi-final, Aryna Sabalenka and Elena Rybakina advance', 'Carlos Alcaraz improved his record to 16-0 to start the season with a solid display and remained on course for a third Indian Wells crown', 'INDIAN WELLS – World No. 1 Carlos Alcaraz charged past Cameron Norrie 6-3, 6-4 on March 12 to set up an Indian Wells semi-final against Daniil Medvedev, after the Russian ended defending champion Jack Draper’s run 6-1, 7-5.\r\n\r\nTop-ranked Aryna Sabalenka also reached the last four in the women’s draw with a 7-6 (7-0), 6-4 win over Victoria Mboko, but Iga Swiatek was unable to find her way past Elina Svitolina and fell 6-2, 4-6, 6-4.\r\n\r\nAlcaraz improved his record to 16-0 to start the season with a solid display and remained on course for a third Indian Wells crown. He was briefly in trouble at 0-2 down in the second set but quickly regained the momentum to see off Briton Norrie.\r\n\r\n“It was really difficult; I struggled with Cameron’s style,” he said.\r\n\r\n“I was trying to play my best but there was a little bit of confusion. His forehand has super topspin and his backhands are very flat, so sometimes it’s tricky to play against him and find the correct shots.\r\n\r\n“But I played solid and aggressive when I could. I’m happy to be at this level.”', 3, 5, 80, 0, NULL, 0, '2026-03-13 18:31:46', '2026-03-13 18:31:46', '2026-03-13 18:31:46'),
(94, 'WHO warns of health risks from ‘black rain’ in Iran', 'Tehran was choked in black smoke on March 9 after an oil refinery was hit', 'GENEVA – The World Health Organization warned on March 10 that the “black rain” falling in Iran after strikes on oil facilities could cause respiratory problems, and it backed Iran’s advisory urging people to remain indoors.\r\n\r\nThe UN health agency, which has an office in Iran and works with the authorities on health emergencies, said it has received multiple reports of oil-laden rain this week.\r\n\r\nTehran was choked in black smoke on March 9 after an oil refinery was hit, in an escalation in strikes on Iran’s domestic energy supplies as part of the US-Israeli campaign.\r\n\r\n“The black rain and the acidic rain coming with it is indeed a danger for the population, respiratory mainly,” WHO spokesperson Christian Lindmeier told a press briefing in Geneva, adding that Iran had advised people to stay indoors.\r\n\r\nPeople should protect themselves\r\nAsked whether the WHO backed that advice, he said: “Given what is at risk right now, the oil storage facilities, the refineries that have been struck, triggering fires, bringing serious air quality concerns, that is definitely a good idea.”\r\n\r\nHe said the strikes had caused “the massive release of toxic hydrocarbons, sulfur oxides and nitrogen compounds, into the air”.\r\n\r\nScientists said inhaling or touching the smoke or particles could cause headaches, skin and eye irritation, and difficulty breathing. Longer-term exposure to some of the compounds increases the risk of some cancers, they added.', 3, 6, 80, 0, NULL, 0, '2026-03-13 18:32:10', '2026-03-13 18:32:10', '2026-03-13 18:32:10'),
(95, 'Lifestyle-driven cancer risk persists despite Singapore’s prevention efforts: Oncologists', 'Lifestyle factors increasingly drive cancer in Singapore\'s ageing, urbanised society, despite strong prevention policies.', 'SINGAPORE – Lifestyle-related risk factors may increase the prevalence of cancer cases here, despite Singapore’s strong cancer prevention policies, said oncologists.\r\n\r\nAchieving a substantial drop in cancer cases will require a multifaceted approach that includes the implementation of effective screening programmes, they add.\r\n\r\nThe World Health Organization unveiled research in February, suggesting that almost four in 10 cancer cases worldwide were linked to preventable causes such as smoking, drinking and air pollution.\r\n\r\nPublished in the journal Nature Medicine, the study called for “context-specific prevention strategies” such as strong tobacco control measures as well as vaccination against HPV and other cancer-causing infections like hepatitis B. \r\n\r\nWhile these findings are applicable to Singapore, the Republic’s more urbanised lifestyle means statistics here may differ slightly from global averages, said Dr Gloria Chan, a consultant with the haematology-oncology department at the National University Cancer Institute, Singapore (NCIS).\r\n\r\n“Singapore is a high-income, ageing society, so lifestyle-related risks play a larger role compared with other countries where infection-related cancers are more dominant,” she said.\r\n\r\nAssistant Professor Dawn Chong, a senior consultant with the medical oncology division at the National Cancer Centre Singapore, said: “We are likely to observe a long-term decline in the incidence of preventable cancers associated with modifiable risk factors.”', 3, 6, 80, 0, 'uploads/articles/1773427062_latest4.webp', 0, '2026-03-13 18:37:42', '2026-03-13 18:37:42', '2026-03-13 18:37:42'),
(96, 'Indonesia sets three-month deadline for online child safety compliance', 'The policy is designed to safeguard children from online threats such as grooming, abuse and harmful content', 'JAKARTA - Social media and other digital platforms in Indonesia now have three months to assess child safety risks under a new government regulation, with users under 16 years old facing restrictions on high-risk services to curb exposure to harmful content and online exploitation.\r\n\r\nAfter a year of drafting and consultations, the Communications and Digital Ministry finally issued its ministerial regulation on March 6 as a technical guideline for Child Protection in Digital Space Regulation (PP Tunas).', 3, 1, 80, 0, NULL, 0, '2026-03-13 18:40:31', '2026-03-13 18:40:31', '2026-03-13 18:40:31'),
(97, 'Will Xi shine the spotlight on Trump in Beijing?', 'Chinese leaders are already signaling that they want to limit the conversation to the economy, knowing full well that Mr Donald Trump’s unilateral weapon of tariffs has evaporated', 'A US$220 million (S$280 million) ad campaign featuring her astride a horse telling migrants to turn back, combined with fumbled congressional testimony, turned Ms Kristi Noem into the story. Then came the end.\r\n\r\nThe firing of the Homeland Security Secretary, hours after she testified to congressional committees about her first year in office, is the latest and clearest proof of one immutable rule in President Donald Trump’s Washington: The moment you become the story instead of him, your time is running out.', 3, 3, 80, 0, 'uploads/articles/1773427672_latest6.webp', 0, '2026-03-13 18:47:52', '2026-03-13 18:47:52', '2026-03-13 18:47:52'),
(98, 'Science experiments and honing debate skills: How after-school enrichment pushes stronger pupils', 'Singapore will expand primary school enrichment programmes from 2027, as the Gifted Education Programme comes to an end.', 'SINGAPORE – Speaking confidently before an audience did not always come naturally to Primary 6 pupil Carys Ang, who describes herself as shy and lacking in confidence when she was younger.\r\n\r\nBut after two years of being part of Blangah Rise Primary School’s after-school debate programme, she can now deliver speeches with poise, backed up by arguments and rebuttals drawing on real-world knowledge.\r\n\r\n“When I was in Primary 4, I went to see my first-ever competitive debate as an audience member, and I was so impressed at how the participants spoke so well,” said Carys. “And I wanted to become like them.”\r\n\r\nToday, Carys, 12, represents her school in competitions, using the weekly sessions taught by its English language teachers to strengthen her voice projection and improve her clarity of speech. Such enrichment includes topics like design thinking and journalism.\r\n\r\nShe is one of 40 pupils who were selected by her school for English language enrichment classes.\r\n\r\nOverall, 15 per cent of the school’s Primary 3 to Primary 6 cohorts are enrolled in various enrichment programmes, including those in mathematics and science, which were introduced in 2019.\r\n\r\nSuch school-based provisions for high-ability pupils are set for a nationwide expansion.', 3, 2, 80, 0, NULL, 0, '2026-03-13 18:53:58', '2026-03-13 18:53:58', '2026-03-13 18:53:58'),
(99, 'BG Tampines Rovers exit ACL2, but vow to return, go one step further', 'BG Tampines Rovers were eliminated from the AFC Champions League Two after a 4-3 aggregate loss to Bangkok United.', 'SINGAPORE – BG Tampines Rovers’ quest for a historic continental semi-final berth may have ended on March 12 following their elimination from the AFC Champions League Two (ACL2), but the team’s deep run has certainly whetted their appetite for future campaigns.\r\n\r\nDespite a hard-fought 2-2 draw against Bangkok United in the quarter-final, second leg at the Jalan Besar Stadium, the Stags fell agonisingly short, as they bowed out 4-3 on aggregate.\r\n\r\nTheir Maltese forward Trent Buhagiar – who was named the Man of the Match for the second leg – is optimistic that the team will be back next season for more.\r\n\r\nThe 28-year-old, who scored the hosts’ first goal of the night, said: “I think that we showed as a club that we are capable of doing well in this competition. We had a tough group; we got through that. We put on some really good performances throughout this whole competition... and stayed consistent. We played really good football in parts, which ultimately led to this position.”\r\n\r\n“We can take a lot of positives from this tournament and we can show in the future that we can go one step closer next season, and that’s the aim. This club needs to ultimately move forward and keep progressing.\r\n\r\n“I’m super proud of the team and obviously the club is moving in the right direction. We’ve got some really good players in here. I’m sure the club will be back in the same position next season and pushing for more.”\r\n\r\nBangkok arrived in Singapore with a slender advantage after a 2-1 first-leg victory in Thailand on March 5, in a game where the Stags were left to rue goalkeeping errors.', 3, 5, 80, 0, 'uploads/articles/1773428187_latest7.webp', 0, '2026-03-13 18:56:27', '2026-03-13 18:56:27', '2026-03-13 18:56:27'),
(100, 'haha', 'fasas', 'hshshs', 65, 3, 80, 0, NULL, 0, '2026-03-14 07:01:07', '2026-03-14 07:01:07', '2026-03-14 07:01:07');

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
(1, 52, 86, '2026-03-11 17:46:10'),
(2, 16, 76, '2026-03-13 20:46:10'),
(3, 41, 61, '2026-03-11 02:46:10'),
(4, 47, 62, '2026-03-11 12:46:10'),
(5, 61, 86, '2026-03-11 05:46:10'),
(6, 24, 78, '2026-03-11 02:46:10'),
(7, 62, 89, '2026-03-10 22:46:10'),
(8, 36, 88, '2026-03-13 13:46:10'),
(9, 5, 87, '2026-03-12 08:46:10'),
(10, 43, 69, '2026-03-13 04:46:10'),
(11, 62, 87, '2026-03-12 15:46:10'),
(12, 55, 96, '2026-03-12 09:46:10'),
(13, 20, 93, '2026-03-12 11:46:10'),
(14, 4, 69, '2026-03-12 12:46:10'),
(15, 58, 63, '2026-03-12 13:46:10'),
(16, 27, 61, '2026-03-11 03:46:10'),
(17, 49, 87, '2026-03-11 02:46:10'),
(18, 55, 61, '2026-03-13 18:46:10'),
(19, 33, 83, '2026-03-13 11:46:10'),
(20, 27, 77, '2026-03-13 15:46:10'),
(21, 47, 92, '2026-03-12 04:46:10'),
(22, 39, 70, '2026-03-11 14:46:10'),
(23, 42, 73, '2026-03-13 08:46:10'),
(24, 44, 86, '2026-03-12 00:46:10'),
(25, 16, 75, '2026-03-13 17:46:10'),
(26, 35, 76, '2026-03-11 12:46:10'),
(27, 20, 90, '2026-03-13 16:46:10'),
(28, 54, 83, '2026-03-11 11:46:10'),
(29, 27, 75, '2026-03-13 07:46:10'),
(30, 22, 74, '2026-03-13 03:46:10'),
(31, 62, 81, '2026-03-13 09:46:10'),
(32, 2, 98, '2026-03-13 16:46:10'),
(33, 63, 93, '2026-03-13 14:46:10'),
(34, 43, 65, '2026-03-12 22:46:10'),
(35, 31, 88, '2026-03-11 06:46:10'),
(36, 25, 88, '2026-03-10 22:46:10'),
(37, 30, 77, '2026-03-11 06:46:10'),
(38, 54, 80, '2026-03-10 21:46:10'),
(39, 28, 83, '2026-03-11 14:46:10'),
(40, 47, 85, '2026-03-10 23:46:10'),
(41, 49, 78, '2026-03-11 01:46:10'),
(42, 21, 65, '2026-03-12 09:46:10'),
(43, 23, 64, '2026-03-11 23:46:10'),
(44, 28, 69, '2026-03-12 13:46:10'),
(45, 38, 92, '2026-03-12 23:46:10'),
(46, 48, 87, '2026-03-11 21:46:10'),
(47, 32, 74, '2026-03-11 12:46:10'),
(48, 29, 72, '2026-03-11 04:46:10'),
(49, 6, 88, '2026-03-13 02:46:10'),
(50, 39, 66, '2026-03-11 03:46:10'),
(51, 22, 92, '2026-03-11 08:46:10'),
(52, 52, 84, '2026-03-11 07:46:10'),
(53, 5, 60, '2026-03-12 18:46:10'),
(54, 8, 69, '2026-03-11 21:46:10'),
(55, 26, 93, '2026-03-12 23:46:10'),
(56, 8, 66, '2026-03-13 18:46:10'),
(57, 35, 80, '2026-03-12 18:46:10'),
(58, 56, 64, '2026-03-13 12:46:10'),
(59, 35, 82, '2026-03-11 12:46:10'),
(60, 53, 95, '2026-03-13 19:46:10'),
(61, 37, 86, '2026-03-11 22:46:10'),
(62, 47, 60, '2026-03-11 18:46:10'),
(63, 16, 67, '2026-03-13 02:46:10'),
(64, 2, 61, '2026-03-13 14:46:10'),
(65, 57, 76, '2026-03-12 17:46:10'),
(66, 23, 83, '2026-03-12 11:46:10'),
(67, 30, 98, '2026-03-12 18:46:10'),
(68, 60, 65, '2026-03-13 09:46:10'),
(69, 32, 76, '2026-03-13 02:46:10'),
(70, 43, 96, '2026-03-13 19:46:10'),
(71, 1, 88, '2026-03-13 13:46:10'),
(72, 33, 97, '2026-03-13 20:46:10'),
(73, 45, 69, '2026-03-13 16:46:10'),
(74, 51, 71, '2026-03-11 18:46:10'),
(75, 30, 74, '2026-03-13 14:46:10'),
(76, 60, 69, '2026-03-13 19:46:10'),
(77, 54, 63, '2026-03-11 21:46:10'),
(78, 51, 81, '2026-03-12 13:46:10'),
(79, 24, 83, '2026-03-11 22:46:10'),
(80, 62, 63, '2026-03-11 03:46:10'),
(81, 21, 84, '2026-03-12 20:46:10'),
(82, 33, 93, '2026-03-12 19:46:10'),
(83, 28, 64, '2026-03-13 15:46:10'),
(84, 60, 82, '2026-03-13 20:46:10'),
(85, 59, 62, '2026-03-12 07:46:10'),
(86, 3, 69, '2026-03-13 12:46:10'),
(87, 62, 86, '2026-03-11 00:46:10'),
(88, 50, 94, '2026-03-12 22:46:10'),
(89, 53, 94, '2026-03-13 11:46:10'),
(90, 63, 60, '2026-03-12 04:46:10'),
(91, 41, 76, '2026-03-12 11:46:10'),
(92, 50, 74, '2026-03-12 16:46:10'),
(93, 31, 74, '2026-03-12 10:46:10'),
(95, 1, 69, '2026-03-12 01:46:10'),
(96, 46, 73, '2026-03-12 21:46:10'),
(97, 47, 72, '2026-03-11 09:46:10'),
(98, 47, 68, '2026-03-12 17:46:10'),
(99, 35, 85, '2026-03-11 20:46:10'),
(100, 29, 95, '2026-03-13 19:46:10'),
(101, 63, 69, '2026-03-12 00:46:10'),
(102, 43, 71, '2026-03-12 22:46:10'),
(103, 54, 64, '2026-03-11 08:46:10'),
(104, 31, 85, '2026-03-11 12:46:10'),
(105, 60, 78, '2026-03-12 22:46:10'),
(106, 5, 90, '2026-03-13 17:46:10'),
(107, 26, 86, '2026-03-11 01:46:10'),
(108, 33, 87, '2026-03-11 05:46:10'),
(109, 41, 62, '2026-03-13 18:46:10'),
(110, 4, 67, '2026-03-12 21:46:10'),
(111, 1, 85, '2026-03-13 12:46:10'),
(112, 31, 81, '2026-03-11 15:46:10'),
(113, 53, 96, '2026-03-13 18:46:10'),
(114, 45, 61, '2026-03-10 22:46:10'),
(115, 1, 74, '2026-03-11 07:46:10'),
(116, 34, 70, '2026-03-11 18:46:10'),
(117, 2, 70, '2026-03-13 08:46:10'),
(118, 49, 70, '2026-03-13 04:46:10'),
(119, 2, 79, '2026-03-12 22:46:10'),
(121, 45, 80, '2026-03-12 20:46:10'),
(122, 24, 97, '2026-03-11 10:46:10'),
(123, 53, 98, '2026-03-10 23:46:10'),
(124, 54, 75, '2026-03-11 01:46:10'),
(125, 41, 87, '2026-03-10 23:46:10'),
(126, 43, 88, '2026-03-12 23:46:10'),
(128, 60, 89, '2026-03-11 00:46:10'),
(129, 4, 96, '2026-03-11 10:46:10'),
(130, 42, 61, '2026-03-13 20:46:10'),
(131, 59, 86, '2026-03-12 17:46:10'),
(132, 57, 87, '2026-03-11 20:46:10'),
(133, 6, 61, '2026-03-12 05:46:10'),
(134, 51, 80, '2026-03-10 22:46:10'),
(135, 36, 82, '2026-03-11 01:46:10'),
(136, 8, 76, '2026-03-12 19:46:10'),
(137, 30, 96, '2026-03-12 23:46:10'),
(138, 57, 73, '2026-03-12 12:46:10'),
(139, 23, 95, '2026-03-11 00:46:10'),
(140, 62, 91, '2026-03-11 21:46:10'),
(141, 22, 62, '2026-03-12 06:46:10'),
(142, 43, 82, '2026-03-12 18:46:10'),
(143, 25, 75, '2026-03-11 20:46:10'),
(144, 43, 73, '2026-03-10 22:46:10'),
(145, 56, 93, '2026-03-12 02:46:10'),
(146, 5, 88, '2026-03-11 08:46:10'),
(147, 23, 89, '2026-03-11 09:46:10'),
(148, 62, 69, '2026-03-12 05:46:10'),
(149, 45, 87, '2026-03-12 07:46:10'),
(150, 28, 97, '2026-03-10 22:46:10'),
(151, 50, 68, '2026-03-12 18:46:10'),
(152, 22, 64, '2026-03-12 23:46:10'),
(153, 34, 95, '2026-03-11 08:46:10'),
(154, 58, 82, '2026-03-11 03:46:10'),
(155, 48, 96, '2026-03-13 15:46:10'),
(156, 1, 77, '2026-03-10 23:46:10'),
(157, 49, 92, '2026-03-11 15:46:10'),
(158, 42, 66, '2026-03-12 01:46:10'),
(159, 53, 70, '2026-03-11 08:46:10'),
(160, 8, 65, '2026-03-13 02:46:10'),
(161, 59, 82, '2026-03-13 16:46:10'),
(162, 6, 97, '2026-03-12 22:46:10'),
(163, 61, 61, '2026-03-13 08:46:10'),
(164, 16, 82, '2026-03-11 19:46:10'),
(165, 6, 69, '2026-03-12 18:46:10'),
(166, 53, 85, '2026-03-12 05:46:10'),
(167, 20, 84, '2026-03-11 18:46:10'),
(168, 1, 72, '2026-03-13 15:46:10'),
(169, 27, 89, '2026-03-12 19:46:10'),
(170, 55, 82, '2026-03-13 20:46:10'),
(171, 43, 89, '2026-03-11 21:46:10'),
(172, 59, 81, '2026-03-13 06:46:10'),
(173, 63, 81, '2026-03-13 13:46:10'),
(174, 34, 92, '2026-03-12 20:46:10'),
(175, 16, 61, '2026-03-10 22:46:10'),
(176, 53, 87, '2026-03-13 16:46:10'),
(177, 9, 87, '2026-03-11 00:46:10'),
(178, 26, 82, '2026-03-12 04:46:10'),
(179, 54, 94, '2026-03-13 09:46:10'),
(180, 60, 95, '2026-03-11 22:46:10'),
(181, 55, 70, '2026-03-13 13:46:10'),
(182, 52, 76, '2026-03-11 20:46:10'),
(183, 51, 89, '2026-03-12 03:46:10'),
(184, 21, 87, '2026-03-13 18:46:10'),
(185, 33, 76, '2026-03-12 07:46:10'),
(186, 2, 76, '2026-03-12 16:46:10'),
(187, 29, 80, '2026-03-13 06:46:10'),
(188, 62, 95, '2026-03-11 08:46:10'),
(189, 60, 99, '2026-03-13 11:46:10'),
(190, 49, 82, '2026-03-13 17:46:10'),
(191, 26, 63, '2026-03-11 12:46:10'),
(192, 27, 94, '2026-03-10 22:46:10'),
(193, 32, 86, '2026-03-11 23:46:10'),
(194, 26, 60, '2026-03-11 14:46:10'),
(195, 48, 77, '2026-03-11 17:46:10'),
(196, 44, 85, '2026-03-12 04:46:10'),
(197, 20, 68, '2026-03-13 04:46:10'),
(198, 50, 90, '2026-03-13 15:46:10'),
(199, 55, 92, '2026-03-11 12:46:10'),
(200, 31, 90, '2026-03-12 01:46:10'),
(201, 30, 66, '2026-03-11 12:46:10'),
(202, 5, 69, '2026-03-13 15:46:10'),
(203, 8, 78, '2026-03-12 00:46:10'),
(204, 49, 61, '2026-03-11 14:46:10'),
(205, 57, 72, '2026-03-13 14:46:10'),
(206, 50, 81, '2026-03-11 18:46:10'),
(207, 41, 71, '2026-03-11 17:46:10'),
(208, 48, 91, '2026-03-11 08:46:10'),
(209, 31, 79, '2026-03-11 10:46:10'),
(210, 53, 86, '2026-03-13 01:46:10'),
(211, 32, 94, '2026-03-13 17:46:10'),
(212, 15, 84, '2026-03-11 09:46:10'),
(213, 30, 85, '2026-03-13 00:46:10'),
(214, 52, 67, '2026-03-12 20:46:10'),
(215, 31, 96, '2026-03-13 18:46:10'),
(216, 40, 73, '2026-03-11 17:46:10'),
(217, 22, 94, '2026-03-13 11:46:10'),
(218, 63, 75, '2026-03-12 12:46:10'),
(219, 47, 73, '2026-03-12 11:46:10'),
(220, 35, 71, '2026-03-13 17:46:10'),
(221, 45, 75, '2026-03-13 07:46:10'),
(222, 40, 79, '2026-03-11 16:46:10'),
(223, 63, 88, '2026-03-12 21:46:10'),
(224, 26, 79, '2026-03-13 03:46:10'),
(225, 31, 91, '2026-03-13 01:46:10'),
(226, 57, 66, '2026-03-13 12:46:10'),
(228, 5, 65, '2026-03-12 11:46:10'),
(229, 15, 85, '2026-03-11 05:46:10'),
(230, 8, 64, '2026-03-11 17:46:10'),
(231, 15, 62, '2026-03-12 21:46:10'),
(232, 39, 67, '2026-03-13 16:46:10'),
(233, 27, 68, '2026-03-11 07:46:10'),
(234, 5, 63, '2026-03-11 21:46:10'),
(235, 56, 65, '2026-03-11 20:46:10'),
(236, 52, 74, '2026-03-12 16:46:10'),
(237, 32, 61, '2026-03-13 05:46:10'),
(238, 9, 88, '2026-03-12 10:46:10'),
(239, 26, 73, '2026-03-12 14:46:10'),
(240, 15, 70, '2026-03-11 10:46:10'),
(241, 42, 76, '2026-03-12 22:46:10'),
(242, 35, 66, '2026-03-11 21:46:10'),
(243, 55, 98, '2026-03-10 21:46:10'),
(244, 61, 90, '2026-03-11 14:46:10'),
(245, 4, 94, '2026-03-11 19:46:10'),
(246, 41, 89, '2026-03-12 07:46:10'),
(247, 63, 70, '2026-03-11 06:46:10'),
(248, 29, 63, '2026-03-13 12:46:10'),
(249, 35, 78, '2026-03-12 07:46:10'),
(250, 61, 74, '2026-03-11 14:46:10'),
(251, 42, 93, '2026-03-12 02:46:10'),
(252, 39, 85, '2026-03-12 15:46:10'),
(253, 15, 78, '2026-03-12 01:46:10'),
(254, 44, 60, '2026-03-13 10:46:10'),
(255, 59, 91, '2026-03-12 06:46:10'),
(256, 29, 65, '2026-03-11 11:46:10'),
(257, 57, 81, '2026-03-13 04:46:10'),
(258, 24, 60, '2026-03-13 08:46:10'),
(259, 44, 82, '2026-03-12 13:46:10'),
(260, 6, 66, '2026-03-11 23:46:10'),
(261, 59, 72, '2026-03-12 20:46:10'),
(262, 56, 81, '2026-03-12 14:46:10'),
(263, 8, 88, '2026-03-11 22:46:10'),
(264, 61, 98, '2026-03-13 07:46:10'),
(265, 55, 60, '2026-03-12 08:46:10'),
(266, 45, 82, '2026-03-13 11:46:10'),
(267, 1, 82, '2026-03-10 22:46:10'),
(268, 1, 66, '2026-03-11 07:46:10'),
(269, 59, 94, '2026-03-13 10:46:10'),
(270, 15, 73, '2026-03-12 17:46:10'),
(271, 59, 99, '2026-03-11 16:46:10'),
(272, 24, 82, '2026-03-11 20:46:10'),
(273, 58, 98, '2026-03-12 03:46:10'),
(274, 27, 64, '2026-03-11 00:46:10'),
(275, 43, 68, '2026-03-11 02:46:10'),
(276, 33, 64, '2026-03-12 11:46:10'),
(277, 5, 68, '2026-03-12 17:46:10'),
(278, 56, 72, '2026-03-13 19:46:10'),
(279, 28, 68, '2026-03-12 22:46:10'),
(280, 49, 99, '2026-03-13 12:46:10'),
(281, 24, 98, '2026-03-12 10:46:10'),
(282, 21, 70, '2026-03-13 12:46:10'),
(283, 23, 63, '2026-03-11 17:46:10'),
(284, 47, 69, '2026-03-13 00:46:10'),
(285, 25, 94, '2026-03-13 01:46:10'),
(286, 8, 91, '2026-03-12 14:46:10'),
(287, 26, 70, '2026-03-13 09:46:10'),
(288, 59, 88, '2026-03-12 18:46:10'),
(289, 53, 64, '2026-03-12 18:46:10'),
(290, 50, 62, '2026-03-12 07:46:10'),
(291, 56, 79, '2026-03-12 07:46:10'),
(292, 51, 63, '2026-03-13 11:46:10'),
(293, 47, 80, '2026-03-13 20:46:10'),
(294, 44, 87, '2026-03-12 10:46:10'),
(295, 22, 80, '2026-03-11 06:46:10'),
(296, 5, 95, '2026-03-13 15:46:10'),
(297, 5, 89, '2026-03-12 09:46:10'),
(298, 48, 88, '2026-03-12 10:46:10'),
(299, 36, 74, '2026-03-11 17:46:10'),
(300, 44, 89, '2026-03-12 03:46:10'),
(301, 45, 76, '2026-03-13 13:46:10'),
(302, 58, 91, '2026-03-11 10:46:10'),
(303, 1, 73, '2026-03-13 14:46:10'),
(304, 62, 90, '2026-03-11 19:46:10'),
(305, 27, 96, '2026-03-11 00:46:10'),
(306, 3, 76, '2026-03-13 12:46:10'),
(307, 35, 68, '2026-03-11 16:46:10'),
(308, 1, 93, '2026-03-12 03:46:10'),
(309, 61, 93, '2026-03-13 06:46:10'),
(310, 6, 89, '2026-03-13 00:46:10'),
(311, 38, 95, '2026-03-11 02:46:10'),
(312, 6, 72, '2026-03-11 08:46:10'),
(313, 47, 87, '2026-03-12 10:46:10'),
(314, 50, 89, '2026-03-12 13:46:10'),
(315, 61, 83, '2026-03-12 02:46:10'),
(316, 51, 76, '2026-03-12 13:46:10'),
(317, 8, 81, '2026-03-13 12:46:10'),
(318, 16, 69, '2026-03-13 20:46:10'),
(319, 5, 77, '2026-03-13 18:46:10'),
(320, 39, 64, '2026-03-12 07:46:10'),
(321, 22, 73, '2026-03-10 22:46:10'),
(322, 59, 60, '2026-03-11 03:46:10'),
(323, 58, 76, '2026-03-12 11:46:10'),
(324, 62, 64, '2026-03-13 14:46:10'),
(325, 21, 99, '2026-03-10 23:46:10'),
(326, 36, 70, '2026-03-13 15:46:10'),
(327, 34, 68, '2026-03-11 11:46:10'),
(328, 49, 80, '2026-03-13 04:46:10'),
(329, 54, 67, '2026-03-13 19:46:10'),
(330, 63, 64, '2026-03-13 01:46:10'),
(331, 52, 93, '2026-03-11 17:46:10'),
(332, 16, 71, '2026-03-11 13:46:10'),
(333, 9, 92, '2026-03-11 21:46:10'),
(334, 49, 65, '2026-03-12 01:46:10'),
(335, 36, 67, '2026-03-11 05:46:10'),
(336, 20, 63, '2026-03-11 05:46:10'),
(337, 4, 93, '2026-03-11 21:46:10'),
(338, 38, 96, '2026-03-11 03:46:10'),
(339, 48, 68, '2026-03-10 21:46:10'),
(340, 25, 84, '2026-03-12 15:46:10'),
(341, 52, 75, '2026-03-11 17:46:10'),
(342, 54, 84, '2026-03-13 14:46:10'),
(343, 26, 77, '2026-03-11 14:46:10'),
(344, 51, 86, '2026-03-11 02:46:10'),
(345, 26, 67, '2026-03-13 14:46:10'),
(346, 8, 89, '2026-03-12 22:46:10'),
(347, 44, 63, '2026-03-13 05:46:10'),
(348, 52, 92, '2026-03-12 01:46:10'),
(349, 37, 85, '2026-03-11 09:46:10'),
(350, 16, 99, '2026-03-12 03:46:10'),
(351, 53, 88, '2026-03-13 13:46:10'),
(352, 46, 63, '2026-03-12 16:46:10'),
(353, 16, 73, '2026-03-11 05:46:10'),
(354, 52, 68, '2026-03-11 17:46:10'),
(355, 42, 74, '2026-03-12 23:46:10'),
(356, 47, 99, '2026-03-11 11:46:10'),
(357, 51, 94, '2026-03-12 14:46:10'),
(358, 25, 90, '2026-03-12 02:46:10'),
(359, 56, 94, '2026-03-11 03:46:10'),
(360, 45, 65, '2026-03-11 23:46:10'),
(361, 22, 91, '2026-03-13 02:46:10'),
(362, 58, 86, '2026-03-12 04:46:10'),
(363, 58, 62, '2026-03-12 10:46:10'),
(364, 56, 87, '2026-03-11 16:46:10'),
(365, 28, 71, '2026-03-11 20:46:10'),
(366, 58, 67, '2026-03-11 01:46:10'),
(368, 28, 67, '2026-03-11 04:46:10'),
(369, 8, 80, '2026-03-12 11:46:10'),
(370, 28, 62, '2026-03-13 06:46:10'),
(371, 25, 73, '2026-03-10 23:46:10'),
(372, 38, 71, '2026-03-11 15:46:10'),
(373, 4, 60, '2026-03-13 20:46:10'),
(374, 30, 84, '2026-03-11 00:46:10'),
(375, 52, 88, '2026-03-12 14:46:10'),
(376, 60, 79, '2026-03-12 22:46:10'),
(377, 29, 86, '2026-03-11 08:46:10'),
(378, 28, 98, '2026-03-13 12:46:10'),
(379, 30, 94, '2026-03-13 20:46:10'),
(380, 15, 80, '2026-03-11 22:46:10'),
(381, 58, 66, '2026-03-13 17:46:10'),
(382, 46, 99, '2026-03-13 09:46:10'),
(383, 46, 75, '2026-03-11 05:46:10'),
(384, 63, 97, '2026-03-11 23:46:10'),
(385, 63, 67, '2026-03-12 10:46:10'),
(386, 38, 85, '2026-03-12 14:46:10'),
(387, 31, 63, '2026-03-13 10:46:10'),
(388, 51, 85, '2026-03-13 14:46:10'),
(389, 5, 70, '2026-03-13 12:46:10'),
(390, 6, 92, '2026-03-11 18:46:10'),
(391, 23, 96, '2026-03-13 12:46:10'),
(392, 32, 75, '2026-03-13 13:46:10'),
(393, 40, 82, '2026-03-10 23:46:10'),
(394, 8, 99, '2026-03-11 23:46:10'),
(395, 56, 83, '2026-03-12 11:46:10'),
(396, 51, 82, '2026-03-11 03:46:10'),
(397, 15, 63, '2026-03-11 16:46:10'),
(398, 27, 91, '2026-03-12 23:46:10'),
(399, 33, 78, '2026-03-11 14:46:10'),
(400, 62, 65, '2026-03-12 06:46:10'),
(401, 22, 97, '2026-03-12 04:46:10'),
(402, 1, 71, '2026-03-11 21:46:10'),
(403, 16, 64, '2026-03-13 17:46:10'),
(404, 8, 92, '2026-03-11 16:46:10'),
(405, 23, 62, '2026-03-11 13:46:10'),
(406, 39, 63, '2026-03-12 19:46:10'),
(407, 34, 71, '2026-03-12 07:46:10'),
(408, 44, 74, '2026-03-11 10:46:10'),
(409, 27, 62, '2026-03-11 17:46:10'),
(410, 61, 66, '2026-03-12 08:46:10'),
(411, 55, 86, '2026-03-13 01:46:10'),
(412, 37, 95, '2026-03-12 18:46:10'),
(413, 46, 96, '2026-03-11 09:46:10'),
(414, 26, 95, '2026-03-13 03:46:10'),
(415, 57, 97, '2026-03-13 01:46:10'),
(416, 21, 77, '2026-03-13 07:46:10'),
(417, 39, 92, '2026-03-13 13:46:10'),
(418, 57, 71, '2026-03-11 21:46:10'),
(419, 40, 90, '2026-03-10 23:46:10'),
(420, 56, 85, '2026-03-11 20:46:10'),
(422, 40, 98, '2026-03-12 13:46:10'),
(423, 56, 99, '2026-03-12 15:46:10'),
(424, 56, 86, '2026-03-11 14:46:10'),
(425, 26, 98, '2026-03-10 21:46:10'),
(426, 44, 67, '2026-03-13 15:46:10'),
(427, 20, 69, '2026-03-11 16:46:10'),
(428, 55, 68, '2026-03-10 22:46:10'),
(429, 52, 83, '2026-03-12 15:46:10'),
(430, 48, 98, '2026-03-11 22:46:10'),
(431, 44, 70, '2026-03-13 17:46:10'),
(432, 25, 74, '2026-03-11 00:46:10'),
(433, 3, 75, '2026-03-12 00:46:10'),
(434, 43, 75, '2026-03-12 23:46:10'),
(435, 37, 80, '2026-03-12 11:46:10'),
(436, 48, 83, '2026-03-12 00:46:10'),
(437, 38, 70, '2026-03-11 13:46:10'),
(438, 36, 73, '2026-03-11 09:46:10'),
(439, 32, 98, '2026-03-13 14:46:10'),
(440, 16, 66, '2026-03-13 04:46:10'),
(441, 50, 84, '2026-03-11 13:46:10'),
(443, 57, 64, '2026-03-12 23:46:10'),
(444, 25, 66, '2026-03-13 19:46:10'),
(445, 1, 91, '2026-03-11 12:46:10'),
(446, 6, 87, '2026-03-13 03:46:10'),
(447, 29, 81, '2026-03-11 15:46:10'),
(448, 31, 62, '2026-03-12 03:46:10'),
(449, 48, 95, '2026-03-11 13:46:10'),
(450, 45, 68, '2026-03-11 09:46:10'),
(451, 44, 68, '2026-03-10 23:46:10'),
(452, 57, 69, '2026-03-10 22:46:10'),
(453, 41, 83, '2026-03-13 05:46:10'),
(454, 49, 98, '2026-03-11 20:46:10'),
(455, 21, 81, '2026-03-12 14:46:10'),
(456, 57, 93, '2026-03-13 05:46:10'),
(457, 48, 66, '2026-03-12 16:46:10'),
(458, 60, 66, '2026-03-12 21:46:10'),
(459, 53, 60, '2026-03-13 03:46:10'),
(460, 56, 78, '2026-03-12 09:46:10'),
(461, 62, 71, '2026-03-11 07:46:10'),
(462, 41, 75, '2026-03-11 22:46:10'),
(463, 23, 78, '2026-03-11 07:46:10'),
(464, 30, 68, '2026-03-11 02:46:10'),
(465, 60, 67, '2026-03-12 04:46:10'),
(466, 45, 93, '2026-03-13 02:46:10'),
(467, 5, 66, '2026-03-12 20:46:10'),
(468, 38, 66, '2026-03-13 01:46:10'),
(470, 51, 68, '2026-03-11 05:46:10'),
(471, 29, 74, '2026-03-11 03:46:10'),
(472, 58, 68, '2026-03-10 23:46:10'),
(473, 44, 78, '2026-03-11 18:46:10'),
(474, 23, 76, '2026-03-13 07:46:10'),
(475, 45, 63, '2026-03-13 17:46:10'),
(476, 49, 63, '2026-03-12 13:46:10'),
(477, 41, 63, '2026-03-11 15:46:10'),
(478, 21, 90, '2026-03-13 14:46:10'),
(479, 38, 72, '2026-03-12 19:46:10'),
(480, 21, 69, '2026-03-11 17:46:10'),
(481, 39, 62, '2026-03-13 19:46:10'),
(482, 42, 75, '2026-03-13 09:46:10'),
(483, 44, 92, '2026-03-12 02:46:10'),
(484, 45, 98, '2026-03-12 08:46:10'),
(485, 38, 93, '2026-03-11 01:46:10'),
(486, 54, 62, '2026-03-13 14:46:10'),
(487, 4, 77, '2026-03-13 04:46:10'),
(488, 39, 89, '2026-03-12 12:46:10'),
(489, 28, 92, '2026-03-13 02:46:10'),
(490, 35, 84, '2026-03-13 15:46:10'),
(491, 48, 86, '2026-03-13 12:46:10'),
(492, 38, 78, '2026-03-13 00:46:10'),
(493, 6, 79, '2026-03-12 12:46:10'),
(494, 6, 98, '2026-03-12 20:46:10'),
(495, 59, 69, '2026-03-11 18:46:10'),
(496, 42, 86, '2026-03-13 17:46:10'),
(497, 3, 89, '2026-03-13 15:46:10'),
(498, 16, 93, '2026-03-11 07:46:10'),
(499, 16, 81, '2026-03-13 16:46:10'),
(500, 48, 94, '2026-03-11 09:46:10'),
(512, 40, 80, '2026-03-10 22:51:15'),
(513, 1, 79, '2026-03-13 10:51:15'),
(514, 39, 81, '2026-03-12 13:51:15'),
(515, 9, 66, '2026-03-11 14:51:15'),
(516, 44, 80, '2026-03-13 16:51:15'),
(517, 37, 79, '2026-03-11 05:51:15'),
(518, 61, 77, '2026-03-13 11:51:15'),
(519, 42, 65, '2026-03-12 02:51:15'),
(520, 52, 87, '2026-03-11 09:51:15'),
(521, 25, 86, '2026-03-12 02:51:15'),
(522, 3, 81, '2026-03-13 16:51:15'),
(523, 42, 80, '2026-03-11 04:51:15'),
(524, 52, 80, '2026-03-11 19:51:15'),
(525, 28, 81, '2026-03-12 09:51:15'),
(526, 20, 76, '2026-03-12 00:51:15'),
(527, 42, 71, '2026-03-12 19:51:15'),
(528, 46, 66, '2026-03-11 17:51:15'),
(529, 27, 81, '2026-03-12 04:51:15'),
(530, 36, 86, '2026-03-11 10:51:15'),
(531, 33, 65, '2026-03-12 17:51:15'),
(532, 3, 74, '2026-03-13 18:51:15'),
(533, 32, 71, '2026-03-11 03:51:15'),
(534, 26, 68, '2026-03-12 06:51:15'),
(535, 45, 86, '2026-03-13 17:51:15'),
(536, 44, 71, '2026-03-11 00:51:15'),
(537, 9, 61, '2026-03-12 14:51:15'),
(538, 35, 79, '2026-03-12 00:51:15'),
(539, 33, 80, '2026-03-11 08:51:15'),
(540, 53, 76, '2026-03-11 21:51:15'),
(541, 33, 69, '2026-03-11 16:51:15'),
(542, 49, 66, '2026-03-13 13:51:15'),
(543, 61, 80, '2026-03-12 03:51:15'),
(544, 48, 69, '2026-03-11 04:51:15'),
(545, 33, 68, '2026-03-12 20:51:15'),
(546, 22, 76, '2026-03-12 19:51:15'),
(548, 51, 74, '2026-03-11 05:51:15'),
(549, 24, 81, '2026-03-12 14:51:15'),
(550, 33, 61, '2026-03-12 15:51:15'),
(551, 25, 87, '2026-03-11 15:51:15'),
(552, 30, 80, '2026-03-13 07:51:15'),
(553, 26, 61, '2026-03-11 11:51:15'),
(554, 4, 71, '2026-03-13 20:51:15'),
(555, 33, 79, '2026-03-13 07:51:15'),
(556, 47, 81, '2026-03-13 10:51:15'),
(557, 9, 79, '2026-03-12 13:51:15'),
(558, 34, 86, '2026-03-13 19:51:15'),
(559, 16, 65, '2026-03-13 07:51:15'),
(560, 3, 71, '2026-03-12 04:51:15'),
(561, 61, 71, '2026-03-11 01:51:15'),
(562, 50, 77, '2026-03-12 19:51:15'),
(563, 56, 74, '2026-03-12 08:51:15'),
(564, 37, 81, '2026-03-13 16:51:15'),
(565, 16, 74, '2026-03-13 14:51:15'),
(566, 48, 76, '2026-03-12 23:51:15'),
(567, 63, 68, '2026-03-13 19:51:15'),
(568, 54, 68, '2026-03-12 14:51:15'),
(569, 24, 66, '2026-03-12 09:51:15'),
(570, 42, 68, '2026-03-12 04:51:15'),
(571, 57, 61, '2026-03-13 17:51:15'),
(572, 3, 77, '2026-03-12 03:51:15'),
(573, 32, 68, '2026-03-13 01:51:15'),
(574, 59, 77, '2026-03-12 01:51:15'),
(575, 8, 87, '2026-03-13 01:51:15'),
(576, 29, 76, '2026-03-13 04:51:15'),
(577, 52, 79, '2026-03-11 13:51:15'),
(578, 40, 68, '2026-03-11 16:51:15'),
(579, 60, 80, '2026-03-13 19:51:15'),
(580, 5, 79, '2026-03-12 14:51:15'),
(581, 50, 65, '2026-03-11 19:51:15'),
(582, 60, 83, '2026-03-11 03:51:15'),
(583, 23, 61, '2026-03-13 04:51:15'),
(584, 39, 87, '2026-03-13 19:51:15'),
(585, 63, 87, '2026-03-11 22:51:15'),
(586, 31, 77, '2026-03-11 12:51:15'),
(587, 58, 71, '2026-03-12 17:51:15'),
(588, 27, 83, '2026-03-11 23:51:15'),
(589, 60, 81, '2026-03-11 15:51:15'),
(590, 2, 87, '2026-03-11 13:51:15'),
(591, 50, 61, '2026-03-13 18:51:15'),
(593, 61, 79, '2026-03-12 08:51:15'),
(594, 41, 69, '2026-03-11 01:51:15'),
(595, 61, 68, '2026-03-13 03:51:15'),
(596, 60, 61, '2026-03-11 22:51:15'),
(642, 28, 76, '2026-03-13 21:15:35'),
(643, 22, 61, '2026-03-14 06:14:03'),
(644, 22, 68, '2026-03-14 06:14:11'),
(645, 22, 98, '2026-03-14 06:19:04'),
(646, 65, 68, '2026-03-14 06:53:56'),
(647, 65, 87, '2026-03-14 06:53:57'),
(650, 65, 97, '2026-03-14 06:55:26');

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
(6, 'Health', 'Health and medical news', NULL, '2026-03-07 19:48:14');

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
(11, 37, 23, 'Interesting', '2026-03-13 07:49:41'),
(13, 87, 65, 'AGAG', '2026-03-14 06:57:06');

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
(15, 'keenangohganaesan@gmail.com', '$2y$10$9r8JycddPP5KqCuijTGoD.NNgUa9PXC2nYng1mnvS5fbmhOIWIQxu', 'Keenan Goh Ganaeson', NULL, NULL, 'free', 0, 0, '2026-03-10 08:36:30', '2026-03-10 08:36:44', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(16, 'marcuskhong@hotmail.sg', '$2y$10$VsIBYe81Vx1iQ9fhgiiZmeAKJzDcr.Wf5HxDzDMk.2JwOKHyhOvea', 'marcus', 'Testing123', NULL, 'premium', 0, 0, '2026-03-10 08:36:51', '2026-03-12 15:35:24', 1, NULL, NULL, NULL, NULL, '25-34', 'male', 1),
(20, 'jd432102003@gmail.com', '$2y$10$nrAjMRRu5qgTT9Og./CRVOuwMnKS3eywy97lcETXKLs/VfEVNrw5u', 'John Doe', 'No', NULL, 'free', 0, 0, '2026-03-10 08:42:41', '2026-03-13 07:25:29', 1, NULL, NULL, NULL, NULL, '18-24', 'male', 1),
(21, 'abcee1900@gmail.com', '$2y$10$BEyBhTOi4SBYIyEE7/I67.eMRAYrlrMeB7gXvXKZ4pDboLp8ZZ3vS', 'Abc', 'Hi', NULL, 'premium', 0, 0, '2026-03-10 09:25:13', '2026-03-13 07:21:15', 1, NULL, NULL, NULL, NULL, '18-24', 'male', 1),
(22, 'chualiwen00@gmail.com', '$2y$10$3VQcxlbFyzREWhhF2OwRnusEatI3tyjS/AH7/cRpKS/z8wWFfOP.6', 'liwen', ':)', NULL, 'premium', 0, 0, '2026-03-11 09:39:27', '2026-03-15 08:55:24', 1, NULL, NULL, NULL, NULL, '18-24', 'female', 1),
(23, 'smoothic2003@gmail.com', '$2y$10$/QWuQkOTArLk0b0I6OxUlew9K9dxz8PS6PjQYed3DDVJofVA8/mZW', 'Mamacita', 'Im female', NULL, 'free', 0, 0, '2026-03-13 07:48:09', '2026-03-13 07:48:50', 1, NULL, NULL, NULL, NULL, '13-17', 'female', 1),
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
(65, 'jabezhee@gmail.com', '$2y$10$ncEfJ.yzGLh3FJQvJp/F6OI5x3JXDTrqsLUw9kdiQqIt11hT.Tv7O', 'jabezhee', 'HIHI ', NULL, 'free', 0, 0, '2026-03-14 06:46:13', '2026-03-14 06:53:48', 1, NULL, NULL, NULL, NULL, '18-24', 'male', 1);

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
(16, 23, 3, '2026-03-13 07:48:50'),
(17, 23, 2, '2026-03-13 07:48:50'),
(18, 23, 5, '2026-03-13 07:48:50'),
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
(150, 65, 3, '2026-03-14 06:53:48');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `article_flags`
--
ALTER TABLE `article_flags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `article_views`
--
ALTER TABLE `article_views`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=659;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `user_interests`
--
ALTER TABLE `user_interests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
