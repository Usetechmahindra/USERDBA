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
class oraClass {
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
        return $sddl;
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
    //End off class
}
