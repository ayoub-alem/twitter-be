<?php
// this file contain a class named user
require('../models/users.php');

$user = new User($link);

$payload = $user->jwtVerification();

$user->setUserSignupPhoto();

$payload = $user->insertPhoto($payload);

//send JWT
$user->jwtUserSend($payload, "Photo added successfully");
