<?php

/*DB에 유저 유뮤 확인 SELECT*/
function isUser($userId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(
                            SELECT * 
                              FROM users 
                             WHERE userId= ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}

/*영화 존재하는지 체크*/
function isMovie($movieId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(
                            SELECT * 
                              FROM movies 
                             WHERE id= ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$movieId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}


function isTheater($theaterID)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(
                            SELECT * 
                              FROM theater 
                             WHERE theaterId = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$theaterID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function isMovieTime($MovieTimeId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(
                            SELECT * 
                              FROM current_movies
                             WHERE id = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$MovieTimeId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function compareCurDate($movieTimeId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(
                            SELECT *
                              FROM current_movies
                             WHERE id = ?
                               AND CURDATE() < date) AS exist";

    $st = $pdo->prepare($query);
    $st->execute([$movieTimeId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function compareEqualDate($movieTimeId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(
                            SELECT *
                              FROM current_movies
                             WHERE id = ?
                               AND CURDATE() = date) AS exist";

    $st = $pdo->prepare($query);
    $st->execute([$movieTimeId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function compareCurTime($movieTimeId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(
                            SELECT *
                              FROM current_movies
                             WHERE id = ?
                               AND CURTIME() < startTime) AS exist";

    $st = $pdo->prepare($query);
    $st->execute([$movieTimeId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}



