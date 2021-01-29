<?php
// this file contain a class named user
require(__DIR__ . './../models/users.php');

$user = new User($link);

$payload = $user->jwtVerification();

$user->getRetweetedPosts($payload);
