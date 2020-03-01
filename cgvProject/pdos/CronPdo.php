<?php
require 'function.php';
require '/var/www/html/api-testserver/pdos/DatabasePdo.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));

function cronTab(){
    $pdo = pdoSqlConnect();
    /*$query = "SELECT fcmToken
                FROM (
                    SELECT userId
                      FROM (
                            SELECT TIME_TO_SEC(startTime) - TIME_TO_SEC(CURTIME()) AS timeDiffer, movieId
                              FROM current_movies
                            ) AS a
                              LEFT OUTER JOIN ticketing t ON t.movieId = a.movieId
                             WHERE a.timeDiffer < 600 AND a.timeDiffer > 0
                    ) AS b LEFT OUTER JOIN users u ON b.userId = u.userId";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();*/

    $checkQuery = "UPDATE ticketing
                 SET isWatched = 1
               WHERE userId = 'gangnam9' AND movieId = 3";


    $st = $pdo->prepare($checkQuery);
    $st->execute();


    $st=null;
    $pdo = null;
    //return $res;
}

cronTab();