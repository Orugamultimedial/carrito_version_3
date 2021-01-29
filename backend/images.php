<?php 
function createImage($connect,$user_id){

  $store = mysqli_fetch_assoc(
              mysqli_query($connect,"SELECT store_id FROM users WHERE id=$user_id")
            );
  $store_id = number_format( $store["store_id"] );

  $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

  $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

  $hash = md5( $params->name ); // GENERA UN HASH de 32 CARACTERES ALEATORIO

  // REALIZA LA QUERY A LA DB

  if ($store_id > 0){
  
    mysqli_query($connect, 
                "INSERT INTO `images` (
                  `id`, 
                  `user_id`, 
                  `store_id`, 
                  `name`,
                  `url`,
                  `hash`, 
                  `active`, 
                  `created_at`, 
                  `updated_at`
                ) VALUES (
                  NULL, 
                  '$user_id', 
                  '$store_id', 
                  '$params->name',
                  '$params->url', 
                  '$hash', 
                  '1', 
                  current_timestamp(), 
                  current_timestamp()
                );"
    );
  
    $last_id =  mysqli_fetch_assoc(
      mysqli_query($connect, "SELECT LAST_INSERT_ID()")
    );
  
    $id = $last_id["LAST_INSERT_ID()"];
  
    $resp = retrieveImage($connect,$id);
  
    if($resp["hash"] == $hash){
    
      return json_encode($resp);
  
    }else{
        return json_encode(
            [
                "error" => "Image could not be created"
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

function retrieveAllImages($connect){

  // REALIZA LA QUERY A LA DB
  $resp = mysqli_query($connect,"SELECT * FROM images");

  // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
  while ($res = mysqli_fetch_assoc($resp))  
  {
    $images = retrieveImage($connect, $res["id"]);
    
    if ( $images != null ) {
      
      $datos[] = $images;

    }
  }
  
  $json = json_encode($datos);
    
  return $json;
}

function retrieveStoreImages($connect,$user_id){

  // REALIZA LA QUERY A LA DB
  $resp = filterOwnerImages($connect,$user_id);

  // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
  while ($res = mysqli_fetch_assoc($resp))  
  {
    $images = retrieveImage($connect, $res["id"]);
    
    if ( $images != null ) {
      
      $datos[] = $images;
      
    }
  }
  
  $json = json_encode($datos);
    
  return $json;
}

function retrieveImage($connect,$id,$user_id = null){

  if ( $user_id != null ) {
    $resp = filterOwnerImages($connect,$user_id);
  }else{
    $resp = mysqli_query($connect,"SELECT * FROM images");
  }

  // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
  while ($res = mysqli_fetch_assoc($resp))  
  {
    if ( number_format( $res["active"] ) == 1 ) {

      if (number_format($id) == number_format($res["id"])){

          $data = [
                      "id"=> $res["id"],
                      "user_id" => $res["user_id"],
                      "name" => $res["name"],
                      "url" => $res["url"],
                      "hash" => $res["hash"],
                      "active" => $res["hash"],
                      "created_at" => $res["created_at"],
                      "updated_at" => $res["updated_at"]
                    ];
      }

    }
  }
  
  return $data;
  
}

function updateImage($connect,$id,$user_id){

  $tag = retrieveImage($connect,$id,$user_id);

  if ( number_format( $tag["id"] ) == number_format( $id ) ) {

    $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

    $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

    // REALIZA LA QUERY A LA DB
    $updated_at = $tag["updated_at"];

    $sql = mysqli_query($connect, 
      "UPDATE `images` 
      SET `name`='$params->name',`url`='$params->url'
      WHERE id=$id"
    );

    $resp = retrieveImage($connect,$id,$user_id);

    if ($resp["updated_at"] != $updated_at ){
    
      return json_encode($resp);

    }else{

        return json_encode(
            [
                "error" => "Image could not be updated"
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

function deleteImage($connect,$id,$user_id){

  $tag = retrieveImage($connect,$id,$user_id);

  if ( number_format( $tag["id"] ) == number_format( $id ) ) {
    
    mysqli_query($connect, "UPDATE `images` SET `active` = 0 WHERE `id`=$id");

    $resp = retrieveImage($connect,$id,$user_id);

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


function filterOwnerImages($connect,$user_id){
  $user =  mysqli_fetch_assoc(
              mysqli_query($connect, "SELECT type,store_id FROM users WHERE id=$user_id")
            );

  $type = $user["type"];
  $store_id = number_format($user["store_id"]);

  if ($type == 'master'){
    $result = mysqli_query($connect,"SELECT * FROM images");

    return $result;

  }else{
    $result = mysqli_query($connect,"SELECT * FROM images WHERE user_id=$user_id OR store_id=$store_id");

    return $result;

  }
}
?>