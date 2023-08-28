<?php
$servidor="mysql:dbname=empresa;host=localhost";
$usuario="root";
$password="";

try {
    $pdo=new PDO($servidor,$usuario,$password,array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES utf8"));
}catch (PDOException $e){
    echo "Erro".$e->getMessage();
    exit;
}

?>