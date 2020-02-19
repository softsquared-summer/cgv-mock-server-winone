<?php

function createUser($id, $password, $email, $name, $sexStatus, $ageStatus)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO users (id, password, email, name, sexStatus, ageStatus) VALUES (?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$id, $password, $email, $name, $sexStatus, $ageStatus]);
    $st = null;
    $pdo = null;

}