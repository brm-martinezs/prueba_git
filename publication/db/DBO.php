<?php 
$include_path = ini_get("include_path");
//echo $include_path;
//@ini_set("include_path", $include_path . ":/home/distrita/public_html/nicolesitalianrestaurant/PEAR");
//@ini_set("include_path", $include_path . ";c:\Appserv\www\PEAR");
@ini_set("include_path", $include_path.";".$_SERVER["DOCUMENT_ROOT"]."/PEAR");
//print_r($include_path.";".$_SERVER["DOCUMENT_ROOT"]."/PEAR");
//@ini_set("include_path", $include_path.":/home/colom25/public_html/PEAR");

require_once("DB.php");
require_once("DB/DataObject.php");

$username_link = "root";
$password_link = "";
//$password_link = "1nt3r4ct1v3";
$database_link = "brm_bmw";

$optionsDataObject = &PEAR::getStaticProperty('DB_DataObject','options');
$optionsDataObject = array(
    'debug'			   => 0, // Permite detallar las consultas que ejecuta, tiene hasta 3 niveles de detalle
    'database'         => "mysql://$username_link:$password_link@localhost/$database_link", // Configura la base de datos
    'schema_location'  => 'C:\xampp5\htdocs\colombina\dbNew',  //\\xampp7\\htdocs  \\Appserv\\www C:\xampp5\htdocs\colombina\dbNew
    'class_location'   => 'C:\xampp5\htdocs\colombina\dbNew',
    'require_prefix'   => 'db/',
    'class_prefix'     => 'DataObjects_',
	'generator_no_ini' => true);
	

?>