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
            $id = $req->id;
            $password = $req->password;
            $email = $req->email;
            $name = $req->name;
            $sexStatus = $req-> sexStatus;
            $ageStatus = $req-> ageStatus;

            $validId = '/^[0-9a-z]{4,12}$/';
            $validPw = '/^(?=.*[a-zA-Z])(?=.*[0-9]).{6,16}$/';
            $validBirth = '/^(19[0-9][0-9]|20\d{2})(0[0-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])$/';

            if (empty($id) || empty($password) || empty($email) || empty($name) || empty($sexStatus) || empty($ageStatus)) {
                $res->isSucces = FALSE;
                $res->code = 200;
                $res->message = "입력을 확인해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            } else {
                if (isUser($id)) {
                    $res->isSucces = FALSE;
                    $res->code = 200;
                    $res->message = "존재하는 ID입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } else if (!preg_match($validId, "$id")) {
                    $res->isSucces = FALSE;
                    $res->code = 200;
                    $res->message = "ID는 영문 숫자 혼용 4 ~ 12글자 사이로 입력해주세요.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } else if (!preg_match($validPw, "$password")) {
                    $res->isSucces = FALSE;
                    $res->code = 200;
                    $res->message = "비밀번호는 6~16 글자 사이의 영문 숫자 혼용이 필요합니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $res->isSucces = FALSE;
                    $res->code = 200;
                    $res->message = "이메일 형식에 부합하지 않습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } else if (!preg_match($validBirth, "$ageStatus")) {
                    $res->isSucces = FALSE;
                    $res->code = 200;
                    $res->message = "생년월일 형식에 부합하지 않습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                else {
                    createUser($id, $password, $email, $name, $sexStatus, $ageStatus);
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "회원가입 완료";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $res->isSucces = FALSE;
                $res->code = 200;
                $res->message = "회원가입에 실패하였습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
        }
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
