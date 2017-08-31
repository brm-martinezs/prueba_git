<?php
require_once('db/requires.php');

$smarty->assign('nombre','Smarty');


$lineas = new Lineas();
$datosLineas = $lineas->getLinea();
//printVar($datos); 
$smarty->assign("linea",$datosLineas);



$smarty->display('index.html');



?>