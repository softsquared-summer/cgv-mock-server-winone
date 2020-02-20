<?php

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
                        FROM (SELECT movieId, count(*) AS count FROM reviews where goldenEggStatus = 1 GROUP BY movieId) AS b
                        JOIN (SELECT movieId, count(*) AS count FROM reviews GROUP BY movieId) AS c
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

                    WHERE movieStatus = 1";
    }
    else if($queryString == "top"){
        $query = "SELECT a.id, a.title, a.viewAge, a.releaseDate, a.mainImg, ifnull(b.goldenEggRatio,0) AS goldenEggRatio, ifnull(c.ticketingRatio,0) AS ticketingRatio

                    FROM movies AS a

                    LEFT OUTER JOIN (
                        SELECT b.movieId AS movieId, TRUNCATE((b.count/c.count*100),0) AS goldenEggRatio
                        FROM (SELECT movieId, count(*) AS count FROM reviews where goldenEggStatus = 1 GROUP BY movieId) AS b
                        JOIN (SELECT movieId, count(*) AS count FROM reviews GROUP BY movieId) AS c
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
function movie($id){

    $pdo = pdoSqlConnect();
    $query = "SELECT a.title,a.titleEn, a.viewAge, a.releaseDate, a.runningTime, a.director, a.actors, a.description, a.genre, a.mainImg, a.subImg, a.video, ifnull(b.goldenEggRatio,0) AS goldenEggRatio, ifnull(c.ticketingRatio,0) AS ticketingRatio

                FROM movies AS a


                LEFT OUTER JOIN (
                    SELECT b.movieId AS movieId, TRUNCATE((b.count/c.count*100),0) AS goldenEggRatio
                    FROM (SELECT movieId, count(*) AS count FROM reviews where goldenEggStatus = 1 GROUP BY movieId) AS b
                    JOIN (SELECT movieId, count(*) AS count FROM reviews GROUP BY movieId) AS c
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
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $query = "SELECT actorsName,actorsEnName FROM actors WHERE movieId = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $actors = $st->fetchAll();

    $res[0]["actors"] = $actors;

    $st = null;
    $pdo = null;

    return $res[0];

}