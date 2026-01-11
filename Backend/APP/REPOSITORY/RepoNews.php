<?php

namespace REPOSITORY;

#region REQUIRE
require_once(__DIR__ . "/../MODELS/NEWS/News.php");
require_once(__DIR__ . "/../MODELS/ACCOUNT/Writer.php");
require_once(__DIR__ . "/../MODELS/ACCOUNT/Media.php");
require_once(__DIR__ . "/../MODELS/GEOGRAPHY/City.php");
require_once(__DIR__ . "/../MODELS/GEOGRAPHY/Country.php");
require_once(__DIR__ . "/../MODELS/GEOGRAPHY/CountryDivision.php");
require_once(__DIR__ . "/../MODELS/CORE/DatabaseConnection.php");
#endregion

#region USE
use MODELS\NEWS\News;
use MODELS\ACCOUNT\Writer;
use MODELS\ACCOUNT\Media;
use MODELS\GEOGRAPHY\City;
use MODELS\GEOGRAPHY\Country;
use MODELS\GEOGRAPHY\CountryDivision;
use MODELS\CORE\DatabaseConnection;
use Exception;
#endregion

class RepoNews extends DatabaseConnection
{
    #region FIELDS
    public DatabaseConnection $db;
    #endregion

    #region CONSTRUCT
    public function __construct()
    {
        $this->db = new DatabaseConnection();
    }
    #endregion
    #region RETRIEVE

    public function findNewsById(int $id): ?News
    {
        $sql = "SELECT
                    n.`id` AS 'news_id',
                    n.`title` AS 'news_title',
                    n.`slug` AS 'news_slug',
                    n.`content` AS 'news_content',
                    n.`view_count` AS 'news_views',
                    ctg.`id` AS 'category_id',
                    ctg.`name` AS 'category_name',
                    ct.`id` AS 'city_id',
                    ct.`name` AS 'city_name',
                    cd.`name` AS 'country_division_name',
                    cm.`name` AS 'country_name',
                    m.`id` AS 'media_id',
                    m.`name` AS 'media_name',
                    m.`slug` AS 'media_slug',
                    m.`logo_ext` AS 'media_logo_ext',
                    w.`username` AS 'writer_username',
                    a.`fullname` AS 'writer_fullname'
                FROM
                    news n
                    INNER JOIN `categories` ctg ON n.`category_id` = ctg.`id`
                    LEFT JOIN `cities` ct ON n.`city_id` = ct.`id`
                    LEFT JOIN `country_divisions` cd ON ct.`country_division_id` = cd.`id`
                    LEFT JOIN `countries` cm ON cd.`country_id` = cm.`id`
                    LEFT JOIN `medias` m ON n.`media_id` = m.`id`
                    LEFT JOIN `writers` w ON n.`writer_username` = w.`username`
                    INNER JOIN `accounts` a ON w.`username` = a.`username`
                WHERE n.`id` = ?;
        ";
        $connection = null;
        $stmt = null;
        try {
            $connection = $this->db->connect();
            $stmt = $connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare findNewsById query");
            }

            $stmt->bind_param("i", $id);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $news = $this->mapSQLResultToNewsObject($row);
            return $news;
        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
            if ($connection) {
                $connection->close();
            }
        }
    }
    #endregion

    #region CREATE
    public function CreateNews(News $news): bool
    {
        $sql = "INSERT INTO `news` (
                    `writer_username`,
                    `media_id`,
                    `category_id`,
                    `city_id`,
                    `title`,
                    `slug`,
                    `content`
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ";
        $connection = null;
        $stmt = null;

        try {
            $connection = $this->db->connect();

            $stmt = $connection->prepare($sql);
            if (!$stmt) {
                throw new Exception(
                    "Failed to prepare createNews query: " . $connection->error
                );
            }

            $arr_news = $news->toArray();

            $mediaId = $arr_news['media']['id'] ?? null;

            $stmt->bind_param(
                "siiisss",
                $arr_news['author']['username'],
                $mediaId,
                $arr_news['category']['id'],
                $arr_news['city']['id'],
                $arr_news['title'],
                $arr_news['slug'],
                $arr_news['content']
            );

            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                return false;
            }

            return true;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
            if ($connection) {
                $connection->close();
            }
        }
    }

    #endregion

    #region UPDATE
    public function updateNews(News $news): bool
    {
        $sql = "UPDATE `news`
                SET
                    `writer_username` = ?,
                    `media_id` = ?,
                    `category_id` = ?,
                    `city_id` = ?,
                    `title` = ?,
                    `slug` = ?,
                    `content` = ?
                WHERE `id` = ?
            ";
        $connection = null;
        $stmt = null;

        try {
            $connection = $this->db->connect();

            $stmt = $connection->prepare($sql);
            if (!$stmt) {
                throw new Exception(
                    "Failed to prepare createNews query: " . $connection->error
                );
            }

            $arr_news = $news->toArray();

            $mediaId = $arr_news['media']['id'] ?? null;

            $stmt->bind_param(
                "siiisssi",
                $arr_news['author']['username'],
                $mediaId,
                $arr_news['category']['id'],
                $arr_news['city']['id'],
                $arr_news['title'],
                $arr_news['slug'],
                $arr_news['content'],
                $arr_news['id']
            );

            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                return false;
            }

            return true;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
            if ($connection) {
                $connection->close();
            }
        }
    }
    #endregion

    #region DELETE
    public function deleteNews(int $news_id): bool
    {
        $sql = "DELETE FROM `news`
                    WHERE `id` = ?
                ";

        $connection = null;
        $stmt = null;

        try {
            $connection = $this->db->connect();

            $stmt = $connection->prepare($sql);
            if (!$stmt) {
                throw new Exception(
                    "Failed to prepare createNews query: " . $connection->error
                );
            }
            $stmt->bind_param(
                "i", $news_id
            );
            $stmt->execute();
            if ($stmt->affected_rows !== 1) {
                return false;
            }
            return true;

        } catch (Exception $e) {
            throw $e;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
            if ($connection) {
                $connection->close();
            }
        }
    }

    #endregion

    #region MAPPER

    private function mapSQLResultToNewsObject(array $row): News
    {
        $news = new News();
        $city = new City();
        $country = new Country();
        $country_division = new CountryDivision();
        $media = new Media();
        $writer = new Writer();
        $country->setName($row['country_name']);
        $country_division->setName($row['country_division_name']);
        $country_division->setCountry($country);
        $city->setId($row["city_id"]);
        $city->setName($row["city_name"]);
        $city->setCountryDivision($country_division);
        $writer->setUsername($row['writer_username']);
        $writer->setFullname($row['writer_fullname']);
        $media->setId($row['media_id']);
        $media->setName($row['media_name']);
        $media->setSlug($row['media_slug']);
        $media->createLogoAddressFromExt($row['media_logo_ext']);
        $news->setId($row['news_id']);
        $news->setTitle($row['news_title']);
        $news->setSlug($row['news_slug']);
        $news->setContent($row['news_content']);
        $news->setViewCount($row['news_views']);
        $news->setCategory($row['category_name']);
        $news->setCity($city);
        $news->setAuthor($writer);
        return $news;
    }

    private function mapSQLResultToNewsThumbnail(array $row): News
    {
        $news = new News();
        return $news;
    }
    #endregion
}
