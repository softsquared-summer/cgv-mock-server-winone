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
            $res->result = movieList($_GET['condition']);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "영화 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 4 ('GET', '/movie/{movieId})
         * API Name : 영화상세조회 API
         * 마지막 수정 날짜 : 20.02.22
         */
        case "movie":
                http_response_code(200);

                if(!isMovie($vars["movieId"])){
                $res->isSucces = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 영화 ID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
                }

                $res->result = movie($vars["movieId"]);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "영화 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

        /*
        * API No. 5 ('POST', '/movie')
        * API Name : 영화목록추가 API
        * 마지막 수정 날짜 : 20.02.21
        */
        case "moviePost":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"]; // jwt

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userId = $userInfo->userId;
            $pw = $userInfo->pw;
            if($userId != "master123"){
                $res->isSucces = FALSE;
                $res->code = 202;
                $res->message = "관리자가 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            moviePost($req->title,$req->titleEn,$req->genre, $req->movieType,$req->description, $req->director, $req->directorImg, $req->runningTime, $req->thumbnail,$req->subImg, $req->movieStatus, $req->viewAge, $req-> video, $req->releaseDate,$req->actors);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "영화 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 6 ('DELETE', '/movie/{movieId})
        * API Name : 영화삭제 API
        * 마지막 수정 날짜 : 20.02.21
        */
        case "movieDelete":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userId = $userInfo->userId;
            $pw = $userInfo->pw;

            if($userId != "master123"){
                $res->isSucces = FALSE;
                $res->code = 202;
                $res->message = "관리자가 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            $movieId = $vars["movieId"];

            if(!isMovie($movieId)){
                $res->isSucces = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 영화 ID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            movieDelete($movieId);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "영화 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "movieDetail":
            http_response_code(200);

            $queryString = $_GET["condition"];
            if(!isMovie($vars["movieId"])){
                $res->isSucces = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 영화 ID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $res->result = movieDetail($vars["movieId"], $queryString);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "영화 관련소식 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        }
    } catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
