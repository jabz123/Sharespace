<?php
//config file. change according to own database parameters

// XAMPP defaults — root got no password. update DB_PASS if have
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'sharedspace');
define('DB_USER', 'shareduser');
define('DB_PASS', 'password123'); 
date_default_timezone_set('Asia/Singapore');


// =============================
// SMTP Email Configuration
// =============================

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'marcuskhongg@gmail.com');
define('SMTP_PASS', 'jltrolbbzzdxhrux'); // Gmail app password (no spaces)
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_FROM_NAME', 'SharedSpace');

