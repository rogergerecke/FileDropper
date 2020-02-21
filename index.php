<?php
// here include the class and creat a object
require './src/fileDropper.php';

// Setting default
$dropper = new fileDropper('', 30, ['12:00', '12:01', '12:02', '12:03', '12:04', '12:05']);

#########################################################################################################
#
#
# Cams ab hier in die Liste eintragen und nur noch diese index.php im Cronjob aufrufen
# !!! Wichtig die  max_execution_time=1800 und memory_limit=1024m !!! oder mehr sonst bricht das script ab
#
#########################################################################################################

$cams[] = ['base_phat' => 'webcam/zaniglas/bord/FI9900P_00626E635898/archive/', 'cam_name' => '40. Webcam Zaniglas'];
$cams[] = ['base_phat' => 'webcam/zaniglas/cam_fc_zaniglas/FI9805E_C4D6553BDA41/archive/', 'cam_name' => 'Kamera Hof'];


############################################
// run all cam folder ...nothing change here
foreach ($cams as $cam) {

    try {
        $dropper
            ->setBasePath($cam['base_phat'])
            ->setCamName($cam['cam_name'])
            ->execute();
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    $output = $dropper->getOutput();

    // text log output
    if ($output) {
        echo "CAM# _ _ " . $output['cam_name'] . " wurden " . $output['delete'] . " Bilder gelöscht. Es sind noch " . $output['rest'] . " übrig. <br>";
    }

    if ($dropper->getCopyCount()){
        echo "Es wurde " . $dropper->getCopyCount() . " Bilder ins Archiv kopiert. <br>";
    }

    if ($dropper->getFailedCopy()) {
        echo "Es konnten " . $dropper->getFailedCopy() . " Bilder nicht ins Archiv kopiert werden. <br>";
    }
}

# HILFE
/*
Nur mal als beispiel die Optionen setzt man jetzt so bei einem object
$dropper
    ->setBasePath($location)
    ->setSaveDays(45)
    ->setProtectTimeWindow('12:00', '12:01', '12:02', '12:03', '12:04', '12:05')
    ->execute();
*/