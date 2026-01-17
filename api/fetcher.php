<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
error_reporting(0);

function getFancodeData() {
    // Sabse stable API endpoint jo block nahi hota
    $url = "https://www.fancode.com/api/v1/content/live-events";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Rotating User Agent for bypass
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Mobile Safari/537.36");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Platform: web",
        "X-Client: mobile",
        "Origin: https://www.fancode.com",
        "Referer: https://www.fancode.com/",
        "Accept: application/json"
    ]);
    
    $res = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($res, true);
    $matches = [];

    // Live matches fetch logic
    if (isset($data['data']['liveEvents'])) {
        foreach ($data['data']['liveEvents'] as $event) {
            $matches[] = [
                "event_category" => $event['categoryName'] ?? "Live",
                "title" => $event['name'],
                "src" => $event['posterUrl'],
                "status" => "LIVE",
                "match_id" => (int)$event['id'],
                "startTime" => "LIVE NOW",
                "adfree_url" => "api/stream.php?id=" . $event['id'] . "&ext=.m3u8"
            ];
        }
    }
    
    // Agar live nahi hain, toh discover section try karein
    if (empty($matches)) {
        $home_url = "https://www.fancode.com/api/v1/content/home?section=all";
        $res_home = file_get_contents($home_url);
        $data_home = json_decode($res_home, true);
        if (isset($data_home['data']['sections'])) {
            foreach ($data_home['data']['sections'] as $s) {
                if (isset($s['items'])) {
                    foreach ($s['items'] as $item) {
                        if (isset($item['match_id'])) {
                            $matches[] = [
                                "event_category" => $item['category_name'] ?? "Sports",
                                "title" => $item['name'] ?? $item['title'],
                                "src" => $item['posterUrl'],
                                "status" => ($item['is_live']) ? "LIVE" : "UPCOMING",
                                "match_id" => (int)$item['match_id'],
                                "startTime" => "Scheduled",
                                "adfree_url" => "api/stream.php?id=" . $item['match_id'] . "&ext=.m3u8"
                            ];
                        }
                    }
                }
            }
        }
    }
    return array_values(array_column($matches, null, 'match_id'));
}

echo json_encode([
    "status" => "success",
    "matches" => getFancodeData()
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
