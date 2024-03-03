<?php
include "config.php";
include "utils.php";


$dbConn =  connect($db);

/*
  listar todos los posts o solo uno
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
      
  if (isset($_GET['id']))
  {
    $sql = $dbConn->prepare("SELECT * FROM usuarios where id=:id");
    $sql->bindValue(':id', $_GET['id']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count==0) {
      header("HTTP/1.1 204 No Content");
      echo "No existe el registro ",$_GET['id'];
      
    }else{
      
      echo "Si existe el registro";
      $sql = $dbConn->prepare("SELECT * FROM usuarios where id=:id");
      $sql->bindValue(':id', $_GET['id']);
      $sql->execute();
      header("HTTP/1.1 200 OK");
      echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
      exit();
    }
    
  }else {
    //Busqueda por empresa left join
    if(isset($_GET['empresa_id'])){
      $sql = $dbConn->prepare("SELECT * FROM usuarios where empresa_id=:empresa_id");
      $sql->bindValue(':empresa_id', $_GET['empresa_id']);
      $sql->execute();
      $row_count =$sql->fetchColumn();
      
      if ($row_count==0) {
        header("HTTP/1.1 204 No Content");
        echo "No existe el registro ",$_GET['empresa_id'];
        
      }else{
        
        echo "Si existe el registro";
        
        $sql = $dbConn->prepare("SELECT empresa.codigo as nombre_corto, empresa.nombre as empresa, usuarios.nombre,usuarios.cedula,usuarios.telefono,usuarios.email FROM usuarios JOIN empresa ON usuarios.empresa_id = empresa.id  where empresa_id=:empresa_id");
        $sql->bindValue(':empresa_id', $_GET['empresa_id']);
        $sql->execute();
        header("HTTP/1.1 200 OK");
        // echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
        echo json_encode( $sql->fetchAll(PDO::FETCH_ASSOC)  );
        exit();
      }

    }else{
      if(isset($_GET['codigo'])){
        $sql = $dbConn->prepare("SELECT * FROM empresa where codigo=:codigo");
        $sql->bindValue(':codigo', $_GET['codigo']);
        $sql->execute();
        $row_count =$sql->fetchColumn();
        
        if ($row_count==0) {
          header("HTTP/1.1 204 No Content");
          echo "No existe el registro ",$_GET['codigo'];
          
        }else{
          
          echo "Si existe el registro";
          
          $sql = $dbConn->prepare("SELECT empresa.codigo as nombre_corto, empresa.nombre as empresa, usuarios.nombre,usuarios.cedula,usuarios.telefono,usuarios.email FROM empresa JOIN usuarios ON usuarios.empresa_id = empresa.id  where codigo=:codigo");
          $sql->bindValue(':codigo', $_GET['codigo']);
          $sql->execute();
          header("HTTP/1.1 200 OK");
          // echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
          echo json_encode( $sql->fetchAll(PDO::FETCH_ASSOC)  );
          exit();
        }
  
      }else{

        //Mostrar lista de post
        $sql = $dbConn->prepare("SELECT * FROM usuarios");
        
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode( $sql->fetchAll()  );
        exit();
      }

    }
  }

}

// Crear un nuevo post
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  if (isset($_POST['cedula'])){
    $sql = $dbConn->prepare("SELECT * FROM usuarios where cedula=:cedula");
    $sql->bindValue(':cedula', $_POST['cedula']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count>0) {
      header("HTTP/1.1 204 No Content");
      echo "Ya existe la cedula ", $_POST['cedula'];
    }else{
      echo "Guardado Exitosamente";
      $input = $_POST;
      $sql = "INSERT INTO usuarios
            (nombre, cedula, telefono, email, empresa_id)
            VALUES
            (:nombre, :cedula, :telefono, :email, :empresa_id)";
      $statement = $dbConn->prepare($sql);
      bindAllValues($statement, $input);
      $statement->execute();
      $postId = $dbConn->lastInsertId();
      if($postId)
      {
        $input['id'] = $postId;
        header("HTTP/1.1 200 OK");
        echo json_encode($input);
        exit();
  	 }
    }
  }else{
    echo "EL campo cedula es obligatorio";
  }

}

//Borrar
if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
  if (isset($_GET['id'])){
  	// $id = $_GET['id'];
    $sql = $dbConn->prepare("SELECT COUNT(*) FROM usuarios where id=:id");
    $sql->bindValue(':id', $_GET['id']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    // echo $row_count;
    if ($row_count == 0) {
      echo "No existe el registro ",$_GET['id'];
      header("HTTP/1.1 400 Bad Request"); //error 400 por no ejecutar el delete

    }else{
      $id = $_GET['id'];
      $statement = $dbConn->prepare("DELETE FROM usuarios where id=:id");
      $statement->bindValue(':id', $id);
      $statement->execute();
      echo "Eliminado el registro ",$_GET['id'];
    	header("HTTP/1.1 200 OK");
    	exit();
    }
  }else{
    echo "El parametro id es obligatorio";
  }


}

//Actualizar
if ($_SERVER['REQUEST_METHOD'] == 'PUT')
{


  if (isset($_GET['cedula'])){
    $sql = $dbConn->prepare("SELECT * FROM usuarios where cedula=:cedula");
    $sql->bindValue(':cedula', $_GET['cedula']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count>0) {
      $input = $_GET;
      $postId = $input['cedula'];
      $fields = getParams($input);

      $sql = "
            UPDATE usuarios
            SET $fields
            WHERE cedula='$postId'
             ";

      $statement = $dbConn->prepare($sql);
      bindAllValues($statement, $input);

      $statement->execute();
      header("HTTP/1.1 200 OK");
      echo "Actualizado Exitosamente el usuario ", $_GET['cedula'];
      exit();
    }else{
      header("HTTP/1.1 204 No Content");
      echo "No existe la cedula ", $_GET['cedula'];
    }
  }else{
    echo "El parametro cedula es obligatorio";
  }
}


//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");

?>