<?php

namespace REPOSITORY;

#region REQUIRE
require_once(__DIR__ . "/../MODELS/ACCOUNT/Account.php");
require_once(__DIR__ . "/../MODELS/ACCOUNT/Media.php");
require_once(__DIR__ . "/../MODELS/ACCOUNT/User.php");
require_once(__DIR__ . "/../MODELS/ACCOUNT/Writer.php");
require_once(__DIR__ . "/../MODELS/GEOGRAPHY/City.php");
require_once(__DIR__ . "/../MODELS/GEOGRAPHY/CountryDivision.php");
require_once(__DIR__ . "/../MODELS/GEOGRAPHY/Country.php");
require_once(__DIR__ . "/../MODELS/CORE/DatabaseConnection.php");
require_once(__DIR__ . "/../MODELS/CORE/Geolocation.php");
require_once(__DIR__ . "/../config.php");
#endregion

#region USE
use MODELS\ACCOUNT\Account;
use MODELS\ACCOUNT\User;
use MODELS\ACCOUNT\Writer;
use MODELS\ACCOUNT\Media;
use MODELS\GEOGRAPHY\City;
use MODELS\GEOGRAPHY\CountryDivision;
use MODELS\GEOGRAPHY\Country;
use MODELS\CORE\DatabaseConnection;
use MODELS\CORE\Geolocation;
use Exception;
#endregion

//TODO: Change the reference bind param to a variable
class RepoAccount
{
    #region FIELDS
    private $db;
    #endregion

    #region CONSTRUCTOR
    public function __construct()
    {
        $this->db = new DatabaseConnection();
    }
    #endregion

    #region LOGIN
    public function login(string $username, string $password)
    {
        $sqlQuery = "SELECT
            a.username,
            a.password,
            a.fullname,
            a.email,
            a.role,
            a.is_active,
            a.locked_until,
            a.profile_picture_ext,

            u.birthdate         AS user_birthdate,
            u.gender            AS user_gender,
            u.phone_number      AS user_phone_number,
            u.biography         AS user_biography,

            cu.id               AS user_country_id,
            cu.code             AS user_country_code,
            cu.name             AS user_country_name,
            cu.telephone        AS user_country_telephone,

            w.biography         AS writer_biography,
            w.is_verified       AS writer_is_verified,

            m.id                AS media_id,
            m.name              AS media_name,
            m.slug              AS media_slug,
            m.company_name      AS media_company_name,
            m.media_type        AS media_type,
            m.picture_ext       AS media_picture_ext,
            m.logo_ext          AS media_logo_ext,
            m.website           AS media_website,
            m.email             AS media_email,
            m.description       AS media_description,

            ct.id               AS media_city_id,
            ct.name             AS media_city_name,
            ct.latitude         AS media_city_latitude,
            ct.longitude        AS media_city_longitude,

            cd.id               AS media_country_division_id,
            cd.name             AS media_country_division_name,

            cm.id               AS media_country_id,
            cm.code             AS media_country_code,
            cm.name             AS media_country_name

        FROM accounts a
        LEFT JOIN users u
            ON u.username = a.username AND a.role = 'user'
        LEFT JOIN countries cu
            ON cu.id = u.country_id
        LEFT JOIN writers w
            ON w.username = a.username AND a.role = 'writer'
        LEFT JOIN medias m
            ON m.id = w.media_id
        LEFT JOIN cities ct
            ON ct.id = m.city_id
        LEFT JOIN country_divisions cd
            ON cd.id = ct.country_division_id
        LEFT JOIN countries cm
            ON cm.id = cd.country_id
        WHERE a.username = ?
        LIMIT 1
    ";

        try {
            $connection = $this->db->connect();
            $stmt = $connection->prepare($sqlQuery);

            if (!$stmt) {
                throw new Exception($connection->error);
            }

            $stmt->bind_param("s", $username);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if (!$row || (!password_verify($password, $row['password']))) {
                throw new Exception("Invalid username or password");
            }
            if ($row['locked_until'] !== null && strtotime($row['locked_until']) > time()) {
                throw new Exception("Account is temporarily locked");
            }

            // Success retrieving account
            if (strtoupper($row['role']) === ACCOUNT_ROLES[0]) {
                return $this->mapSQLResultToUser($row);
            }
            if (strtoupper($row['role']) === ACCOUNT_ROLES[1]) {
                return $this->mapSQLResultToWriter($row);
            }
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

    #region CREATE ACCOUNT
    private function createAccount(Account $account, string $hashedPassword, $conn): bool
    {
        $sql = "INSERT INTO accounts (
                    username,
                    password,
                    fullname,
                    email,
                    role,
                    profile_picture_ext
                )
                VALUES (?, ?, ?, ?, ?, ?)";
        try {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare account insert");
            }

            //tambah
            $username = $account->getUsername();
            $fullname = $account->getFullname();
            $email = $account->getEmail();
            $role = $account->getRole();
            $picExt = $account->getProfilePictureExtension();

            $stmt->bind_param(
                "ssssss",
                $username,
                $hashedPassword,
                $fullname,
                $email,
                $role,
                $picExt
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create account: {$stmt->error}");
            }
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            throw $e;
        } finally {
            $stmt->close();
        }
    }
    #endregion
    #region CREATE USER
    public function createUser(User $user, string $hashedPassword): bool
    {
        $conn = $this->db->connect();
        $stmt = null;

        try {
            $conn->begin_transaction();

            $this->createAccount($user, $hashedPassword, $conn);

            $sql = "INSERT INTO users (
                        username,
                        birthdate,
                        gender,
                        phone_number,
                        biography,
                        country_id
                    )
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare user insert");
            }

            $username = $user->getUsername();
            $birthdate = $user->getBirthdate();
            $gender = $user->getGender();
            $phone = $user->getPhoneNumber();
            $biography = $user->getBiography();
            $profile_picture_ext = $user->getProfilePictureExtension();
            //nambah country
            $countryId = null;
            if ($user->getCountry()) {
                $countryId = $user->getCountry()->getId();
            } else {
                 throw new Exception("Country data is missing for user creation");
            }

            $stmt->bind_param(
                "sssssi",
                $username,
                $birthdate,
                $gender,
                $phone,
                $biography,
                $countryId
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create user profile: {$stmt->error}");
            }
            $conn->commit();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            $conn->rollback();
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
    #region CREATE WRITER
    public function createWriter(Writer $writer, string $hashedPassword): bool
    {
        $conn = $this->db->connect();
        $stmt = null;

        try {
            $conn->begin_transaction();

            $this->createAccount($writer, $hashedPassword, $conn);

            $sql = "INSERT INTO writers (
                        username,
                        biography,
                        is_verified,
                        media_id
                    )
                    VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare writer insert");
            }

            $username = $writer->getUsername();
            $biography = $writer->getBiography();
            $isVerified = $writer->isVerified() ? 1 : 0;
            $mediaId = $writer->getMedia()->getId();

            $stmt->bind_param(
                "ssii",
                $username,
                $biography,
                $isVerified,
                $mediaId
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create writer profile: {$stmt->error}");
            }

            $conn->commit();
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            $conn->rollback();
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

    #region UPDATE ACCOUNT
    private function updateAccount(Account $account, $conn): void
    {
        $stmt = null;

        try {
            $sql = "UPDATE accounts
                SET fullname = ?,
                    email = ?,
                    profile_picture_address = ?
                WHERE username = ?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare account update");
            }

            $stmt->bind_param(
                "ssss",
                $account->getFullname(),
                $account->getEmail(),
                $account->getProfilePictureAddress(),
                $account->getUsername()
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to update account: {$stmt->error}");
            }
        } finally {
            if ($stmt) {
                $stmt->close();
            }
        }
    }
    #endregion
#region UPDATE USER
    public function updateUser(User $user): bool
    {
        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $conn->begin_transaction();

            $this->updateAccount($user, $conn);

            $sql = "UPDATE users
                SET birthdate = ?,
                    gender = ?,
                    phone_number = ?,
                    biography = ?,
                    profile_picture_ext = ?,
                    country_id = ?
                WHERE username = ?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare user update");
            }

            $countryId = $user->getCountry() ? $user->getCountry()->getId() : null;

            $stmt->bind_param(
                "sssssis",
                $user->getBirthdate(),
                $user->getGender(),
                $user->getPhoneNumber(),
                $user->getBiography(),
                $user->getProfilePictureExtension(),
                $countryId,
                $user->getUsername()
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to update user: {$stmt->error}");
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            if ($conn) {
                $conn->rollback();
            }
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
#region UPDATE WRITER
    public function updateWriter(Writer $writer): bool
    {
        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();
            $conn->begin_transaction();

            $this->updateAccount($writer, $conn);

            $sql = "UPDATE writers
                SET biography = ?,
                    is_verified = ?,
                    media_id = ?
                WHERE username = ?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare writer update");
            }

            $stmt->bind_param(
                "siis",
                $writer->getBiography(),
                $writer->isVerified() ? 1 : 0,
                $writer->getMedia()->getId(),
                $writer->getUsername()
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to update writer: {$stmt->error}");
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            if ($conn) {
                $conn->rollback();
            }
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
#region DELETE ACCOUNT
    public function deleteAccount(string $username): bool
    {
        $conn = null;
        $stmt = null;

        try {
            $conn = $this->db->connect();

            $sql = "DELETE FROM accounts WHERE username = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to prepare account delete");
            }

            $stmt->bind_param("s", $username);

            if (!$stmt->execute()) {
                throw new Exception("Failed to delete account: {$stmt->error}");
            }

            return $stmt->affected_rows > 0;
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
    private function mapSQLResultToUser(array $row): User
    {
        $country = null;
        if ($row['user_country_id']) {
            $country = (new Country())
                ->setId((int) $row['user_country_id'])
                ->setCode($row['user_country_code'])
                ->setName($row['user_country_name'])
                ->setTelephone($row['user_country_telephone']);
        }

        $user = new User();
        $user->setUsername($row['username']);
        $user->setFullname($row['fullname']);
        $user->setEmail($row['email']);
        $user->setRole($row['role']);
        if (!empty($row['profile_picture_ext'])) {
            $fullPath = IMAGE_DATABASE_ADDRESS . "USERS/" . $row['username'] . "." . $row['profile_picture_ext'];
            $user->setProfilePictureAddress($fullPath);
        } else {
            $user->setProfilePictureAddress(IMAGE_DATABASE_ADDRESS . "default.png");
        }
        $user->setBirthdate($row['user_birthdate']);
        $user->setGender(strtoupper($row['user_gender']));
        $user->setPhoneNumber($row['user_phone_number']);
        $user->setBiography($row['user_biography']);
        $user->setCountry($country);

        return $user;
    }
    private function mapSQLResultToWriter(array $row): Writer
    {
        $country = (new Country())
            ->setId((int) $row['media_country_id'])
            ->setCode($row['media_country_code'])
            ->setName($row['media_country_name']);

        $countryDivision = (new CountryDivision())
            ->setId((int) $row['media_country_division_id'])
            ->setName($row['media_country_division_name'])
            ->setCountry($country);

        $city = (new City())
            ->setId((int) $row['media_city_id'])
            ->setName($row['media_city_name'])
            ->setGeolocation(
                new Geolocation(
                    (float) $row['media_city_latitude'],
                    (float) $row['media_city_longitude']
                )
            )
            ->setCountryDivision($countryDivision);

        $media = (new Media())
            ->setId((int) $row['media_id'])
            ->setName($row['media_name'])
            ->setSlug($row['media_slug'])
            ->setCompanyName($row['media_company_name'])
            ->setType($row['media_type'])
            ->setWebsite($row['media_website'])
            ->setEmail($row['media_email'])
            ->setDescription($row['media_description'])
            ->setCity($city);

        $writer = new Writer();
        $writer->setUsername($row['username']);
        $writer->setFullname($row['fullname']);
        $writer->setEmail($row['email']);
        $writer->setRole($row['role']);
       if (!empty($row['profile_picture_ext'])) {
            $fullPath = IMAGE_DATABASE_ADDRESS . "WRITER/" . $row['username'] . "." . $row['profile_picture_ext'];
            $writer->setProfilePictureAddress($fullPath); 
        } else {
            $writer->setProfilePictureAddress(IMAGE_DATABASE_ADDRESS . "default.png");
        }
        $writer->setBiography($row['writer_biography']);
        $writer->setIsVerified((bool) $row['writer_is_verified']);
        $writer->setMedia($media);
        return $writer;
    }

    #endregion
}