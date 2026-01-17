<?php
error_reporting(0);
header('Content-Type: application/json');

$baseUrl = "https://fancode-ind.vercel.app";

function getFanCodeLive($baseUrl) {
    $url = "https://www.fancode.com/api/v1/content/live-events";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Origin: https://www.fancode.com", "Referer: https://www.fancode.com/"]);
    $res = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($res, true);
    $matches = [];

    if (isset($data['data']['liveEvents']) && !empty($data['data']['liveEvents'])) {
        foreach ($data['data']['liveEvents'] as $event) {
            $matches[] = [
                "name" => $event['name'],
                "image" => $event['posterUrl'] ?? "https://www.fancode.com/skin/images/fancode-logo.png",
                "url" => $baseUrl . "/api/stream.php?id=" . $event['id'] . "&ext=.m3u8"
            ];
        }
    } else {
        // Test match taaki JSON khali na dikhe jab tak koi live na ho
        $matches[] = [
            "name" => "No Live Match Currently",
            "image" => "https://www.fancode.com/skin/images/fancode-logo.png",
            "url" => $baseUrl . "/api/stream.php?id=test&ext=.m3u8"
        ];
    }
    return $matches;
}

$output = [
    "status" => "success",
    "last_updated" => date("Y-m-d H:i:s"),
    "matches" => getFanCodeLive($baseUrl)
];

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
