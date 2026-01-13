<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../APP/config.php';
require_once '../APP/REPOSITORY/RepoNews.php';

use REPOSITORY\RepoNews;

try {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    $repo = new RepoNews();
    $newsList = $repo->findAllNews($limit, $offset);

    echo json_encode([
        'status' => 'success',
        'page' => $page,
        'limit' => $limit,
        'data' => $newsList
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>