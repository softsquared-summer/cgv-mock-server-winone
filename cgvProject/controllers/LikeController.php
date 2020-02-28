<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {

        case "likePost":
            http_response_code(201);

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
            $movieId = $vars["movieId"];

            if(!isMovie($movieId)){
                $res->isSucces = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 영화 ID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(isAlreadyLiked($userId, $movieId)){
                if(likedState($userId, $movieId)){
                    updateLikedStatus($userId, $movieId);
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "볼래요 상태에 추가되었습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    updateLikedStatus($userId, $movieId);
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "볼래요가 취소되었습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
            }

            likePost($userId, $movieId);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "볼래요 상태에 추가 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "likeCount":
            http_response_code(200);
            $movieId = $vars["movieId"];

            if(!isMovie($movieId)){
                $res->isSucces = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 영화 ID입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $res->result = likeCount($vars["movieId"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "영화 좋아요 갯수 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}