<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of remedyClass
 *
 * @author Administrador
 */
class remedyClass {
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
    // Cargar o crear remedy.
    public function loadremedy()
    {
        // Controlar 15 caracteres y 3 primeros INC
        if (strlen($_SESSION['requestid']) <> 15)
        {
            echo "La longitud correcta del ticket remedy es de 15.";
            return 0;
        }
        $_SESSION['requestid'] = strtoupper($_SESSION['requestid']);
        if (substr($_SESSION['requestid'], 0, 3) <> 'INC'){
            echo "Los ticket remedy comienzan por INC.";
            return 0;
        }
        // Controlar parte numerica
        if (!is_numeric(substr($_SESSION['requestid'],-12))) {
            echo "Caracteres no numéricos en la parte numérica del ticket:".substr($_SESSION['requestid'],-12);
            return 0;
        }
        $conn = $this->conectar();
        $sselect="select * from remedy where requestid='".$_SESSION['requestid']."'";
        $result = $conn->query($sselect) or exit("Codigo de error ({$conn->errno}): {$conn->error}");
        $rowremedy = mysqli_fetch_array($result);
        // Control ticket en B.D.
        if(mysqli_num_rows($result)== 0){
            return $this->insertremedy();
        }
        else{
            // Existia, retornar resultado.
             return $rowremedy;
        }
    }
    // Actualizar Usuarios
    public function updateremedy($pfila = NULL)
    {
        // Control post
        $conn = $this->conectar();
        // Controlar por fila
        $aupdate = array();
        if (!empty($pfila)) {
            $aupdate['peticionario']=$pfila['peticionario'];
            $aupdate['fauto']=$pfila['fauto'];
            $aupdate['estado']=$pfila['estado'];
            $aupdate['dbname']=$pfila['dbname'];
            $aupdate['userdba']=$pfila['userdba'];
            $aupdate['conexion']=$pfila['conexion'];
            $aupdate['comentario']=$pfila['comentario'];
            $aupdate['requestid']=$pfila['requestid'];
        }else{
            $aupdate['peticionario']=$_POST['peticionario'];
            $aupdate['fauto']=$_POST['fauto'];
            $aupdate['estado']=$_POST['estado'];
            $aupdate['dbname']=$_POST['dbname'];
            $aupdate['userdba']=$_POST['userdba'];
            $aupdate['conexion']=$_POST['conexion'];
            $aupdate['comentario']=$_POST['comentario'];
            $aupdate['requestid']=$_POST['requestid'];
        }
        
        // Preparar sentencia
        $stmt = $conn->prepare("UPDATE remedy SET peticionario = ?,
            fauto = ?, 
            estado = ?,  
            dbname = ?,
            userdba = ?,
            conexion = ?,
            comentario = ?
            WHERE requestid = ?");
        // Bind variables
        $stmt->bind_param('ssisssss',
        $aupdate['peticionario'],
        $aupdate['fauto'],
        $aupdate['estado'],
        $aupdate['dbname'],
        $aupdate['userdba'],
        $aupdate['conexion'],
        $aupdate['comentario'],
        $aupdate['requestid']);
        //echo "stmt bind_param correcto.";
        // Ejecutar
        if (!$stmt->execute()) {
            echo "Falló la ejecución del update: (" . $stmt->errno . ") " . $stmt->error;
            return -1;
        }
        // Finalizar
        $stmt->close();
        return 1;
    }
    public function deleteremedy()
    {
        $conn = $this->conectar();
        $stmt = $conn->prepare("DELETE FROM remedy WHERE requestid = ?");
        // Vincular variables
        if (!$stmt->bind_param("i", $_POST['requestid'])) {
            echo "Falló la vinculación de parámetros: (" . $stmt->errno . ") " . $stmt->error;
            return -1;
        }
        // Ejecutar
        if (!$stmt->execute()) {
            echo "Falló la ejecución del delete: (" . $stmt->errno . ") " . $stmt->error;
            return -1;
        }else{
            //echo "Se borró correctamente el ID:".$_POST['idbitdelete'];
        }
        $stmt->close();
        return 1;
    }
    public function insertremedy()
    {
        $conn = $this->conectar();
        // Al logarse lo primero en oracle, se tienen q tener las variables de sesión.
        $sinsert = "INSERT INTO remedy (requestid,dbname,userdba,conexion) VALUES ('".$_SESSION['requestid']."','".$_SESSION['vsid']."','".$_SESSION['vuser']."','".$_SESSION['vconnoracle']."')";
       // echo $sinsert;
        
        if ($conn->query($sinsert) === TRUE)
        {
            //echo "Nuevo bitname creado.";
        } else {
            echo "Falló la inserción: (" . $conn->errno . ") " . $conn->error;
        }
        // Cargar y retornar.
        $sselect="select * from remedy where requestid='".$_SESSION['requestid']."'";
        
        $result = $conn->query($sselect) or exit("Codigo de error ({$dbhandle->errno}): {$dbhandle->error}");
        $rowremedy = mysqli_fetch_array($result);
        $conn->close();
        return $rowremedy;
    }
    public function resetremedy()
    {
        // Borra los usuarios del remedy y deja el estado a 0 del ticket
        $conn = $this->conectar();
        $ssql = array();
        $ssql[0]= "delete from usuario where requestid ='".$_SESSION['requestid']."'";
        // Repintar las variables de conexión
        $ssql[1]= "update remedy set estado=0, "
                . "dbname = '".$_SESSION['vsid']."',"
                . "userdba = '".$_SESSION['vuser']."',"
                . "conexion = '".$_SESSION['vconnoracle']."'"
                . "where requestid ='".$_SESSION['requestid']."'";
        // Recorrer el array de insert
        foreach ($ssql as $vsql) {
            if ($conn->query($vsql) === TRUE)
            {
            } else {
                echo "Falló en el reset: (" . $conn->errno . ") " . $conn->error;
                return -1;
            }
        } 
        // Retornar la llamada a la funcion
       return 1;
        
    }
    public function updateexcel()
    {
        $target_dir = "excel/";
        //$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $target_file = $target_dir . $_SESSION['requestid'].".xlsx";
        $uploadOk = 1;
        $FileType = pathinfo($target_file,PATHINFO_EXTENSION);
        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = filesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check !== false) {
                $uploadOk = 1;
            } else {
                echo "El fichero no se ha detectado, selecione otro fichero.";
                $uploadOk = 0;
            }
        }
        // Check if file already exists. Sustituir
        if (file_exists($target_file)) {
        //    echo "Se procederá a sustituir la imagen actual.";
            //$uploadOk = 0;
        }
        // Check file size. "2MB"
        if ($_FILES["fileToUpload"]["size"] > 2097152) {
            echo "Sólo se permite subir ficheros de hasta 2MB.";
            $uploadOk = 0;
        }
        // Allow certain file formats
        if($FileType != "xlsx") {
            echo "El formato compatible es xlsx.Formato de fichero:".$FileType;
            $uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Lo sentimos, el fichero no puede subirse al servidor.";
        // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                // Sólo actualizar si el estado es 0.
                if ($_POST['estado'] < 1) 
                {
                    $_POST['estado'] = 1;
                }
                // Retornar la llamada a la funcion
                if ($this->updateremedy() > 0)
                {
                    return $this->loadremedy();
                }
            } else {
                echo "Lo sentimos, se produjo un error en la subida del fichero. Vuelva a intentarlo.";
                return $this->loadremedy();
            }
        }
    }
    public function procexcel()
    {
        // Ya esta cargada la libreria PHPExcel
        // Crear nuevo objeto excel
        $objPHPExcel = new PHPExcel();
        $target_dir = "excel/";
        // Solapa de lectura y 11 columnas
        $sheetcarga = 'DB USUARIO'; 
        $inputFileName = $target_dir . $_SESSION['requestid'].".xlsx";

        try {           
            /**  Identificar tipo de fichero **/
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            /**  Crear el lector del fichero con el tipo identifcado **/
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            /**  Sólo leer la sheetname deseada **/ 
            $objReader->setLoadSheetsOnly($sheetname); 
            /**  Load $inputFileName to a PHPExcel Object  **/ 
            $objPHPExcel = $objReader->load($inputFileName); 
            // Controlar que la sheet 0 es la sheetcarga
            try {
                $objWorksheet =  $objPHPExcel->setActiveSheetIndex(0); 
            }
            catch (Exception $e) {
                echo 'El fichero no contiene la solapa: '.$sheetcarga;
                return $this->loadremedy();
            }
            // Controlar el nombre de la solapa 0
            if ($objWorksheet->getTitle()<> $sheetcarga)
            {
                echo "La primera solapa no tiene el nombre correcto:".$objWorksheet->getTitle()." no es igual a ".$sheetcarga;
                return $this->loadremedy();
            }
            // Cargado $objPHPExcel y con solapa correcta.
            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            array_shift($sheetData);
            // Recorrer todas las filas de la hoja
            $test_array = array();
            foreach($sheetData as $key=>$val){
                // Crear fila usuario y filas grants
                if ($this->insertuser($val) < 0)
                {
                    $this->resetremedy();
                    return $this->loadremedy();
                }
            }

        } catch(PHPExcel_Reader_Exception $e) {
            die('Error al cargar fichero: '.$e->getMessage());
        }
        // Después del proceso retornar la fila remedy.
        // Si llega hasta aqui. Actualizar remedy
        if ($_POST['estado'] < 2) 
        {
            $_POST['estado'] = 2;
        }
        // Retornar la llamada a la funcion
        if ($this->updateremedy() > 0)
        {
            return $this->loadremedy();
        }
        return 1;
    }

    private function insertuser($afila)
    {
//      Se le pasa la fila del excel y se realiza insert en tabla user.Las columnas sólo deben tener 1 fila
//      A- INSTANCIA  --> Nombre de la instancia en la que se quiere crear el usuario - OBLIGATORIO
//      B- USUARIO  --> nombre del usuario a crear - OBLIGATORIO
//      C- PASSWORD  --> si se especifica  la contraseña, hay que tener en cuenta que ésta se debe cambiar con el primer uso. - OPCIONAL
//      D- DEFAULT TABLESPACE --> tablespace por defecto al que asignar el usuario (no debe ser USER, TEMP, SYSTEM, SYSAUX, TOOLS) - OBLIGATORIO
//      E- TEMPORARY TABLESPACE --> nombre del tablespace temporal, sólo si es necesario especificar uno que no sea el que se asigna por defecto. - OPCIONAL 
        $conn = $this->conectar();
        $iopeuser = 0;
        // Control de conexión oracle igual a columna 1 de excel.
        if ($_SESSION['vsid'] <> $afila['A'])
        {
            echo "La columna instancia no corresponde con la conexión de instacia oracle:".$_SESSION['vsid']." <> ".$afila['A'].".";
            return -1;
        }
        // Si tiene password es alta, sino es modificación 1,2.
        if (!empty($afila['C']))
        {
            $iopeuser= 1;
        }else{
            $iopeuser= 2;
        }
        $sinsert = "INSERT INTO usuario (requestid,tipoop,usuario,password,default_tablespace,temporary_tablespace) "
                . "VALUES ('".$_SESSION['requestid']."',".$iopeuser.","
                ."'".trim($afila['B'])."','".trim($afila['C'])."','".trim($afila['D'])."','".trim($afila['E'])."')";
        // echo $sinsert;
        
        if ($conn->query($sinsert) === TRUE)
        {
            //echo "Nueva fila usuario.";
        } else {
            echo "Falló la inserción: (" . $conn->errno . ") " . $conn->error;         
            return -1;
        }
        // Coger el último id
        $iduser =  $conn->insert_id;
        // Meter los insert de grants
        if ($this->insertgrants($afila,$iduser)< 0)
        {
            $this->deleteuser($iduser);
            return -1;
        }
        return 1;
    }
    
    // Borrar el usuario. Borra en cascada grants.
    private function deleteuser($iduser)
    {
        $conn = $this->conectar();
        $stmt = $conn->prepare("DELETE FROM usuario WHERE idusuario = ?");
        // Vincular variables
        if (!$stmt->bind_param("i", $iduser)) {
            echo "Falló la vinculación de parámetros: (" . $stmt->errno . ") " . $stmt->error;
            return -1;
        }
        // Ejecutar
        if (!$stmt->execute()) {
            echo "Falló la ejecución del delete: (" . $stmt->errno . ") " . $stmt->error;
            return -1;
        }else{
            //echo "Se borró correctamente el ID:".$_POST['idbitdelete'];
        }
        $stmt->close();
        return 1;       
    }
    // Insert grants
    private function insertgrants($afila,$iduser)
    {
//      Se le pasa la fila del excel y se realiza insert en tabla grant_usuario. Pueden tener varias filas
//      G- ROLE --> rol que se debe asignar (una linea por role) - OPCIONAL
//      H- GRANT OBJECT --> permisos sobre objetcos (hay que especificar el permiso) . (una linea por permiso y objeto) - OPCIONAL
//      I- OBJECT GRANTED -> objeto (tabla,, procedimiento, funcio, etc) nsobre el que asignar lso permisos descritos en el campo anterior (una linea por permiso y objeto) - OPCIONAL
//      J- GRANT SYSTEM --> permisos del sistema (si fuera necesario especificar permisos diferenes a lo que se incluyen en los rols) (una linea por permiso) OPCIONAL
//      K- PROFILE --> perfil que se debe asignar. - OPCIONAL

        $conn = $this->conectar();
        // Hacer un array de insert y ejecutarlos del tiron
        $sinsert = array();
        $icont = 0;
        // Controlar F
        $acelda = explode("\n", $afila['F']);
        if(!empty($acelda)) 
        {
            // Recorrer las posibles filas de la celda
            foreach ($acelda as $vfila) {
                $sinsert[$icont] = "insert INTO grant_usuario(idusuario,quota_tablespace)"
                        . "VALUES (".$iduser.",'".$vfila."')";
                $icont++;
            }
            
        }
        // Controlar G
        $acelda = explode("\n", $afila['G']);
        if(!empty($acelda)) 
        {
            // Recorrer las posibles filas de la celda
            foreach ($acelda as $vfila) {
                $sinsert[$icont] = "insert INTO grant_usuario(idusuario,role)"
                        . "VALUES (".$iduser.",'".$vfila."')";
                $icont++;
            }
            
        }
        // Controlar H,I tipo_grant,grant_object  Mismo Nº filas
        $aceldah = explode("\n", $afila['H']);
        $aceldai = explode("\n", $afila['I']);
        if(!empty($aceldah) and !empty($aceldai) and count($aceldah)==count($aceldai)) 
        {
            // Recorrer las posibles filas de la celda
            $igrant = 0;
            foreach ($aceldah as $vfila) {
                $sinsert[$icont] = "insert INTO grant_usuario(idusuario,tipo_grant,grant_object)"
                        . "VALUES (".$iduser.",'".$vfila."','".$aceldai[$igrant]."')";
                $igrant++;
                $icont++;
            }
        }else{
            // Error en columnas o no concuerdan
            echo "Los datos de las columnas H y G no concuerdan.";
            return -1;    
        }
        // Controlar J grant_system
        $acelda = explode("\n", $afila['J']);
        if(!empty($acelda)) 
        {
            // Recorrer las posibles filas de la celda
            foreach ($acelda as $vfila) {
                $sinsert[$icont] = "insert INTO grant_usuario(idusuario,grant_system)"
                        . "VALUES (".$iduser.",'".$vfila."')";
                $icont++;
            }  
        }
        // Controlar K profile 
        $acelda = explode("\n", $afila['K']);
        if(!empty($acelda)) 
        {
            // Recorrer las posibles filas de la celda
            foreach ($acelda as $vfila) {
                $sinsert[$icont] = "insert INTO grant_usuario(idusuario,profile)"
                        . "VALUES (".$iduser.",'".$vfila."')";
                $icont++;
            }  
        }
        // Recorrer el array de insert
        foreach ($sinsert as $vsql) {
            if ($conn->query($vsql) === TRUE)
            {
            } else {
                echo "Falló la inserción: (" . $conn->errno . ") " . $conn->error;
                return -1;
            }
        } 
        // Retornar la llamada a la funcion
       return 1;

    }
//End of class.
}
