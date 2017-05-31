<?php
    session_start();
    $db = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=172.17.5.24)(PORT=1523))(CONNECT_DATA=(SERVER=dedicated)(SID=ono10g)))";
    $db = $_SESSION['vconnoracle'];
    
    echo $db;
    if($c = OCILogon($_SESSION['vuser'], $_SESSION['vpass'], $db))
    {
        echo "Conexion a oracle realizada correctamente.\n";
	$stid = oci_parse($c, 'SELECT * FROM v$instance');
oci_execute($stid);

echo "<table border='1'>\n";
while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
    echo "<tr>\n";
    foreach ($row as $item) {
        echo "    <td>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "") . "</td>\n";
    }
    echo "</tr>\n";
}
echo "</table>\n";
        OCILogoff($c);
    }
    else
    {
        $err = OCIError();
        echo "Connection failed." . $err[text];
    }
?>


