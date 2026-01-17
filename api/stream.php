<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/vnd.apple.mpegurl");

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    // FanCode resolver logic
    // Filhaal hum test m3u8 par bhej rahe hain, asali matches ke liye resolver replace hoga
    $stream_link = "https://dai.google.com/linear/hls/event/sid/master.m3u8";
    header("Location: $stream_link");
    exit;
}
?>
