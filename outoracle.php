<?php
$ClassOra = new oraClass();
$rlineas=$ClassOra->ddluser($_SESSION['requestid']);
?>

<div id="doracle">
    <h3 style="color:#e60202">Previo sentencias Oracle</h3>
    <form id="foracle" method="post" enctype="multipart/form-data">
    <table border="0" width="90%" cellspacing="10">
        <thead>
<!--           <tr>
             <th>DDL</th>
             <th>Estado</th>
             <th>festado</th>
             <th>err_code</th>
             <th>err_message</th>
           </tr>-->
       </thead>
        <tbody>
            <tr>
                <td>
                <textarea rows="6" cols="70" style="background-color:#cccccc;" name="ddlgrant" id="ddlgrant" readonly><?php 
                // Recorrer las filas de datos
                while($row = mysqli_fetch_assoc($rlineas))
                {
                    echo $row['ddl'].' | ';
                }
                ?>
                </textarea> 
                </td>
            </tr>
        </tbody>
    </table>
    </form>
</div>