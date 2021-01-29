<?php 
function createTag($connect,$user_id){

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
                "INSERT INTO `tags` (
                  `id`, 
                  `user_id`, 
                  `store_id`, 
                  `name`, 
                  `hash`, 
                  `active`, 
                  `created_at`, 
                  `updated_at`
                ) VALUES (
                  NULL, 
                  '$user_id', 
                  '$store_id', 
                  '$params->name', 
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
  
    $resp = retrieveTag($connect,$id,$user_id);
  
    if($resp["hash"] == $hash){
    
      return json_encode($resp);
  
    }else{
        return json_encode(
            [
                "error" => "Tag could not be created"
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

function retrieveAllTags($connect){

  // REALIZA LA QUERY A LA DB
  $resp = mysqli_query($connect,"SELECT * FROM tags");

  // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
  while ($res = mysqli_fetch_assoc($resp))  
  {
    $tag = retrieveTag($connect,$res["id"]);

    if ( $tag != null ) {
      
      $datos[] = $tag;
      
    }
  }
  
  $json = json_encode($datos);
    
  return $json;
}

function retrieveStoreTags($connect,$user_id){

  // REALIZA LA QUERY A LA DB
  $resp = filterOwnerTags($connect,$user_id);

  // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
  while ($res = mysqli_fetch_assoc($resp))  
  {

    $tag = retrieveTag($connect,$res["id"],$user_id);

    if ( $tag != null ) {
      
      $datos[] = $tag;

    }
  }
  
  $json = json_encode($datos);
    
  return $json;
}

function retrieveTag($connect,$id,$user_id=null){

  // REALIZA LA QUERY A LA DB
  if ( $user_id != null ){
    $resp = filterOwnerTags($connect,$user_id);
  }else{
    $resp = mysqli_query($connect,"SELECT * FROM tags");
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

function updateTag($connect,$id,$user_id){

  $tag = retrieveTag($connect,$id,$user_id);

  if ( number_format( $tag["id"] ) == number_format( $id ) ) {

    $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

    $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

    // REALIZA LA QUERY A LA DB
    $updated_at = $tag["updated_at"];

    $sql = mysqli_query($connect, 
      "UPDATE `tags` 
      SET `name`='$params->name'
      WHERE id=$id"
    );

    $resp = retrieveTag($connect,$id,$user_id);

    if ($resp["updated_at"] != $updated_at ){
    
      return json_encode($resp);

    }else{

        return json_encode(
            [
                "error" => "Tag could not be updated"
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

function deleteTag($connect,$id,$user_id){

  $tag = retrieveTag($connect,$id,$user_id);

  if ( number_format( $tag["id"] ) == number_format( $id ) ) {
    
    mysqli_query($connect, "UPDATE `tags` SET `active` = 0 WHERE `id`=$id");

    $resp = retrieveTag($connect,$id,$user_id);

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


function filterOwnerTags($connect,$user_id){
  $user =  mysqli_fetch_assoc(
              mysqli_query($connect, "SELECT type,store_id FROM users WHERE id=$user_id")
            );

  $type = $user["type"];
  $store_id = number_format($user["store_id"]);

  if ($type == 'master'){
    $result = mysqli_query($connect,"SELECT * FROM tags");

    return $result;

  }else{
    $result = mysqli_query($connect,"SELECT * FROM tags WHERE user_id=$user_id OR store_id=$store_id");

    return $result;

  }
}
?>