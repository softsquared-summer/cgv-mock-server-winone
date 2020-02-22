<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {

        /*
         * API No. 7 ('GET', '/movie/{movieId})
         * API Name : 영화상세조회 API
         * 마지막 수정 날짜 : 20.02.22
         */
        case "selectMovie":
            http_response_code(200);

            $res->result = selectMovie();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 8 ('GET', '/movie/{movieId})
         * API Name : 영화상세조회 API
         * 마지막 수정 날짜 : 20.02.22
         */

        case "checkMovieTime":
            http_response_code(200);
            $movieId = $vars["movieId"];
            $date = $_GET["date"];

            if(!isMovie($movieId)){
                $res->isSucces = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 영화 ID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $res->result = checkMovieTime($movieId, $date);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "영화 시간 조회성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
