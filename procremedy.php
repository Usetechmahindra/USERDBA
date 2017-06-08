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
    require('./libs/oraClass.php');
    /** Administración excel PHPExcel */
    require('./libs/PHPExcel.php');
    // Control de variable de session
    if(!isset($_SESSION['requestid']))
    {
        header("Location: adminremedy.php");
    }
    // Si el estado < 2 mandar a primer formulario
    if($_SESSION['ESTADOTICKET'] < 2)
    {
        header("Location: adminremedy.php");
    }
    // Control de post
    $ClaseRemedy = new remedyClass();
    $ClassOra = new oraClass();
    
    // Controlar clase Oracle. Si actualizar actualiza todo act a 3 el ticket.
    if(isset($_POST['exec_oracle']))
    {   
       $_SESSION['VTEXTPROC'] = "";
       $rlineas=$ClassOra->execoracle();
    }else{
           $rlineas=$ClassOra->ddluser($_SESSION['requestid']); 
    }
    
    
    if(!empty($_SESSION['requestid']))
    {
        $rowremedy=$ClaseRemedy->loadremedy();
        // Coger la variable de sesion del estado
        $_SESSION['ESTADOTICKET']=$rowremedy['estado'];
    }

    // Controlar valor.
    if (isset($_POST['reload_remedy']))
    {
        $ClaseRemedy->resetremedy();
        header("Location: adminremedy.php");
    }

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