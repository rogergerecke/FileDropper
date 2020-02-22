<?php
// here include the class and creat a object
use App\FileDropper\fileDropper;

require './src/FileDropper/fileDropper.php';

// Setting default
$dropper = new fileDropper();


############################################
// run all cam folder ...nothing change here

        $dropper
            ->setWorkDir('web/images/')->setProtectTimeWindow(['12:00-13:03']);

