<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../APP/config.php';
require_once '../APP/MODELS/ACCOUNT/Media.php';
require_once '../APP/MODELS/GEOGRAPHY/City.php';
require_once '../APP/REPOSITORY/RepoMedia.php';

use MODELS\ACCOUNT\Media;
use MODELS\GEOGRAPHY\City;
use REPOSITORY\RepoMedia;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $companyName = $_POST['company_name'] ?? '';
    $type = $_POST['type'] ?? 'NEWS';
    $website = $_POST['website'] ?? '';
    $email = $_POST['email'] ?? '';
    $description = $_POST['description'] ?? '';
    $cityId = $_POST['city_id'] ?? 1;

    if (empty($name) || empty($slug) || empty($companyName)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Data nama, slug, dan perusahaan wajib diisi.']);
        exit;
    }

    try {
        $media = new Media();
        $media->setName($name);
        $media->setSlug($slug);
        $media->setCompanyName($companyName);
        $media->setType($type);
        $media->setWebsite($website);
        $media->setEmail($email);
        $media->setDescription($description);
        
        $city = new City();
        $city->setId((int)$cityId);
        $media->setCity($city);
        
        $logoExt = ' ';
        $pictureExt = ' ';

        function handleUpload($fileInputName, $slug, $subfolder) {
            if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
                $fileNameCmps = explode(".", $_FILES[$fileInputName]['name']);
                $fileExtension = strtolower(end($fileNameCmps));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($fileExtension, $allowedExtensions)) {
                    // Path: ../APP/DATABASE/IMAGE/MEDIA/{LOGO|PICTURE}/{slug}.{ext}
                    $uploadDir = "../APP/DATABASE/IMAGE/MEDIA/" . $subfolder . "/";
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $destPath = $uploadDir . $slug . "." . $fileExtension;
                    if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $destPath)) {
                        return $fileExtension;
                    }
                }
            }
            return null;
        }
        
        $uploadedLogoExt = handleUpload('logo', $slug, 'LOGO');
        if ($uploadedLogoExt) {
            $media->setLogoAddress("dummy." . $uploadedLogoExt); 
        } else {
            $media->setLogoAddress("default.png");
        }

        $uploadedPicExt = handleUpload('picture', $slug, 'PICTURE');
        if ($uploadedPicExt) {
            $media->setPictureAddress("dummy." . $uploadedPicExt);
        } else {
             $media->setPictureAddress("default.jpg");
        }

        $repo = new RepoMedia();
        if ($repo->createMedia($media)) {
            echo json_encode(['status' => 'success', 'message' => 'Media berhasil dibuat']);
        } else {
            throw new Exception("Gagal menyimpan media ke database.");
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}
?>