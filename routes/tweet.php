<?php
// this file contain a class named user
require(__DIR__ . './../models/users.php');


$options = array(
  'cluster' => 'eu',
  'useTLS' => true
);
$pusher = new Pusher\Pusher(
  '0b3634e12be4cfa3970b',
  'e32c114afcdbba71d4b9',
  '1133711',
  $options
);



//create new user
$user = new User($link);

$payload = $user->jwtVerification();


$lastInsertedPost = $user->insertPost($payload);

$ids_list = $user->getFollowedUsers($payload);

$prenom = $payload["prenom"];
$nom = $payload["nom"];


$data['message'] = "$prenom $nom just tweeted, go to top of flux to see it !";
$data['post'] = $lastInsertedPost;

$pusher->trigger($ids_list, 'new_tweet', $data);

res($lastInsertedPost, 200);
