<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <meta charset="UTF-8">
    <head>
        <title></title>
            <link href="css/jquery-ui.css" rel="stylesheet" type="text/css"/> 
            <script src="java/jquery.js"></script>
            <script src="java/jquery-ui.js"></script>
             <script>
                $(function() {
                    $( "#menuacordeon" ).accordion();
                });
            </script>
    </head>
    <body>
       <?php
            session_start();
            // Controlar conexión oracle
            if(empty($_SESSION['vconnoracle']))
            {
                header("Location: login.php");
            }
        ?>
        <div id="menuacordeon">
            <h4>
                <div id="divtech">
                    <img src="imagenes/Tech_white.jpg" alt="Logpie"/>
                </div>
                <h4>
                    <?php
                    // Cargar el menú custom. Nivel igual o inferior
                    $_SESSION['nivel'] = 100;
                    $vsql = "select descripcion,php,nivel "
                            . "from menucustom "
                            . "where nivel <= ".$_SESSION['nivel'].";";
                    //echo $vsql;
                    // Recorrer los resultados
                    $resmenu = mysql_query($vsql);
                    while($row = mysql_fetch_array($resmenu)) {
                        $vdescripcion = $row['descripcion'];
                        $vphp = $row['php'];
                        echo "<br>";
                        // Control de menú seleccionado
                        if ($_SESSION['pag'] == $vphp) {
                            echo "<pos>";
                        }else{
                            echo "<npos>";
                        }  
                        // Añadir menú con datos de select
                        $vmenu ="<p onClick=\"location.href='".$vphp."'\" onMouseover=\"\" style=\" cursor: pointer;\">".$vdescripcion."</p>";
                        echo $vmenu;
                        echo "</pos>";
                        echo "<npos>";
                    }
                    mysql_free_result($resmenu);       
                    ?>  
<!--                Dejar último br.-->
                    <br>
                </h4>
            </h4>
        </div>
    </body>
</html>