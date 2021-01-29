<?php
function createProduct($connect,$user_id){

    $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

    $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

    $store_id = number_format($params->store_id);

    checkUserBelongStore($connect,$store_id,$user_id);

    $hash = md5( $params->name ); // GENERA UN HASH de 32 CARACTERES ALEATORIO

    $dimensions = json_encode($params->dimensions);
    $related_ids = json_encode($params->related_ids);
    $upsell_ids = json_encode($params->upsell_ids);
    $cross_sell_ids = json_encode($params->cross_sell_ids);
    $categories = json_encode($params->categories);
    $tags = json_encode($params->tags);
    $images = json_encode($params->images);
    $attributes = json_encode($params->attributes);
    $variations = json_encode($params->variations);
    $grouped_products = json_encode($params->grouped_products);

    // REALIZA LA QUERY A LA DB

    mysqli_query($connect,
        "INSERT INTO `products` (
            `id`, 
            `store_id`, 
            `active`, 
            `created_at`, 
            `updated_at`, 
            `hash`, 
            `name`, 
            `slug`, 
            `type`, 
            `status`, 
            `featured`, 
            `description`, 
            `short_description`, 
            `sku`, 
            `price`, 
            `regular_price`, 
            `sale_price`, 
            `total_sales`, 
            `stock_quantity`, 
            `weight`, 
            `dimensions`, 
            `rating`, 
            `related_ids`, 
            `upsell_ids`, 
            `cross_sell_ids`, 
            `parent_id`, 
            `purchase_note`, 
            `categories`, 
            `tags`, 
            `images`, 
            `attributes`, 
            `variations`, 
            `grouped_products`, 
            `processing_time`, 
            `shipping_policy`, 
            `refund_policy`, 
            `cancellation_policy`, 
            `shipping_class_id`
            ) 
        VALUES (
            NULL, 
            '$params->store_id', 
            '0', 
            current_timestamp(), 
            current_timestamp(), 
            '$hash', 
            '$params->name', 
            '$params->slug', 
            '$params->type', 
            '$params->status', 
            '$params->featured', 
            '$params->description', 
            '$params->short_description', 
            '$params->sku', 
            '$params->price', 
            '$params->regular_price', 
            '$params->sale_price', 
            '$params->total_sales', 
            '$params->stock_quantity', 
            '$params->weight', 
            '$dimensions', 
            '$params->rating', 
            '$related_ids', 
            '$upsell_ids', 
            '$cross_sell_ids', 
            '$params->parent_id', 
            '$params->purchase_note', 
            '$categories', 
            '$tags', 
            '$images', 
            '$attributes', 
            '$variations', 
            '$grouped_products', 
            '$params->processing_time', 
            '$params->shipping_policy', 
            '$params->refund_policy', 
            '$params->cancellation_policy', 
            '$params->shipping_class_id'
        )"
    );

    $last_id =  mysqli_fetch_assoc(
                    mysqli_query($connect, "SELECT LAST_INSERT_ID()")
                );

    $id = $last_id["LAST_INSERT_ID()"];  

    $resp = retrieveProduct($connect,$id);

    if($resp["hash"] == $hash){
        
        return json_encode($resp);

    }else{
        return json_encode(
            [
                "error" => "Product could not be created"
            ]
        );
    }
}

function retrieveAllProducts($connect){
    // REALIZA LA QUERY A LA DB
    $resp = mysqli_query($connect, "SELECT * FROM products");

    // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
    while ($res = mysqli_fetch_assoc($resp))  
    {
        $product = retrieveProduct( $connect,$res["id"] );

        if ( $product != null ) {
            
            $datos[] = $product;
        }
    }

    header('Content-Type: application/json');

    http_response_code(200);

    $json = json_encode($datos);
    
    return $json;
}

function retrieveAllProductsShop($connect,$store_id){
    // REALIZA LA QUERY A LA DB
    $resp = mysqli_query($connect, "SELECT * FROM products");

    // RECORRE EL RESULTADO Y LO GUARDA EN UN ARRAY
    while ($res = mysqli_fetch_assoc($resp))  
    {
        $product = retrieveProduct( $connect,$res["id"] );

        if ( $product != null ) {

            if ( number_format( $product["store_id"]["id"] ) == $store_id) {
            
                $datos[] = $product;

            }
        }
    }

    header('Content-Type: application/json');

    http_response_code(200);

    $json = json_encode($datos);
    
    return $json;
}

function retrieveProduct($connect,$id){
    // REALIZA LA QUERY A LA DB
    $result = mysqli_fetch_assoc(
        mysqli_query($connect, "SELECT * FROM products WHERE id=$id")
    );

    $result["dimensions"] = json_decode($result["dimensions"]);
    $result["related_ids"] = json_decode($result["related_ids"]);
    $result["cross_sell_ids"] = json_decode($result["cross_sell_ids"]);
    $result["categories"] = json_decode($result["categories"]);
    $result["tags"] = json_decode($result["tags"]);

    //retrieve images
    $result["images"] = json_decode($result["images"]);
    for ($i=0; $i < count($result["images"]); $i++) { 
        $images[] = retrieveImage($connect,$result["images"][$i]);

    }
    $result["images"] = $images;

    //$result["images"] = $images;
    $result["attributes"] = json_decode($result["attributes"]);
    $result["variations"] = json_decode($result["variations"]);
    $result["grouped_products"] = json_decode($result["grouped_products"]);

    //retrieve store_id
    $store = retrieveStore($connect,$result["store_id"] );
    $result["store_id"] = $store;


    if ( number_format( $result["active"] ) == 1 
        && number_format( $result["stock_quantity"] ) >= 1 ) {

        return $result;
    }

}

function updateProduct($connect,$id,$user_id){

    $store = mysqli_fetch_assoc(
                mysqli_query($connect,"SELECT store_id FROM products WHERE id=$id")
            );

    $store_id = number_format( $store["store_id"] );
    
    checkUserBelongStore($connect,$store_id,$user_id);

    $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

    $params = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

    $dimensions = json_encode($params->dimensions);
    $related_ids = json_encode($params->related_ids);
    $upsell_ids = json_encode($params->upsell_ids);
    $cross_sell_ids = json_encode($params->cross_sell_ids);
    $categories = json_encode($params->categories);
    $tags = json_encode($params->tags);
    $images = json_encode($params->images);
    $attributes = json_encode($params->attributes);
    $variations = json_encode($params->variations);
    $grouped_products = json_encode($params->grouped_products);



    // REALIZA LA QUERY A LA DB
    $lastUpdate = retrieveOrder($connect,$id);
    $updated_at = $lastUpdate["updated_at"];

    $sql = mysqli_query($connect, 
    "UPDATE products 
    SET name='$params->name',
        slug='$params->slug', 
        type='$params->type',
        status='$params->status',
        featured='$params->featured',
        description='$params->description',
        short_description='$params->short_description', 
        sku='$params->sku',
        price=$params->price, 
        regular_price=$params->regular_price, 
        sale_price=$params->sale_price, 
        total_sales=$params->total_sales,
        stock_quantity=$params->stock_quantity, 
        weight=$params->weight, 
        dimensions='$dimensions', 
        rating=$params->rating,
        related_ids='$related_ids',
        upsell_ids='$upsell_ids',
        cross_sell_ids='$cross_sell_ids',
        parent_id=$params->parent_id,
        purchase_note='$params->purchase_note',
        categories='$categories',
        tags='$tags',
        images='$images',
        attributes='$attributes',
        variations='$variations',
        grouped_products='$grouped_products',
        processing_time=$params->processing_time,
        shipping_policy='$params->shipping_policy',
        refund_policy='$params->refund_policy',
        cancellation_policy='$params->cancellation_policy'
    WHERE id=$id");
    
    $resp = retrieveProduct($connect,$id);

    if ($resp["updated_at"] != $updated_at ){
    
        return json_encode($resp);
  
      }else{
  
          return json_encode(
              [
                  "error" => "Product could not be updated"
              ]
          );
      }
}

function deleteProduct($connect,$id,$user_id){
    $store = mysqli_fetch_assoc(
        mysqli_query($connect,"SELECT store_id FROM products WHERE id=$id")
    );

    $store_id = number_format( $store["store_id"] );

    checkUserBelongStore($connect,$store_id,$user_id);

    // REALIZA LA QUERY A LA DB
    mysqli_query($connect,"UPDATE `products` SET `active`=0 WHERE `id`=$id");
    
    $resp = retrieveProduct($connect,$id);

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
?>