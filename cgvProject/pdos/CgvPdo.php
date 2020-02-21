<?php
/*API NO.2*/
function createUser($userId, $pw, $email, $userName, $sexStatus, $ageStatus)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO users (userId, pw, email, userName, sexStatus, ageStatus) VALUES (?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userId, $pw, $email, $userName, $sexStatus, $ageStatus]);
    $st = null;
    $pdo = null;

}

/*API NO.3*/
function movieList($queryString){
    $pdo = pdoSqlConnect();
    $query = "";

    if(!$queryString){
        $query = "SELECT a.id, a.title, a.viewAge, a.releaseDate, a.mainImg, ifnull(b.goldenEggRatio,0) AS goldenEggRatio, ifnull(c.ticketingRatio,0) AS ticketingRatio
                    FROM movies AS a
                    LEFT OUTER JOIN (
                                    SELECT b.movieId AS movieId, TRUNCATE((b.count/c.count*100),0) AS goldenEggRatio
                                    FROM (
                                          SELECT movieId, count(*) AS count 
                                            FROM reviews 
                                           WHERE goldenEggStatus = 1 
                                           GROUP BY movieId) AS b
                                            JOIN (
                                                  SELECT movieId, count(*) AS count 
                                                    FROM reviews 
                                                   GROUP BY movieId) AS c
                                              ON b.movieId = c.movieId
                                    ) AS b
                                  ON a.id = b.movieId

                    LEFT OUTER JOIN (
                                    SELECT a.id AS movieId,TRUNCATE(c.ticketingCount/c.totalCount*100,1) AS ticketingRatio
                                      FROM movies AS a
                                      JOIN (
                                            SELECT movieId, count(*) AS ticketingCount,(SELECT count(*) FROM ticketing) AS totalCount 
                                              FROM ticketing 
                                             GROUP BY movieId) AS c
                                        ON a.id = c.movieId
                                     ) AS c
                                  ON a.id = c.movieId
                    WHERE movieStatus = 1";
    }
    else if($queryString == "top"){
        $query = "SELECT a.id, a.title, a.viewAge, a.releaseDate, a.mainImg, ifnull(b.goldenEggRatio,0) AS goldenEggRatio, ifnull(c.ticketingRatio,0) AS ticketingRatio
                    FROM movies AS a
                    LEFT OUTER JOIN (
                                     SELECT b.movieId AS movieId, TRUNCATE((b.count/c.count*100),0) AS goldenEggRatio
                                       FROM (
                                             SELECT movieId, count(*) AS count 
                                               FROM reviews WHERE goldenEggStatus = 1 
                                              GROUP BY movieId) AS b
                                       JOIN (
                                             SELECT movieId, count(*) AS count 
                                               FROM reviews 
                                              GROUP BY movieId) AS c
                                         ON b.movieId = c.movieId
                                      ) AS b
                                   ON a.id = b.movieId
                    LEFT OUTER JOIN (
                                     SELECT a.id AS movieId,TRUNCATE(c.ticketingCount/c.totalCount*100,1) AS ticketingRatio
                                       FROM movies AS a
                                       JOIN (
                                             SELECT movieId, count(*) AS ticketingCount,(SELECT count(*) FROM ticketing) AS totalCount FROM ticketing GROUP BY movieId) AS c
                                         ON a.id = c.movieId
                                            ) AS c
                                 ON a.id = c.movieId
                   WHERE movieStatus = 1
                   ORDER BY c.ticketingRatio DESC";
    }

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

/*API NO.4*/
function movie($movieId){

    $pdo = pdoSqlConnect();
    $query = "SELECT a.title,a.titleEn, a.viewAge, a.releaseDate, a.runningTime, a.director, a.description, a.genre, a.mainImg, a.subImg, a.video, ifnull(b.goldenEggRatio,0) AS goldenEggRatio, ifnull(c.ticketingRatio,0) AS ticketingRatio
                FROM movies AS a
                LEFT OUTER JOIN (
                                SELECT b.movieId AS movieId, TRUNCATE((b.count/c.count*100),0) AS goldenEggRatio
                                  FROM (
                                        SELECT movieId, count(*) AS count 
                                          FROM reviews 
                                         WHERE goldenEggStatus = 1 
                                         GROUP BY movieId) AS b
                                  JOIN (
                                        SELECT movieId, count(*) AS count 
                                          FROM reviews 
                                         GROUP BY movieId) AS c
                                   ON b.movieId = c.movieId
                                ) AS b
                              ON a.id = b.movieId
                              
                LEFT OUTER JOIN (
                    SELECT a.id AS movieId,TRUNCATE(c.ticketingCount/c.totalCount*100,1) AS ticketingRatio
                    FROM movies AS a
                    JOIN (SELECT movieId, count(*) AS ticketingCount,(SELECT count(*) FROM ticketing) AS totalCount FROM ticketing GROUP BY movieId) AS c
                    ON a.id = c.movieId
                ) AS c

                    ON a.id = c.movieId

                WHERE a.id = ?
                ";

    $st = $pdo->prepare($query);
    $st->execute([$movieId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $query = "SELECT actorsName,actorsEnName 
                FROM actors 
               WHERE movieId = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$movieId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $actors = $st->fetchAll();

    $res[0]["actors"] = $actors;

    $st = null;
    $pdo = null;

    return $res[0];
}
/*API NO.5*/
function moviePost($title,$titleEn,$genre, $specialStatus,$description, $director, $runningTime, $mainImg,$subImg, $movieStatus, $viewAge, $video, $releaseDate,$actors)
{

    $pdo = pdoSqlConnect();



    $query = "INSERT INTO movies (title, titleEn, genre, specialStatus, description, director, runningTime, mainImg, subImg, movieStatus, viewAge, video, releaseDate) 
                   VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$title,$titleEn,$genre, $specialStatus,$description, $director, $runningTime, $mainImg,$subImg, $movieStatus, $viewAge, $video, $releaseDate]);

    //getMaxId

    $getMaxId = "SELECT max(id) as maxId 
                   from movies;";
    $getId = $pdo->prepare($getMaxId);
    $getId->execute();
    $getId->setFetchMode(PDO::FETCH_ASSOC);
    $res = $getId->fetchAll();

    $movieId = $res[0]["maxId"];

    for($i=0; $i<count($actors); $i++) {
        $actorsQuery = "INSERT INTO actors (movieId,actorsName,actorsEnName) 
                           VALUES (?,?,?);";
        $castSt = $pdo->prepare($actorsQuery);
        $castSt->execute([$movieId, $actors[$i]->actorsName, $actors[$i]->actorsEnName]);
    }
    $st = null;
    $pdo = null;
}

/*API NO.6*/
function movieDelete($movieId)
{
    $pdo = pdoSqlConnect();

    $query = "DELETE FROM movies 
               WHERE id = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$movieId]);

    $query = "DELETE FROM actors 
               WHERE movieId = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$movieId]);

    $st = null;
    $pdo = null;

}