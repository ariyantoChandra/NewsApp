<?php
#region HEADER
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
#endregion

#region REQUIRE
require_once '../APP/config.php';
require_once '../APP/MODELS/NEWS/News.php';
require_once '../APP/MODELS/ACCOUNT/Writer.php';
require_once '../APP/MODELS/ACCOUNT/Media.php';
require_once '../APP/MODELS/GEOGRAPHY/City.php';
require_once '../APP/REPOSITORY/RepoNews.php';
require_once '../APP/REPOSITORY/RepoImage.php';
require_once '../APP/REPOSITORY/RepoTag.php';
require_once '../APP/REPOSITORY/RepoNewsTag.php';
#endregion

#region USE
use MODELS\NEWS\News;
use MODELS\ACCOUNT\Writer;
use MODELS\ACCOUNT\Media;
use MODELS\GEOGRAPHY\City;
use REPOSITORY\RepoNews;
use REPOSITORY\RepoImage;
use REPOSITORY\RepoTag;
use REPOSITORY\RepoNewsTag;
#endregion

#region FUNCTION
function createSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}

function reArrayFiles(&$file_post) {
    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }
    return $file_ary;
}
#endregion

#region JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $categoryId = $_POST['category_id'] ?? 0;
    $writerUsername = $_POST['writer_username'] ?? '';
    $cityId = $_POST['city_id'] ?? 1;
    $tagsInput = $_POST['tags'] ?? [];

    if (empty($title) || empty($writerUsername) || empty($categoryId)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Data text tidak lengkap.']);
        exit;
    }

    if (!isset($_FILES['images'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Tidak ada gambar yang diupload.']);
        exit;
    }

    $countFiles = is_array($_FILES['images']['name']) ? count($_FILES['images']['name']) : 1;
    if ($countFiles < 4) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Minimal upload 4 gambar.']);
        exit;
    }
    $writer = new Writer();
    $writer->setUsername($writerUsername);
    
    $media = new Media(); 
    $media->setId(1); 
    $writer->setMedia($media);

    $city = new City();
    $city->setId((int)$cityId);

    $categoryData = ['id' => (int)$categoryId, 'name' => '']; 

    $news = new News();
    $news->setTitle($title);
    $news->setContent($content);
    $slug = createSlug($title);
    $news->setSlug($slug);
    $news->setAuthor($writer);
    $news->setCity($city);
    $news->setCategory($categoryData); 
    $news->setMedia($media);

    try {
        $repoNews = new RepoNews();
        $repoImage = new RepoImage();
        $newNewsId = $repoNews->CreateNews($news);

        if ($newNewsId > 0) {
            $repoTag = new RepoTag();
            $repoNewsTag = new RepoNewsTag();
            $processedTags = [];
            if (!is_array($tagsInput)) {
                $tagsInput = explode(',', $tagsInput);
            }

            foreach ($tagsInput as $tagName) {
                $cleanTagName = trim($tagName);
                if (empty($cleanTagName)) {
                    continue;
                }

                try {
                    $tagId = $repoTag->findTagIdByNameAndCategory($cleanTagName, (int)$categoryId);

                    if (!$tagId) {
                        $repoTag->createTag((int)$categoryId, $cleanTagName);
                        $tagId = $repoTag->findTagIdByNameAndCategory($cleanTagName, (int)$categoryId);
                    }

                    if ($tagId) {
                        try {
                            $repoNewsTag->createNewsTag($newNewsId, $tagId);
                            $processedTags[] = $cleanTagName;
                        } catch (Exception $e) {
                        }
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
            $fileAry = reArrayFiles($_FILES['images']);
            $uploadedPaths = [];
            $uploadErrors = [];
            $relativeFolder = "DATABASE/IMAGE/WRITER/" . $writerUsername . "/" . $slug . "/";
            $uploadFileDir = "../APP/" . $relativeFolder; 

            if (!file_exists($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $counter = 1;
            foreach ($fileAry as $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $fileNameCmps = explode(".", $file['name']);
                    $fileExtension = strtolower(end($fileNameCmps));
                    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');

                    if (in_array($fileExtension, $allowedfileExtensions)) {
                        $newFileName = "image_" . $counter . "." . $fileExtension;
                        $dest_path = $uploadFileDir . $newFileName;
                        if(move_uploaded_file($file['tmp_name'], $dest_path)) {
                            $dbImagePath = $relativeFolder . $newFileName;
                            $repoImage->createImage($newNewsId, $dbImagePath, "News Image " . $counter, $counter);
                            
                            $uploadedPaths[] = $dbImagePath;
                            $counter++;
                        } else {
                            $uploadErrors[] = "Gagal upload: " . $file['name'];
                        }
                    }
                }
            }

            echo json_encode([
                'status' => 'success', 
                'message' => 'Berita berhasil dibuat.',
                'data' => [
                    'news_id' => $newNewsId,
                    'slug' => $slug,
                    'images_count' => count($uploadedPaths),
                    'tags_added' => $processedTags
                ]
            ]);

        } else {
            throw new Exception("Gagal menyimpan berita ke database.");
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}
#endregion
?>