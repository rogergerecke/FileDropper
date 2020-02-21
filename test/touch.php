<?php
$path = realpath(dirname(__FILE__)).'/';
$path = str_replace('test/','',$path);

$test[] = ['base_phat' => 'web/css/'];
$test[] = ['base_phat' => 'web/images/'];
$test[] = ['base_phat' => 'web/js/'];

$test[] = ['file' => 'web/css/styleNew_1.css'];
$test[] = ['file' => 'web/css/styleNew_2.css'];
$test[] = ['file' => 'web/css/styleNew_3.css'];
$test[] = ['file' => 'web/css/styleOLD_1.css', 'time' => '-20'];
$test[] = ['file' => 'web/css/styleOLD_2.css', 'time' => '-10'];
$test[] = ['file' => 'web/css/styleOLD_3.css', 'time' => '-10'];

$test[] = ['file' => 'web/images/imageNew_1.jpg'];
$test[] = ['file' => 'web/images/imageNew_2.jpg'];
$test[] = ['file' => 'web/images/imageNew_3.jpg'];
$test[] = ['file' => 'web/images/imageOLD_1.jpg', 'time' => '-20'];
$test[] = ['file' => 'web/images/imageOLD_2.jpg', 'time' => '-10'];
$test[] = ['file' => 'web/images/imageOLD_3.jpg', 'time' => '-10'];

$test[] = ['file' => 'web/js/javascriptNew_1.js'];
$test[] = ['file' => 'web/js/javascriptNew_2.js'];
$test[] = ['file' => 'web/js/javascriptNew_3.js'];
$test[] = ['file' => 'web/js/javascriptOLD_1.js', 'time' => '-20'];
$test[] = ['file' => 'web/js/javascriptOLD_2.js', 'time' => '-10'];
$test[] = ['file' => 'web/js/javascriptOLD_3.js', 'time' => '-10'];


$one_hour = 3600;
$one_day = $one_hour * 24;

// run all cam folder ...nothing change here
foreach ($test as $t) {

    // create test folder
    if (isset($t['base_phat'])) {
        makeFolder($path.$t['base_phat']);
    }

    // create test files
    if (isset($t['file'])){
        makeFile($path.$t['file']);
    }

    // change file time
    if (isset($t['time']) and isset($t['file'])){

        if (stripos($t['time'],'-') !== false){
            $days = ($t['time'] * $one_day);
            $time = time() - $days;
        }

        if (stripos($t['time'],'+') !== false){
            $days = ($t['time'] * $one_day);
            $time = time() + $days;
        }


        makeFileTime($path.$t['file'],$time);
    }

}

/**
 * Create a archive save dir
 * @throws Exception
 */

function makeFolder($folder)
{
    if (file_exists($folder)) {
        // folder exist nothing to do
        return true;
    }

    if (!mkdir($folder, 0777, true)) {
        throw new Exception('Make folder function failure');
    }

    return true;
}


/**
 * @param $file
 * @return bool
 * @throws Exception
 */
function makeFile($file)
{
    if (file_exists($file)) {
        // file exist nothing to do
        return true;
    }

    if (!fopen($file,'a+')) {
        throw new Exception('Make file function failure');
    }

    return true;
}

function makeFileTime($file,$time)
{
    if (!file_exists($file)) {
        return false;
    }

    if (!touch($file, $time)) {
        echo 'Ein Fehler ist aufgetreten ...  <br>';
    } else {
        echo 'Änderung der Modifikationszeit war erfolgreich <br>';
    }

    return true;
}