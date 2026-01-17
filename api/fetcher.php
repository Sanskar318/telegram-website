<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Direct file path
$json_data = file_get_contents('../matches.json');

// Check karein ki kya file HTML toh nahi hai
if (strpos($json_data, '<!DOCTYPE') !== false) {
    echo json_encode(["status" => "error", "message" => "Source Blocked by FanCode", "matches" => []]);
} else {
    echo $json_data;
}
?>
