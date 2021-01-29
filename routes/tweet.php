<?php
// this file contain a class named user
require(__DIR__ .'./../models/users.php');

//create new user
$user = new User($link);

$payload = $user->jwtVerification();

$user->insertPost($payload);
