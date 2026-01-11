<?php

namespace MODELS\ACCOUNT;

#region REQUIRE
require_once(__DIR__ . "/../GEOGRAPHY/City.php");
require_once(__DIR__ . "/../../config.php");
#endregion

#region USE
use MODELS\GEOGRAPHY\City;
use Exception;
#endregion

class Account
{
    #region FIELDS
    protected string $username;
    protected string $fullname;
    protected string $email;
    protected string $role;
    protected string $profile_picture_address;
    #endregion

    #region CONSTRUCTOR
    public function __construct()
    {
    }
    #endregion

    #region GETTER
    public function getUsername(): string
    {
        return $this->username;
    }
    public function getFullname(): string
    {
        return $this->fullname;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getRole(): string
    {
        return $this->role;
    }
    public function getProfilePictureAddress(): string
    {
        return $this->profile_picture_address;
    }

        public function getProfilePictureExtension(): string
    {
        if (empty($this->profile_picture_address)) {
            return '';
        }
        return strtolower(pathinfo($this->profile_picture_address, PATHINFO_EXTENSION));
    }
    #endregion

    #region SETTER
    public function setUsername(string $username)
    {
        $username = trim($username);

        if ($username === '') {
            throw new Exception("Account Username cannot be empty");
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            throw new Exception("Account Username may contain letters, numbers, and underscores only");
        }

        if (strlen($username) < 6 || strlen($username) > USERNAME_MAX_LENGTH) {
            throw new Exception("Account Username length must be between 6 and " . USERNAME_MAX_LENGTH);
        }

        $this->username = $username;
    }

    public function setFullname(string $fullname)
    {
        $fullname = trim($fullname);

        if (strlen($fullname) < 3 || strlen($fullname) > 100) {
            throw new Exception("Account Full name must be between 3 and 100 characters");
        }

        $this->fullname = $fullname;
    }

    public function setEmail(string $email)
    {
        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Account Invalid email format");
        }

        $this->email = $email;
    }

    public function setRole(string $role)
    {
        if (!in_array($role, ACCOUNT_ROLES, true)) {
            throw new Exception("Account Invalid account role");
        }

        $this->role = $role;
    }

    public function setProfilePictureAddress(string $address)
    {
        $address = trim($address);

        if ($address === '') {
            throw new Exception("Account Profile picture address cannot be empty");
        }

        $this->profile_picture_address = $address;
    }
    #endregion
    
    #region UTILITIES
    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'fullname' => $this->fullname,
            'email' => $this->email,
            'role' => $this->role,
            'profile_picture_address' => $this->profile_picture_address
        ];
    }
    #endregion
}
