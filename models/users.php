<?php
//DDB CON
require( __DIR__ . './../config/connectionBD.php');
//JWT
require __DIR__ . './../vendor/autoload.php';

use \Firebase\JWT\JWT;

class User
{
    public $user_id;
    public $nom;
    public $prenom;
    public $email;
    public $date_de_naissance;
    public $mdp;
    public $description;
    public $link;
    public $completed;
    public $photo = "default-pic.png";


    public function __construct($link)
    {
        $this->link = $link;
    }

    public function setUserLogin()
    {
        $this->email = trim($_POST["email"]);
        $this->mdp = $_POST["mdp"];
    }

    public function insertPhoto($payload)
    {
        $this->user_id = $payload["user_id"];
        $requeteUpdate = "UPDATE users SET photo='$this->photo' WHERE user_id = '$this->user_id'";
        $result = $this->requete($requeteUpdate);
        if (!$result) res("Smahlina mabghatsh tstocka had l image", 500);
        $payload["photo"] = $this->photo;
        return $payload;
    }


    public function setUserSignupPhoto()
    {
        $photoName = $this->validateUploadingImage();
        if ($photoName) $this->photo = $photoName;
    }



    public function validateUploadingImage()
    {
        if (empty($_FILES["photo"])) return null;
        $temp_name = $_FILES["photo"]["tmp_name"];
        if ($_FILES["photo"]["error"]) return null;
        if (!is_uploaded_file($temp_name)) return null;
        if ($_FILES["photo"]["size"] > 1000000) res("Photo's size is too large abn 3emmi N9ss shwia ahbibi ...", 400);
        $this->checkPhotoExtension();
        $photoName = time() . "-" . $_FILES["photo"]["name"];
        if (!move_uploaded_file($temp_name, __DIR__ . "./../pictures/" . $photoName))  res("Smahlina an error Occured", 500);
        return $photoName;
    }

    public function checkPhotoExtension()
    {
        $infosfichier = pathinfo($_FILES["photo"]['name']);
        $extension_upload = strtolower($infosfichier['extension']);
        $extensions_autorisees = array('png', 'jpeg', 'jpg');
        if (!in_array($extension_upload, $extensions_autorisees)) res("Uploadi Image abn 3mmi ...", 400);
        return $extension_upload;
    }

    public function setUserSignUp()
    {
        $this->nom = trim($_POST["nom"]);
        $this->prenom = trim($_POST["prenom"]);
        $this->email = trim($_POST["email"]);
        $this->date_de_naissance = trim($_POST["date_de_naissance"]);
        $this->mdp = password_hash($_POST["mdp"], PASSWORD_BCRYPT);
        $this->completed = 0;
    }

    // ================  Query  database =======================
    // METHOD accept one parameter as a String Type wich is the query
    // RETURN the result as a table of rows

    public function requete(string $query)
    {
        return mysqli_query($this->link, $query);
    }



    // ================  Insert User =======================
    // METHOD accept no parameters;
    // RETURN the result of the query

    public function insert_user()
    {
        $query_insert_user = "insert into users 
            VALUES(
                DEFAULT,
                '$this->nom',
                '$this->prenom',
                '$this->email', 
                '$this->date_de_naissance', 
                '$this->mdp', 
                '$this->photo',
                NULL,
                $this->completed
            )";
        $resultQuery = $this->requete($query_insert_user);
        if (!$resultQuery) res("Something went wrong boy ...", 500);
    }

    // ================  Searching for a user by email =======================
    // METHOD accept no parameters
    // RETURN the result of the query

    public function search_user_byEmail()
    {
        $query_search_user = "select user_id, nom, prenom, email,
                              date_de_naissance, photo, description, mdp, completed
                             from users where email = '$this->email'";
        return $this->requete($query_search_user);
    }

    public function search_user_byUser_Id()
    {
        $query_search_user = "select user_id, nom, prenom, email,
                              date_de_naissance, photo, description, mdp, completed
                             from users where user_id = $this->user_id";
        return $this->requete($query_search_user);
    }



    // ================  Does_user_exist =======================
    // METHOD accept no parameters
    // In case the user exist return http_response_code = 400 *BAD REQUEST


    public function Does_user_exist()
    {
        $existance = $this->search_user_byEmail();
        $rows_number = mysqli_fetch_assoc($existance);
        if ($rows_number) res("Email already exist ...", 400);
    }

    // ================  Request validation =======================
    // METHOD accept no parameters
    // In case the request variables don't respect the requirements it response with hrc = 400 

    public function validateRequest()
    {
        $validation = (strlen(trim($_POST["nom"])) <= 50) &&
            (strlen(trim($_POST["prenom"])) <= 50) &&
            (strlen(trim($_POST["email"])) <= 100) &&
            (strlen(trim($_POST["date_de_naissance"])) <= 10) &&
            (strlen($_POST["mdp"]) >= 8);

        $existance = isset($_POST["nom"]) &&
            isset($_POST["prenom"]) &&
            isset($_POST["email"])  &&
            isset($_POST["date_de_naissance"]) &&
            isset($_POST["mdp"]);

        if (!($validation && $existance)) res('Please respect the input fields requirements', 400);
    }


    public function validateLogin()
    {
        $existance = $this->search_user_byEmail();
        if (!mysqli_num_rows($existance)) res("This email does not exist, try again boy ...", 400);
        $row = mysqli_fetch_assoc($existance);
        if (!password_verify($this->mdp, $row["mdp"])) res("Password incorrect, try again boy ...", 400);
    }


    // ================  Payload Creation =======================
    // METHOD accept no parameters
    // Return a payload for user Signup

    public function payloadUser()
    {
        $result = mysqli_fetch_assoc($this->search_user_byEmail());
        unset($result["mdp"]);
        return $result;
    }

    public function payloadUserBest()
    {
        $result = mysqli_fetch_assoc($this->search_user_byEmail());
        unset($result["mdp"]);
        return $result;
    }


    // ================  Request validation =======================
    // METHOD accept no parameters
    // send the Jwt

    public function jwtUserSend($payload, $mssg)
    {
        $jwt = JWT::encode($payload, customGetEnv("JWT_KEY"), 'HS256');
        header("x-auth-token: $jwt", true);
        res($mssg, 200);
    }

    public function jwtVerification()
    {
        try {
            $decoded = JWT::decode($_SERVER["HTTP_X_AUTH_TOKEN"], customGetEnv("JWT_KEY"), array('HS256'));
        } catch (\Throwable $th) {
            res("Signature verification failed boy ...", 401);
        }
        return  (array) $decoded;
    }

    public function insertBio($payload)
    {
        if (!isset($_POST["description"])) return $payload;
        $escapedDescription = mysqli_real_escape_string($this->link, $_POST["description"]);
        $this->description = $_POST["description"];
        $this->user_id = $payload["user_id"];
        $requeteUpdate = "UPDATE users SET description ='$escapedDescription' WHERE user_id = '$this->user_id'";
        $result = $this->requete($requeteUpdate);
        if (!$result) res("Smahlina mabghatsh tstocka, had l bio mnhussa", 500);
        $payload["description"] = $this->description;
        return $payload;
    }
    public function insertSubject($payload)
    {
        $json = json_decode(file_get_contents("php://input"));
        $sujets = (array) $json->sujets;
        if (!isset($sujets)) return $payload;
        $this->user_id = $payload["user_id"];
        $requete = $this->loopOverSujetId($sujets);
        $result = $this->requete($requete);
        if (!$result) res("Smahlina mabghawsh ytstockaw, les sujets khaybin endk eemm hhhh", 500);
        return $payload;
    }

    public function loopOverSujetId($sujets)
    {
        $requete = "INSERT INTO users_sujets VALUES ";
        for ($i = 0; $i < count($sujets); $i++) {
            $requete = $requete . "($this->user_id, $sujets[$i]),";
        }
        $requete = substr_replace($requete, "", -1) . ";";
        return $requete;
    }

    public function retrieveSuggestions($payload)
    {
        $this->user_id = $payload["user_id"];
        $requete = "SELECT user_id, photo, nom, prenom FROM users WHERE user_id != '$this->user_id'";
        $result = $this->requete($requete);
        if (!$result) res("sorry there is an error", 500);
        $result = resultInTable($result);
        res($result, 200);
    }

    public function insertSuivre($payload)
    {
        $this->user_id = $payload["user_id"];
        $followed_id = $_POST["followed_id"];
        $requete = "INSERT INTO suivre_user VALUES ($this->user_id, $followed_id)";
        $result = $this->requete($requete);
        if (!$result) res("Sorry you cannot follow this user for now !", 500);
        res("Followed succesfully", 200);
    }
    public function deleteSuivre($payload)
    {
        $this->user_id = $payload["user_id"];
        $unfollowed_id = $_POST["unfollowed_id"];
        $requete = "DELETE FROM suivre_user WHERE follower_id = $this->user_id AND followed_id = $unfollowed_id";
        $result = $this->requete($requete);
        if (!$result) res("Sorry you cannot unfollow this user for now !", 500);
        res("Unfollowed succesfully", 200);
    }

    public function insertMessage($payload)
    {
        $this->user_id = $payload["user_id"];
        $receiver_id = $_POST["receiver_id"];
        $message = $_POST["message"];
        $escapedMessage = mysqli_real_escape_string($this->link, $_POST["message"]);
        $requete = "INSERT INTO messages VALUES ($this->user_id, $receiver_id, '$escapedMessage', DEFAULT)";
        $result = $this->requete($requete);
        if (!$result) res("Sorry cannot be sent for now", 500);
        res("Message has been sent succesfully", 200);
    }


    public function insertPost($payload)
    {
        $photoName = $this->validateUploadingImage();
        $this->user_id = $payload["user_id"];
        $description_post = $_POST["description_post"];
        $escapedDescription_post = mysqli_real_escape_string($this->link, $_POST["description_post"]);
        if (!$photoName) {
            $requete = "INSERT INTO posts VALUES (DEFAULT ,$this->user_id, '$escapedDescription_post', NULL, DEFAULT, DEFAULT)";
        } else {
            $requete = "INSERT INTO posts VALUES (DEFAULT ,$this->user_id, '$escapedDescription_post', '$photoName', DEFAULT, DEFAULT)";
        }
        $result = $this->requete($requete);
        if (!$result) res("Soryy you cannot post a tweet for now", 500);
        res("Successful tweet", 200);
    }

    public function getSubjects()
    {
        $requete = "SELECT * from sujets";
        $result = $this->requete($requete);
        $resultTable = resultInTable($result);
        res($resultTable, 200);
    }
    public function getUsers($payload)
    {
        $this->user_id = $payload["user_id"];
        $requete = "SELECT user_id, nom, prenom, description, photo, IF(user_id IN (Select followed_id From suivre_user Where follower_id = $this->user_id), '1', '0') as isFollowed from users Where user_id != $this->user_id";
        $result = $this->requete($requete);
        $resultTable = resultInTable($result);
        res($resultTable, 200);
    }
    public function completeSignup($payload)
    {
        $this->user_id = $payload["user_id"];
        $requete = "UPDATE users SET completed = 1 Where user_id = $this->user_id";
        $result = $this->requete($requete);
        if (!$result) res("Refresh the page an error occured !", 500);
        $payload["completed"] = "1";
        return $payload;
    }
    public function getPosts($payload)
    {
        $this->user_id = $payload["user_id"];
        $requete = "SELECT user_id, nom, prenom, photo, post_id, post_date as compare_date, description_post, post_photo 
        FROM users JOIN posts USING(user_id) WHERE user_id IN (SELECT followed_id from suivre_user WHERE follower_id = $this->user_id) Or users.user_id = $this->user_id Order BY(compare_date) DESC";
        $result = $this->requete($requete);
        if (!$result) res("Refresh the page an error occurred !", 500);
        $resultTable = resultInTable($result);
        if ($resultTable) res($resultTable, 200);
    }

    public function getRetweetedPosts($payload)
    {
        $this->user_id = $payload["user_id"];
        $requete = "SELECT 	us.user_id , us.nom, us.prenom, rt.retweet_date as compare_date , rt.post_id, 
		orgUs.user_id as post_owner_id, p.description_post, p.post_photo, 
		p.post_date,  orgUs.nom as post_owner_nom, orgUs.prenom as post_owner_prenom,
		orgUs.photo as post_owner_photo, true as retweet
        FROM users us
        JOIN retweet rt
        ON us.user_id = rt.retweeter_id
        JOIN posts p
        ON p.post_id = rt.post_id
        JOIN users orgUs
        ON p.user_id = orgUs.user_id
        WHERE us.user_id IN (
        select followed_id from suivre_user
        WHERE follower_id = $this->user_id) Or us.user_id = $this->user_id Order BY(compare_date) DESC";
        $result = $this->requete($requete);
        if (!$result) res("Refresh the page an error occurred :=", 500);
        $resultTable = resultInTable($result);
        if ($resultTable) res($resultTable, 200);
    }

    public function retweet($payload)
    {
        $this->user_id = $payload["user_id"];
        if (!isset($_POST["post_id"])) res("Sorry the request doesn't have the post_id", 500);
        $post_id = $_POST["post_id"];
        $requete = "INSERT INTO retweet VALUES ($post_id,$this->user_id,DEFAULT)";
        $result = $this->requete($requete);
        if (!$result) res("Sorry you can not retweet the same post again, and you cannot retweet a retweeted post :)", 500);
        res("Post retweeted successfully :)", 200);
    }
}
