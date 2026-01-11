<?php
namespace REPOSITORY;

#region REQUIRE
require_once(__DIR__ . "/../MODELS/CORE/DatabaseConnection.php");
#endregion

#region USE
use MODELS\CORE\DatabaseConnection;
use Exception;
#endregion

class RepoTag
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
    public function createTag(int $categoryId, string $name): bool
    {
        $tagName = trim($name);

        if ($tagName === '') {
            throw new Exception("Tag name cannot be empty");
        }

        $sql = "
        INSERT INTO tags (category_id, name)
        VALUES (?, ?)
    ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare tag insert statement");
            }

            $cat_id = $categoryId;
            $tag = $tagName;

            $stmt->bind_param("is", $cat_id, $tag);

            if (!$stmt->execute()) {
                // duplicate tag per category
                if ($conn->errno === 1062) {
                    throw new Exception("Tag already exists in this category");
                }

                throw new Exception("Failed to create tag: " . $stmt->error);
            }

            return $stmt->affected_rows > 0;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
            if ($conn) {
                $conn->close();
            }
        }
    }
    #endregion

    #region RETRIEVE
    public function findTagByCategoryId(int $category_id): array
    {
        $sql = "
            SELECT name
            FROM tags
            WHERE category_id = ?
            ORDER BY name ASC
        ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare tag retrieval statement");
            }

            $stmt->bind_param("i", $category_id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to retrieve tags: " . $stmt->error);
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
            if ($stmt) {
                $stmt->close();
            }
            if ($conn) {
                $conn->close();
            }
        }
    }

    
    public function findTagByName(string $name): array
    {
        $name = "%{$name}%";
        $sql = "
            SELECT name
            FROM tags
            WHERE name IS LIKE {$name};
        ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare tag retrieval statement");
            }

            $stmt->bind_param("s", $name);

            if (!$stmt->execute()) {
                throw new Exception("Failed to retrieve tags: " . $stmt->error);
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
            if ($stmt) {
                $stmt->close();
            }
            if ($conn) {
                $conn->close();
            }
        }
    }
    #endregion

    #region UPDATE
    public function updateTag(int $tagId, string $newName): bool
    {
        $name = trim($newName);

        if ($name === '') {
            throw new Exception("Tag name cannot be empty");
        }

        $sql = "
            UPDATE tags
            SET name = ?
            WHERE id = ?
        ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare tag update statement");
            }

            $id = $tagId;

            $stmt->bind_param("si", $name, $id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update tag: " . $stmt->error);
            }

            return $stmt->affected_rows > 0;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
            if ($conn) {
                $conn->close();
            }
        }
    }
    #endregion

    #region DELETE
    public function deleteTag(int $tagId): bool
    {
        $sql = "
            DELETE FROM tags
            WHERE id = ?
        ";

        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare tag delete statement");
            }

            $id = $tagId;
            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to delete tag: " . $stmt->error);
            }

            return $stmt->affected_rows > 0;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
            if ($conn) {
                $conn->close();
            }
        }
    }
    #endregion
}
