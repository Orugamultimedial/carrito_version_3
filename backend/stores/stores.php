<?php

function createStore($connect, $user_id){

    checkUserMaster($connect,$user_id);

    $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

    $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

    $hash = md5( $params->name ); // GENERA UN HASH de 32 CARACTERES ALEATORIO


    $offers = json_encode($params->offers);
    $working_hours = json_encode($params->working_hours);
    $shops_categories = json_encode($params->shop_categories);
    $stories = json_encode($params->stories);
    $marketplace_categories = json_encode($params->marketplace_categories);

    // REALIZA LA QUERY A LA DB
    mysqli_query($connect,"INSERT INTO `stores` (`id`, `active`, `created_at`, `updated_at`, `hash`, `slug`, `name`, `logo`, `banner_desktop`, `banner_mobile`, `offers`, `desription`, `shipping_policy`, `refund_policy`, `cancellation_policy`, `working_hours`, `shops_categories`, `stories`, `marketplace_categories`) 
                                        VALUES (NULL, '0', current_timestamp(), current_timestamp(), '$hash', '$params->slug', '$params->name', '$params->logo', '$params->banner_desktop', '$params->banner_mobile', '$offers', '$params->description', '$params->shipping_policy', '$params->refund_policy', '$params->cancellation_policy', '$working_hours', '$shops_categories', '$stories', '$marketplace_categories');");

    $last_id =  mysqli_fetch_assoc(
                    mysqli_query($connect, "SELECT LAST_INSERT_ID()")
                );
    
    $id = $last_id["LAST_INSERT_ID()"];

    $resp = retrieveStore($connect,$id);

    if($resp["hash"] == $hash){
        
        return json_encode($resp);

    }else{
        return json_encode(
            [
                "error" => "Store could not be created"
            ]
        );
    }

}

function retrieveAllStores($connect){
  // REALIZA LA QUERY A LA DB
  $resp = mysqli_query($connect, "SELECT * FROM stores");

  // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
  while ($res = mysqli_fetch_assoc($resp))  
  {
  $datos[] = [
            "id" => $res["id"],
            "active" => $res["active"],
            "created_at" => $res["created_at"],
            "updated_at" => $res["updated_at"],
            "name" => $res["name"],
            "slug" => $res["slug"],
            "logo" => $res["logo"],
            "banner_desktop" => $res["banner_desktop"],
            "banner_mobile" => $res["banner_mobile"],
            "rating" => $res["rating"],
            "offers" => json_decode($res["offers"]),
            "description" => $res["description"],
            "shipping_policy" => $res["shipping_policy"],
            "refund_policy" => $res["refund_policy"],
            "cancellation_policy" => $res["cancellation_policy"],
            "working_hours" => json_decode($res["working_hours"]),
            "shops_categories" => json_decode($res["shops_categories"]),
            "stories" => json_decode($res["stories"]),
            "marketplace_categories" => json_decode($res["marketplace_categories"])
        ];
    }



    header('Content-Type: application/json');

    http_response_code(200);

    $json = json_encode($datos);
    
    return $json;
}

function retrieveStore($connect,$id,$user_id = null){

    if ($user_id != null){
        checkUserBelongStore($connect,$id,$user_id);
    }

    // REALIZA LA QUERY A LA DB
    $result = mysqli_fetch_assoc(
                mysqli_query($connect, "SELECT * FROM stores WHERE id=$id")
            );

    // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
    $result["offers"] = json_decode($result["offers"]);
    $result["working_hours"] = json_decode($result["working_hours"]);
    $result["shops_categories"] = json_decode($result["shops_categories"]);
    $result["stories"] = json_decode($result["stories"]);
    $result["marketplace_categories"] = json_decode($result["marketplace_categories"]);
    
    return $result; 

}

function updateStore($connect,$id,$user_id){

    checkUserBelongStore($connect,$id,$user_id);

    $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

    $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE
    
    $offers = json_encode($params->offers);
    $working_hours = json_encode($params->working_hours);
    $shops_categories = json_encode($params->shop_categories);
    $stories = json_encode($params->stories);
    $marketplace_categories = json_encode($params->marketplace_categories);

    // REALIZA LA QUERY A LA DB
    $lastUpdate = retrieveStore($connect,$id);
    $updated_at = $lastUpdate["updated_at"];

    $sql = mysqli_query($connect, 
    "UPDATE stores 
    SET slug='$params->slug',
        name='$params->name',
        logo='$params->logo',
        banner_desktop='$params->banner_desktop',
        banner_mobile='$params->banner_mobile',
        offers='$offers',
        working_hours='$working_hours',
        shops_categories='$shops_categories',
        stories='$stories',
        marketplace_categories='$marketplace_categories'
    WHERE id=$id");

    $resp = retrieveStore($connect,$id);

    if ($resp["updated_at"] != $updated_at ){
    
        return json_encode($resp);

    }else{

        return json_encode(
            [
                "error" => "store could not be updated"
            ]
        );
    }
}

function deleteStore($connect,$id,$user_id){

    checkUserBelongStore($connect,$id,$user_id);

    // REALIZA LA QUERY A LA DB
    mysqli_query($connect, "UPDATE `stores` SET `active`=0 WHERE `id`=$id");
    
    $resp = retrieveStore($connect,$id);

    if ( number_format( $resp["active"] ) == 0 ) {
        return json_encode(
            [
                "id" => $resp["id"],
                "name" => $resp["name"],
                "disabled" => "true"
            ]
        );
    }else{
        return json_encode(
            [
                "error" => $resp["name"]." could not be deactivated"
            ]
        );
    }

}



function checkUserMaster($connect,$user_id){

    $user = mysqli_fetch_assoc(
                mysqli_query($connect,"SELECT type FROM users WHERE id=$user_id")
            );

    if ( $user["type"] != 'master') {
        $text= json_encode(
            [
            "error" => 'access denied'
            ]
        );

        mysqli_close($connect);

        die($text);
    }
    
}

function checkUserBelongStore($connect,$id,$user_id){

    $user = mysqli_fetch_assoc(
                mysqli_query($connect,"SELECT type, store_id FROM users WHERE id=$user_id")
            );

    if ($user["type"] != 'master'){

        if ( number_format( $user["store_id"] ) != number_format( $id ) ) {

            $text= json_encode(
                [
                "error" => 'access denied'
                ]
            );

            mysqli_close($connect);

            die($text);
        }

    }
    
}



