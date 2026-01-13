<?php
namespace REPOSITORY;

#region REQUIRE
require_once(__DIR__ . "/../MODELS/CORE/DatabaseConnection.php");
#endregion

#region USE
use MODELS\CORE\DatabaseConnection;
use Exception;
#endregion

class RepoNewsTag
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

    #region RETRIEVE
    // returns array of tag names
    public function findTagByNewsId(int $news_id): array
    {
        $sql = "
            SELECT t.name
            FROM news_tags nt
            INNER JOIN tags t ON nt.tags_id = t.id
            WHERE nt.news_id = ?
            ORDER BY t.name ASC
        ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare news-tag retrieval statement");
            }

            $newsId = $news_id;
            $stmt->bind_param("i", $newsId);

            if (!$stmt->execute()) {
                throw new Exception("Failed to retrieve tags for news: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $tags = [];

            while ($row = $result->fetch_assoc()) {
                $tags[] = $row['name'];
            }

            return $tags;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) $stmt->close();
            if ($conn) $conn->close();
        }
    }
    #endregion

    #region CREATE
    public function createNewsTag(int $news_id, int $tag_id): bool
    {
        $sql = "
            INSERT INTO news_tags (news_id, tags_id)
            VALUES (?, ?)
        ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare news-tag insert statement");
            }

            $newsId = $news_id;
            $tagId  = $tag_id;

            $stmt->bind_param("ii", $newsId, $tagId);

            if (!$stmt->execute()) {
                // duplicate composite PK
                if ($conn->errno === 1062) {
                    throw new Exception("Tag already attached to this news");
                }

                throw new Exception("Failed to attach tag to news: " . $stmt->error);
            }

            return true;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) $stmt->close();
            // if ($conn) $conn->close();
        }
    }
    #endregion

    #region DELETE
    public function deleteNewsTag(int $news_id, int $tag_id): bool
    {
        $sql = "
            DELETE FROM news_tags
            WHERE news_id = ? AND tags_id = ?
        ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare news-tag delete statement");
            }

            $newsId = $news_id;
            $tagId  = $tag_id;

            $stmt->bind_param("ii", $newsId, $tagId);

            if (!$stmt->execute()) {
                throw new Exception("Failed to detach tag from news: " . $stmt->error);
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
