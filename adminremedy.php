<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <link rel="stylesheet" type="text/css" href="css/onoestilos.css">
    <head>
    <?php
	// creamos la sesion y clase de admin de conexiones.
    session_start();
    require('./libs/ConnectionClass.php');
    require('./libs/remedyClass.php');
    /** Administración excel PHPExcel */
    require('./libs/PHPExcel.php');
    // Crear clase de para llamada a funciones genericas
    $ClaseConn = new ConnectionClass();
    $ClaseConn->conectarBDMySQLuserdb();
    ?>
        <meta charset="UTF-8">
        <title>Ventana administración ticket Remedy</title>
    </head>
    <body>
        <div id="contenedor">
        <div id="cabecera">
             <div id="imgcabecera">
                 <img src="imagenes/vf_logo1.jpg" alt="Logo"/>
             </div>
        </div>
        <div id="cuerpo">
            <div id="menuleft">
            <?php                include 'menuleft.php';
            ?>         
            </div>
            <div id="imput">
            <?php
                include 'inputremedy.php';
            ?>
            </div>
            <div id="output">
            <?php

            ?>
        </div>
        </div> <!-- Fin del cuperpo -->
        <div id="pie">
        </div>
        </div> <!-- Fin del contendor principal -->
    </body>
</html>