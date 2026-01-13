<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../APP/config.php';
require_once '../APP/MODELS/ACCOUNT/User.php';
require_once '../APP/MODELS/GEOGRAPHY/Country.php';
require_once '../APP/REPOSITORY/RepoAccount.php';
require_once '../APP/REPOSITORY/RepoCountry.php';

use REPOSITORY\RepoAccount;
use MODELS\ACCOUNT\User;
use MODELS\GEOGRAPHY\Country;
use REPOSITORY\RepoCountry;

$input = json_decode(file_get_contents('php://input'), true);

function sendResponse($status, $message, $data = null) {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    $user = new User();
    if (isset($input['username'])) $user->setUsername($input['username']);
    if (isset($input['fullname'])) $user->setFullname($input['fullname']);
    if (isset($input['email'])) $user->setEmail($input['email']);
    $user->setRole('USER'); 
    $defaultPic = isset($input['profile_picture_address']) ? $input['profile_picture_address'] : "";
    $user->setProfilePictureAddress($defaultPic);
    if (isset($input['birthdate'])) $user->setBirthdate($input['birthdate']);
    if (isset($input['phone_number'])) $user->setPhoneNumber($input['phone_number']);
    if (isset($input['gender'])) $user->setGender($input['gender']);
    if (isset($input['biography'])) $user->setBiography($input['biography']);
    if (isset($input['country_id'])) {
        $repoCountry = new RepoCountry();
        $foundCountry = $repoCountry->findCountryById((int)$input['country_id']);
        if ($foundCountry) {
            $user->setCountry($foundCountry);
        } else {
            throw new Exception("Country ID " . $input['country_id'] . " Not Found");
        }
    } else {
        throw new Exception("Country ID should field!");
    }

    if (empty($input['password'])) {
        throw new Exception("Password cannot be empty");
    }
    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
    $repo = new RepoAccount();
    if ($repo->createUser($user, $hashedPassword)) {
        sendResponse('success', 'User berhasil didaftarkan', $user->toArray());
    } else {
        sendResponse('error', 'Gagal mendaftarkan user (mungkin username/email duplikat)');
    }

} catch (Exception $e) {
    http_response_code(400);
    sendResponse('error', $e->getMessage());
}
?>