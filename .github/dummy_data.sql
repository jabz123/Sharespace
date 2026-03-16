
-- to import, just go into phpmyadmin and import this file.

DROP DATABASE IF EXISTS sharedspace;
CREATE DATABASE sharedspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sharedspace;

-- table creation

-- need to have shit like age group(dropdown), interests(select from dropdown), gender
-- will do in future
CREATE TABLE users (
    id           INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email        VARCHAR(255) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    full_name    VARCHAR(255),
    bio          TEXT,
    avatar_url   TEXT,
    role         ENUM('free','premium','category_admin','system_admin','ai_trainer') NOT NULL DEFAULT 'free',
    is_premium   TINYINT(1)   NOT NULL DEFAULT 0,
    is_suspended TINYINT(1)   NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id            INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL UNIQUE,
    description   TEXT,
    admin_user_id INT,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- make field for images
-- do in future
CREATE TABLE articles (
    id              INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(500) NOT NULL,
    excerpt         TEXT         NOT NULL,
    content         LONGTEXT     NOT NULL,
    author_id       INT          NOT NULL,
    category_id     INT          NOT NULL,
    trust_score     TINYINT      NOT NULL DEFAULT 80,
    has_media       TINYINT(1)   NOT NULL DEFAULT 0,
    is_premium_only TINYINT(1)   NOT NULL DEFAULT 0,
    published_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_author    (author_id),
    INDEX idx_category  (category_id),
    INDEX idx_published (published_at),
    FOREIGN KEY (author_id)   REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
    id         INT      NOT NULL AUTO_INCREMENT PRIMARY KEY,
    article_id INT      NOT NULL,
    user_id    INT      NOT NULL,
    content    TEXT     NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_article (article_id),
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE article_flags (
    id         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    article_id INT          NOT NULL,
    user_id    INT          NOT NULL,
    reason     VARCHAR(255) NOT NULL DEFAULT 'inappropriate',
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_flag (article_id, user_id),
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE saved_articles (
    id         INT      NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    INT      NOT NULL,
    article_id INT      NOT NULL,
    saved_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_saved (user_id, article_id),
    INDEX idx_user    (user_id),
    INDEX idx_article (article_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE site_feedback (
    id          INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id     INT,
    name        VARCHAR(255) NOT NULL,
    role        VARCHAR(255) NOT NULL DEFAULT '',
    rating      TINYINT      NOT NULL DEFAULT 5,
    content     TEXT         NOT NULL,
    is_approved TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- need to have table for landing page shit
-- tentative
-- CREATE TABLE landing_page_content (
--     id         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
--     section    VARCHAR(255) NOT NULL,
--     content    TEXT         NOT NULL,
--     created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




-- insert dummy data into categories

INSERT INTO categories (name, description) VALUES
    ('Technology', 'Tech news and innovations'),
    ('Science',    'Scientific discoveries'),
    ('Politics',   'Political news and analysis'),
    ('Economy',    'Business and economic news'),
    ('Sports',     'Sports news and results'),
    ('Health',     'Health and medical news');


-- insert into users

-- dummy data users. password is Demo1234 for users 1-3
-- password is Reader55 for user 4.

INSERT INTO users (email, password, full_name, bio, role, is_premium, created_at) VALUES
('alex.morgan@example.com',   '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'Alex Morgan',   'Technology and business journalist covering emerging markets.',  'free', 0, '2024-03-01 08:00:00'),
('priya.sharma@example.com',  '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'Priya Sharma',  'Freelance writer covering health, wellness, and nutrition.',     'free', 0, '2024-03-03 09:00:00'),
('lucas.ford@example.com',    '$2y$12$gxbVunjvFtZxnQCMc58fHe2s6o7rQAi1H.fdvuRoYTGDBtorkV9Yu', 'Lucas Ford',    'Independent journalist covering science and the environment.',  'free', 0, '2024-03-05 10:00:00'),
('reader@example.com',        '$2y$12$gxbVunjvFtZxnQCMc58fHej3sEpCV4rMb5TbKhxjjXAOn99Pjvg8e', 'Jamie Lee',     'Avid reader interested in science and technology news.',         'free', 0, '2024-04-01 08:00:00');



-- insert into articles
-- rn got no premium so is all 0
--

INSERT INTO articles (title, excerpt, content, author_id, category_id, trust_score, has_media, is_premium_only, published_at) VALUES

('OpenAI Announces GPT-5 With Multimodal Reasoning Capabilities',
 'The latest flagship model from OpenAI promises a significant leap in reasoning, code generation, and real-time visual understanding.',
 'OpenAI has officially unveiled GPT-5, its most capable large language model to date. The new model introduces what the company calls "chain-of-thought fusion" — a technique that allows the model to reason across text, images, and structured data simultaneously.\n\nIn benchmark tests, GPT-5 outperformed its predecessor on the MMLU reasoning suite by 14 percentage points and achieved near-human performance on the MedQA dataset used for medical licensing examinations.\n\nChief Technology Officer Mira Murati described the release as "a step change rather than an incremental improvement." The model is immediately available to ChatGPT Plus subscribers and through the OpenAI API.\n\nSafety researchers say GPT-5 underwent six months of red-teaming and alignment training before release. An independent audit found no evidence of deceptive behaviour during testing.',
 1, 1, 80, 0, 0, '2025-01-08 09:00:00'),

('EU''s AI Act Enforcement Begins: What Tech Companies Must Do Now',
 'With the first wave of AI Act obligations now in force, companies operating in Europe face strict transparency and risk classification requirements.',
 'The European Union''s landmark Artificial Intelligence Act has entered its first enforcement phase, requiring companies deploying AI systems in Europe to classify their tools by risk level and implement corresponding compliance measures.\n\nHigh-risk AI systems — including those used in hiring, credit scoring, and biometric identification — must now undergo mandatory conformity assessments and register in a new EU-wide AI database.\n\nThe European AI Office confirmed that penalties for non-compliance can reach €35 million or 7% of global annual turnover, whichever is higher.\n\nSeveral major US technology firms including Microsoft, Google, and Meta have established dedicated EU AI compliance teams in Brussels.',
 1, 1, 80, 0, 0, '2025-01-20 08:00:00'),

('Are Foldable Phones Finally Ready for the Mainstream?',
 'With improved durability and falling prices, foldable smartphones are attracting a wider audience — but concerns about longevity remain.',
 'Foldable smartphones have been available since Samsung introduced the Galaxy Fold in 2019, but the category has long struggled to break into mainstream adoption due to high prices and fragile displays.\n\nSamsung''s latest Galaxy Z Fold 6 starts at $1,099 — down from $1,799 at launch four years ago — and independent durability testing rated the hinge mechanism for over 200,000 folds before showing degradation.\n\nIDC data shows foldable smartphone shipments grew 52% year-over-year in 2024, reaching 28 million units globally.\n\nCritics note that crease visibility, display brightness in direct sunlight, and software optimisation remain areas where foldables lag behind conventional flagship devices.',
 1, 1, 80, 0, 0, '2025-03-01 10:00:00'),

('Breakthrough Cancer Immunotherapy Shows 94% Remission Rate in Trial',
 'A personalised mRNA vaccine combined with checkpoint inhibitors has produced remarkable results in a Phase 2 melanoma trial.',
 'Researchers at Memorial Sloan Kettering Cancer Center have published results from a Phase 2 clinical trial showing a 94% remission rate in patients with advanced melanoma treated with a combination of a personalised mRNA cancer vaccine and the checkpoint inhibitor pembrolizumab.\n\nThe trial enrolled 157 participants with stage III or IV melanoma who had not previously responded to standard treatment. After 18 months of follow-up, 94% showed no evidence of disease.\n\nThe personalised vaccine is manufactured individually for each patient based on genomic sequencing of their tumour. The FDA has granted Breakthrough Therapy designation.\n\nHealth economists estimate the process currently costs approximately $100,000 per patient, though mass production techniques could reduce this substantially.',
 3, 2, 80, 0, 0, '2025-01-09 08:30:00'),

('Antarctic Ice Sheet Loss Accelerating Faster Than Models Predicted',
 'New satellite data reveals ice mass loss from the West Antarctic Ice Sheet has increased by 75% over the past decade.',
 'A study published in Nature Geoscience has found that the West Antarctic Ice Sheet is losing mass at a rate 75% higher than measurements taken a decade ago, significantly exceeding predictions of the most pessimistic climate models.\n\nUsing data from the ESA''s CryoSat-2 satellite and NASA''s GRACE-FO mission, researchers calculated annual ice mass loss of 212 gigatons in 2024 — up from 121 gigatons in 2015.\n\nThe acceleration is primarily attributed to warm circumpolar deep water intruding beneath the Thwaites and Pine Island glaciers.\n\nIf current trends continue, the researchers project sea level contributions from West Antarctica alone could reach 0.5 metres by 2100.',
 3, 2, 80, 0, 0, '2025-02-14 08:00:00'),

('New Study Links Ultra-Processed Food to 32 Adverse Health Outcomes',
 'A meta-analysis of 45 studies covering over 10 million participants finds strong associations between UPF intake and cardiovascular disease and depression.',
 'A landmark meta-analysis published in the British Medical Journal has found consistent associations between high consumption of ultra-processed foods and 32 negative health outcomes, including a 50% increased risk of cardiovascular disease-related death and a 48% higher risk of depression.\n\nThe analysis synthesised data from 45 pooled studies covering more than 10 million participants across North America, Europe, and Australia.\n\nThe study''s authors acknowledge important limitations: most underlying research is observational, making it impossible to establish causation from association alone.\n\nDespite these caveats, the researchers argue the breadth of associations provides a strong basis for public health recommendations.',
 2, 2, 80, 0, 0, '2025-02-05 10:00:00'),

('Federal Reserve Holds Rates Steady as Inflation Proves Stubborn',
 'The FOMC voted unanimously to maintain the federal funds rate at 4.25-4.5%, citing persistent services inflation and a resilient labour market.',
 'The Federal Open Market Committee voted unanimously to hold the federal funds rate in the target range of 4.25% to 4.5%, pausing what had been an extended easing cycle.\n\nIn the statement accompanying the decision, the Committee noted that inflation remains somewhat elevated, with core services inflation running at approximately 3.6%.\n\nChair Jerome Powell indicated that the Committee would need to see "several more months of good data" before resuming rate cuts.\n\nEconomists at JPMorgan and Goldman Sachs both revised their Fed rate forecasts, now projecting only one 25-basis-point cut in 2025.',
 1, 4, 80, 0, 0, '2025-01-30 08:30:00'),

('The Housing Crisis Is Getting Worse in Every Major City',
 'A structural shortage of homes, restrictive planning laws, and rising construction costs are keeping housing unaffordable across the developed world.',
 'Housing affordability has deteriorated to historically extreme levels across major cities, with the median house price now representing more than 10 times the median household income in cities including London, Sydney, Vancouver, and San Francisco.\n\nRestrictive zoning laws in most major cities prohibit high-density housing in large swaths of land close to employment centres, limiting supply precisely where demand is greatest.\n\nConstruction cost inflation has made new development increasingly marginal for housebuilders, rising approximately 40% in real terms since 2020.\n\nNew Zealand''s experience following zoning reform in 2021 is frequently cited as evidence that supply-side reform can work, with building consents increasing 40% and rent growth decelerating.',
 1, 4, 80, 0, 0, '2025-02-25 09:30:00'),

('Six Nations 2025: Ireland''s Dominance Continues With Grand Slam',
 'Ireland secured a third Grand Slam in eight years with a comprehensive 31-18 victory over England at Twickenham.',
 'Ireland completed a third Six Nations Grand Slam since 2018 with a dominant 31-18 victory over England at Twickenham, confirming their status as the world''s top-ranked rugby union team under head coach Andy Farrell.\n\nIreland''s victory was built on a clinical first half in which they scored three tries through Hugo Keenan, James Lowe, and Caelan Doris, taking a 24-6 lead into the break.\n\nFly-half Jack Crowley delivered a composed performance, landing five conversions and a penalty.\n\nFrance, despite finishing third, ended the tournament with the most tries scored and will be regarded as Ireland''s most credible challenger heading into the 2027 World Cup cycle.',
 2, 5, 80, 0, 0, '2025-03-16 18:00:00'),

('The Science of Sports Nutrition: What the Evidence Actually Says',
 'From protein timing to creatine and cold plunges, we separate well-supported sports science from marketing hype.',
 'Sports nutrition is a field where rigorous science, commercial interests, and social media trends collide in ways that make it difficult for athletes to know what works.\n\nProtein consumption is the area with the strongest evidence base. A 2022 meta-analysis found that protein supplementation significantly increases muscle mass and strength gains from resistance training, with a plateau effect around 1.6g per kilogram of body weight daily.\n\nCreatine monohydrate has one of the strongest evidence profiles of any legal performance supplement, with hundreds of studies supporting its effectiveness for short-duration, high-intensity exercise.\n\nCold water immersion is effective at reducing delayed onset muscle soreness but may blunt long-term hypertrophy adaptations by suppressing the inflammatory signalling that drives muscle growth.',
 2, 5, 80, 0, 0, '2025-04-03 09:30:00'),

('NHS Approves Ozempic for Cardiovascular Risk Reduction',
 'NICE has approved semaglutide for patients with established cardiovascular disease and BMI over 27, regardless of whether they have type 2 diabetes.',
 'NICE has approved semaglutide for use in NHS patients with established cardiovascular disease and a BMI of 27 or above, regardless of whether they have a diabetes diagnosis.\n\nThe decision follows publication of the SELECT trial, which found that semaglutide reduced the risk of major adverse cardiovascular events by 20% compared to placebo over an average follow-up of 33 months.\n\nThe approval represents the first time any weight management medication has been approved on the basis of cardiovascular outcomes rather than weight loss alone.\n\nNHS England estimates approximately 340,000 patients would initially qualify, though supply constraints will require a phased rollout over at least two years.',
 2, 6, 80, 0, 0, '2025-01-25 08:30:00'),

('Sleep Science Update: Trackers, Chronotypes, and Caffeine',
 'Wearable sleep trackers are less accurate than manufacturers claim, and caffeine timing matters more than quantity.',
 'Sleep trackers manufactured by Garmin, Apple, Fitbit, and Oura have made consumer-grade sleep staging a mainstream feature. However, a 2024 validation study found that commercial wearables misclassify sleep stages in 30-40% of epochs.\n\nChronotype research has moved beyond the simple morning/evening dichotomy. Genome-wide association studies have identified over 350 genetic variants associated with chronotype, suggesting it is a complex continuous trait.\n\nCaffeine''s half-life of 5-6 hours means a 200mg dose at 2pm leaves 50mg active at midnight. A 2024 randomised trial found that morning-only caffeine consumption improved sleep quality scores by an average of 14% compared to afternoon consumption of the same total dose.',
 2, 6, 80, 0, 0, '2025-03-03 10:00:00');



-- dummy comments
INSERT INTO comments (article_id, user_id, content, created_at) VALUES
(1, 4, 'Really interesting — I wonder how long before this starts affecting white-collar jobs at scale.', '2025-01-08 11:30:00'),
(1, 2, 'The safety section is reassuring but I''d like to see more detail on the red-teaming methodology.', '2025-01-08 14:00:00'),
(4, 4, 'As someone who lost a family member to melanoma this gives me real hope. When might this reach the NHS?', '2025-01-09 12:00:00'),
(4, 3, 'The $100k cost per patient is the most significant barrier. Even with Breakthrough Therapy designation the regulatory path takes years.', '2025-01-10 08:30:00'),
(9, 4, 'Ireland have been extraordinary to watch this tournament. Crowley stepping into Sexton''s boots seamlessly is remarkable.', '2025-03-17 09:00:00'),
(11, 2, 'The cardiovascular outcomes data from SELECT is genuinely impressive. Good to see approvals based on endpoints beyond weight loss.', '2025-01-26 10:00:00');


