<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Total Active Links
    $res = $pdo->prepare("SELECT COUNT(*) as cnt FROM links WHERE user_id = ?");
    $res->execute([$user_id]);
    $active_links = $res->fetch()['cnt'];

    // 2. Base query for user's clicks
    $base_query = "FROM clicks c JOIN links l ON c.link_id = l.id WHERE l.user_id = ?";

    // 3. Total Clicks
    $res = $pdo->prepare("SELECT COUNT(*) as cnt $base_query");
    $res->execute([$user_id]);
    $total_clicks = $res->fetch()['cnt'];

    // 4. Clicks Today
    $res = $pdo->prepare("SELECT COUNT(*) as cnt $base_query AND DATE(c.clicked_at) = CURDATE()");
    $res->execute([$user_id]);
    $clicks_today = $res->fetch()['cnt'];

    // 5. Clicks Over Time (Last 7 days)
    $res = $pdo->prepare("
        SELECT DATE(c.clicked_at) as date, COUNT(*) as cnt 
        $base_query AND c.clicked_at >= DATE(NOW()) - INTERVAL 6 DAY 
        GROUP BY DATE(c.clicked_at) 
        ORDER BY DATE(c.clicked_at) ASC
    ");
    $res->execute([$user_id]);
    $clicks_by_date = $res->fetchAll();
    
    // Fill in missing days
    $dates = [];
    $counts = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $found = 0;
        foreach ($clicks_by_date as $row) {
            if ($row['date'] === $d) $found = $row['cnt'];
        }
        $dates[] = date('D', strtotime($d)); // 'Mon', 'Tue'
        $counts[] = $found;
    }

    // 6. Top Sources
    $res = $pdo->prepare("SELECT IFNULL(l.utm_source, 'Direct') as name, COUNT(*) as cnt $base_query GROUP BY name ORDER BY cnt DESC LIMIT 5");
    $res->execute([$user_id]);
    $sources = $res->fetchAll();

    // 7. Top Campaigns
    $res = $pdo->prepare("SELECT IFNULL(l.utm_campaign, 'None') as name, COUNT(*) as cnt $base_query GROUP BY name ORDER BY cnt DESC LIMIT 5");
    $res->execute([$user_id]);
    $campaigns = $res->fetchAll();

    // 8. Top Mediums
    $res = $pdo->prepare("SELECT IFNULL(l.utm_medium, 'None') as name, COUNT(*) as cnt $base_query GROUP BY name ORDER BY cnt DESC LIMIT 5");
    $res->execute([$user_id]);
    $mediums = $res->fetchAll();

    // 9. Top Countries
    $res = $pdo->prepare("SELECT IFNULL(c.country, 'Unknown') as name, COUNT(*) as cnt $base_query GROUP BY name ORDER BY cnt DESC LIMIT 5");
    $res->execute([$user_id]);
    $countries = $res->fetchAll();

    // Country name -> code fallback map for data stored before [XX] format
    $country_map = [
        'india'=>'in','united states'=>'us','united kingdom'=>'gb','germany'=>'de','france'=>'fr',
        'canada'=>'ca','australia'=>'au','brazil'=>'br','japan'=>'jp','china'=>'cn','russia'=>'ru',
        'south korea'=>'kr','italy'=>'it','spain'=>'es','mexico'=>'mx','indonesia'=>'id',
        'netherlands'=>'nl','turkey'=>'tr','saudi arabia'=>'sa','switzerland'=>'ch','sweden'=>'se',
        'poland'=>'pl','belgium'=>'be','argentina'=>'ar','norway'=>'no','austria'=>'at',
        'united arab emirates'=>'ae','israel'=>'il','singapore'=>'sg','malaysia'=>'my',
        'philippines'=>'ph','thailand'=>'th','vietnam'=>'vn','egypt'=>'eg','south africa'=>'za',
        'nigeria'=>'ng','pakistan'=>'pk','bangladesh'=>'bd','sri lanka'=>'lk','nepal'=>'np',
        'colombia'=>'co','chile'=>'cl','peru'=>'pe','portugal'=>'pt','ireland'=>'ie',
        'denmark'=>'dk','finland'=>'fi','new zealand'=>'nz','czech republic'=>'cz','romania'=>'ro',
        'ukraine'=>'ua','hungary'=>'hu','greece'=>'gr','kenya'=>'ke','ghana'=>'gh','morocco'=>'ma',
        'iraq'=>'iq','iran'=>'ir','afghanistan'=>'af','myanmar'=>'mm','cambodia'=>'kh',
        'taiwan'=>'tw','hong kong'=>'hk','qatar'=>'qa','kuwait'=>'kw','oman'=>'om','bahrain'=>'bh'
    ];

    // Parse country codes for flags
    $countries_with_codes = [];
    foreach ($countries as $c) {
        $name = $c['name'];
        $code = '';
        if (preg_match('/\[([A-Z]{2})\]/', $name, $m)) {
            $code = strtolower($m[1]);
            $name = trim(preg_replace('/\s*\[.*?\]/', '', $name));
        } elseif (isset($country_map[strtolower(trim($name))])) {
            $code = $country_map[strtolower(trim($name))];
        }
        $countries_with_codes[] = ['name' => $name, 'code' => $code, 'cnt' => $c['cnt']];
    }

    echo json_encode([
        'total_clicks' => $total_clicks,
        'clicks_today' => $clicks_today,
        'active_links' => $active_links,
        'top_source' => $sources[0]['name'] ?? '—',
        'top_source_count' => $sources[0]['cnt'] ?? 0,
        
        'chart_clicks' => ['labels' => $dates, 'data' => $counts],
        'chart_sources' => [
            'labels' => array_column($sources, 'name'), 
            'data' => array_column($sources, 'cnt')
        ],
        'chart_campaigns' => [
            'labels' => array_column($campaigns, 'name'), 
            'data' => array_column($campaigns, 'cnt')
        ],
        'chart_mediums' => [
            'labels' => array_column($mediums, 'name'), 
            'data' => array_column($mediums, 'cnt')
        ],
        'chart_countries' => [
            'labels' => array_column($countries, 'name'),
            'data' => array_column($countries, 'cnt')
        ],
        'countries_detail' => $countries_with_codes
    ]);

} catch (\Exception $e) {
    echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
}
?>
