<?php
#region HEADER
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
#endregion

#region PREFLIGHT
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
#endregion

include_once '../APP/config.php';
include_once '../APP/MODELS/CORE/DatabaseConnection.php';
include_once '../APP/REPOSITORY/RepoLike.php';

use REPOSITORY\RepoLike;
$repoLike = new RepoLike();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->news_id) && !empty($data->username)) {
    try {
        $newsId = $data->news_id;
        $username = $data->username;
        $repoLike->toggleDislike((int)$newsId, $username);
        $stats = $repoLike->getLikeStats((int)$newsId);
        $userStatus = $repoLike->getUserLikeStatus((int)$newsId, $username);
        echo json_encode([
            'status' => 'success',
            'message' => 'Dislike status updated',
            'data' => [
                'user_status' => $userStatus,
                'stats' => [
                    'like_count' => $stats['likes'],
                    'dislike_count' => $stats['dislikes']
                ]
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Incomplete data']);
}
?>