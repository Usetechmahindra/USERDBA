<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <link rel="stylesheet" type="text/css" href="css/onoestilos.css">
    <?php
	// creamos la sesion y comprobamos si el user ha dado al boton del form.
    session_start();
    require('./libs/ConnectionClass.php');
    $ClaseConn = new ConnectionClass();
    if($_POST['btenviado'])
    {
        //Si se ha dado al boton metemos variables
        $_SESSION['vuser']=$_POST['user'];
        $_SESSION['vpass']=$_POST['passuser'];
        $_SESSION['vconnoracle']=$_POST['cbdb'];
        $acadena = explode("=",$_SESSION['vconnoracle']);
        $_SESSION['vsid'] = $scadena=substr($acadena[8],0,-3); 
        // Conectar a oracle. Pruebas
        if ($ClaseConn->conectarOracle() > 0)
        {
            header("Location: adminremedy.php");
        }
    }
    ?>
    <head>
        <meta charset="UTF-8">
        <title>Ventana de entrada DBA ONO</title>
    </head>
    <body>
    <div id="contenedor">
    <div id="cabecera">
         <div id="imgcabecera">
             <img src="imagenes/vf_logo1.jpg" alt="Logo"/>
         </div>
    </div>
    <div id="imputlogin">
        <form id="login" method="post">
            <table border="0">
                <tbody>
                    <tr>
                        <br>
                        <td>
			<p>Usuario DBA: </p>
                        <input type="text" name="user" required="required"> <br> <br>
                        <p>Password: </p>
                        <input type="password" name="passuser" required="required"> <br> <br>
						<p> <label for="cbdb">Elegir base de datos</label></p>
						<select name="cbdb" id="cbdb"> 
						<?php
                                                    // Crear clase y cargar combo
                                                    $ClaseConn->foraclecombo();
						?>
						</select>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="submit" value="Validar" name="btenviado">
                        <br>
                        <p>
                        <?php
                            echo $_SESSION['textsesion'];
                            //if (CheckLogin() == true)
                           // {
                           //     header("Location: );
                           // }
                        ?>
                        </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            </form>
    </div>
    </div> <!-- Fin del cuperpo -->
    <div id="pie">
    </div>
    </body>
</html>