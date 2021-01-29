<?php
//ENV 
require __DIR__ . './../vendor/autoload.php';
//CORS and exposing x-auth-token header 
require(__DIR__  . '/cors.php');

//dotENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

header("Content-Type: application/json");

//getENV
function customGetEnv($name)
{
    return $_ENV[$name];
}


class Database
{

    // ================= Database Configurations ==================

    private $host;
    private $dbUserName;
    private $dbPass;
    private $dbName;

    public function __construct()
    {
        $this->host = customGetEnv("HOST_NAME");
        $this->dbUserName = customGetEnv("DB_USER_NAME");
        $this->dbPass = customGetEnv("DB_PASS");
        $this->dbName = customGetEnv("DB_NAME");
    }


    // ================  Connect to database =======================
    // METHOD accept no parameters
    // RETURN Link of db 

    public function connect()
    {
        $link = @mysqli_connect($this->host, $this->dbUserName, $this->dbPass, $this->dbName);
        if (!$link) {
            http_response_code(500);
            echo json_encode("Something went wrong ...");
            exit();
        }
        return $link;
    }


    // ================  storeResult in an associative array =======================
    // METHOD accept one parameter wich is the result of the query 
    // RETURN the result as an assc array

}

function resultInTable($result)
{
    $array = array();
    while ($data = mysqli_fetch_assoc($result)) {
        array_push($array, $data);
    }
    return $array;
}

// Custom response

function res($input, int $code)
{
    http_response_code($code);
    echo json_encode($input);
    exit();
}


// ================= connect to Database ==================

$db = new Database();
$link = $db->connect();
