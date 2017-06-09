<?php
// Control de post
$ClaseRemedy = new remedyClass();
if(isset($_POST['load_remedy']) and !empty($_POST['requestid']))
{
    $_SESSION['requestid'] = strtoupper($_POST['requestid']);
    $rowremedy=$ClaseRemedy->loadremedy();
}
// Carga excel en server y actaliza estado si procede.
if(isset($_POST['load_excel']) and !empty($_POST['requestid']))
{
    $_SESSION['requestid'] = strtoupper($_POST['requestid']);
    $rowremedy=$ClaseRemedy->updateexcel();
}

// Procesar excel
if(isset($_POST['proc_excel']) and !empty($_POST['requestid']))
{
    $_SESSION['requestid'] = strtoupper($_POST['requestid']);
    $rowremedy=$ClaseRemedy->procexcel();
}
// Control de aplicar EXCEL
if ($rowremedy['estado'] > 1)
{
    header("Location: procremedy.php");
}

?>
<script src="java/jquery.js"></script>
<script src="java/jquery-ui.js"></script>
<script src="java/jquery.multi-select.js"></script>
<script>
    $.datepicker.regional['es'] = {
    closeText: 'Cerrar',
    prevText: '<Ant',
    nextText: 'Sig>',
    currentText: 'Hoy',
    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
    weekHeader: 'Sm',
    dateFormat: 'yy-mm-dd',
    firstDay: 1,
    isRTL: false,
    showMonthAfterYear: false,
    yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['es']);
    //Meter punteros a los diferentes fecha
    $(function () {
        $("#fauto").datepicker({
            changeMonth: true,
            changeYear: true
        });
        // Controlar si hay un post para asignar valor
        $( "#fauto" ).datepicker("setDate", new Date("<?php echo $rowremedy['fauto'];?>"))

    });
</script>
<div id="dremedy">
    <h3 style="color:#e60202">Administración ticket Remedy</h3>
    <form id="fremedy" method="post" enctype="multipart/form-data">
    <table border="0" width="90%" cellspacing="10">
        <tbody>
            <tr>
                <td>
                <p>Ticket Remedy</p>
                <input type="text" name="requestid" size=15 id="requestid" value="<?php echo $rowremedy['requestid']; ?>" required="required"/>
                <input type="submit" name="load_remedy" value="Cargar/Crear"/>
                </td>
            </tr>
            <tr>
                <!--Datos de conexión readonly.-->
                <td>
                    <p>DB Oracle</p> 
                    <input type="text" style="background-color:#cccccc;" name="dbname" id="dbname" value="<?php echo $rowremedy['dbname'];?>" readonly/>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="conexion">Cadena conexión</label> 
                    <input type="text" style="background-color:#cccccc;" size="70" name="conexion" id="conexion" value="<?php echo $rowremedy['conexion'];?>" readonly/>
                </td>
                <td>
                    <p>Usuario DBA</p> 
                    <input type="text" style="background-color:#cccccc;" name="userdba" id="userdba" value="<?php echo $rowremedy['userdba'];?>" readonly/>                
                </td>
            </tr>
            <tr>
                <td>
                    <label for="email">Email peticionario</label> 
                    <input type="email" size="70" name="peticionario" id="peticionario" value="<?php echo $rowremedy['peticionario'];?>"/>
                </td>
                <td>
                    <label for="fauto">Fecha autorización</label>
                    <input type="text" name="fauto" id="fauto" value="<?php echo $rowremedy['fauto'];?>"/>
                </td>
            </tr>
            <tr>
                <td>
                <label for="comentario">Comentario</label>
                <input type="text" size="70" name="comentario" id="comentario" value="<?php echo $rowremedy['comentario'];?>"/> 
                </td>
                <td>
                    <label for="estado">Estado</label>
                    <select name = "estado" disabled style="width: 12em;">
                        <option value="0" <?php if($rowremedy['estado'] == 0) {echo " SELECTED ";} echo">"; ?>Creado</option>
                        <option value="1" <?php if($rowremedy['estado'] == 1) {echo " SELECTED ";} echo">"; ?>Excel cargado</option>
                        <option value="2" <?php if($rowremedy['estado'] == 2) {echo " SELECTED ";} echo">"; ?>Excel procesado</option>
                        <option value="3" <?php if($rowremedy['estado'] == 3) {echo " SELECTED ";} echo">"; ?>Usuario aplicado</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <p>Fichero Excel</p>
                    <?php
                        if ($rowremedy['estado'] == 0 and !empty($_POST['requestid']))
                        {
                            echo '<input type="file" accept=".xlsx" name="fileToUpload" id="fileToUpload"/>';
                            echo '<input type="submit" name="load_excel" value="Cargar Excel"/>';
                        }
                        else {
                            echo '<input type="text" style="background-color:#cccccc;" size="70" name="excelfile" id="excelfile" value="'.$rowremedy['requestid'].'.xlsx" readonly/>';
                        }
                        if ($rowremedy['estado'] == 1 and !empty($_POST['requestid']))
                        {
                            echo '<input type="submit" name="proc_excel" value="Procesar Excel"/>';
                        }
                    ?>
                </td>
            </tr>
        </tbody>
    </table>
    </form>
</div>
