<?php
// handles authentication logic for the system
// process login, register, logout and email verification reset password actions
// validate credentials and insertion of new accounts into the database
// sends verification emails using PHPMailer to verify user account
// generates a password reset token and expiry time (30min)
// sends an email containing the reset password link 
// Manages user sessions (store user_id, check logged-in user, require login for pages)

require_once __DIR__ . '/../../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../vendor/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//handles all authentication logic like login, register, logout, session, all that shit
//returns onl data or redirects.

require_once __DIR__ . '/../entities/User.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/OnboardingController.php';
class AuthController {


    //login with email and password
    //if success store userid in session
    //if fail return error
    public function login(string $email, string $password): array {
        $row = DB::first(
            'SELECT * FROM users WHERE email = ?',
            [strtolower(trim($email))]
        );

        if (!$row || !password_verify($password, $row['password'])) {
            return ['error' => 'Invalid email or password.'];
        }
        if ($row['is_suspended']) {
            return ['error' => 'This account has been suspended.'];
        }
        if (!$row['email_verified']) {
            return ['error' => 'Please verify your email before logging in.'];
        }

        $_SESSION['user_id'] = $row['id'];
        session_regenerate_id(true);

        return ['ok' => true];
    }

    // send email using PHPMailer
    private function sendEmail(string $to, string $subject, string $message): void {

        $mail = new PHPMailer(true);
        try {
            $mail->Timeout = 10;
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;

            $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);

            $mail->addAddress($to);

            $mail->isHTML(true);   // enable HTML email

            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->send();

        } catch (Exception $e) {

            error_log("Mailer Error: {$mail->ErrorInfo}");

        }
    }


    //register new free user
    public function register(string $name, string $email, string $password, string $confirm): array {
        $email = strtolower(trim($email));
        $name  = trim($name);
        $token = bin2hex(random_bytes(32)); // random token for email verification

        if (empty($name)) {
            return ['error' => 'Full name is required.'];
        }
        if ($password !== $confirm) {
            return ['error' => "Passwords don't match."];
        }
        if (strlen($password) < 6) {
            return ['error' => 'Password must be at least 6 characters.'];
        }
        if (DB::first('SELECT id FROM users WHERE email = ?', [$email])) {
            return ['error' => 'An account with that email already exists.'];
        }

        //hash password with bcrypt
        // insert new user into database
        // email_verified = 0 means user need verify email first
        // verification_token will be used in the email verification link
        DB::execute(
            'INSERT INTO users (email, password, full_name, role, email_verified, verification_token)
            VALUES (?, ?, ?, ?, 0, ?)',
            [$email, password_hash($password, PASSWORD_BCRYPT), $name, 'free', $token]
        );
        // detect whether the website is using http or https
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

        // get current domain (example: localhost or AWS server IP)
        $host = $_SERVER['HTTP_HOST'];

        // create verification link that will be sent to the user's email
        // when user clicks it, verify_email.php will verify the account
        $verify_link = $protocol . $host . "/verify_email.php?token=" . $token;

        //temporary for testing
        //  echo $verify_link;
        //  exit();

        // email subject
        $subject = "Verify Your Email";

        // email content
        $message = "
            <h2>Welcome to SharedSpace</h2>

            <p>Thanks for registering! Please verify your email by clicking the button below.</p>

            <p style='margin:30px 0;'>
                <a href='$verify_link'
                style='background:#4CAF50;
                        color:white;
                        padding:12px 20px;
                        text-decoration:none;
                        border-radius:5px;
                        font-weight:bold;'>
                    Verify Email
                </a>
            </p>

            <p>If the button doesn't work, copy and paste this link into your browser:</p>

            <p>$verify_link</p>
            ";

        $this->sendEmail($email, $subject, $message);

        return ['ok' => true];
    }


    // resend verification email if user didn't receive the first one
    public function resendVerification(string $email): array {

    $email = strtolower(trim($email));

    // check if account exists
    $user = DB::first(
        "SELECT id, email_verified, last_verification_email
         FROM users WHERE email = ?",
        [$email]
    );

    if (!$user) {
        return ['error' => 'No account found with that email.'];
    }

    // if already verified
    if ($user['email_verified']) {
        return ['error' => 'This email is already verified.'];
    }

    // spam protection: check last resend time
    if ($user['last_verification_email']) {

        $last = strtotime($user['last_verification_email']);

        $now = time();
        if ($last) {
        $diff = $now - $last;
        // if timezone is ahead, reset it eg: amercia time : sg time
        if ($diff < 0) {
            $diff = 0;
        }

        if ($diff < 60) {
            $remaining = 60 - $diff;
            return [
                'cooldown' => $remaining
            ];
        }
    }
}
    // generate new verification token
    $token = bin2hex(random_bytes(32));

    DB::execute(
        "UPDATE users 
        SET verification_token = ?, last_verification_email = NOW()
        WHERE id = ?",
        [$token, $user['id']]
    );

        // detect protocol
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

        $host = $_SERVER['HTTP_HOST'];

        $verify_link = $protocol . $host . "/verify_email.php?token=" . $token;
        //temporary for testing
        //  echo $verify_link;
        //  exit();

        // send email
        $subject = "Verify Your Email";
        $message = "
            <h2>Welcome to SharedSpace</h2>

            <p>Thanks for registering! Please verify your email by clicking the button below.</p>

            <p style='margin:30px 0;'>
                <a href='$verify_link'
                style='background:#4CAF50;
                        color:white;
                        padding:12px 20px;
                        text-decoration:none;
                        border-radius:5px;
                        font-weight:bold;'>
                    Verify Email
                </a>
            </p>

            <p>If the button doesn't work, copy and paste this link into your browser:</p>

            <p>$verify_link</p>
            ";
        $this->sendEmail($email, $subject, $message);

        return ['ok' => true];
    }


    //logout, destroy session
    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }


    //return log in user or null if not log in
    public function currentUser(): ?User {
        if (empty($_SESSION['user_id'])) return null;
        static $cache = [];
        $id = $_SESSION['user_id'];
        if (!isset($cache[$id])) {
            $row        = DB::first('SELECT * FROM users WHERE id = ?', [$id]);
            $cache[$id] = $row ? new User($row) : null;
        }
        return $cache[$id];
    }

    // redirect to login page if no session
    // also ensures user has completed onboarding
    public function requireAuth(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }
        // get current user
        $user = $this->currentUser();

        // check if onboarding completed
        $onboardCtrl = new OnboardingController();
        if (!$onboardCtrl->isCompleted($user->id)) {
            header('Location: /pages/onboarding.php');
            exit;
        }
    }
    public function requestPasswordReset(string $email): array {

    $email = strtolower(trim($email));

    $user = DB::first(
        "SELECT id FROM users WHERE email = ?",
        [$email]
    );

    if (!$user) {
        return ['error' => 'No account found with that email.'];
    }

    $token = bin2hex(random_bytes(32));

    DB::execute(
        "UPDATE users
         SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
         WHERE id = ?",
        [$token, $user['id']]
    );

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];

    $link = $protocol . $host . "/reset_password.php?token=" . $token;

    $this->sendEmail(
        $email,
        "Reset Your Password",
        "<p>Click below to reset your password:</p>
        <a href='$link'>$link</a>"
    );

    return ['ok' => true];
}

}
