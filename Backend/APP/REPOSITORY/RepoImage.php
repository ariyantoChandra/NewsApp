<?php
namespace REPOSITORY;

#region REQUIRE
require_once(__DIR__ . "/../MODELS/CORE/DatabaseConnection.php");
#endregion

#region USE
use MODELS\CORE\DatabaseConnection;
use Exception;
#endregion

class RepoImage
{
    #region FIELDS
    private DatabaseConnection $db;
    #endregion

    #region CONSTRUCTOR
    public function __construct()
    {
        $this->db = new DatabaseConnection();
    }
    #endregion

    #region CREATE
    public function createImage(
        int $news_id,
        string $image_address,
        ?string $alt_text = null,
        int $sort_order = 0
    ): bool
    {
        $path = trim($image_address);

        if ($path === '') {
            throw new Exception("Image path cannot be empty");
        }

        $sql = "
            INSERT INTO news_images (news_id, image_path, alt_text, sort_order)
            VALUES (?, ?, ?, ?)
        ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare image insert statement");
            }

            $newsId = $news_id;
            $image  = $path;
            $alt    = $alt_text;
            $order  = $sort_order;

            $stmt->bind_param("issi", $newsId, $image, $alt, $order);

            if (!$stmt->execute()) {
                throw new Exception("Failed to create image: " . $stmt->error);
            }

            return $stmt->affected_rows > 0;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) $stmt->close();
            if ($conn) $conn->close();
        }
    }
    #endregion

    #region RETRIEVE
    public function findImageByNewsId(int $news_id): array
    {
        $sql = "
            SELECT *
            FROM news_images
            WHERE news_id = ?
            ORDER BY sort_order ASC, id ASC
        ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare image retrieval statement");
            }

            $newsId = $news_id;
            $stmt->bind_param("i", $newsId);

            if (!$stmt->execute()) {
                throw new Exception("Failed to retrieve images: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $images = [];

            while ($row = $result->fetch_assoc()) {
                $images[] = $row;
            }

            return $images;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) $stmt->close();
            if ($conn) $conn->close();
        }
    }

    public function findTopImageByNewsId(int $news_id): ?array
    {
        $sql = "
            SELECT *
            FROM news_images
            WHERE news_id = ?
            ORDER BY sort_order ASC, id ASC
            LIMIT 1
        ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare top image retrieval statement");
            }

            $newsId = $news_id;
            $stmt->bind_param("i", $newsId);

            if (!$stmt->execute()) {
                throw new Exception("Failed to retrieve top image: " . $stmt->error);
            }

            $row = $stmt->get_result()->fetch_assoc();
            return $row ?: null;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) $stmt->close();
            if ($conn) $conn->close();
        }
    }
    #endregion

    #region UPDATE
    public function updateImage(
        int $picture_id,
        string $image_address,
        ?string $alt_text = null,
        int $sort_order = 0
    ): bool
    {
        $path = trim($image_address);

        if ($path === '') {
            throw new Exception("Image path cannot be empty");
        }

        $sql = "
            UPDATE news_images
            SET image_path = ?, alt_text = ?, sort_order = ?
            WHERE id = ?
        ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare image update statement");
            }

            $imageId = $picture_id;
            $image   = $path;
            $alt     = $alt_text;
            $order   = $sort_order;

            $stmt->bind_param("ssii", $image, $alt, $order, $imageId);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update image: " . $stmt->error);
            }

            return $stmt->affected_rows > 0;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) $stmt->close();
            if ($conn) $conn->close();
        }
    }
    #endregion

    #region DELETE
    public function deleteImage(int $picture_id): bool
    {
        $sql = "
            DELETE FROM news_images
            WHERE id = ?
        ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare image delete statement");
            }

            $imageId = $picture_id;
            $stmt->bind_param("i", $imageId);

            if (!$stmt->execute()) {
                throw new Exception("Failed to delete image: " . $stmt->error);
            }

            return $stmt->affected_rows > 0;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) $stmt->close();
            if ($conn) $conn->close();
        }
    }
    #endregion
}
