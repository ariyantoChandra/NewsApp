<?php
namespace REPOSITORY;

#region USE
use MODELS\CORE\DatabaseConnection;
use mysqli_sql_exception;
#endregion

class RepoLike {
    #region FIELDS
    private $conn;
    #endregion

    #region CONSTRUCT
    public function __construct() {
        $db = new DatabaseConnection();
        $this->conn = $db->connect();
    }
    #endregion

    #region FUNCTION
    public function toggleLike($newsId, $username) {
        $currentStatus = $this->checkUserStatus($newsId, $username);

        if ($currentStatus === 1) {
            $this->deleteInteraction($newsId, $username);
        } 
        else if ($currentStatus === 0) {
            $this->updateInteraction($newsId, $username, 1);
        } 
        else {
            try {
                $this->insertInteraction($newsId, $username, 1);
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    $this->updateInteraction($newsId, $username, 1);
                } else {
                    throw $e; 
                }
            }
        }
    }

    public function toggleDislike($newsId, $username) {
        $currentStatus = $this->checkUserStatus($newsId, $username);

        if ($currentStatus === 0) {
            $this->deleteInteraction($newsId, $username);
        } 
        else if ($currentStatus === 1) {
            $this->updateInteraction($newsId, $username, 0);
        } 
        else {
            try {
                $this->insertInteraction($newsId, $username, 0);
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    $this->updateInteraction($newsId, $username, 0);
                } else {
                    throw $e;
                }
            }
        }
    }

    public function getUserLikeStatus($newsId, $username) {
        return $this->checkUserStatus($newsId, $username);
    }

    public function getLikeStats($newsId) {
        $query = "SELECT 
                    SUM(CASE WHEN is_like = 1 THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN is_like = 0 THEN 1 ELSE 0 END) as dislikes
                  FROM likes
                  WHERE news_id = ?";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $newsId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return [
            'likes' => $row['likes'] ? (int)$row['likes'] : 0,
            'dislikes' => $row['dislikes'] ? (int)$row['dislikes'] : 0
        ];
    }

    private function checkUserStatus($newsId, $username) {
        $query = "SELECT is_like FROM likes WHERE news_id = ? AND username = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $newsId, $username);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['is_like']; 
        }
        return -1;
    }

    private function insertInteraction($newsId, $username, $status) {
        $query = "INSERT INTO likes (news_id, username, is_like, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isi", $newsId, $username, $status);
        $stmt->execute();
    }

    private function updateInteraction($newsId, $username, $status) {
        $query = "UPDATE likes SET is_like = ?, created_at = NOW() WHERE news_id = ? AND username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iis", $status, $newsId, $username);
        $stmt->execute();
    }

    private function deleteInteraction($newsId, $username) {
        $query = "DELETE FROM likes WHERE news_id = ? AND username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $newsId, $username);
        $stmt->execute();
    }
    #endregion
}
?>