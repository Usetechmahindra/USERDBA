<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <link rel="stylesheet" type="text/css" href="css/onoestilos.css">
    <link rel="stylesheet" type="text/css" href="jquery-ui.css">
    <head>
    <?php
    // creamos la sesion y clase de admin de conexiones.
    session_start();
    require('./libs/ConnectionClass.php');
    require('./libs/remedyClass.php');
    require('./libs/oraClass.php');
    /** Administración excel PHPExcel */
    require('./libs/PHPExcel.php');
    // Crear clases
    // Crear clase de para llamada a funciones genericas
    $ClaseConn = new ConnectionClass();
    $ClaseRemedy = new remedyClass();
    $ClassOra = new oraClass();
    
    // Texto antes de procesar clases
    $_SESSION['textsesion'] = "";
    // Control de variable de session
    if(!isset($_SESSION['requestid']))
    {
        header("Location: adminremedy.php");
    }
    // Cargar datos remedy
    $rowremedy=$ClaseRemedy->loadremedy();
    $_SESSION['ESTADOTICKET'] = $rowremedy['estado'];
    // Si el estado < 2 mandar a primer formulario
    if($_SESSION['ESTADOTICKET'] < 2)
    {
        header("Location: adminremedy.php");
    }
    // Actualizar comentario, fecha y email
    if (isset($_POST['update_remedy']))
    {
        $rowremedy['fauto']=$_POST['fauto'];
        $rowremedy['peticionario']=$_POST['peticionario'];
        $rowremedy['comentario']=$_POST['comentario'];
        $ClaseRemedy->updateremedy($rowremedy);
    }
    // Controlar valor.
    if (isset($_POST['reload_remedy']))
    {
        $ClaseRemedy->resetremedy();
        header("Location: adminremedy.php");
    }
    // Control de post
    
    // Controlar clase Oracle. Si actualizar actualiza todo act a 3 el ticket.
    if(isset($_POST['exec_oracle']))
    {   
       // Si acaba bien actualizar estado ticket.
       if ($ClassOra->execoracle()> 0)
       {
           $rowremedy['estado'] = 3;
           $_SESSION['ESTADOTICKET'] = $rowremedy['estado'];
           $ClaseRemedy->updateremedy($rowremedy);
           $ClaseRemedy->mailfinal($rowremedy);
       }
    }
    // Cargar lineas
    $rlineas=$ClassOra->ddluser($_SESSION['requestid']); 

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
            <?php include 'menuleft.php';?>         
            </div>
            <div id="imput">
            <?php
                // Control de aplicar EXCEL
                include 'inputoracle.php';     
            ?>
            </div>
            <div id="output">
            <?php
                include 'outoracle.php';    
            ?>
        </div>
        </div> <!-- Fin del cuperpo -->
        <div id="pie">
        </div>
        </div> <!-- Fin del contendor principal -->
    </body>
</html>