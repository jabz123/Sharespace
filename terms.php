<?php
require_once __DIR__ . '/includes/layout.php';

page_head('Terms of Service');
?>

<link rel="stylesheet" href="/public/css/termsofservice.css" />

<div class="terms-container">
    <div class="terms-card">

        <h1 class="terms-title">Terms of Service</h1>
        <p class="terms-updated">Last updated: <?= date('F Y') ?></p>

        <section class="terms-section">
            <h2>1. Introduction</h2>
            <p>
                Welcome to SharedSpace. By accessing or using our platform, you agree to comply with
                these Terms of Service. If you do not agree, please do not use the platform.
            </p>
        </section>

        <section class="terms-section">
            <h2>2. User Accounts</h2>
            <p>
                You are responsible for maintaining the confidentiality of your account credentials.
                You agree to provide accurate and complete information when registering.
            </p>
        </section>

        <section class="terms-section">
            <h2>3. Content Responsibility</h2>
            <p>
                Users are responsible for the content they publish. SharedSpace uses AI-assisted tools
                to evaluate content credibility, but we do not guarantee absolute accuracy.
            </p>
        </section>

        <section class="terms-section">
            <h2>4. Acceptable Use</h2>
            <ul>
                <li>No illegal or harmful content</li>
                <li>No misinformation or deceptive practices</li>
                <li>No abuse of platform features</li>
            </ul>
        </section>

        <section class="terms-section">
            <h2>5. Account Suspension</h2>
            <p>
                We reserve the right to suspend or terminate accounts that violate our policies
                without prior notice.
            </p>
        </section>

        <section class="terms-section">
            <h2>6. Intellectual Property</h2>
            <p>
                All platform design, branding, and systems belong to SharedSpace. Users retain ownership
                of their submitted content.
            </p>
        </section>

        <section class="terms-section">
            <h2>7. Limitation of Liability</h2>
            <p>
                SharedSpace is not liable for any damages arising from the use of the platform.
                Use the service at your own risk.
            </p>
        </section>

        <section class="terms-section">
            <h2>8. Changes to Terms</h2>
            <p>
                We may update these Terms at any time. Continued use of the platform means you accept
                the updated terms.
            </p>
        </section>

        <section class="terms-section">
            <h2>9. Contact</h2>
            <p>
                If you have any questions about these Terms, please contact us at:
                <br><strong>sharedspaceplatform@gmail.com</strong>
            </p>
        </section>

        <div class="terms-footer">
            <a href="/" class="btn-back">← Back to Home</a>
        </div>

    </div>
</div>

<?php page_foot(); ?>