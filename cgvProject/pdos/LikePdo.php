<?php

function likePost($userId,$movieId)
{

$pdo = pdoSqlConnect();

$query = "INSERT INTO likes (userId, movieId) VALUES (?,?);";

$st = $pdo->prepare($query);
$st->execute([$userId, $movieId]);

$st = null;
$pdo = null;

}

function updateLikedStatus($userId, $movieId)
{

    $pdo = pdoSqlConnect();

    $query = "UPDATE likes
                 SET isLiked = CASE WHEN isLiked = 1 THEN 0
                                    WHEN isLiked = 0 THEN 1
                                     END
               WHERE movieId = ? AND userId = ?";

    $st = $pdo->prepare($query);
    $st->execute([$movieId, $userId]);

    $st = null;
    $pdo = null;

}

function likeCount($movieId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT count(*) AS count FROM likes WHERE movieId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$movieId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]["count"];
}