<?php
// this file return a link of a db connection with a variable name $db
require(__DIR__ . './../models/users.php');

//create new user
$user = new User($link);

//Set user Properties
$user->setUserSignUp();

//check if the user exist using email
$user->Does_user_exist();

//validating the request variables
$user->validateRequest();

//inserting the user into db
$resultQuery = $user->insert_user();

//generate the payload
$payload = $user->payloadUser();

//send JWT
$user->jwtUserSend($payload, "SignUp successfully");
