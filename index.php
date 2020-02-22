<?php


use Redkitty\FileDropper\FileDropper;

require_once __DIR__.'./vendor/autoload.php';


// Setting default
$dropper = new FileDropper();


############################################

        $dropper
            ->setWorkDir('web/images/')->setProtectTimeWindow(['12:00-13:03'])->execute();


        if($dropper->getDeleted()) echo "Es wurden ".$dropper->getDeleted()." Datein gelöscht <br>";



        // after delete create new test files
        include './test/touch.php';