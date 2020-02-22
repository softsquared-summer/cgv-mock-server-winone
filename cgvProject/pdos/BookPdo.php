<?php

/*API NO.7*/
function selectMovie(){
    $pdo = pdoSqlConnect();

        $query = "SELECT a.id, a.title, a.mainImg
                    FROM movies AS a
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

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

/*API NO.8*/
function checkMovieTime($movieId, $date){
    $pdo = pdoSqlConnect();

//    if(!$date){
        $query = "SELECT title, cm.date, viewAge, runningTime, mainImg
                    FROM movies
                    LEFT JOIN current_movies cm on movieId = movies.id
                   GROUP BY title, cm.date, viewAge, runningTime, mainImg, movies.movieStatus, movies.id
                  HAVING movies.movieStatus = 1 and movies.id = ? and cm.date = '2020-02-23'";

        $st = $pdo->prepare($query);
        $st->execute([$movieId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $query = "SELECT theater.theaterId,theater.theaterName, theater.floor, cm.room
                    FROM theater
                    LEFT JOIN current_movies cm on theater.theaterId = cm.theaterId
                   GROUP BY theater.theaterId, theater.theaterName, theater.floor, cm.room, cm.movieId, cm.date
                  HAVING cm.movieId = ? and cm.date = '2020-02-23'";

         $st = $pdo->prepare($query);
         $st->execute([$movieId]);
         $st->setFetchMode(PDO::FETCH_ASSOC);
         $theater = $st->fetchAll();

        $res[0]["theaters"] = $theater;
        
        /*
        $query = "SELECT t.id, cm.startTime, cm.endTime, t.totalSeat
                    FROM current_movies cm
                    LEFT JOIN theater t ON cm.theaterId = t.theaterId AND cm.room = t.roomId
                   GROUP BY t.id, cm.startTime, cm.endTime, t.totalSeat, cm.date, cm.movieId
                  HAVING cm.movieId = 1 AND cm.date = '2020-02-23'
                   ORDER BY id";

        $st = $pdo->prepare($query);
        $st->execute([$movieId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $timeTable = $st->fetchAll();

        $res[0]["theaters"]["timeTable"] = $timeTable;
*/
//        $query = "";

  //      $st = $pdo->prepare($query);
    //    $st->execute([$movieId]);
      //  $st->setFetchMode(PDO::FETCH_ASSOC);

        //    }

    /*
     * else {
        $query = "";
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
    }

     *
     * */

    $st = null;
    $pdo = null;

    return $res;
}
