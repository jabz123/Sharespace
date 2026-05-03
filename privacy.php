<?php
require_once __DIR__ . '/includes/layout.php';

page_head('Privacy Policy');
?>

<link rel="stylesheet" href="/public/css/policy.css" />

<div class="policy-container">
    <div class="policy-card">

        <h1 class="policy-title">Privacy Policy</h1>
        <p class="policy-updated">Last updated: <?= date('F Y') ?></p>

        <section class="policy-section">
            <h2>1. Introduction</h2>
            <p>
                This Privacy Policy explains how SharedSpace collects, uses, and protects your
                personal information when you use our platform.
            </p>
        </section>

        <section class="policy-section">
            <h2>2. Information We Collect</h2>
            <ul>
                <li>Account information (name, email)</li>
                <li>User-generated content (articles, comments)</li>
                <li>Usage data (interactions, views)</li>
            </ul>
        </section>

        <section class="policy-section">
            <h2>3. How We Use Your Information</h2>
            <p>
                We use your information to provide and improve our services, personalize your
                experience, and maintain platform security.
            </p>
        </section>

        <section class="policy-section">
            <h2>4. Data Protection</h2>
            <p>
                We implement appropriate security measures to protect your data, including secure
                authentication and encrypted connections where applicable.
            </p>
        </section>

        <section class="policy-section">
            <h2>5. Sharing of Information</h2>
            <p>
                We do not sell your personal data. Information may be shared only when required by
                law or to protect the integrity of the platform.
            </p>
        </section>

        <section class="policy-section">
            <h2>6. Cookies</h2>
            <p>
                We may use cookies and similar technologies to enhance your browsing experience
                and analyze usage patterns.
            </p>
        </section>

        <section class="policy-section">
            <h2>7. User Rights</h2>
            <p>
                You may request access, correction, or deletion of your personal data by contacting us.
            </p>
        </section>

        <section class="policy-section">
            <h2>8. Changes to This Policy</h2>
            <p>
                We may update this Privacy Policy from time to time. Continued use of the platform
                indicates acceptance of any updates.
            </p>
        </section>

        <section class="policy-section">
            <h2>9. Contact</h2>
            <p>
                For any privacy-related concerns, contact us at:
                <br><strong>sharedspaceplatform@gmail.com</strong>
            </p>
        </section>

        <div class="policy-footer">
            <a href="/" class="btn-back">← Back to Home</a>
        </div>

    </div>
</div>

<?php page_foot(); ?>