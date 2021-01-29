<?php
// this file contain a class named user
require(__DIR__ .'./../models/users.php');

$user = new User($link);

$payload = $user->jwtVerification();

$payload = $user->completeSignup($payload);

$user->jwtUserSend($payload, "SignUp completed successfully. Welcome !");

