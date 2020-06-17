<?php namespace app;
use \app\controllers\IndexController as Index;

include_once(dirname(__FILE__) . '/' . 'autoload.php');

date_default_timezone_set('Europe/Moscow');

$index = new Index();
$index->init();

?>
