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

#region REQUIRE
require_once '../APP/config.php';
require_once '../APP/REPOSITORY/RepoNews.php';
#endregion

#region USE
use REPOSITORY\RepoNews;
#endregion

#region LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $newsId = null;
        if (isset($input['news_id'])) {
            $newsId = $input['news_id'];
        } 
        elseif (isset($_POST['news_id'])) {
            $newsId = $_POST['news_id'];
        }

        if (empty($newsId)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error', 
                'message' => 'news_id tidak boleh kosong.'
            ]);
            exit();
        }

        $repo = new RepoNews();
        if ($repo->incrementViewCount((int)$newsId)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'View count berhasil ditambahkan.',
                'news_id' => $newsId
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal menambahkan view count. Berita tidak ditemukan.'
            ]);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method request harus POST.'
    ]);
}
#endregion
?>