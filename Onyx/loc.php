<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'));
    $lat = $data->latitude;
    $lon = $data->longitude;

    // Optional: Use reverse geocoding via an API (see below)
    // For now just log it
    file_put_contents('user_locations.txt', "Lat: $lat, Lon: $lon\n", FILE_APPEND);
}
?>
