<html>
<head>
<meta charset="UTF-8">
<style>
div#dgridvf {
    background-color: white;
    overflow: hidden;
    overflow-y: scroll;
    height: 90%;
    width: 98%;
}
</style>
<title></title>
<?php
$ClassOra = new oraClass();
$rlineas=$ClassOra->ddluser($_SESSION['requestid']);
?>
</head>
<body>
    <h3 style="color:#e60202">Previo sentencias Oracle</h3>
    <div id="dgridvf">
        <form id="fgrid" method="post">
        <table class="tgridvf">
        <thead>
           <tr>
             <th>ESTADO</th>
             <th>DDL</th>
             <th>F.ESTADO</th>
             <th>ERROR</th>
           </tr>
       </thead>
        <tbody>
           <?php while($row = mysqli_fetch_assoc($rlineas) ){
           ?>
            <input type="hidden" name="idsql[]" value="<?php echo $row['idsql'];?>">
            <tr>
                <!--Columnas de la fila tr-->
                <td>
                <select name = "estado[]" disabled>
                    <option value="0" <?php if($row['estado'] == '0') {echo " SELECTED ";} echo">"; ?>SIN APLICAR</option>
                    <option value="-1" <?php if($row['estado'] == '-1') {echo " SELECTED ";} echo">"; ?>ERROR</option>
                    <option value="1" <?php if($row['estado'] == '1') {echo " SELECTED ";} echo">"; ?>PROCESADO</option>
                </select>
                </td>
                <td style="width: 350px;"><?php echo $row['ddl'];?></td>
                <td><?php echo date("d/m/Y", strtotime($row['festado']));?></td>
                <td style="width: 300px;"><?php 
                    if(!empty($row['err_code'])) {
                        echo '[ '.$row['err_code'].'] '.$row['err_message'];
                    }
                    ?>
                </td> 
            </tr>
        <!--Fin while PHP-->
        <?php } ?>
        </tbody>
    </table>
    </form>
</div>
</body>
</html>