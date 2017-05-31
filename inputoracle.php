<?php
// Control de post
$ClaseRemedy = new remedyClass();
// Procesar oracle
if(!empty($_SESSION['requestid']))
{
    $rowremedy=$ClaseRemedy->loadremedy();
}
// Controlar que quiere cargar un excel reseteando el estado

// Si el estado < 2 mandar a primer formulario
if($rowremedy['estado'] < 2)
{
    header("Location: adminremedy.php");
}

// Controlar valor.
if (isset($_POST['reload_remedy']))
{
    $ClaseRemedy->resetremedy();
    header("Location: adminremedy.php");
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
    <h3 style="color:#e60202">Procesar datos Oracle</h3>
    <form id="fremedy" method="post" enctype="multipart/form-data">
    <table border="0" width="90%" cellspacing="10">
        <tbody>
            <tr>
                <td>
                <p>Ticket Remedy</p>
                <input type="text" name="requestid" size=15 id="requestid" value="<?php echo $rowremedy['requestid']; ?>" required="required"/>
                <?php
                    // Dejar recargar solo si estado = 2
                    if($rowremedy['estado'] == 2)
                    {
                        echo '<input type="submit" name="reload_remedy" value="Recargar Excel"/>';
                    }
                ?>
                </td>
            </tr>
            <tr>
                <!--Datos de conexión readonly.-->
                <td>
                    <p>DB Oracle</p>
                    <input type="text" style="background-color:#cccccc;" name="dbname" id="dbname" value="<?php echo $rowremedy['dbname'];?>" readonly/>
                    <p>Usuario DBA</p>
                    <input type="text" style="background-color:#cccccc;" name="userdba" id="userdba" value="<?php echo $rowremedy['userdba'];?>" readonly/>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="conexion">Cadena conexión</label> 
                    <input type="text" style="background-color:#cccccc;" size="70" name="conexion" id="conexion" value="<?php echo $rowremedy['conexion'];?>" readonly/>
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

            </tr>
        </tbody>
    </table>
    </form>
</div>
