<?php
// this file return a link of a db connection with a variable name $db
require(__DIR__ .'./../models/users.php');

//create new user
$user = new User($link);

//set User Properties
$user->setUserLogin();

//check if the user exist using email
$user->validateLogin();

// $resultQuery = $link->insert_id;
$payload = $user->payloadUser();

//send JWT
$user->jwtUserSend($payload, "SignIn successfully");
// echo "$user->nom  $user->prenom $user->email $user->mdp $user->date_de_naissance";
