//Deviant
<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

set_time_limit(0);
ini_set('mysql.connect_timeout',1600);
ini_set('max_execution_time', 1600);
ini_set('default_socket_timeout',1600);
ini_set("mysql.trace_mode", "0");


//set the timezone to hotforex
date_default_timezone_set('America/Los_Angeles');

//lets start dat session



/** =========
 *  INCLUDES
 *  =========
 */

//set root path
define('API_PATH','/mnt/deviantspy-master/api/');

//operational paths
define('LIB_PATH',API_PATH . 'lib/');
define('HELPER_PATH',API_PATH . 'helper/');
define('MODEL_PATH',API_PATH . 'model/');
define('CONTROLLER_PATH',API_PATH . 'controller/');
define('LOG_PATH',API_PATH . '../log/');
define('TMP_PATH',API_PATH . 'tmp/');


define('SUPPORT_EMAIL','##########');  // Gmail Credentials
define('SUPPORT_EMAIL_PASSWORD','##########');
define('SUPPORT_EMAIL_NAME','Deviant Spy Support');


//network IDs
define('NETWORK_REVCONTENT',1);
define('NETWORK_CONTENTAD',2);



define('SERIALIZE_DATABASE',0);
define('SERIALIZE_JSON',1);

//whether we should scan the click url until we get to the final url
define('SCAN_ALL_REDIRECTS',true);
define('SCRAPE_LIVE',true);
define('SAVE_SCRAPE',false);
define('ENABLED_PROXY',true);

define('CRAWL_LIMIT',false); //either an integer or false
define('REDIRECT_COUNT',10);
define('ENABLE_LOGS',true);

define('CACHE_DURATION',10800);



//libraries

include(LIB_PATH . 'database.class.php');
include(LIB_PATH . 'utility.php');

//models

//include(MODEL_PATH . 'agent.model.php');
include(MODEL_PATH . 'scrape.model.php');
include(MODEL_PATH . 'geo.model.php');
include(MODEL_PATH . 'network.model.php');
include(MODEL_PATH . 'creative.model.php');
include(MODEL_PATH . 'offer.model.php');
include(MODEL_PATH . 'placement.model.php');
include(MODEL_PATH . 'publisher.model.php');

//object classes
include(LIB_PATH . 'crawler.class.php');
include(LIB_PATH . 'creative.class.php');
include(LIB_PATH . 'offer.class.php');


require_once(LIB_PATH . 'phpFastCache/phpFastCache.php');



\phpFastCache\CacheManager::setup(array("path" => TMP_PATH));
\phpFastCache\CacheManager::CachingMethod("phpfastcache");

/**
 *  FLUSH CACHE
 */
//$cache = \phpFastCache\CacheManager::getInstance(); $cache->clean();//exit();

//Response codes
define('RESPONSE_SUCCESS',1);
define('RESPONSE_ERROR',0);

//for debugging purposes
define('DEBUG',false);

//for environment purposes
//define('MODE',(isset($_SERVER['MODE']))?$_SERVER['MODE']:'prod');

define('MODE','prod');
//if(MODE=='prod') error_reporting(0);


/** =================
 *  MYSQL CREDENTIALS
 *  =================
 */


switch(MODE)
{
    case 'local':
        define('WWW_HOST','report.deviantspy.com');
        define('API_HOST','api.deviantspy.com');
        define('DATABASE_HOST','dspy-1.##########.us-east-1.rds.amazonaws.com');
        define('DATABASE_PORT',3306);
        define('DATABASE_NAME','deviantspy');
        define('DATABASE_USERNAME','##########');
        define('DATABASE_PASSWORD','##########');
        break;
    default:

    case 'prod':
        define('WWW_HOST','report.deviantspy.com');
        define('API_HOST','api.deviantspy.com');
        define('DATABASE_HOST','dspy-1.##########.us-east-1.rds.amazonaws.com');//'localhost');//'8.29.137.75');
        define('DATABASE_PORT',3306);
        define('DATABASE_NAME','deviantspy');
        define('DATABASE_USERNAME','##########');
        define('DATABASE_PASSWORD','##########');
        break;

}



/** ==============
 *  ERROR MESSAGES
 *  ==============
 */

define('ERROR_INVALID_PARAMETERS','Invalid parameters passed');
define('ERROR_INVALID_OBJECT','Invalid object');
define('ERROR_INVALID_USER_ID','Invalid ID for object');
define('ERROR_INVALID_FUNCTION','Invalid object function');
define('ERROR_NO_DATA_AVAILABLE','No data available for object');
define('ERROR_PARSING_DATA','An internal error occurred attempting to parse the data from the source');


/** ===========
 *  URL MAPPING
 *  ===========
 */

//Apache rewrite handler
//exit(print_r($argv));
if(isset($argv[1])) $q = explode('/',$argv[1]);

else $q = explode('/',$_SERVER['REQUEST_URI']);
//exit(print_r($q));

$controllerObject = strtolower((isset($q[1]))?$q[1]:'');
$controllerFunction = strtolower((isset($q[2]))?$q[2]:'');
$controllerID = strtolower((isset($q[3]))?$q[3]:'');
$controllerData = strtolower((isset($q[4]))?$q[4]:'');




/** ==================
 *  CONTROLLER ROUTING
 *  ==================
 */
//Load the object's appropriate controller
$_controller = CONTROLLER_PATH . $controllerObject . '.controller.php';
if(file_exists($_controller))  include($_controller);
    else
    api_response(array(
        'code'=> RESPONSE_ERROR,
        'data'=> array('message'=> $_controller . '' .ERROR_INVALID_OBJECT)
    ));


/** ============
 *  API RESPONSE
 *  ============
 */
function api_response($res)
{
    $response_code = $res['code'];
    $response_data = $res['data'];

    header('Content-Type: application/json');
    if(DEBUG)
    {
        exit('<pre>' . print_r(
            array(
                'response'=>$response_code,
                'data'=>$response_data

            ),true));
    }
    exit(json_encode(
        array(
            'response'=>$response_code,
            'data'=>$response_data
        )
    ));
}

/** ==================
 *  CATCH FATAL ERRORS
 *  ==================
 */

function shutdown() {
    $error = error_get_last();
    if ($error['type'] === E_ERROR) {
        // fatal error has occured
        //ds_error('parse error function: parse_network_content(): ' . PHP_EOL .'error:'.PHP_EOL . $e->getMessage().'network:' . $network_id . PHP_EOL . 'content:' . $content. PHP_EOL);
        ds_error('FATAL ERROR:' . PHP_EOL. 'msg:' .$last_error['message']. PHP_EOL. 'file:'. $last_error['file'] . PHP_EOL. 'line:'. $last_error['line']);
        return false;
    }
}

register_shutdown_function('shutdown');
