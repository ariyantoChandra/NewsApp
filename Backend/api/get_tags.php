<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../APP/config.php';
require_once '../APP/REPOSITORY/RepoTag.php';

use REPOSITORY\RepoTag;

try {
    $repo = new RepoTag();
    $data = [];
    if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
        $categoryId = (int)$_GET['category_id'];
        $data = $repo->getTagsByCategoryId($categoryId);
    } else {
        $data = $repo->getAllTags();
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>