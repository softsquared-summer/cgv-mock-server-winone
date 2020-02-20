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
             * API No.3 (GET', '/movie]) 기본영화조회
             * API Name : movie API
             * 마지막 수정 날짜 : 20.02.20
             */

        case "movieList":
            http_response_code(200);
            $res->result = movieList($_GET['best']);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "영화 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
            break;

        /*
         * API No. 4 ('GET', '/movie/{id}) 영화 상세 조회
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 20.02.20
         */
        case "movie":
                http_response_code(200);
                $res->result = movie($vars["id"]);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "영화 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);

        }
    } catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
