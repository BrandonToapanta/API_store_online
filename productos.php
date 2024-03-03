<?php
include "config.php";
include "utils.php";


$dbConn =  connect($db);


if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
  // ontenemos al producto por cedula o ruc se nesesita el parametro prod_codigo
  if (isset($_GET['prod_codigo']))
  {
    $sql = $dbConn->prepare("SELECT * FROM producto where prod_codigo=:prod_codigo");
    $sql->bindValue(':prod_codigo', $_GET['prod_codigo']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count==0) {
      header("HTTP/1.1 204 No Content");
      echo "No existe el registro ",$_GET['prod_codigo'];
      
    }else{
// en caso de haver realizado la consulta con exito muestra el resultado en json
      echo "Si existe el registro";
      $sql = $dbConn->prepare("SELECT * FROM producto where prod_codigo=:prod_codigo");
      $sql->bindValue(':prod_codigo', $_GET['prod_codigo']);
      $sql->execute();
      header("HTTP/1.1 200 OK");
      echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
      exit();
    }

  }
  else {
    // busqueda por correo se ocupa el parametro prod_nombre
    if(isset($_GET['prod_nombre'])){
      $sql = $dbConn->prepare("SELECT * FROM producto where prod_nombre=:prod_nombre");
      $sql->bindValue(':prod_nombre', $_GET['prod_nombre']);
      $sql->execute();
      $row_count =$sql->fetchColumn();
      
      if ($row_count==0) {
        header("HTTP/1.1 204 No Content");
        echo "No existe el registro ",$_GET['prod_nombre'];
        
      }else{
        // al ser positiva la busqueda muestra el resultado en json
        echo "Si existe el registro";
        
        $sql = $dbConn->prepare("SELECT * FROM producto where prod_nombre=:prod_nombre");
        $sql->bindValue(':prod_nombre', $_GET['prod_nombre']);
        $sql->execute();
        header("HTTP/1.1 200 OK");
        // echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
        echo json_encode( $sql->fetchAll(PDO::FETCH_ASSOC)  );
        exit();
      }

    }else{
      // muestra todos los productos registrados 
      //Mostrar lista de post
      $sql = $dbConn->prepare("SELECT * FROM producto");
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
  if (isset($_POST['prod_codigo'])){
    // buscamos si se encuentra registrado por prod_codigo
    // prod_codigo
    // prod_nombre
    // prod_descripcion
    // prod_precio
    // prod_stok
    $sql = $dbConn->prepare("SELECT * FROM producto where prod_codigo=:prod_codigo");
    $sql->bindValue(':prod_codigo', $_POST['prod_codigo']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count>0) {
      header("HTTP/1.1 204 No Content");
      echo "Ya existe el producto ", $_POST['prod_codigo'];
    }else{
      echo "Guardado Exitosamente";
      $input = $_POST;
      // inestamos los datos del producto con un campo basado en el codigo
      $sql = "INSERT INTO producto
            VALUES
            ('',:prod_codigo, :prod_nombre, :prod_descripcion, :prod_precio, :prod_stok)";
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
    echo "EL campo prod_codigo es obligatorio";
  }

}

//Borrar
if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
  // se borrara el producto por el codigo con parametro prod_codigo

  if (isset($_GET['prod_codigo'])){
    $sql = $dbConn->prepare("SELECT COUNT(*) FROM producto where prod_codigo=:prod_codigo");
    $sql->bindValue(':prod_codigo', $_GET['prod_codigo']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    // si la consulta realizda no tiene datos ira por if
    if ($row_count == 0) {
      echo "No existe el registro ",$_GET['prod_codigo'];
      header("HTTP/1.1 400 Bad Request"); //error 400 por no ejecutar el delete

    }else{
      // si obtubo datos de la consulta anterior procese con la eliminacion
      $prod_codigo = $_GET['prod_codigo'];
      $statement = $dbConn->prepare("DELETE FROM producto where prod_codigo=:prod_codigo");
      $statement->bindValue(':prod_codigo', $prod_codigo);
      $statement->execute();
      echo "Eliminado el registro ",$_GET['prod_codigo'];
    	header("HTTP/1.1 200 OK");
    	exit();
    }
  }else{
    echo "El parametro prod_codigo es obligatorio";
  }


}

//Actualizar
if ($_SERVER['REQUEST_METHOD'] == 'PUT')
{
  // actualizar los datos del cleinte por cedula con parametro prod_codigo y parametros
    // prod_nombre
    // prod_descripcion
    // prod_precio
    // prod_stok
  if (isset($_GET['prod_codigo'])){
    $sql = $dbConn->prepare("SELECT * FROM producto where prod_codigo=:prod_codigo");
    $sql->bindValue(':prod_codigo', $_GET['prod_codigo']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count>0) {
      $input = $_GET;
      $postId = $input['prod_codigo'];
      $fields = getParams($input);

      $sql = "
            UPDATE producto
            SET $fields
            WHERE prod_codigo='$postId'
             ";

      $statement = $dbConn->prepare($sql);
      bindAllValues($statement, $input);

      $statement->execute();
      header("HTTP/1.1 200 OK");
      echo "Actualizado Exitosamente el producto ", $_GET['prod_codigo'];
      exit();
    }else{
      header("HTTP/1.1 204 No Content");
      echo "No existe el prod_codigo ", $_GET['prod_codigo'];
    }
  }else{
    echo "El parametro prod_codigo es obligatorio";
  }
}


//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");

?>