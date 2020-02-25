<?php

function reviewPost($movieId, $userId, $content, $goldenEggStatus)
{

    $pdo = pdoSqlConnect();

    $query = "INSERT INTO reviews (movieId, userId, content, goldenEggStatus) VALUES (?,?,?,?)";

    $st = $pdo->prepare($query);
    $st->execute([$movieId,$userId,$content,$goldenEggStatus]);

    $st = null;
    $pdo = null;

}

function reviewDelete($movieId, $userId)
{

    $pdo = pdoSqlConnect();

    $query = "DELETE from reviews WHERE movieId = ? AND userId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$movieId, $userId]);

    $st = null;
    $pdo = null;

}