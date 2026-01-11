<?php
namespace REPOSITORY;

#region REQUIRE
require_once(__DIR__ . "/../MODELS/CORE/DatabaseConnection.php");
require_once(__DIR__ . "/../MODELS/GEOGRAPHY/City.php");
require_once(__DIR__ . "/../MODELS/ACCOUNT/Media.php");
#endregion

#region USE
use MODELS\CORE\DatabaseConnection;
use MODELS\ACCOUNT\Media;
use MODELS\GEOGRAPHY\City;
use Exception;
#endregion

class RepoMedia
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
    public function createMedia(Media $media): bool
    {
        $sql = "INSERT INTO medias (
                name, slug, company_name, media_type,
                picture_ext, logo_ext,
                website, email, description, city_id
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            );";
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare media insert statement");
            }
            $name = $media->getName();
            $slug = $media->getSlug();
            $company_name = $media->getCompanyName();
            $type = $media->getType();
            $picture_ext = $media->getPictureExtension();
            $logo_ext = $media->getLogoExtension();
            $website = $media->getWebsite();
            $email = $media->getEmail();
            $description = $media->getDescription();
            $city_id = $media->getCity()->getId();
            $stmt->bind_param(
                "sssssssssi",
                $name,
                $slug,
                $company_name,
                $type,
                $picture_ext,
                $logo_ext,
                $website,
                $email,
                $description,
                $city_id
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create media: " . $stmt->error);
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
    public function findMediaById(int $id, bool $withDeleted = false): Media
    {
        $is_deleted = ($withDeleted ? "" : "AND deleted_at IS NULL");
        $sql = "SELECT 
                    m.*,
                    c.id as 'city_id',
                    c.name as 'city_name'
            FROM medias m
            INNER JOIN cities c
            ON m.city_id = c.id
            WHERE id = ?
            {$is_deleted} 
        ";
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to retrieve media: " . $stmt->error);
            }

            $result = $stmt->get_result();
            return $this->mapSQLResultToMediaObject($result->fetch_assoc());
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
    public function findMediaByName(string $name, bool $withDeleted = false): Media
    {
        $is_deleted = ($withDeleted ? "" : "AND deleted_at IS NULL");
        $sql = "SELECT 
                    m.*,
                    c.id as 'city_id',
                    c.name as 'city_name'
            FROM medias m
            INNER JOIN cities c
            ON m.city_id = c.id
            WHERE name = ?
            {$is_deleted} 
        ";
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $name);

            if (!$stmt->execute()) {
                throw new Exception("Failed to retrieve media: " . $stmt->error);
            }

            $result = $stmt->get_result();
            return $this->mapSQLResultToMediaObject($result->fetch_assoc());
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
#region UPDATE
    public function updateMedia(int $id, Media $media): bool
    {
        $sql = "UPDATE medias
        SET
            name = ?,
            company_name = ?,
            media_type = ?,
            picture_ext = ?,
            logo_ext = ?,
            website = ?,
            email = ?,
            description = ?,
            city_id = ?
        WHERE id = ?
          AND deleted_at IS NULL";

        $stmt = null;
        $conn = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare media update statement");
            }

            $name = $media->getName();
            $companyName = $media->getCompanyName();
            $mediaType = $media->getType();
            $pictureExt = $media->getPictureExtension();
            $logoExt = $media->getLogoExtension();
            $website = $media->getWebsite();
            $email = $media->getEmail();
            $description = $media->getDescription();
            $cityId = $media->getCity()->getId();
            $mediaId = $id;

            $stmt->bind_param(
                "ssssssssii",
                $name,
                $companyName,
                $mediaType,
                $pictureExt,
                $logoExt,
                $website,
                $email,
                $description,
                $cityId,
                $mediaId
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to update media: " . $stmt->error);
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
    public function deleteMedia(int $id, bool $soft = true): bool
    {
        try {
            return $soft
                ? $this->softDelete($id)
                : $this->hardDelete($id);
        } catch (Exception $e) {
            throw $e;
        }
    }


    public function softDelete(int $id): bool
    {
        $sql = "
        UPDATE medias
        SET deleted_at = NOW(), is_active = FALSE
        WHERE id = ?
          AND deleted_at IS NULL
    ";

        $stmt = null;
        $conn = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare soft delete statement");
            }

            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to soft delete media: " . $stmt->error);
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

    public function hardDelete(int $id): bool
    {
        $sql = "DELETE FROM medias WHERE id = ?";

        $stmt = null;
        $conn = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare hard delete statement");
            }

            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to permanently delete media: " . $stmt->error);
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
    public function restoreMedia(int $id): bool
    {
        $sql = "
        UPDATE medias
        SET deleted_at = NULL, is_active = TRUE
        WHERE id = ?
    ";

        $stmt = null;
        $conn = null;

        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare restore media statement");
            }

            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to restore media: " . $stmt->error);
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

    #region MAPPER
    private function mapSQLResultToMediaObject(array $row): Media
    {
        $city = new City();
        $city->setId($row["city_id"]);
        $city->setName($row["city_name"]);
        $media = new Media();
        $media->setId($row['id']);
        $media->setName($row['name']);
        $media->setDescription($row['description']);
        $media->setWebsite($row['website']);
        $media->setEmail($row['email']);
        $media->createLogoAddressFromExt($row['logo_ext']);
        $media->createPictureAddressFromExt($row['picture_ext']);
        $media->setCity($city);
        return $media;
    }
    #endregion
}
