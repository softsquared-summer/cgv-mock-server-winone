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
         * API No. 7 ('GET', '/book)
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
         * API No. 8 ('GET', '/book/{movieId})
         * API Name : 영화상세조회 API
         * 마지막 수정 날짜 : 20.02.23
         */
        case "checkTheater":
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

            $res->result = checkTheater($movieId, $date);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "영화 시간 조회성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "checkBookMovie":
            http_response_code(200);
            $movieId = $vars["movieId"];
            $theaterId = $vars["theaterId"];
            $date = $_GET["date"];

            if(!isMovie($movieId)){
                $res->isSucces = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 영화입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(!isTheater($theaterId)){
                $res->isSucces = FALSE;
                $res->code = 204;
                $res->message = "존재하지 않는 영화관입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            $res->result = checkBookMovie($movieId, $theaterId, $date);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "영화 시간 조회성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "ticketInfo":
            http_response_code(200);

            $movieTimeId = $vars["movieTimeId"];

            if(!isMovieTime($movieTimeId)){
                $res->isSucces = FALSE;
                $res->code = 201;
                $res->message = "존재하지 않는 영화 시간 번호입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(compareCurDate($movieTimeId)){
                $res->result = ticketInfo($movieTimeId);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "영화 예매 정보창 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            } else {
                if(compareEqualDate($movieTimeId)){
                    if(compareCurTime($movieTimeId)){
                        $res->result = ticketInfo($movieTimeId);
                        $res->isSuccess = TRUE;
                        $res->code = 100;
                        $res->message = "영화 예매 정보창 조회 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return ;
                    }
                    else {
                        $res->isSucces = FALSE;
                        $res->code = 201;
                        $res->message = "이미 시간이 지난 영화입니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }
                } else{
                    $res->isSucces = FALSE;
                    $res->code = 201;
                    $res->message = "이미 시간이 지난 영화입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
            }
            
        case "selectSeatNPeople":
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
            if (!isUser($userId)) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "존재하지 않는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $countAdult = $req->countAdult;
            $countStudent = $req->countStudent;
            $countSpecial = $req->countSpecial;
            if(!$countAdult) $countAdult = 0;
            if(!$countStudent) $countStudent = 0;
            if(!$countSpecial) $countSpecial = 0;
            $peopleCount = (int)$countAdult + (int)$countStudent + (int)$countSpecial;
            $movieTimeId = $vars["movieTimeId"];

            if(!isMovieTime($movieTimeId)){
                $res->isSucces = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 영화 시간 번호입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            if(compareCurDate($movieTimeId)){
                if(bookAvailable($peopleCount, $movieTimeId)){
                    $res->isSucces = FALSE;
                    $res->code = 204;
                    $res->message = "잔여 좌석이 부족합니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                $res->result = selectSeatNPeople($userId, $peopleCount, $movieTimeId);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "영화 예매 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            } else {
                if(compareEqualDate($movieTimeId)){
                    if(compareCurTime($movieTimeId)){
                        if(bookAvailable($peopleCount, $movieTimeId)){
                            $res->isSucces = FALSE;
                            $res->code = 204;
                            $res->message = "잔여 좌석이 부족합니다.";
                            echo json_encode($res, JSON_NUMERIC_CHECK);
                            return;
                        }
                        $res->result = selectSeatNPeople($userId, $peopleCount, $movieTimeId);
                        $res->isSuccess = TRUE;
                        $res->code = 100;
                        $res->message = "영화 예매 성공";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }
                    else {
                        $res->isSucces = FALSE;
                        $res->code = 201;
                        $res->message = "이미 시간이 지난 영화입니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        return;
                    }
                } else{
                    $res->isSucces = FALSE;
                    $res->code = 201;
                    $res->message = "이미 시간이 지난 영화입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
            }
            break;

        case "pastTimeMovie":
            http_response_code(200);

            pastTimeMovie();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "시간지난 영화 관람완료상태로 변경 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }


} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
