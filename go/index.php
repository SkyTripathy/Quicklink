<?php
// Core Tracker Engine for your-redirect-domain.com

$host = '127.0.0.1';           // Your database host
$db   = 'your_database_name';  // Your database name
$user = 'your_database_user';  // Your database username
$pass = 'your_database_pass';  // Your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (\PDOException $e) {
    die("Database connection failed. Please check credentials.");
}

// The requested URL path without leading slash
$uri = ltrim($_SERVER['REQUEST_URI'], '/');
$short_code = explode('?', $uri)[0];

if (empty($short_code)) {
    die("TrackFlow Engine Active. Provide a short code.");
}

// Find link
$stmt = $pdo->prepare("SELECT id, original_url, utm_source, utm_medium, utm_campaign, utm_term, utm_content FROM links WHERE short_code = ?");
$stmt->execute([$short_code]);
$link = $stmt->fetch();

if (!$link) {
    die("Link not found.");
}

// Extract tracking data
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$referer = $_SERVER['HTTP_REFERER'] ?? null;

// Simple Device Type detection
$device_type = 'Desktop';
$ua_lower = strtolower($user_agent);
if (strpos($ua_lower, 'mobile') !== false || strpos($ua_lower, 'android') !== false || strpos($ua_lower, 'iphone') !== false) {
    $device_type = 'Mobile';
} elseif (strpos($ua_lower, 'tablet') !== false || strpos($ua_lower, 'ipad') !== false) {
    $device_type = 'Tablet';
}

// Log Click
try {
    $logStmt = $pdo->prepare("INSERT INTO clicks (link_id, ip_address, user_agent, country, device_type, referer) VALUES (?, ?, ?, ?, ?, ?)");
    $country = 'Unknown'; 
    if ($ip !== '127.0.0.1' && $ip !== '::1' && !empty($ip)) {
        $ch = curl_init("http://ip-api.com/json/{$ip}?fields=country,countryCode");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $res = curl_exec($ch);
        curl_close($ch);
        if ($res) {
            $geo = json_decode($res);
            if (!empty($geo->country)) {
                $country = $geo->country;
                if (!empty($geo->countryCode)) {
                    $country .= ' [' . $geo->countryCode . ']';
                }
            }
        }
    }

    $logStmt->execute([$link['id'], $ip, $user_agent, $country, $device_type, $referer]);
} catch (\Exception $e) {
    // Fail silently to not interrupt redirect
}

// Build final redirect URL with UTM parameters appended
$redirect_url = $link['original_url'];
$utm_params = [];
if (!empty($link['utm_source']))   $utm_params['utm_source']   = $link['utm_source'];
if (!empty($link['utm_medium']))   $utm_params['utm_medium']   = $link['utm_medium'];
if (!empty($link['utm_campaign'])) $utm_params['utm_campaign'] = $link['utm_campaign'];
if (!empty($link['utm_term']))     $utm_params['utm_term']     = $link['utm_term'];
if (!empty($link['utm_content']))  $utm_params['utm_content']  = $link['utm_content'];

if (!empty($utm_params)) {
    $separator = (parse_url($redirect_url, PHP_URL_QUERY)) ? '&' : '?';
    $redirect_url .= $separator . http_build_query($utm_params);
}

header("Location: " . $redirect_url);
exit;
?>
