<?php

class ConnectionClass {
    # Escritura de datos obtenidos
    function readcfg() {
      // Analizar sin secciones
      $arraycfg = parse_ini_file("cfg/mysqlinventario.ini");
      //print_r($arraycfg);
      //print_r($arraycfg['aduserinv']);
      return $arraycfg;
    }


    public function conectarBDMySQLInvent() {
      $acfg = $this->readcfg();
      if (!($linkdb=mysql_connect( $acfg["myserverinv"], $acfg["myuserinv"], $acfg["mypassinv"])))  {
         echo "Error conectando a la base de datos.";
         exit();
      }

      if (!mysql_select_db($acfg["mybdinv"],$linkdb)) {
         echo "Error seleccionando la base de datos de ".$database;
         exit();
      }
      mysql_set_charset('utf8');
      // Asignar a Variables de sesión
      $_SESSION['serverdb'] = $acfg["myserverinv"];
      $_SESSION['dbuser'] = $acfg["myuserinv"];
      $_SESSION['dbpass'] = $acfg["mypassinv"];
      $_SESSION['dbname'] = $acfg["mybdinv"];
      return $linkdb;
    }
    
    public function conectarBDMySQLuserdb() {
      $acfg = $this->readcfg();
      if (!($linkdb=mysql_connect( $acfg["myserverdba"], $acfg["myuserdba"], $acfg["mypassdba"])))  {
         echo "Error conectando a la base de datos.";
         exit();
      }

      if (!mysql_select_db($acfg["mybddba"],$linkdb)) {
         echo "Error seleccionando la base de datos de ".$database;
         exit();
      }
      mysql_set_charset('utf8'); 
      // Asignar a Variables de sesión
      $_SESSION['serverdb'] = $acfg["myserverdba"];
      $_SESSION['dbuser'] = $acfg["myuserdba"];
      $_SESSION['dbpass'] = $acfg["mypassdba"];
      $_SESSION['dbname'] = $acfg["mybddba"];
      return $linkdb;
    }
    
   public function foraclecombo()
    { // La funcion carga los datos de las B.D. Oracle en inventario. Pinta un combo donde se realiza la llamada
        // Entorno de test
        echo $vcombo="<option value=0> Seleccione una instanacia </option>";
        $dbName = 'Entorno Test';
        $cadena = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=172.17.5.24)(PORT=1523))(CONNECT_DATA=(SERVER=dedicated)(SID=ono10g)))";
        $vcombo="<option value=".$cadena.">".$dbName."</option>";
        echo $vcombo;
        return 0;
        // Cargar Array de parámetros
        $_SESSION['arraycfg'] = readcfg();
        $linkdb=conectarBDMySQLInvent($_SESSION['arraycfg']);
        $sql="select s.id as id, s.nombre as DbName, "; 
        $sql.="s.ip_servicio as ip_servicio,IFNULL(s.puerto,1521) as puerto, ";
        $sql.="s.descripcion ";
        $sql.="from Servicio s, "; 
        $sql.="TipoServicio ts, "; 
        $sql.="EstadoServicio es, "; 
        $sql.="Plataforma p ";
        $sql.="where s.tipo_servicio_id = ts.id ";
        $sql.="and ts.nombre =  'Base de datos Oracle' ";
        $sql.="and s.plataforma_id = p.id ";
        $sql.="and s.estado_id = es.id ";
        $sql.="and es.nombre = 'Activo' ";
        $sql.="group by s.direccion_servicio;";
        $resdb = mysql_query($sql,$linkdb) or die ("Error al seleccionar bases de datos Oracle");
        // Primera fila
        echo $vcombo="<option value=0> Seleccione una instanacia </option>";
        while($row = mysql_fetch_array($resdb)) { 
            $dbName = substr($row["DbName"],0,30);
            $cadena = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=".$row["ip_servicio"].")(PORT=".$row["puerto"]."))(CONNECT_DATA=(SERVER=dedicated)(SID=".$row["DbName"].")))";
            $vcombo="<option value=".$cadena.">".$dbName."</option>";
            echo $vcombo;
	}
	// Cerrar conexion
	mysql_free_result($resdb);  
    }
    
    public function conectarOracle()
    {
        // Realizar conexión con valores de session.
        $cora = oci_connect($_SESSION['vuser'], $_SESSION['vpass'], $_SESSION['vconnoracle']);
        //$cora = oci_pconnect($_SESSION['vuser'], $_SESSION['vpass'], $_SESSION['vconnoracle']);
        if (!$cora) {
          $e = oci_error();
          $_SESSION['textsesion']= "Conexión fallida a oracle." . $err[text];
          return -1;
        }
        $_SESSION['textsesion']= "";
        $_SESSION['cora'] = $cora;
        return 1;
    }
    
    public function registraMsgMantenimiento ($proceso,$msg,$conn) {

       $q_insert = "insert into mantenimiento.LogProceso (proceso, mensaje) values (\"".$proceso."\",\"".$msg."\")";
       $result = mysql_query($q_insert,$conn);
       //echo $msg_error;

    }
    
    
    // End of class
}
