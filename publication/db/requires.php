<?php
global $prefijo;

error_reporting(0);

require_once("db/DBO.php");
//require_once("db/conexion.php");
require_once("Smarty/libs/Smarty.class.php");
require_once("db/db.linea.php");
require_once("class/lineas.php");


$smarty = new Smarty();


//$smarty->display('./index.html');
/*
$smarty->compile_check = true;
$smarty->left_delimiter = '{#';
$smarty->right_delimiter = '#}';

//Ruta absoluta de las imagenes
//$smarty->assign("rutaImagenes","http://72.34.50.144/~colom25/");
$smarty->assign("rutaImagenes","http://www.colombina.com/");
*/

?>