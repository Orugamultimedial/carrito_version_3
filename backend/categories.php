<?php 
function createCategory($connect,$user_id){

    if( false == checkUserType($connect,$user_id) ) {

        $text = json_encode(
            [
                "error" => "access denied"
            ]
        );

        die($text);
    }

  $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

  $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

  $hash = md5( $params->name ); // GENERA UN HASH de 32 CARACTERES ALEATORIO

  // REALIZA LA QUERY A LA DB
  
    mysqli_query($connect, 
                "INSERT INTO `categories` (
                  `id`, 
                  `user_id`, 
                  `name`, 
                  `hash`, 
                  `active`, 
                  `created_at`, 
                  `updated_at`
                ) VALUES (
                  NULL, 
                  '$user_id', 
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
  
    $category = retrieveCategory($connect,$id);
    
    if ($category != null) {
        
        $resp = $category;
        
    }
  
    if ( $resp["hash"] == $hash ) {
    
      return json_encode($resp);
  
    }else{
        return json_encode(
            [
                "error" => "Category could not be created"
            ]
        );
    }

}

function retrieveAllCategories($connect){

  // REALIZA LA QUERY A LA DB
  $resp = mysqli_query($connect,"SELECT * FROM categories");

  // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
  while ($res = mysqli_fetch_assoc($resp))  
  {
    $category = retrieveCategory($connect,$res["id"]);
    
    if( $category != null){
        
        $datos[] = $category;
    
    }

  }
  
  $json = json_encode($datos);
    
  return $json;
}

function retrieveCategory($connect,$id){

  // REALIZA LA QUERY A LA DB
  $resp = mysqli_query($connect, "SELECT * FROM categories WHERE id=$id");

  // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
  $res = mysqli_fetch_assoc($resp);

    if ( number_format( $res["active"] ) == 1 ) {
    
        return $res;

    }else{
        return null;
    }
  
}

function updateCategory($connect,$id,$user_id){

    if( false == checkUserType($connect,$user_id) ) {

        $text = json_encode(
            [
                "error" => "access denied"
            ]
        );

        die($text);
    }

    $category = retrieveCategory($connect,$id);

    $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

    $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

    // REALIZA LA QUERY A LA DB
    $updated_at = $category["updated_at"];

    $sql = mysqli_query($connect, 
            "UPDATE `categories` 
            SET `name`='$params->name'
            WHERE id=$id"
        );

    $resp = retrieveCategory($connect,$id);

    if ($resp["updated_at"] != $updated_at ){
    
        return json_encode($resp);

    }else{
        $text = json_encode(
        [
            "error" => "access denied"
        ]
        );
        die($text);
    }
}

function deleteCategory($connect,$id,$user_id){
    
    if( false == checkUserType($connect,$user_id) ) {

        $text = json_encode(
            [
                "error" => "access denied"
            ]
        );

        die($text);
    }

    $category = retrieveCategory($connect,$id);

    if ( number_format( $category["id"] ) == number_format( $id ) ) {
        
        mysqli_query($connect, "UPDATE `categories` SET `active` = 0 WHERE `id`=$id");

        $resp = retrieveCategory($connect,$id);

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


function checkUserType($connect,$user_id){
    $user =  mysqli_fetch_assoc(
                mysqli_query($connect, "SELECT type,store_id FROM users WHERE id=$user_id")
                );

    $type = $user["type"];
    $store_id = number_format($user["store_id"]);

    if ($type == 'master'){

        return true;

    }else{

        return false;

    }
}

?>