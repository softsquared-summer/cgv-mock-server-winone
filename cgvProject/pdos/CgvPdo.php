<?php
/*API NO.2*/
function createUser($userId, $pw, $email, $userName, $sex, $birth)
{
    $pdo = pdoSqlConnect();
    //$hash  = password_hash($pw, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (userId, pw, email, userName, sex, birth) VALUES (?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userId, $pw, $email, $userName, $sex, $birth]);
    $st = null;
    $pdo = null;

}

/*API NO.3*/
function movieList($queryString){
    $pdo = pdoSqlConnect();
    $query = "";

    if(!$queryString){
        $query = "SELECT a.id, a.title, a.viewAge, a.releaseDate, a.thumbnail, ifnull(b.goldenEggRatio,0) AS goldenEggRatio, ifnull(c.ticketingRatio,0) AS ticketingRatio
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
                    WHERE movieStatus = 1 || movieStatus = 2";
    }
    else if($queryString == "best"){
        $query = "SELECT a.id, a.title, a.viewAge, a.releaseDate, a.thumbnail, ifnull(b.goldenEggRatio,0) AS goldenEggRatio, ifnull(c.ticketingRatio,0) AS ticketingRatio
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
                   WHERE movieStatus = 1 || movieStatus = 2
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
    $query = "SELECT a.title,a.titleEn, a.viewAge, date_format(a.releaseDate, '%Y.%m.%d 개봉') AS date, a.runningTime, a.director, a.directorEnName, a.directorImg, a.description, a.genre, a.thumbnail, a.subImg, a.video, ifnull(b.goldenEggRatio,0) AS goldenEggRatio, ifnull(c.ticketingRatio,0) AS ticketingRatio
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

    $query = "SELECT actorsName,actorsEnName,actorsImg
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
function moviePost($title,$titleEn,$genre, $movieType,$description, $director, $directorImg, $runningTime, $thumbnail,$subImg, $movieStatus, $viewAge, $video, $releaseDate,$actors)
{

    $pdo = pdoSqlConnect();



    $query = "INSERT INTO movies (title, titleEn, genre, movieType, description, director, directorImg, runningTime, thumbnail, subImg, movieStatus, viewAge, video, releaseDate) 
                   VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$title,$titleEn,$genre, $movieType,$description, $director, $directorImg, $runningTime, $thumbnail,$subImg, $movieStatus, $viewAge, $video, $releaseDate]);

    //getMaxId

    $getMaxId = "SELECT max(id) as maxId 
                   from movies;";
    $getId = $pdo->prepare($getMaxId);
    $getId->execute();
    $getId->setFetchMode(PDO::FETCH_ASSOC);
    $res = $getId->fetchAll();

    $movieId = $res[0]["maxId"];

    for($i=0; $i<count($actors); $i++) {
        $actorsQuery = "INSERT INTO actors (movieId,actorsName,actorsEnName,actorsImg) 
                           VALUES (?,?,?,?);";
        $castSt = $pdo->prepare($actorsQuery);
        $castSt->execute([$movieId, $actors[$i]->actorsName, $actors[$i]->actorsEnName, $actors[$i]->actorsImg]);
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

/*API NO.16*/
function movieDetail($movieId){

    $pdo = pdoSqlConnect();


        $query = "SELECT s.movieId, s.totalCount, s.maleRatio, s.femaleRatio
                    FROM (
                          SELECT m.movieId, TRUNCATE(m.count/(m.count + f.count) *100,0) AS maleRatio,
                                            TRUNCATE(f.count/(m.count + f.count) * 100,0) AS femaleRatio, 
                                            m.count + f.count AS totalCount
                            FROM (
                                  SELECT movieId, count(*) AS count
                                    FROM (
                                           SELECT t.movieId, u.userId, u.sex
                                             FROM users u
                                             JOIN ticketing t on t.userId = u.userId
                                            WHERE u.sex = 1
                                         ) AS t
                                   GROUP BY t.movieId
                                  ) AS m
                 LEFT OUTER JOIN (
                                  SELECT movieId, count(*) AS count
                                    FROM (
                                          SELECT t.movieId, u.userId, u.sex
                                            FROM users u
                                            JOIN ticketing t on t.userId = u.userId
                                   WHERE u.sex = 2
                                         ) AS t
                                   GROUP BY t.movieId
                                  ) AS f on m.movieId = f.movieId
                           ) AS s
                     WHERE s.movieId = ?";

        $st = $pdo->prepare($query);
        $st->execute([$movieId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $percentQuery = "SELECT SUBSTRING(year(now()) - year(u.birth), 1,1) AS age, count(*) as count, t.movieId
               FROM ticketing t
               LEFT JOIN users u on t.userId = u.userId
              GROUP BY age, t.movieId
             HAVING t.movieId = ?";

        $st = $pdo->prepare($percentQuery);
        $st->execute([$movieId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $percentAge = $st->fetchAll();

        $sum = 0;
        for($i = 0; $i < count($percentAge); $i++){
            $sum += $percentAge[$i]["count"];
        }
        for($i = 0; $i < 5; $i++){
            if($percentAge[$i]["age"] == 1){
                $res[0]["teenAgePercent"] = round((int)$percentAge[$i]["count"] / $sum * 100);
            }
            else if($percentAge[$i]["age"] == 2){
                $res[0]["twentiesPercent"] = round((int)$percentAge[$i]["count"] / $sum * 100);
            }
            else if($percentAge[$i]["age"] == 3){
                $res[0]["thirtiesPercent"] = round((int)$percentAge[$i]["count"] / $sum * 100);
            }
            else if($percentAge[$i]["age"] == 4){
                $res[0]["fortiesPercent"] = round((int)$percentAge[$i]["count"] / $sum * 100);
            }
            else {
                $res[0]["fiftiesPercent"] = round((int)$percentAge[4]["count"] / $sum * 100);
            }

        }

        $st = null;
        $pdo = null;
        return $res[0];
}
