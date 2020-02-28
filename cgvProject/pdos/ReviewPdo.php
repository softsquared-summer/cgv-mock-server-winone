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
        $query = "SELECT id,userId,goldenEggStatus,content, (CASE WHEN TIMESTAMPDIFF(second, createdAt, NOW()) < 3600 THEN '방금 전'
                                          ELSE DATE_FORMAT(createdAt,'%m월%d일 %p %l:%i')
                                          END) AS DATE
                FROM reviews
               WHERE movieId = ? AND goldenEggStatus = 1
               ORDER BY DATE DESC";

        $st = $pdo->prepare($query);
        $st->execute([$movieId]);
    }
    else if($queryString == "dislike"){
        $query = "SELECT id,userId,goldenEggStatus,content, (CASE WHEN TIMESTAMPDIFF(second, createdAt, NOW()) < 3600 THEN '방금 전'
                                          ELSE DATE_FORMAT(createdAt,'%m월%d일 %p %l:%i')
                                          END) AS DATE
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
        $query = "SELECT id,userId,goldenEggStatus,content, (CASE WHEN TIMESTAMPDIFF(second, createdAt, NOW()) < 3600 THEN '방금 전'
                                          ELSE DATE_FORMAT(createdAt,'%m월%d일 %p %l:%i')
                                          END) AS DATE
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

    $res["review"] = $st->fetchAll();


    $countReview = count($res["review"]);
    for($i=0;$i<$countReview;$i++){
        $text = $res["review"][$i]["userId"];
        if(strpos($res["review"][$i]["DATE"], "PM")){
            $res["review"][$i]["DATE"] = str_replace("PM", "오후", $res["review"][$i]["DATE"]);
        }
        else if (strpos($res[$i]["DATE"], "AM")){
            $res["review"][$i]["DATE"] = str_replace("AM", "오전", $res["review"][$i]["DATE"]);
        }
        $res["review"][$i]["userId"] = substr_replace($text, '**',2,1);
    }
    /* for($j=0;$j<$countReview;$j++){
         $res[$i]["DATE"] = substr($res[$i]["DATE"], 0, 4);
     }*/
    if(count($res["review"]) == 0){
        return "리뷰가 존재하지 않습니다.";
    }

    $res["reviewCount"] = $countReview;
    $st = null;
    $pdo = null;

    return $res;
}

function reviewDetail($movieId)
{

    $pdo = pdoSqlConnect();

    $query = "SELECT a.movieId, a.maleRatio, a.femaleRatio, b.teenAgePercent, b.twentiesPercent, b.thirtiesPercent, b.fortiesPercent, b.fiftiesPercent
  FROM (
       SELECT s.movieId, s.maleRatio, s.femaleRatio
FROM (
         SELECT mt.movieId,
                ifnull(TRUNCATE(m.count / mt.count * 100, 0), 0) AS maleRatio,
                ifnull(TRUNCATE(f.count / ft.count * 100, 0), 0) AS femaleRatio
         FROM (SELECT t.movieId, count(*) AS count
               FROM (SELECT r.movieId, u.userId, u.sex
                     FROM reviews AS r
                              JOIN users AS u
                                   ON r.userId = u.userId
                     where u.sex = 1) AS t
               GROUP BY t.movieId
              ) AS mt
                  LEFT OUTER JOIN
              (SELECT t.movieId, count(*) AS count
               FROM (SELECT r.movieId, u.userId, u.sex
                     FROM reviews AS r
                              JOIN users AS u
                                   ON r.userId = u.userId
                     where u.sex = 2) AS t
               GROUP BY movieId
              ) AS ft
              ON mt.movieId = ft.movieId
                  LEFT OUTER JOIN
              (SELECT t.movieId, count(*) AS count
               FROM (SELECT r.movieId, u.userId, u.sex
                     FROM reviews AS r
                              JOIN users AS u
                                   ON r.userId = u.userId
                     where r.goldenEggStatus = 1) AS t
               where sex = 1
               GROUP BY movieId) AS m
              ON mt.movieId = m.movieId
                  LEFT OUTER JOIN
              (SELECT t.movieId, count(*) AS count
               FROM (SELECT r.movieId, u.userId, u.sex
                     FROM reviews AS r
                              JOIN users AS u
                                   ON r.userId = u.userId
                     where r.goldenEggStatus = 1) AS t
               where sex = 2
               GROUP BY movieId) AS f
              ON mt.movieId = f.movieId
     ) AS s
    ) AS a
 JOIN (
     SELECT t.movieId, t.teenAgePercent, t.twentiesPercent, t.thirtiesPercent, t.fortiesPercent, t.fiftiesPercent
  FROM(
       SELECT at.movieId, ifnull(TRUNCATE(a.count/at.count*100,0),0) AS teenAgePercent,
                ifnull(TRUNCATE(b.count/bt.count*100,0),0) AS twentiesPercent,
                ifnull(TRUNCATE(c.count/ct.count*100,0),0) AS thirtiesPercent,
                ifnull(TRUNCATE(d.count/dt.count*100,0),0) AS fortiesPercent,
                ifnull(TRUNCATE(e.count/et.count*100,0),0) AS fiftiesPercent
         FROM(
            SELECT a.movieId, SUBSTRING(year(now()) - year(a.birth), 1,1) AS age, count(*) as count
                 FROM (
                        SELECT r.movieId, u.birth, r.goldenEggStatus
                         FROM users u
                         JOIN reviews r on r.userId = u.userId
                        WHERE r.movieId = ?
                          ) AS a
                        GROUP BY age
                          HAVING age = 1
                ) AS at
             LEFT OUTER JOIN ( SELECT b.movieId, SUBSTRING(year(now()) - year(b.birth), 1,1) AS age, count(*) as count
                    FROM (
                        SELECT r.movieId, u.birth, r.goldenEggStatus
                         FROM users u
                         JOIN reviews r on r.userId = u.userId
                        WHERE r.movieId = ? AND r.goldenEggStatus = 1
                          ) AS b
                 GROUP BY age
                          HAVING age = 1
             ) AS a ON a.movieId = at.movieId
             LEFT OUTER JOIN ( SELECT movieId, SUBSTRING(year(now()) - year(a.birth), 1,1) AS age, count(*) as count
                               FROM(
                                    SELECT r.movieId, u.birth, r.goldenEggStatus
                         FROM users u
                         JOIN reviews r on r.userId = u.userId
                        WHERE r.movieId = ?
                          ) AS a
                        GROUP BY age
                          HAVING age = 2
                 ) AS bt ON bt.movieId = at.movieId
              LEFT OUTER JOIN(
                             SELECT a.movieId, SUBSTRING(year(now()) - year(a.birth), 1,1) AS age, count(*) as count
                               FROM(
                                    SELECT r.movieId, u.birth, r.goldenEggStatus
                         FROM users u
                         JOIN reviews r on r.userId = u.userId
                        WHERE r.movieId = ? AND r.goldenEggStatus = 1
                          ) AS a
                        GROUP BY age
                          HAVING age = 2
             ) AS b on bt.movieId = b.movieId
            LEFT OUTER JOIN ( SELECT a.movieId, SUBSTRING(year(now()) - year(a.birth), 1,1) AS age, count(*) as count
                               FROM(
                                    SELECT r.movieId, u.birth, r.goldenEggStatus
                         FROM users u
                         JOIN reviews r on r.userId = u.userId
                        WHERE r.movieId = ?
                          ) AS a
                        GROUP BY age
                          HAVING age = 3
                 ) AS ct ON ct.movieId = at.movieId
            LEFT OUTER JOIN(
                             SELECT a.movieId, SUBSTRING(year(now()) - year(a.birth), 1,1) AS age, count(*) as count
                               FROM(
                                    SELECT r.movieId, u.birth, r.goldenEggStatus
                         FROM users u
                         JOIN reviews r on r.userId = u.userId
                        WHERE r.movieId = ? AND r.goldenEggStatus = 1
                          ) AS a
                        GROUP BY age
                          HAVING age = 3
             ) AS c on bt.movieId = c.movieId
            LEFT OUTER JOIN ( SELECT a.movieId, SUBSTRING(year(now()) - year(a.birth), 1,1) AS age, count(*) as count
                               FROM(
                                    SELECT r.movieId, u.birth, r.goldenEggStatus
                         FROM users u
                         JOIN reviews r on r.userId = u.userId
                        WHERE r.movieId = ?
                          ) AS a
                        GROUP BY age
                          HAVING age = 4
                 ) AS dt ON dt.movieId = at.movieId
             LEFT OUTER JOIN(
                             SELECT a.movieId, SUBSTRING(year(now()) - year(a.birth), 1,1) AS age, count(*) as count
                               FROM(
                                    SELECT r.movieId, u.birth, r.goldenEggStatus
                         FROM users u
                         JOIN reviews r on r.userId = u.userId
                        WHERE r.movieId = ? AND r.goldenEggStatus = 1
                          ) AS a
                        GROUP BY age
                          HAVING age = 4
             ) AS d on d.movieId = c.movieId
            LEFT OUTER JOIN ( SELECT a.movieId, SUBSTRING(year(now()) - year(a.birth), 1,1) AS age, count(*) as count
                               FROM(
                                    SELECT r.movieId, u.birth, r.goldenEggStatus
                         FROM users u
                         JOIN reviews r on r.userId = u.userId
                        WHERE r.movieId = ?
                          ) AS a
                        GROUP BY age
                          HAVING age = 5
                 ) AS et ON et.movieId = at.movieId
            LEFT OUTER JOIN(
                             SELECT a.movieId, SUBSTRING(year(now()) - year(a.birth), 1,1) AS age, count(*) as count
                               FROM(
                                    SELECT r.movieId, u.birth, r.goldenEggStatus
                         FROM users u
                         JOIN reviews r on r.userId = u.userId
                        WHERE r.movieId = ? AND r.goldenEggStatus = 1
                          ) AS a
                        GROUP BY age
                          HAVING age = 5
             ) AS e on e.movieId = c.movieId
          ) AS t
      ) AS b on a.movieId = b.movieId ";

    $st = $pdo->prepare($query);
    $st->execute([$movieId, $movieId, $movieId, $movieId, $movieId, $movieId, $movieId, $movieId, $movieId, $movieId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $goldenQuery = "SELECT ge.movieID, ge.goldenEggPercent, ge.reviewCount, ge.maniaCount
    FROM (
        SELECT g.movieId, ifnull(TRUNCATE(g.goldenEggCount/tr.reviewCount*100,0),0)AS goldenEggPercent, tr.reviewCount, mc.maniaCount
          FROM (
                SELECT movieId, count(*) AS goldenEggCount
                  FROM reviews
                 WHERE movieId = ? AND goldenEggStatus = 1
                   ) AS g
          LEFT OUTER JOIN (
                 SELECT movieId, count(*) AS reviewCount
                   FROM reviews r
                  WHERE movieId = ?
              ) AS tr ON tr.movieId = g.movieId
          LEFT OUTER JOIN(
                SELECT count(*) AS maniaCount, movieId
                  FROM (
                       SELECT u.userId, rm.movieId
                         FROM reviews rm
                         LEFT JOIN users u on u.userId = rm.userId
                         WHERE u.maniaStatus = 1 AND movieId = ?
                           ) AS sortmc
              ) AS mc ON tr.movieId = mc.movieId
           ) AS ge";

    $st = $pdo->prepare($goldenQuery);
    $st->execute([$movieId, $movieId, $movieId]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $goldenEgg = $st->fetchAll();

    $res[0]["goldenEggPercent"] = $goldenEgg[0]["goldenEggPercent"];
    $res[0]["reviewCount"] = $goldenEgg[0]["reviewCount"];
    $res[0]["maniaCount"] = $goldenEgg[0]["maniaCount"];

    $st = null;
    $pdo = null;

    return $res[0];
}
