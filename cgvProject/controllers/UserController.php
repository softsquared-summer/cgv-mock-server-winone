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
         * API No. 0
         * API Name : 유저생성 API
         * 마지막 수정 날짜 : 20.02.19
         */
        case "createUser":
        {
            $userId = $req->userId;
            $pw = $req->pw;
            $email = $req->email;
            $userName = $req->userName;
            $sex = $req-> sex;
            $birth = $req-> birth;

            $validId = '/^[0-9a-z]{4,12}$/';
            $validPw = '/^(?=.*[a-zA-Z])(?=.*[0-9]).{6,16}$/';
            $validBirth = '/^(19[0-9][0-9]|20\d{2})(0[0-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])$/';

            if (empty($userId) || empty($pw) || empty($email) || empty($userName) || empty($sex) || empty($birth)) {
                $res->isSucces = FALSE;
                $res->code = 200;
                $res->message = "입력을 확인해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            } else {
                if (isUser($userId)) {
                    $res->isSucces = FALSE;
                    $res->code = 201;
                    $res->message = "존재하는 ID입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } else if (!preg_match($validId, "$userId")) {
                    $res->isSucces = FALSE;
                    $res->code = 202;
                    $res->message = "ID는 영문 숫자 혼용 4 ~ 12글자 사이로 입력해주세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } else if (!preg_match($validPw, "$pw")) {
                    $res->isSucces = FALSE;
                    $res->code = 203;
                    $res->message = "비밀번호는 6~16 글자 사이의 영문 숫자 혼용이 필요합니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $res->isSucces = FALSE;
                    $res->code = 204;
                    $res->message = "이메일 형식에 부합하지 않습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } else if (!preg_match($validBirth, "$birth")) {
                    $res->isSucces = FALSE;
                    $res->code = 205;
                    $res->message = "생년월일 형식에 부합하지 않습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    createUser($userId, $pw, $email, $userName, $sex, $birth);
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "회원가입 완료";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $res->isSucces = FALSE;
                $res->code = 206;
                $res->message = "회원가입에 실패하였습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
        }
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
