<?php
$path = getenv('TEMP') . DIRECTORY_SEPARATOR . 'ogrenci-toplu-kayit-sablonu.xlsx';
$zip = new ZipArchive();
$result = $zip->open($path);
var_dump($result);
if ($result === true) {
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        echo $stat['name'], PHP_EOL;
    }
    echo "--- workbook ---", PHP_EOL;
    var_dump($zip->getFromName('xl/workbook.xml'));
    echo "--- sheet ---", PHP_EOL;
    var_dump($zip->getFromName('xl/worksheets/sheet1.xml'));
    $zip->close();
}
