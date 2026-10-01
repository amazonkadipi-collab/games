<?php
if (!defined('R_PILOT')) { exit(); }

if (empty($_POST['login_id']) || !isset($_POST['login_key'])) {
    $data['error_message'] = $lang['empty_place'] ?? 'Please enter your login details.';
    header('Content-type: application/json');
    echo json_encode($data);
    exit();
}

$loginUser = trim((string)$_POST['login_id']);
$loginPass = (string)$_POST['login_key'];

$account = false;
if (isset($GameMonetizeConnect)) {
    $safeUser = $GameMonetizeConnect->real_escape_string($loginUser);
    $account = $GameMonetizeConnect->query(
        "SELECT * FROM " . ACCOUNTS . " WHERE (username='{$safeUser}' OR email='{$safeUser}' OR id=" . (ctype_digit($loginUser) ? (int)$loginUser : 0) . ") AND active=1 LIMIT 1"
    );
}

if ($account && $account->num_rows === 1) {
    $user = $account->fetch_assoc();
    $stored = (string)($user['password'] ?? '');

    // Support both the Neon migration's plain password records and legacy CMS hashes.
    $valid = $stored !== '' && hash_equals($stored, $loginPass);
    if (!$valid && isset($encryption)) {
        $legacy = sha1(str_rot13($loginPass . $encryption));
        $valid = hash_equals($stored, $legacy);
    }

    if ($valid) {
        $cookieOptions = [
            'expires' => time() + (60 * 60 * 24 * 30),
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        setcookie('gm_ac_u', (string)$user['id'], $cookieOptions);
        setcookie('gm_ac_p', $stored, $cookieOptions);

        // Do not call the legacy external GameMonetize callback here.
        // It can block a Vercel/FrankenPHP request and cause a 504.
        $data['status'] = 200;
        $data['redirect_url'] = siteUrl() . '/admin';
    } else {
        $data['error_message'] = $lang['invalid_data'] ?? 'Invalid login details.';
    }
} else {
    $data['error_message'] = $lang['invalid_data'] ?? 'Invalid login details.';
}

header('Content-type: application/json');
echo json_encode($data);
if (isset($GameMonetizeConnect)) {
    $GameMonetizeConnect->close();
}
exit();
