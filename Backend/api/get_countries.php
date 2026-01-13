<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once '../APP/config.php';
require_once '../APP/REPOSITORY/RepoCountry.php';

use REPOSITORY\RepoCountry;

try {
    $repo = new RepoCountry();
    $countries = $repo->findAllCountries();

    echo json_encode([
        'status' => 'success',
        'data' => $countries
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>