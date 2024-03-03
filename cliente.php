<?php
include "config.php";
include "utils.php";


$dbConn =  connect($db);


if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
  // ontenemos al cliente por cedula o ruc se nesesita el parametro clien_cedula_ruc
  if (isset($_GET['clien_cedula_ruc']))
  {
    $sql = $dbConn->prepare("SELECT * FROM cliente where clien_cedula_ruc=:clien_cedula_ruc");
    $sql->bindValue(':clien_cedula_ruc', $_GET['clien_cedula_ruc']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count==0) {
      header("HTTP/1.1 204 No Content");
      echo "No existe el registro ",$_GET['clien_cedula_ruc'];
      
    }else{
// en caso de haver realizado la consulta con exito muestra el resultado en json
      echo "Si existe el registro";
      $sql = $dbConn->prepare("SELECT cliente.clien_nombre, cliente.clien_apellido, producto.prod_nombre FROM pedido JOIN producto ON pedido.id_producto= producto.id JOIN cliente ON pedido.id_cliente= cliente.id where cliente.clien_cedula_ruc=:clien_cedula_ruc ");
      $sql->bindValue(':clien_cedula_ruc', $_GET['clien_cedula_ruc']);
      $sql->execute();
      header("HTTP/1.1 200 OK");
      echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
      exit();
    }

  }
  else {
    // busqueda por correo se ocupa el parametro clien_correo
    if(isset($_GET['clien_correo'])){
      $sql = $dbConn->prepare("SELECT * FROM cliente where clien_correo=:clien_correo");
      $sql->bindValue(':clien_correo', $_GET['clien_correo']);
      $sql->execute();
      $row_count =$sql->fetchColumn();
      
      if ($row_count==0) {
        header("HTTP/1.1 204 No Content");
        echo "No existe el registro ",$_GET['clien_correo'];
        
      }else{
        // al ser positiva la busqueda muestra el resultado en json
        echo "Si existe el registro";
        
        $sql = $dbConn->prepare("SELECT * FROM cliente where clien_correo=:clien_correo");
        $sql->bindValue(':clien_correo', $_GET['clien_correo']);
        $sql->execute();
        header("HTTP/1.1 200 OK");
        // echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
        echo json_encode( $sql->fetchAll(PDO::FETCH_ASSOC)  );
        exit();
      }

    }else{
      // muestra todos los clientes registrados 
      //Mostrar lista de post
      $sql = $dbConn->prepare("SELECT * FROM cliente");
      $sql->execute();
      $sql->setFetchMode(PDO::FETCH_ASSOC);
      header("HTTP/1.1 200 OK");
      echo json_encode( $sql->fetchAll()  );
      exit();
    }
  }

}

// Crear un nuevo post
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  if (isset($_POST['clien_cedula_ruc'])){
    // buscamos si se encuentra registrado por la cedula o ruc
    // clien_apellido
// clien_edad
// clien_genero
// clien_cedula_ruc
// clien_telefono
// clien_direccion
// clien_correo
    $sql = $dbConn->prepare("SELECT * FROM cliente where clien_cedula_ruc=:clien_cedula_ruc");
    $sql->bindValue(':clien_cedula_ruc', $_POST['clien_cedula_ruc']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count>0) {
      header("HTTP/1.1 204 No Content");
      echo "Ya existe el cliente ", $_POST['clien_cedula_ruc'];
    }else{
      echo "Guardado Exitosamente";
      $input = $_POST;
      // inestamos los datos del cliente con un campo basio que es el id
      $sql = "INSERT INTO cliente
            VALUES
            ('',:clien_nombre, :clien_apellido, :clien_edad, :clien_genero, :clien_cedula_ruc, :clien_telefono, :clien_direccion, :clien_correo)";
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
    echo "EL campo clien_cedula_ruc es obligatorio";
  }

}

//Borrar
if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
  // se borrara el cliente por cedula o ruc con parametro clien_cedula_ruc clien_nombre

  if (isset($_GET['clien_cedula_ruc'])){
    $sql = $dbConn->prepare("SELECT COUNT(*) FROM cliente where clien_cedula_ruc=:clien_cedula_ruc");
    $sql->bindValue(':clien_cedula_ruc', $_GET['clien_cedula_ruc']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    // si la consulta realizda no tiene datos ira por if
    if ($row_count == 0) {
      echo "No existe el registro ",$_GET['clien_cedula_ruc'];
      header("HTTP/1.1 400 Bad Request"); //error 400 por no ejecutar el delete

    }else{
      // si ontubo datos la consulta anterior procese con la eliminacion
      $clien_cedula_ruc = $_GET['clien_cedula_ruc'];
      $statement = $dbConn->prepare("DELETE FROM cliente where clien_cedula_ruc=:clien_cedula_ruc");
      $statement->bindValue(':clien_cedula_ruc', $clien_cedula_ruc);
      $statement->execute();
      echo "Eliminado el registro ",$_GET['clien_cedula_ruc'];
    	header("HTTP/1.1 200 OK");
    	exit();
    }
  }else{
    echo "El parametro clien_cedula_ruc es obligatorio";
  }


}

//Actualizar
if ($_SERVER['REQUEST_METHOD'] == 'PUT')
{
  // actualizar los datos del cleinte por cedula con parametro clien_cedula_ruc y parametros clien_nombre
// clien_apellido
// clien_edad
// clien_genero
// clien_cedula_ruc
// clien_telefono
// clien_direccion
// clien_correo opcionales
  if (isset($_GET['clien_cedula_ruc'])){
    $sql = $dbConn->prepare("SELECT * FROM cliente where clien_cedula_ruc=:clien_cedula_ruc");
    $sql->bindValue(':clien_cedula_ruc', $_GET['clien_cedula_ruc']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count>0) {
      $input = $_GET;
      $postId = $input['clien_cedula_ruc'];
      $fields = getParams($input);

      $sql = "
            UPDATE cliente
            SET $fields
            WHERE clien_cedula_ruc='$postId'
             ";

      $statement = $dbConn->prepare($sql);
      bindAllValues($statement, $input);

      $statement->execute();
      header("HTTP/1.1 200 OK");
      echo "Actualizado Exitosamente el cliente ", $_GET['clien_cedula_ruc'];
      exit();
    }else{
      header("HTTP/1.1 204 No Content");
      echo "No existe el clien_cedula_ruc ", $_GET['clien_cedula_ruc'];
    }
  }else{
    echo "El parametro clien_cedula_ruc es obligatorio";
  }
}


//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");

?>