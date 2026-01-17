<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
error_reporting(0);

function getFancodeData() {
    // FanCode ki sabse stable Mobile API
    $url = "https://www.fancode.com/api/v1/content/layout?pageType=home&section=all";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Real Android Phone ka User-Agent taaki block na ho
    curl_setopt($ch, CURLOPT_USERAGENT, "FanCode/3.15.0 (Android 12; Pixel 6 Build/SD1A.210817.036)");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Platform: android",
        "X-Client: mobile",
        "Accept: application/json",
        "Origin: https://www.fancode.com",
        "Referer: https://www.fancode.com/"
    ]);
    
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Agar Vercel block hai toh khali array bhej do
    if ($httpCode != 200) return [];

    $raw = json_decode($res, true);
    $matches = [];

    // Layout data ko parse karna
    if (isset($raw['data']['layout'])) {
        foreach ($raw['data']['layout'] as $section) {
            if (isset($section['widgets'])) {
                foreach ($section['widgets'] as $widget) {
                    if (isset($widget['items'])) {
                        foreach ($widget['items'] as $item) {
                            $matchId = $item['match_id'] ?? $item['id'] ?? null;
                            if ($matchId) {
                                $matches[] = [
                                    "event_category" => $item['category_name'] ?? "Live Sports",
                                    "title" => $item['name'] ?? $item['title'],
                                    "src" => $item['posterUrl'] ?? $item['image_url'],
                                    "status" => ($item['is_live'] == true) ? "LIVE" : "UPCOMING",
                                    "match_id" => (int)$matchId,
                                    "startTime" => date("h:i A", ($item['startTime'] / 1000)),
                                    "adfree_url" => "api/stream.php?id=" . $matchId . "&ext=.m3u8"
                                ];
                            }
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
