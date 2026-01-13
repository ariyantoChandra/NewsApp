<?php
namespace REPOSITORY;

#region REQUIRE
require_once(__DIR__ . "/../MODELS/CORE/DatabaseConnection.php");
#endregion

#region USE
use MODELS\CORE\DatabaseConnection;
use Exception;
#endregion

class RepoCategory
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
    public function createCategory(string $name): bool
    {
        $name = trim($name);
        $sql = "INSERT IGNORE INTO categories (name)
            VALUES (?)";
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare category insert statement");
            }

            $stmt->bind_param("s", $name);

            if (!$stmt->execute()) {
                throw new Exception("Failed to create category (maybe duplicate): " . $stmt->error);
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
    public function findCategoryById(int $id): string
    {
        $sql = "SELECT `name`
            FROM categories
            WHERE id = ?
            LIMIT 1";
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            $stmt->bind_param("i", $id);
            $stmt->execute();

            $result = $stmt->get_result();
            return $result->fetch_assoc()['name'];
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
    public function findCategoryByName(string $name): string
    {
        $sql = "SELECT `name`
            FROM categories
            WHERE name = ?
            LIMIT 1";
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            $stmt->bind_param("s", $name);
            $stmt->execute();

            $result = $stmt->get_result();
            return $result->fetch_assoc()['name'];
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

    public function findCategory(): array
    {
        $sql = "SELECT id, name FROM categories ORDER BY name ASC";
        $categories = [];
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $result = $stmt->get_result();
            //return $result->fetch_assoc()['name'];
            while ($row = $result->fetch_assoc()) {
                $categories[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name']
                ];
            }
            return $categories;
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
    public function updateCategoryWithId(int $id, string $newName): bool
    {
        $newName = trim($newName);
        if ($newName === '') {
            throw new Exception("Category name cannot be empty");
        }
        $sql = "UPDATE categories
            SET name = ?
            WHERE id = ?";
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            $stmt->bind_param("si", $newName, $id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update category: " . $stmt->error);
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
    public function updateCategoryWithOldName(string $newName, string $oldName): bool
    {
        $newName = trim($newName);
        if ($newName === '') {
            throw new Exception("Category name cannot be empty");
        }
        $sql = "UPDATE categories
            SET `name` = ?
            WHERE `name` = ?";
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            $stmt->bind_param("ss", $newName, $oldname);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update category: " . $stmt->error);
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
    public function deleteCategoryById(int $id): bool
    {
        $sql = "DELETE FROM categories
            WHERE id = ?";
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to delete category: " . $stmt->error);
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
    public function deleteCategoryByName(string $name): bool
    {
        $sql = "DELETE FROM categories
            WHERE name = ?";
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            $stmt->bind_param("s", $name);

            if (!$stmt->execute()) {
                throw new Exception("Failed to delete category: " . $stmt->error);
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
