<?php
// this file contain a class named user
require(__DIR__ . './../models/users.php');

$user = new User($link);

$payload = $user->jwtVerification();

$payload = $user->insertBio($payload);

//send JWT
$user->jwtUserSend($payload, "Bio added successfully");
