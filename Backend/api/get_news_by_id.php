<?php
#region HEADER
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
#endregion

#region REQUIRE
require_once '../APP/config.php';
require_once '../APP/REPOSITORY/RepoNews.php';
require_once '../APP/REPOSITORY/RepoLike.php';
#endregion

#region USE
use REPOSITORY\RepoNews;
use REPOSITORY\RepoLike;
#endregion

#region LOGIC
try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Parameter id diperlukan.'
        ]);
        exit();
    }

    $id = (int)$_GET['id'];
    $username = isset($_GET['username']) ? trim($_GET['username']) : '';
    $repo = new RepoNews();
    $repoLike = new RepoLike();
    $news = $repo->findNewsById($id);

    if ($news) {
        $newsData = $news->toArray();
        $stats = $repoLike->getLikeStats($id);
        
        $userStatus = 0;
        if (!empty($username)) {
            $userStatus = $repoLike->getUserLikeStatus($id, $username);
        }
        $newsData['like_count'] = isset($stats['likes']) ? $stats['likes'] : 0;
        $newsData['dislike_count'] = isset($stats['dislikes']) ? $stats['dislikes'] : 0;
        
        $newsData['user_status'] = $userStatus;

        echo json_encode([
            'status' => 'success',
            'data' => $newsData
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Berita dengan ID tersebut tidak ditemukan.'
        ]);
    }

} catch (Exception $e) {
    // Error server internal
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
#endregion
?>