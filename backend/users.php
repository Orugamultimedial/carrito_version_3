<?php 

function createUser($connect){
  
  $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

  $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

  $hash = md5( $params->name ); // GENERA UN HASH de 32 CARACTERES ALEATORIO

  // REALIZA LA QUERY A LA DB
  $query = "INSERT INTO `users`(
                            `name`, 
                            `email`, 
                            `birthdate`, 
                            `gender`, 
                            `hash`, 
                            `pass`, 
                            `type`, 
                            `phone`, 
                            `city`, 
                            `state`, 
                            `address`, 
                            `country`
                          ) 
            VALUES (
              '$params->name',
              '$params->email',
              '$params->birthdate',
              '$params->gender',
              '$hash',
              '$params->pass',
              '$params->type',
              '$params->phone',
              '$params->city',
              '$params->state',
              '$params->address',
              '$params->country'
            )";

  mysqli_query($connect, $query);

  $last_id =  mysqli_fetch_assoc(
                  mysqli_query($connect, "SELECT LAST_INSERT_ID()")
              );

  $id = $last_id["LAST_INSERT_ID()"];

  $resp = retrieveUser($connect,$id);

  if($resp["hash"] == $hash){

    return json_encode($resp);

  }else{
    return json_encode(
                        [
                        "error" => "User could not be created"
                        ]
                      );
  }

}

function retrieveAllUsers($connect){

  // REALIZA LA QUERY A LA DB
  $result = mysqli_query($connect, "SELECT id, name, email FROM users");

  // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
  while ($res = mysqli_fetch_assoc($result))  
  {
    $user = retrieveUser($connect,$res["id"]);

    if ( $user != null ) {

      $data[] = $user;
      
    }


  }
  
  $json = json_encode($data);
    
  return $json;
}

function retrieveUser($connect,$id,$user_id = null){
  
  if ($user_id != null){
    checkUser($id,$user_id);
  }

  $id = number_format($id);

  $result = mysqli_fetch_assoc( mysqli_query( $connect, "SELECT * FROM users WHERE id=$id" ) );
  
  if ( number_format( $result["active"] ) == 1 ) {
    return $result;
  }
}

function updateUser($connect,$id,$user_id){

  checkUser($id,$user_id);

  $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

  $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

  // REALIZA LA QUERY A LA DB
  $lastUpdate = retrieveUser($connect,$id);
  $updated_at = $lastUpdate["updated_at"];

  mysqli_query($connect,
  "UPDATE `users` 
  SET `name`='$params->name', 
      `pass`='$params->pass',
      `phone`='$params->phone',
      `city`='$params->city',
      `state`='$params->state',
      `address`='$params->address',
      `country`='$params->country'
  WHERE `id`=$id");

  $resp = retrieveUser($connect,$id);

  if ($resp["updated_at"] != $updated_at ){
  
      return json_encode($resp);

  }else{

      return json_encode(
          [
              "error" => "User could not be updated"
          ]
      );
  }
}

function deleteUser($connect,$id,$user_id){

  checkUser($id,$user_id);

  mysqli_query($connect,"UPDATE `users` SET `active`=0 WHERE `id`=$id");
  
  $resp = retrieveUser($connect,$id);

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

}

function checkUser($id,$user_id){
  if ( number_format( $user_id ) != number_format( $id ) ) {
    $text= json_encode(
      [
        "error" => 'access denied'
      ]
    );
    die($text);
						
  }
  
}



function sendValidationMail($params, $hash){

  // ENVIO EMAIL DE VALIDACION 
  $to      = $params->email;
  $subject = 'carrito.com.ar | Verificacion'; 
  $message = '
  
  Gracias por Registrarte!
  Tu cuenta fue creada, solo debes activarla desde el siguiente link:
  
  ------------------------
  Usuario: '.$params->email.'
  Contraseña: '.$params->passDecrypted.'
  ------------------------
  Haz click en el link para activar tu cuenta:
  https://www.carrito.com.ar/wp/api_php/verify.php?email='.$params->email.'&hash='.$hash.'
  
  '; // Our message above including the link                   
  $headers = 'From:verificacion@carrito.com.ar' . "\r\n"; // Set from headers
  
  mail($to, $subject, $message, $headers); // Send our email

}

?>