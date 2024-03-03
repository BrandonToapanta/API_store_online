<?php
include "config.php";
include "utils.php";


$dbConn =  connect($db);

/*
  listar todos los posts o solo uno
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
  // optenemos los pedidos por el parametro ped_codigo
  if (isset($_GET['ped_codigo']))
  {
    $sql = $dbConn->prepare("SELECT * FROM pedido where ped_codigo=:ped_codigo");
    $sql->bindValue(':ped_codigo', $_GET['ped_codigo']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count==0) {
      header("HTTP/1.1 204 No Content");
      echo "No existe el registro ",$_GET['ped_codigo'];
      
    }else{
      // muestra los resultados obtenidos 
      echo "Si existe el registro";
      $sql = $dbConn->prepare("SELECT * FROM pedido where ped_codigo=:ped_codigo");
      $sql->bindValue(':ped_codigo', $_GET['ped_codigo']);
      $sql->execute();
      header("HTTP/1.1 200 OK");
      echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
      exit();
    }

  }
  else {
    // Realizara una consulta de los productos obtenidos por el numero de pedido por el parametro ped_numero
    if(isset($_GET['ped_numero'])){
      $sql = $dbConn->prepare("SELECT * FROM pedido where ped_numero=:ped_numero");
      $sql->bindValue(':ped_numero', $_GET['ped_numero']);
      $sql->execute();
      $row_count =$sql->fetchColumn();
      
      if ($row_count==0) {
        header("HTTP/1.1 204 No Content");
        echo "No existe el registro ",$_GET['ped_numero'];
        
      }else{
        
        echo "Si existe el registro";
        
        $sql = $dbConn->prepare("SELECT pedido.ped_numero as numero_pedido, producto.prod_nombre FROM pedido JOIN producto ON pedido.id_producto= producto.id where ped_numero=:ped_numero");
        $sql->bindValue(':ped_numero', $_GET['ped_numero']);
        $sql->execute();
        header("HTTP/1.1 200 OK");
        // echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
        echo json_encode( $sql->fetchAll(PDO::FETCH_ASSOC)  );
        exit();
      }

    }else{
      //Mostrar todos los pedidos
      $sql = $dbConn->prepare("SELECT * FROM pedido");
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
  // ingresar pedidos con el parametro ped_codigo 
//  id_cliente
// id_producto
// ped_numero
// ped_fecha
// ped_estado
  if (isset($_POST['ped_codigo'])){
    $sql = $dbConn->prepare("SELECT * FROM pedido where ped_codigo=:ped_codigo");
    $sql->bindValue(':ped_codigo', $_POST['ped_codigo']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count>0) {
      header("HTTP/1.1 204 No Content");
      echo "Ya existe la ped_codigo ", $_POST['ped_codigo'];
    }else{
      echo "Guardado Exitosamente";
      $input = $_POST;
      // ingresamos los datos con un espacio basio para el id
      $sql = "INSERT INTO pedido
            VALUES
            ('',:id_cliente,:id_producto ,:ped_codigo,:ped_numero,:ped_fecha, :ped_estado)";
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
    echo "EL campo ped_codigo es obligatorio";
  }

}

//Borrar
if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
  // eliminar el pedido por el parametro ped_codigo
  if (isset($_GET['ped_codigo'])){
    $sql = $dbConn->prepare("SELECT COUNT(*) FROM pedido where ped_codigo=:ped_codigo");
    $sql->bindValue(':ped_codigo', $_GET['ped_codigo']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    // echo $row_count;
    if ($row_count == 0) {
      echo "No existe el registro ",$_GET['ped_codigo'];
      header("HTTP/1.1 400 Bad Request"); //error 400 por no ejecutar el delete

    }else{
      $ped_codigo = $_GET['ped_codigo'];
      $statement = $dbConn->prepare("DELETE FROM pedido where ped_codigo=:ped_codigo");
      $statement->bindValue(':ped_codigo', $ped_codigo);
      $statement->execute();
      echo "Eliminado el registro ",$_GET['ped_codigo'];
    	header("HTTP/1.1 200 OK");
    	exit();
    }
  }else{
    echo "El parametro ped_codigo es obligatorio";
  }


}

//Actualizar
if ($_SERVER['REQUEST_METHOD'] == 'PUT')
{

// actualizar el pedido por el parametro ped_codigo y los parametros opcionales 
  // ingresar pedidos con el parametro ped_codigo 
//  id_cliente
// ped_fecha
// ped_estado
  if (isset($_GET['ped_codigo'])){
    // buscamos si el pedido existe
    $sql = $dbConn->prepare("SELECT * FROM pedido where ped_codigo=:ped_codigo");
    $sql->bindValue(':ped_codigo', $_GET['ped_codigo']);
    $sql->execute();
    $row_count =$sql->fetchColumn();
    if ($row_count>0) {
      $input = $_GET;
      $postId = $input['ped_codigo'];
      $fields = getParams($input);
      // al ser encontrado el pedido realizamos la actualizacion de datos con los parametros enviados  
      $sql = "
            UPDATE pedido
            SET $fields
            WHERE ped_codigo='$postId'
             ";

      $statement = $dbConn->prepare($sql);
      bindAllValues($statement, $input);

      $statement->execute();
      header("HTTP/1.1 200 OK");
      echo "Actualizado Exitosamente la pedido ", $_GET['ped_codigo'];
      exit();
    }else{
      header("HTTP/1.1 204 No Content");
      echo "No existe el ped_codigo ", $_GET['ped_codigo'];
    }
  }else{
    echo "El parametro ped_codigo es obligatorio";
  }
}


//En caso de que ninguna de las opciones anteriores se haya ejecutado
header("HTTP/1.1 400 Bad Request");

?>