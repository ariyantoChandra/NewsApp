<?php
#region HEADER
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
#endregion

#region REQUIRE
require_once '../APP/REPOSITORY/RepoCategory.php';
#endregion

#region USE
use REPOSITORY\RepoCategory;
#endregion

#region JSON
$input = json_decode(file_get_contents('php://input'), true);
if (isset($input['name']) && !empty($input['name'])) {
    try {
        $repo = new RepoCategory();
        if ($repo->createCategory($input['name'])) {
            echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil dibuat']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal membuat kategori']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Nama kategori tidak boleh kosong']);
}
#endregion
?>