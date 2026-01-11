<?php

namespace MODELS\ACCOUNT;

#region REQUIRE
require_once(__DIR__ . "/Account.php");
require_once(__DIR__ . "/../GEOGRAPHY/Country.php");
require_once(__DIR__ . "/../CORE/Geolocation.php");
#endregion

#region USE
use MODELS\ACCOUNT\Account;
use MODELS\CORE\Geolocation;
use MODELS\GEOGRAPHY\Country;
use Exception;
#endregion

class User extends Account
{
    #region FIELDS
    private string $birthdate;
    private string $phone_number;
    private string $gender;
    private string $biography;
    private Geolocation $current_location;
    private Country $country;
    #endregion

    #region CONSTRUCTOR
    public function __construct(
    ) {
        parent::__construct();
    }
    #endregion

    #region GETTERS
    public function getBirthdate(): string
    {
        return $this->birthdate;
    }

    public function getPhoneNumber(): string
    {
        return $this->phone_number;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function getBiography(): string
    {
        return $this->biography;
    }

    public function getCurrentLocation(): Geolocation
    {
        return $this->current_location;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }
    #endregion

    #region SETTERS
    public function setBirthdate(string $birthdate): self
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate))
            throw new Exception("User Birthdate must be in YYYY-MM-DD format");

        if (strtotime($birthdate) >= time())
            throw new Exception("User Birthdate must be in the past");

        $this->birthdate = $birthdate;
        return $this;
    }

    public function setPhoneNumber(string $phone_number): self
    {
        $phone_number = preg_replace('/\s+/', '', $phone_number);
        if (!preg_match('/^[0-9]{8,15}$/', $phone_number))
            throw new Exception("User Phone number must contain 8–15 digits");
        $this->phone_number = $phone_number;
        return $this;
    }

    public function setGender(string $gender): self
    {
        if (!in_array($gender, USER_GENDERS, true))
            throw new Exception("User has an invalid gender value");
        $this->gender = $gender;
        return $this;
    }

    public function setBiography(string $biography): self
    {
        $biography = trim($biography);
        if (strlen($biography) > 500)
            throw new Exception("User Biography must not exceed 500 characters");
        $this->biography = $biography;
        return $this;
    }

    public function setCurrentLocation(Geolocation $location): self
    {
        if (!($location instanceof Geolocation))
            throw new Exception("User current_location must be an instance of Geolocation");
        $this->current_location = $location;
        return $this;
    }

    public function setCountry(Country $country): self
    {
        $this->country = $country;
        return $this;
    }
    #endregion

    #region UTILITIES
    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                "birthdate" => $this->birthdate,
                "phone_number" => $this->phone_number,
                "gender" => $this->gender,
                "biography" => $this->biography,
                "current_location" => $this->current_location->toArray(),
                "country" => $this->country->toArray()
            ]
        );
    }
    #endregion
}
