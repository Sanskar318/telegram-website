<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

/**
 * Ab hum FanCode ki website block bypass karne ke liye 
 * ek public proxy API use kar rahe hain jo block nahi hoti.
 */

function getMatches() {
    // Ye ek alternate open API source hai jo sports data fetch karti hai
    $url = "https://raw.githubusercontent.com/L some-random-api-source-here/data.json"; 
    
    // Lekin hum FanCode ka direct mobile link use karenge ek bypasser ke saath
    $bypasserUrl = "https://api.allorigins.win/get?url=" . urlencode("https://www.fancode.com/api/v1/content/home?section=all");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $bypasserUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($response, true);
    $data = json_decode($json['contents'], true);

    $matches = [];

    if (isset($data['data']['sections'])) {
        foreach ($data['data']['sections'] as $section) {
            if (isset($section['items'])) {
                foreach ($section['items'] as $item) {
                    if (isset($item['match_id'])) {
                        $matches[] = [
                            "title" => $item['name'] ?? $item['title'],
                            "src" => $item['posterUrl'],
                            "status" => ($item['is_live']) ? "LIVE" : "UPCOMING",
                            "match_id" => (int)$item['match_id'],
                            "adfree_url" => "api/stream.php?id=" . $item['match_id'] . "&ext=.m3u8"
                        ];
                    }
                }
            }
        }
    }
    return array_values(array_column($matches, null, 'match_id'));
}

echo json_encode([
    "status" => "success",
    "matches" => getMatches()
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
