<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
error_reporting(0);

function getFancodeData() {
    $url = "https://www.fancode.com/api/v1/content/home?section=all";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Platform: web",
        "Origin: https://www.fancode.com",
        "Referer: https://www.fancode.com/"
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    
    $raw = json_decode($res, true);
    $matches = [];
    if (isset($raw['data']['sections'])) {
        foreach ($raw['data']['sections'] as $section) {
            if (isset($section['items'])) {
                foreach ($section['items'] as $item) {
                    if (isset($item['match_id']) || $item['type'] == 'MATCH') {
                        $id = $item['id'] ?? $item['match_id'];
                        $matches[] = [
                            "event_category" => $item['category_name'] ?? "Sports",
                            "title" => $item['name'] ?? $item['title'],
                            "src" => $item['posterUrl'] ?? $item['image_url'],
                            "status" => ($item['is_live']) ? "LIVE" : "UPCOMING",
                            "match_id" => (int)$id,
                            "startTime" => date("h:i A", ($item['startTime'] / 1000)),
                            "adfree_url" => "api/stream.php?id=" . $id . "&ext=.m3u8"
                        ];
                    }
                }
            }
        }
    }
    return array_values(array_column($matches, null, 'match_id'));
}

echo json_encode([
    "last update time" => date("h:i:s A d-m-Y"),
    "matches" => getFancodeData()
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
