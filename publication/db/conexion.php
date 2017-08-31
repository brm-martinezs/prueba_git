<?php
class mysql
{
#____________________________________________________________________________ 
//    private $localhost = "localhost";    
//    private $usuario = "sistemas_vsa";
//    private $password = "sistemas";
//    private $database = "sistemas_vsa"; 
   private $localhost = "localhost";    
    private $usuario = "root";
    private $password = "";
    private $database = "colombina"; 
#___________________________________________________________________________
    /* METODO PARA CONECTAR CON LA BASE DE DATOS*/
 public function conectar() 
{
  if(!isset($this->conexion)){
    $this->conexion = (mysqli_connect($this->localhost, $this->usuario,$this->password,  $this->database)) or die(mysqli_error() );
    mysqli_set_charset($this->conexion, 'utf8');
    mysqli_select_db($this->conexion,$this->database) or die(mysqli_error());      
  }
 }     

 public function conectar_otro() 
{
  if(!isset($this->conexion)){
    $this->conexion = (mysqli_connect($this->localhost, $this->usuario,$this->password,  $this->database)) or die(mysqli_error() );
    mysqli_set_charset('utf8');
    mysqli_select_db($this->conexion,$this->database) or die(mysqli_error());      
  }
 } 
#____________________________________________________________________________
 // METODO PARA REALIZAR UNA CONSULTA 
 // INPUT: $q -> consulta
 // OUTPUT: $result
 public function query($q)
 {
    $resultado = mysqli_query($this->conexion,$q);
    if(!$resultado){
    
     exit;
    }
    return $resultado; 
 }

 public function ejecutar($sql){
    mysqli_query($this->conexion,$sql);
 }
#____________________________________________________________________________
 // METODO PARA CONTAR EL NUMERO DE FILAS DEVUELTAS
 // INPUT: $r
 // OUTPUT: numero de filas 
 function numero_de_filas($result){
  if(!is_resource($result)) 
            return false;
  return mysqli_num_rows($result);
 }
#____________________________________________________________________________

 
 
 }