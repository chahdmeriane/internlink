<?php
// ─────────────────────────────────────────────
//  send_otp.php — internLink
//  Called by login.php after credentials pass.
//  Generates a 6-digit OTP, saves it to DB,
//  and emails it to the user.
//
//  This is NOT called directly by the browser.
//  It is included by login.php internally.
// ─────────────────────────────────────────────

require_once __DIR__ . '/mailer.php';

/**
 * Generate OTP, store in DB, send to user email.
 *
 * @param PDO    $pdo   Database connection
 * @param array  $user  User row from DB (must have id, email, first_name)
 * @return bool         true if email sent, false if failed
 */
function sendOtp(PDO $pdo, array $user): bool
{
    // Generate a cryptographically secure 6-digit OTP
    $otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', time() + 600); // expires in 10 minutes

    // Save OTP to DB
    try {
        $pdo->prepare(
            "UPDATE users SET two_fa_code = ?, two_fa_expires = ? WHERE id = ?"
        )->execute([$otp, $expires, $user['id']]);
    } catch (PDOException $e) {
        error_log('send_otp DB error: ' . $e->getMessage());
        return false;
    }

    // Build the email HTML
    $name    = htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8');
    $subject = 'Your internLink verification code';
    $body    = "
    <div style='font-family:sans-serif;max-width:480px;margin:auto;padding:32px;'>
        <h2 style='color:#4f8ef7;margin-bottom:8px;'>internLink</h2>
        <p style='color:#333;font-size:15px;'>Hi {$name},</p>
        <p style='color:#333;font-size:15px;'>
            Your login verification code is:
        </p>
        <div style='
            font-size:36px;
            font-weight:bold;
            letter-spacing:12px;
            color:#111;
            background:#f0f4ff;
            border:1px solid #d0dcff;
            border-radius:10px;
            padding:20px 32px;
            text-align:center;
            margin:24px 0;
        '>{$otp}</div>
        <p style='color:#555;font-size:13px;'>
            This code expires in <strong>10 minutes</strong>.<br/>
            If you did not try to log in, you can safely ignore this email.
        </p>
        <hr style='border:none;border-top:1px solid #eee;margin:24px 0;'/>
        <p style='color:#aaa;font-size:12px;'>internLink — Connecting students with opportunities.</p>
    </div>
    ";

    return sendMail($user['email'], $user['first_name'], $subject, $body);
}
