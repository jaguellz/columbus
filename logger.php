<?php
include "connection.php";
define ('SITE_ROOT', realpath(dirname(__FILE__)));
if (!$_FILES) exit();
$file = $_FILES['file'];
//$dir =  SITE_ROOT.'/tmp/'. basename($_FILES['file']['name']);
//move_uploaded_file($file['tmp_name'], $dir);
$content = file_get_contents($file['tmp_name']);
$content = iconv('CP1251', 'UTF-8', $content);
$lines = explode("\r\n", $content);
$data = [];
foreach( $lines as $key => $line ){
    $data[] = str_getcsv( $line, ';' );
}
$d1 = ["Код;Название;Error"];
$d1 = array_merge($d1, array_map(function ($v) {
    $s = array_map(function ($v){
        return preg_match('/[^0-9A-Za-zА-Яа-яЁё\-.]/u', $v) ? $v:null;
    }, str_split($v[1]));
    $s = array_filter($s);
    $s = implode(',', $s);
    return '"'.implode('";"', $v).'";'. ($s ? "Недопустимый(е) символ(ы) \"$s\" в поле Название" : '');
}, $data));

$data = array_map(function ($v) {return preg_match('/[^0-9A-Za-zА-Яа-яЁё\-.]/u', $v[1]) ? null:$v;}, $data);
$d1 = array_filter($d1);
$d1 = implode("\r\n", $d1);
$data = array_filter($data);
$d2 = array_map(function ($v) {return '(\''.implode('\',\'', $v).'\')';}, $data);
$d2 = implode(',', $d2);
$q = "INSERT into `$table` (`code`, `name`) VALUES $d2";

$link->query($q);
$file = fopen('tmp/log.csv', 'w');
$d1 = iconv("UTF-8", 'CP1251', $d1);
fwrite($file, $d1);
fclose($file);
$file = 'tmp/log.csv';
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename='.basename($file));
header('Content-Transfer-Encoding: binary');
readfile($file);
unlink($file);
exit();