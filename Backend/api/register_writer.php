<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../APP/config.php';
require_once '../APP/MODELS/ACCOUNT/Writer.php';
require_once '../APP/MODELS/ACCOUNT/Media.php';
require_once '../APP/MODELS/GEOGRAPHY/Country.php';
require_once '../APP/REPOSITORY/RepoAccount.php';
require_once '../APP/REPOSITORY/RepoCountry.php';

use REPOSITORY\RepoAccount;
use REPOSITORY\RepoCountry;
use MODELS\ACCOUNT\Writer;
use MODELS\ACCOUNT\Media;
use MODELS\GEOGRAPHY\Country;

$input = json_decode(file_get_contents('php://input'), true);

try {
    if (empty($input['username']) || empty($input['password']) || empty($input['email']) || empty($input['media_id'])) {
        throw new Exception("Data tidak lengkap (Username, Password, Email, Media ID wajib diisi)");
    }

    $writer = new Writer();
    $writer->setUsername($input['username']);
    $writer->setFullname($input['fullname'] ?? $input['username']);
    $writer->setEmail($input['email']);
    $writer->setRole('WRITER'); 
    $writer->setBiography($input['biography'] ?? '');
    $writer->setIsVerified(false);

    $media = new Media();
    $media->setId((int)$input['media_id']);
    $writer->setMedia($media);

    $defaultPic = isset($input['profile_picture_address']) ? $input['profile_picture_address'] : "";
    $writer->setProfilePictureAddress($defaultPic);
    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
    $repo = new RepoAccount();

    if ($repo->createWriter($writer, $hashedPassword)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Writer berhasil didaftarkan',
            'data' => [
                'username' => $writer->getUsername(),
                'role' => 'WRITER',
                'media_id' => $input['media_id']
            ]
        ]);
    } else {
        throw new Exception('Gagal mendaftarkan writer (mungkin username/email sudah dipakai)');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>