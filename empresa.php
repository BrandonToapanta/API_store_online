<?php
include "config.php";
include "utils.php";


$dbConn =  connect($db);

/*
  listar todos los posts o solo uno
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
  // optenemos los detalle_pedidos por el parametro detal_codigo
  if (isset($_GET['detal_codigo']))
  {
    $sql = $dbConn->prepare("SELECT * FROM detalle_pedido where detal_codigo=:detal_codigo");
    $sql->bindValue(':detal_codigo', $_GET['detal_codigo']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count==0) {
      header("HTTP/1.1 204 No Content");
      echo "No existe el registro ",$_GET['detal_codigo'];
      
    }else{
      // muestra los resultados obtenidos 
      echo "Si existe el registro";
      $sql = $dbConn->prepare("SELECT * FROM detalle_pedido where detal_codigo=:detal_codigo");
      $sql->bindValue(':detal_codigo', $_GET['detal_codigo']);
      $sql->execute();
      header("HTTP/1.1 200 OK");
      echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
      exit();
    }

  }
  else {
    // Realizara una consulta de los productos obtenidos por el numero de detalle_pedido por el parametro id
    if(isset($_GET['id'])){
      $sql = $dbConn->prepare("SELECT * FROM detalle_pedido where id=:id");
      $sql->bindValue(':id', $_GET['id']);
      $sql->execute();
      $row_count =$sql->fetchColumn();
      
      if ($row_count==0) {
        header("HTTP/1.1 204 No Content");
        echo "No existe el registro ",$_GET['id'];
        
      }else{
        
        echo "Si existe el registro";
        
        $sql = $dbConn->prepare("SELECT * FROM detalle_pedido where id=:id ");
        $sql->bindValue(':id', $_GET['id']);
        $sql->execute();
        header("HTTP/1.1 200 OK");
        // echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
        echo json_encode( $sql->fetchAll(PDO::FETCH_ASSOC)  );
        exit();
      }

    }else{
      //Mostrar todos los detalle_pedidos
      $sql = $dbConn->prepare("SELECT * FROM detalle_pedido");
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
  // ingresar detalle_pedidos con el parametro
  // id_pedido
  // detal_codigo
  // detal_cantidad
  // detal_precio_unitario
  // detal_precio_asociado
  if (isset($_POST['detal_codigo'])){
    $sql = $dbConn->prepare("SELECT * FROM detalle_pedido where detal_codigo=:detal_codigo");
    $sql->bindValue(':detal_codigo', $_POST['detal_codigo']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count>0) {
      header("HTTP/1.1 204 No Content");
      echo "Ya existe la detal_codigo ", $_POST['detal_codigo'];
    }else{
      echo "Guardado Exitosamente";
      $input = $_POST;
      // ingresamos los datos con un espacio basio para el id
      $sql = "INSERT INTO detalle_pedido
            VALUES
            ('', :id_pedido, :detal_codigo, :detal_cantidad,:detal_precio_unitario, :detal_precio_asociado)";
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
    echo "EL campo detal_codigo es obligatorio";
  }

}

//Borrar
if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
  // eliminar el detalle_pedido por el parametro detal_codigo
  if (isset($_GET['detal_codigo'])){
    $sql = $dbConn->prepare("SELECT COUNT(*) FROM detalle_pedido where detal_codigo=:detal_codigo");
    $sql->bindValue(':detal_codigo', $_GET['detal_codigo']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    // echo $row_count;
    if ($row_count == 0) {
      echo "No existe el registro ",$_GET['detal_codigo'];
      header("HTTP/1.1 400 Bad Request"); //error 400 por no ejecutar el delete

    }else{
      $detal_codigo = $_GET['detal_codigo'];
      $statement = $dbConn->prepare("DELETE FROM detalle_pedido where detal_codigo=:detal_codigo");
      $statement->bindValue(':detal_codigo', $detal_codigo);
      $statement->execute();
      echo "Eliminado el registro ",$_GET['detal_codigo'];
    	header("HTTP/1.1 200 OK");
    	exit();
    }
  }else{
    echo "El parametro detal_codigo es obligatorio";
  }


}

//Actualizar
if ($_SERVER['REQUEST_METHOD'] == 'PUT')
{

// actualizar el detalle_pedido por el parametro detal_codigo y los parametros opcionales 
  // ingresar detalle_pedidos con el parametro 
  // id_pedido
  // detal_cantidad
  // detal_precio_unitario
  // detal_precio_asociado
  if (isset($_GET['detal_codigo'])){
    // buscamos si el detalle_pedido existe
    $sql = $dbConn->prepare("SELECT * FROM detalle_pedido where detal_codigo=:detal_codigo");
    $sql->bindValue(':detal_codigo', $_GET['detal_codigo']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count>0) {
      $input = $_GET;
      $postId = $input['detal_codigo'];
      $fields = getParams($input);
      // al ser encontrado el detalle_pedido realizamos la actualizacion de datos con los parametros enviados  
      $sql = "
            UPDATE detalle_pedido
            SET $fields
            WHERE detal_codigo='$postId'
             ";

      $statement = $dbConn->prepare($sql);
      bindAllValues($statement, $input);

      $statement->execute();
      header("HTTP/1.1 200 OK");
      echo "Actualizado Exitosamente la detalle_pedido ", $_GET['detal_codigo'];
      exit();
    }else{
      header("HTTP/1.1 204 No Content");
      echo "No existe el detal_codigo ", $_GET['detal_codigo'];
    }
  }else{
    echo "El parametro detal_codigo es obligatorio";
  }
}


//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");

?>