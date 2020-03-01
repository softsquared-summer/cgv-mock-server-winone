<?php
require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './vendor/autoload.php';
require './pdos/CgvPdo.php';
require './pdos/ValidationPdo.php';
require './pdos/encryptDBPdo.php';
require './pdos/BookPdo.php';
require './pdos/ReviewPdo.php';
require './pdos/LikePdo.php';
use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
//error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   Test   ****************** */
    $r->addRoute('GET', '/', ['IndexController', 'index']);
    $r->addRoute('GET', '/test', ['IndexController', 'test']);
    $r->addRoute('GET', '/test/{testNo}', ['IndexController', 'testDetail']);
    $r->addRoute('POST', '/test', ['IndexController', 'testPost']);


    /* ******************   CGV   ****************** */
    $r->addRoute('GET', '/jwt', ['MainController', 'validateJwt']);
    $r->addRoute('POST', '/jwt', ['MainController', 'createJwt']);
    $r->addRoute('POST', '/users', ['UserController', 'createUser']);
    //$r->addRoute('GET', '/users/movie', ['UserController', 'watchedMovie']);  //   내가 본 영화 목록


    $r->addRoute('GET', '/movie', ['CgvController', 'movieList']);
    $r->addRoute('GET', '/movie/{movieId}', ['CgvController', 'movie']);
    $r->addRoute('POST', '/movie', ['CgvController', 'moviePost']);
    $r->addRoute('DELETE', '/movie/{movieId}', ['CgvController', 'movieDelete']); // API NO.6
    $r->addRoute('GET', '/movie/{movieId}/detail', ['CgvController', 'movieDetail']); // API NO.16 관련 소식 비율

    $r->addRoute('POST', '/movie/{movieId}/liked', ['LikeController', 'likePost']); // API NO.17 볼래요 추가 취소 API
    $r->addRoute('GET', '/movie/{movieId}/liked', ['LikeController', 'likeCount']); // API NO.18 관련 소식 비율


    $r->addRoute('GET', '/book', ['BookController', 'selectMovie']); // API NO.7
    $r->addRoute('GET', '/book/{movieId}', ['BookController', 'checkTheater']); // API NO.8
    $r->addRoute('GET', '/book/{movieId}/theater/{theaterId}', ['BookController', 'checkBookMovie']); // API NO.9, theaterId 쿼리스트링으로

    $r->addRoute('GET', '/ticket/{movieTimeId}', ['BookController', 'ticketInfo']); // API NO.10
    $r->addRoute('POST', '/ticket/{movieTimeId}', ['BookController', 'selectSeatNPeople']); // API NO.11

    $r->addRoute('GET', '/past-time', ['BookController', 'pastTimeMovie']); // API NO.12 isWatched상태 업뎃

    $r->addRoute('POST', '/review', ['ReviewController', 'reviewPost']); // API NO.13 영화 리뷰 등록 API
    $r->addRoute('DELETE', '/review/{movieId}', ['ReviewController', 'reviewDelete']); // API NO.14 특정 영화 본인 리뷰 삭제 API
    $r->addRoute('GET', '/review/{movieId}', ['ReviewController', 'reviewMovie']); // API NO.15 특정영화조회 리뷰 API
    $r->addRoute('GET', '/review/{movieId}/detail', ['ReviewController', 'reviewDetail']); // API NO.19 실관람평 상세 정보 API

    $r->addRoute('GET', '/fcmtest', ['ReviewController', 'fcmTest']); // API NO.20


    //$r->addRoute('POST', '/book/{movieId}/theater/{theaterId}', ['BookController', 'bookMovie']);

    //    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'MainController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/MainController.php';
                break;
            case 'UserController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/UserController.php';
                break;
            case 'CgvController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/CgvController.php';
                break;
            case 'BookController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/BookController.php';
                break;
            case 'ReviewController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/ReviewController.php';
                break;
            case 'LikeController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/LikeController.php';
                break;
            /*case 'EventController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/EventController.php';
                break;
            case 'ProductController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ProductController.php';
                break;
            case 'SearchController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/SearchController.php';
                break;
            case 'ReviewController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ReviewController.php';
                break;
            case 'ElementController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ElementController.php';
                break;
            case 'AskFAQController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/AskFAQController.php';
                break;*/
        }

        break;
}
