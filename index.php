<?php
use Redkitty\FileDropper\FileDropper;
require_once __DIR__ . './vendor/autoload.php';
// Setting default
$dropper = new FileDropper();
############ t.o.d.o #######################
// todo recursive
// todo handle ../../
// todo end slash/
// todo user input filter
// todo index to test
############################################

$dropper
    ->setWorkDir('web/images/')
    ->setProtectTimeWindow(['12:00-13:03'])
    ->setBackupFolder('../backup')
    ->execute();


echo '<br> ################# FILE HANDLING ######################################################### <br>';
if ($dropper->getDeleted()) echo "Es wurden " . $dropper->getDeleted() . " Datein gelöscht <br>";
if ($dropper->getCopied()) echo "Es wurden " . $dropper->getCopied() . " Kopiert <br>";



echo '<br> ################# PATH INFOS ############################################################ <br>';
if ($dropper->getBackupFolder()) echo "Der Backup-Ordner ist: " . $dropper->getBackupFolder()."<br>";
if ($dropper->getBackupFolder()) echo "Der Backup-Ordner ist: " . $dropper->getBackupFolder()."<br>";



echo '<br> ################# CREATE TEST FILES ##################################################### <br>';
// after delete create new test files
include './test/touch.php';