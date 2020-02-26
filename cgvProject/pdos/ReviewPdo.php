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

function reviewMovie($movieId, $queryString)
{

    $pdo = pdoSqlConnect();

    if($queryString == "like"){
        $query = "SELECT id,userId,goldenEggStatus,content, DATE_FORMAT(createdAt,'%m월%d일 %p %l:%i') AS DATE
                FROM reviews
               WHERE movieId = ? AND goldenEggStatus = 1
               ORDER BY DATE DESC";

        /*  $query = "SELECT id,userId,goldenEggStatus,content, DATE_FORMAT(createdAt,'%Y%m%d %l:%i %p') AS DATE
                      FROM reviews
                     WHERE movieId = ?";*/

        $st = $pdo->prepare($query);
        $st->execute([$movieId]);
    }
    else if($queryString == "dislike"){
        $query = "SELECT id,userId,goldenEggStatus,content, DATE_FORMAT(createdAt,'%m월%d일 %p %l:%i') AS DATE
                FROM reviews
               WHERE movieId = ? AND goldenEggStatus = 0
               ORDER BY DATE DESC";

        /*  $query = "SELECT id,userId,goldenEggStatus,content, DATE_FORMAT(createdAt,'%Y%m%d %l:%i %p') AS DATE
                      FROM reviews
                     WHERE movieId = ?";*/

        $st = $pdo->prepare($query);
        $st->execute([$movieId]);
    }

    else {
        $query = "SELECT id,userId,goldenEggStatus,content, DATE_FORMAT(createdAt,'%m월%d일 %p %l:%i') AS DATE
                FROM reviews
               WHERE movieId = ?
               ORDER BY DATE DESC";

        /*  $query = "SELECT id,userId,goldenEggStatus,content, DATE_FORMAT(createdAt,'%Y%m%d %l:%i %p') AS DATE
                      FROM reviews
                     WHERE movieId = ?";*/

        $st = $pdo->prepare($query);
        $st->execute([$movieId]);
    }
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $countReview = count($res);
    /*    $s = 60; //1분 = 60초
        $h = $s * 60; //1시간 = 60분
        $d = $h * 24; //1일 = 24시간
        $y = $d * 10; //1년 = 1일 * 10일*/
    for($i=0;$i<$countReview;$i++){
        /*$diff = time() - strtotime($res[$i]["DATE"]);
        if($diff < $s * 30){
            $res[$i]["DATE"] = '방금전';
            continue;
        }*/
        if(strpos($res[$i]["DATE"], "PM")){
            $res[$i]["DATE"] = str_replace("PM", "오후", $res[$i]["DATE"]);
        }
        else{
            $res[$i]["DATE"] = str_replace("AM", "오전", $res[$i]["DATE"]);
        }
    }
    /* for($j=0;$j<$countReview;$j++){
         $res[$i]["DATE"] = substr($res[$i]["DATE"], 0, 4);
     }*/
    if(count($res) == 0){
        return "리뷰가 존재하지 않습니다.";
    }

    $st = null;
    $pdo = null;

    return $res;
}
