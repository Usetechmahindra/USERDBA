<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of oraClass
 *
 * @author Administrador
 */
class oraClass extends ConnectionClass{
    // Connect
    function conectar()
    {
        // Control post
        $mysqli = new mysqli($_SESSION['serverdb'],$_SESSION['dbuser'],$_SESSION['dbpass'],$_SESSION['dbname']);
        if ($mysqli->connect_errno)
        {
            echo $mysqli->host_info."\n";
            exit();
        }
        // Importante juego de caracteres
        if (!mysqli_set_charset($mysqli, "utf8")) {
            printf("Error cargando el conjunto de caracteres utf8: %s\n", mysqli_error($mysqli));
            exit();
        }
        return $mysqli;
    }
    public function ddluser($requestid)
    {
        // Con los datos de la tabla usuario genera la DDL CREATE/ALTER user oracle
        $conn = $this->conectar();
        // Cursor de todos los registros de usuario con el mismo requestid
        $sselect="select * from usuario where requestid='".$requestid."'";
        $sddl = array();
        
        $result = $conn->query($sselect) or exit("Codigo de error ({$conn->errno}): {$conn->error}");
        $icont = 0;
        while($row = mysqli_fetch_assoc($result))
        {
            // Controlar usuario
            if($row['tipoop'] == 1)
            {
                $sddl[$icont] = "CREATE USER ".$row['usuario']." IDENTIFIED BY ".$row['password'];
            }
            // Control de default tbs
            if(!empty($row['default_tablespace']))
            {
                $sddl[$icont] .= " DEFAULT TABLESPACE ".$row['default_tablespace'];
            }
            // Control tbs temporal
            if(!empty($row['temporary_tablespace']))
            {
                $sddl[$icont] .= " TEMPORARY TABLESPACE ".$row['temporary_tablespace'];
            }
            // Sólo pintar alert si se va a modificar algo del user
            if ($row['tipoop'] <> 1)
            {
                if(!empty($sddl[$icont]))
                {
                    $sddl[$icont] = "ALTER USER ".$row['usuario'].$sddl[$icont]; 
                }else{
                    // Al restar no se ve una fila sin datos.
                    $icont --;
                }
            }
            // Añadir las ddl de grants
            // Controlar el array de grants
            $agrants = $this->ddlgrants($row['idusuario'],$row['usuario']);
            foreach ($agrants as &$grant) {
                array_push($sddl,$grant);
            }
            // Si no hay lineas para el idusuario, crear
            if ($this->create_sql($row['idusuario'],$sddl) < 0)
            {
                return -1;
            }
            // Nueva fila
            $icont ++;
        }
        // Retornar todas las lineas del ticket
        return $this->getddl($requestid);
    }
    
    public function ddlgrants($iduser,$nombreuser)
    {
        // Con los datos de la tabla usuario genera la DDL CREATE/ALTER user oracle
        $conn = $this->conectar();
        // Cursor de todos los registros de usuario con el mismo requestid
        $sselect="select * from grant_usuario where idusuario=".$iduser."";
        $sddl = array();
        
        $result = $conn->query($sselect) or exit("Codigo de error ({$conn->errno}): {$conn->error}");
        $icont = 0;
        while($row = mysqli_fetch_assoc($result))
        {
            // Control quota
            if(!empty($row['quota_tablespace']))
            {
                $sddl[$icont] = "ALTER USER ".$nombreuser." QUOTA ".$row['quota_tablespace'];
                $icont ++;
            }
            // Control role
            if(!empty($row['role']))
            {
                $sddl[$icont] = "GRANT ".$row['role']." TO ".$nombreuser;
                $icont ++;
            }
            // Control grant. Tiene tipo y grant_object
            if(!empty($row['tipo_grant']))
            {
                $sddl[$icont] = "GRANT ".$row['tipo_grant']." ON ".$row['grant_object']." TO ".$nombreuser;
                $icont ++;
            }
            // Control system
            if(!empty($row['grant_system']))
            {
                $sddl[$icont] = "GRANT ".$row['grant_system']." TO ".$nombreuser;
                $icont ++;
            } 
            // Control profile
            if(!empty($row['profile']))
            {
                $sddl[$icont] = "ALTER USER ".$nombreuser." PROFILE ".$row['profile'];
                $icont ++;
            } 
        }
        return $sddl;
    }
    // Crear lineas_sql 
    private function create_sql($iduser,$alineas)
    {
        $conn = $this->conectar();
        $sselect = "select count(1) from lineas_sql where idusuario = ".$iduser;
        $result = $conn->query($sselect) or exit("Codigo de error ({$conn->errno}): {$conn->error}");
        $row = mysqli_fetch_array($result);
        // Si no hay lineas
        if($row[0] == 0)
        {
            // Recorrer el array de insert
            foreach ($alineas as $vsql) {
                $sinsert = "insert INTO lineas_sql(idusuario,ddl) VALUES (".$iduser.",'".$vsql."')";
                //echo $sinsert;
                if ($conn->query($sinsert) === TRUE)
                {
                } else {
                    echo "Falló la inserción: (" . $conn->errno . ") " . $conn->error;
                    return -1;
                }
            } 
        }
        return 1;
    }
    
    // Funcion que retorna las lineas sql.
    private function getddl($requestid)
    {
        // Con los datos de la tabla usuario genera la DDL CREATE/ALTER user oracle
        $conn = $this->conectar();
        $sselect="select * from lineas_sql where idusuario in(select idusuario from usuario where requestid ='".$requestid."')";
        $result = $conn->query($sselect) or exit("Codigo de error ({$conn->errno}): {$conn->error}");
        // Retornar el resultado
        return $result;
    }
    
    private function updateddl($fila,$conn)
    {
        // Preparar sentencia. Formato timestamp
        $nowtstamp = date('Y-m-d G:i:s');
        $stmt = $conn->prepare("UPDATE lineas_sql SET idusuario = ?,
            ddl = ?, 
            estado = ?,
            festado = ?,
            err_code = ?,
            err_message = ?
            WHERE idsql = ?");
        // Bind variables
        $stmt->bind_param('isisssi',
        $fila['idusuario'],
        $fila['ddl'],
        $fila['estado'],
        $nowtstamp,
        $fila['err_code'],
        $fila['err_message'],
        $fila['idsql']);
        //echo "stmt bind_param correcto.";
        // Ejecutar
        if (!$stmt->execute()) {
            $_SESSION['textsesion']="Falló la ejecución del update: (" . $stmt->errno . ") " . $stmt->error;
            return -1;
        }
        return 1;
    }


    // La función más importante aplicar los cambios oracle
    public function execoracle()
    {
        // LAS DDL tiene implicito el COMMIT. Sólo procesar las no procesadas.
        // /////////////////////////////////////////////////////////////////// //
        // Con los datos de la tabla usuario genera la DDL CREATE/ALTER user oracle
        $ierr = 0;
        $conn = $this->conectar();
        // Si hay problemas el usuario se borra. Volver a procesar todas las lineas.
        $sselect="select * from lineas_sql where idusuario in(select idusuario from usuario where requestid ='".$_SESSION['requestid']."')";
        // Maxima seguridad meter en y bucle try catch
        $result = $conn->query($sselect) or exit("Codigo de error ({$conn->errno}): {$conn->error}");
        // Meter en transacción todas las ejecuciones
        try {
            // Control conexión Oracle
            if ($this->conectarOracle() < 0)
            {
                return -1;
            }
            // Recorer todas las dll he intentar ejecutarlas transaccionalmente
            while($row = mysqli_fetch_assoc($result))
            {
                // Aplicar linea 
                $stid = oci_parse($_SESSION['cora'], $row['ddl']);
                $r = oci_execute($stid, OCI_NO_AUTO_COMMIT);
                if (!$r) {
                    // Aumentar contador de errores
                    $ierr +=1;
                    $e = oci_error($stid);
                    //trigger_error(htmlentities($e['message']), E_USER_ERROR);
                    // Lanzar update de la fila
                    $row['estado'] = -1;
                    $row['err_code'] = $e['code'];
                    $row['err_message'] = $e['message'];
                    
                }else {
                    // Lanzar update de la fila
                    $row['estado'] = 1;
                    $row['err_code'] = 0;
                    $row['err_message'] = "Sin errores";
                }
                // Llamar a la función de update con bin variables
                if ($this->updateddl($row,$conn) < 0)
                {
                    return -1;
                }
            }
        } catch (Exception $e) {
            $_SESSION['textsesion']='Excepción capturada: '.$e->getMessage();
            // Hacer rolback oracle
            oci_rollback($_SESSION['cora']);
            return -1;
        }
        // Si llega aqui todo OK
        // Control errores
        if ($ierr == 0){
            $_SESSION['textsesion'] = "Actualización Oracle realizada correctamente.";
        }else{
            oci_rollback($_SESSION['cora']);
            // Al ser ddl no es transaccional. Borrar los usuarios creados en oracle
            $this->rollbackddl();
            return -1;
        }
        return 1;
        
    }
    
    private function rollbackddl()
    {
        // Las ddl no son transaccionales. Por lo tanto se hay creación de usuarios se tienen que borrar posteriormente.
        // Sólo borrar el usuario si se ha creado desde el programa estado = 1.
        // Los alter no es necesario revertir dado que seguro q se vuelven a aplicar.
        $_SESSION['textsesion'] = "Actualización realizada correctamente.";
        $ierr = 0;
        $conn = $this->conectar();
        $sselect = "SELECT r.requestid,
                    r.dbname,
                    r.userdba,
                    r.conexion,
                    u.idusuario,
                    u.usuario
                    FROM remedy r,usuario u, lineas_sql l
                    where r.requestid = u.requestid
                    and u.idusuario = l.idusuario
                    and l.estado = 1
                    and l.ddl like '%CREATE USER%'
                    and r.requestid='".$_SESSION['requestid']."'
                    and u.tipoop = 1;";
               // Maxima seguridad meter en y bucle try catch
        $result = $conn->query($sselect) or exit("Codigo de error ({$conn->errno}): {$conn->error}");
        // Meter en transacción todas las ejecuciones
        try {
            // Control conexión Oracle
            if ($this->conectarOracle() < 0)
            {
                return -1;
            }
            // Recorer todas las dll he intentar ejecutarlas transaccionalmente
            while($row = mysqli_fetch_assoc($result))
            {
                // Aplicar linea 
                $sdrop ="DROP USER ".$row['usuario'];
                //echo $sdrop;
                $stid = oci_parse($_SESSION['cora'], $sdrop);
                $r = oci_execute($stid, OCI_NO_AUTO_COMMIT);
                if (!$r) {
                    // Aumentar contador de errores
                    $ierr +=1;
                    $e = oci_error($stid);
                    //trigger_error(htmlentities($e['message']), E_USER_ERROR);
                    // Lanzar update de la fila
                    $row['estado'] = -1;
                    $row['err_code'] = $e['code'];
                    $row['err_message'] = $e['message'];
                    $_SESSION['textsesion'] = $e['message'];
                    return -1;
                }
            }
        } catch (Exception $e) {
            $_SESSION['textsesion']='Excepción capturada: '.$e->getMessage();
            // Hacer rolback oracle
            oci_rollback($_SESSION['cora']);
            return -1;
        }
        // Si llega aqui todo OK
        // Control errores
        if ($ierr == 0){
            $_SESSION['textsesion'] = "Estado B.D. Oracle.Revertidos los cambios de creación de usuario.";
        }else{
            oci_rollback($_SESSION['cora']);
            return -1;
        }
        return 1;
    }
    //End off class
}
