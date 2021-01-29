<?php 

function createOrder($connect){

  $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

  $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE
  
  $user_id = number_format($_SERVER['HTTP_X_USER_ID']);

  $hash = md5( $user_id ); // GENERA UN HASH de 32 CARACTERES ALEATORIO

  $billing = json_encode($params->billing);
  $shipping = json_encode($params->shipping);
  $products = json_encode($params->products);


  // REALIZA LA QUERY A LA DB

  $query = "INSERT INTO `orders`(
                              store_id,
                              hash,
                              currency,
                              subtotal,
                              shipping_total,
                              total,
                              billing,
                              shipping,
                              payment_method, 
                              transaction_id,
                              date_paid,
                              products,
                              user_id,
                              processing_time
                              ) 
                  VALUES (
                    $params->store_id,
                    '$hash',
                    '$params->currency',
                    $params->subtotal,
                    $params->shipping_total,
                    $params->total,
                    '$billing',
                    '$shipping',
                    '$params->payment_method', 
                    '$params->transaction_id',
                    '$params->date_paid',
                    '$products',
                    $user_id,
                    $params->processing_time
                  )";

  mysqli_query($connect, $query);

  $last_id =  mysqli_fetch_assoc(
    mysqli_query($connect, "SELECT LAST_INSERT_ID()")
  );

  $id = $last_id["LAST_INSERT_ID()"];

  $resp = retrieveOrder($connect,$id);

  if($resp["hash"] == $hash){

    sendNewOrder($connect, $user_id, $params);
    
    return json_encode($resp);

  }else{
      return json_encode(
          [
              "error" => "Order could not be created"
          ]
      );
  }

}

function retrieveAllOrders($connect,$user_id){

  // REALIZA LA QUERY A LA DB
  $resp = filterOwnerOrders($connect,$user_id);

  // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
  while ($res = mysqli_fetch_assoc($resp))  
  {
    $order = retrieveOrder($connect,$res["id"],$user_id);

    if ( $order != null ) {
      $datos[] = $order;
    }
  }
  
  $json = json_encode($datos);
    
  return $json;
}

function retrieveOrder($connect,$id,$user_id){

    // REALIZA LA QUERY A LA DB
    $resp = filterOwnerOrders($connect,$user_id);

    // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
    while ($res = mysqli_fetch_assoc($resp))  
    {
      if ( number_format( $res["active"] ) == 1 ) {
        if (number_format($id) == number_format($res["id"])){
            $data = [
                        "id"=> $res["id"],
                        "store_id" => $res["store_id"],
                        "hash" => $res["hash"],
                        "active" => $res["active"],
                        "created_at" => $res["created_at"],
                        "updated_at" => $res["updated_at"],
                        "delivery_man" => $res["delivery_man"],
                        "status" => $res["status"],
                        "currency" => $res["currency"],
                        "subtotal" => $res["subtotal"],
                        "shipping_total" => $res["shipping_total"],
                        "total" => $res["total"],
                        "billing" => json_decode($res["billing"]),
                        "shipping" => json_decode($res["shipping"]),
                        "payment_method" => $res["payment_method"],
                        "transaction_id" => $res["transaction_id"],
                        "date_paid" => $res["date_paid"],
                        "products" => json_decode($res["products"]),
                        "user_id" => $res["user_id"],
                        "processing_time" => $res["processing_time"]
                      ];
        }
      }
    }


      return $data;
    
    
}

function updateOrder($connect,$id,$user_id){

  $order = retrieveOrder($connect,$id,$user_id);

  if ( number_format( $order["id"] ) == number_format( $id ) ) {

    $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

    $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

    // REALIZA LA QUERY A LA DB
    $updated_at = $order["updated_at"];

    $sql = mysqli_query($connect, 
      "UPDATE `orders` 
      SET status='$params->status',
          delivery_man=$params->delivery_man
      WHERE id=$id"
    );

    $resp = retrieveOrder($connect,$id,$user_id);

    if ($resp["updated_at"] != $updated_at ){
    
      return json_encode($resp);

    }else{

        return json_encode(
            [
                "error" => "Order could not be updated"
            ]
        );
    }
  }else{
    $text = json_encode(
      [
        "error" => "access denied"
      ]
    );
    die($text);
  }
}

function deleteOrder($connect,$id,$user_id){

  $order = retrieveOrder($connect,$id,$user_id);

  if ( number_format( $order["id"] ) == number_format( $id ) ) {
    
    mysqli_query($connect, "UPDATE `orders` SET `active` = 0 WHERE `id`=$id");

    $resp = retrieveOrder($connect,$id,$user_id);

    if ( number_format( $resp["active"] ) == 0 ) {
        return json_encode(
            [
                "id" => $id,
                "disabled" => "true"
            ]
        );
    }else{
        return json_encode(
            [
                "error" => $resp["id"]." could not be deactivated"
            ]
        );
    }
  }else{
    $text = json_encode(
      [
        "error" => "access denied"
      ]
    );
    die($text);
  }
}


function filterOwnerOrders($connect,$user_id){
  $user =  mysqli_fetch_assoc(
              mysqli_query($connect, "SELECT type,store_id FROM users WHERE id=$user_id")
            );

  $type = $user["type"];
  $store_id = number_format($user["store_id"]);

  if ($type == 'master'){
    $result = mysqli_query($connect,"SELECT * FROM orders");

    return $result;

  }else{
    $result = mysqli_query($connect,"SELECT * FROM orders WHERE user_id=$user_id OR store_id=$store_id");

    return $result;

  }
}

function sendNewOrder($connect,$user_id,$params){

  $query = mysqli_query($connect, "SELECT email, name FROM users WHERE user_id=$user_id");
  
  while ( $res = mysqli_fetch_array($query) )  
  {
  $datos[] = $res;
  }
  
  $json = json_encode($datos["email"]); // GENERA EL JSON CON LOS DATOS OBTENIDOS
  
  // ENVIO EMAIL DE VALIDACION 
  $to      = $json;
  $subject = 'carrito.com.ar | ESTAMOS PROCESANDO TU PEDIDO'; 
  $message = '
  
  Gracias por tu compra!
  Tu pedido está siendo procesado, te mantendremos informado a cada paso.
  
  ------------------------
  Tiempo de espera: '.$params->processing_time.'Hs.';

  $headers = 'From:pedidos@carrito.com.ar' . "\r\n"; // Set from headers
  
  mail($to, $subject, $message, $headers); // Send our email

}

?>