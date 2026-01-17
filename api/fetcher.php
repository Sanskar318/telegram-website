<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

// Apni hi repo ki file padhna
$file = '../matches.json';
if (!file_exists($file)) {
    echo json_encode(["status" => "error", "message" => "File not found"]);
    exit;
}

$raw = json_decode(file_get_contents($file), true);
$matches = [];

if (isset($raw['data']['sections'])) {
    foreach ($raw['data']['sections'] as $section) {
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

echo json_encode([
    "status" => "success",
    "matches" => array_values(array_column($matches, null, 'match_id'))
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
