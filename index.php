<?php
// here include the class and creat a object
use App\FileDropper\fileDropper;

require './src/FileDropper/fileDropper.php';

// Setting default
$dropper = new fileDropper();


############################################
// run all cam folder ...nothing change here

        $dropper
            ->setBasePath('web/images/')->setProtectDateRange(['09.12.2020-22.12.2020']);

