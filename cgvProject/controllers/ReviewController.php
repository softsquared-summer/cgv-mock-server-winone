<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {

        case "reviewPost":
            http_response_code(201);
            $movieId = $req->movieId;
            $goldenEggStatus = $req->goldenEggStatus;
            $content = $req->content;
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

            if(!$movieId || !$content){
                $res->isSuccess = FALSE;
                $res->code = 204;
                $res->message = "영화선택 또는 댓글을 달아주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isMovie($movieId)){
                $res->isSucces = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 영화입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(!isWatchedMovie($movieId, $userId)){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "실관람객이 아닙니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isAlreadyWritten($movieId, $userId)){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "하나의 영화에 실관람댓글은 한 번만 작성할 수 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            reviewPost($movieId, $userId, $content, $goldenEggStatus);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "실관람평 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "reviewDelete":
            http_response_code(201);
            $movieId = $vars["movieId"];
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

            if(!isMovie($movieId)){
                $res->isSucces = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 영화입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(!isAlreadyWritten($movieId, $userId)){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "작성한 리뷰가 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            reviewDelete($movieId, $userId);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "실관람평 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "reviewMovie":
            http_response_code(200);
            $movieId = $vars["movieId"];
            $queryString = $_GET['condition'];
            if(!isMovie($movieId)){
                $res->isSucces = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 영화입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $res->result = reviewMovie($movieId, $queryString);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "영화 리뷰 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "reviewDetail":
            http_response_code(200);
            $movieId = $vars["movieId"];
            if(!isMovie($movieId)){
                $res->isSucces = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 영화입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $res->result = reviewDetail($movieId);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "영화 리뷰 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "fcmTest":
            http_response_code(200);
            $fcmToken = 'd6DvQXqrVJc:APA91bFUL1iYVCY-k8Cr18WJ40GoqPw-EJJ0Vra8owhxNVuvJF-S2j6YRk8vb7iKju74LGaAII_ml40OQMLzhMpcZF2iPE58nEpNaezATBmffjT6WlKNK-fMtHwKdaA6OLJzGlIOjZ9O';
            sendFcm($fcmToken);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "fcm 테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }


} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
