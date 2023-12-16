<?php
/*Get SQL DB parameters from config file to the $SQL variable with actual data
$SQL = array(
    'host' => '',
    'user' => '',
    'pass' => '',
    'dbName' => ''
);
*/
require_once 'config.php';
require_once 'lib.php';

$DB = new DBConnection($SQL['host'], $SQL['user'], $SQL['pass'], $SQL['dbName']);
$DB->setTableAlias('goods', 's1_goods');
$DB->setTableAlias('categories', 's1_categories');

if (isset($_GET['getCat'])) {
    $categories = new Categories();
    echo json_encode($categories->getAll(), JSON_PRETTY_PRINT);
} elseif (isset($_GET['getGoodsByCat'])) {
    $goods = new Goods();
    echo json_encode($goods->getGoodsByCat((int)$_GET['getGoodsByCat'], $_GET['sorting']), JSON_PRETTY_PRINT);
} elseif (isset($_GET['getItemInfo'])) {
    $goods = new Goods();
    echo json_encode($goods->getItemInfo((int)$_GET['getItemInfo']), JSON_PRETTY_PRINT);
}