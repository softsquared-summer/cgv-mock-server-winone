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