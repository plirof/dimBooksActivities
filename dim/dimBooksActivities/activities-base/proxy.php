<?php
// proxy.php - Simple CORS proxy for fetching remote JSON data
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Get the URL parameter
$url = isset($_GET['url']) ? $_GET['url'] : '';

// Validate URL
if (empty($url)) {
    http_response_code(400);
    echo json_encode(['error' => 'URL parameter is required']);
    exit;
}

// Only allow HTTP/HTTPS protocols
if (!preg_match('/^https?:\/\//i', $url)) {
    http_response_code(400);
    echo json_encode(['error' => 'Only HTTP/HTTPS URLs are allowed']);
    exit;
}

// Use cURL to fetch the remote content
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Proxy/1.0)');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch remote data', 'status' => $httpCode]);
    exit;
}

// Try to decode JSON, if it fails return as is
$json = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode($json);
} else {
    echo $response;
}
?>