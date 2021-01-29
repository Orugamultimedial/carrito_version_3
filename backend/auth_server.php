<?php
require('connection.php');
require('./users.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers, X-USER, X-PASS ');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
// para que no guarde en cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


$connect = connection();

$method = strtoupper( $_SERVER['REQUEST_METHOD'] );

if ( $method == 'POST' ) {
    if ( !array_key_exists( 'HTTP_X_USER', $_SERVER ) || !array_key_exists( 'HTTP_X_PASS', $_SERVER ) ) {
       
        http_response_code( 400 );

        die( 'Faltan parametros' );

    }

    $user = $_SERVER['HTTP_X_USER'];
    $pass = $_SERVER['HTTP_X_PASS'];

    $resp = mysqli_query($connect,"SELECT id, hash FROM users WHERE email='$user' AND pass='$pass' ");
    
    $result = mysqli_fetch_assoc($resp);
    $userId = $result["id"];
    $hash = json_encode( $result["hash"] );
    
    if ( !is_null($result) && !empty($result) ) {

        $token = sha1($hash);

        echo json_encode(
            [
                "id" => $userId,
                "token" => $token
            ]
        );
        
        mysqli_close($connect);
        exit;

    }else{

        die ( "No autorizado" );

    }

} elseif ( $method == 'GET' ) {

    if ( !array_key_exists( 'HTTP_X_TOKEN', $_SERVER ) || !array_key_exists( 'HTTP_X_USER_ID', $_SERVER ) ) {
        http_response_code( 400 );

        die ( 'Faltan parametros' );
    }else{
        
        $userId = number_format($_SERVER['HTTP_X_USER_ID']);
        $tokenUser = $_SERVER['HTTP_X_TOKEN'];

        $query = mysqli_query($connect,"SELECT hash FROM users WHERE id=$userId");
    
        $result = mysqli_fetch_assoc($query);
    
        $hash = json_encode( $result["hash"] );
        
        if ( !is_null($result) && !empty($result) ) {
            
            $token = sha1($hash);
    
            if ( $tokenUser == $token ) {
                
                echo 'true';
            } else {
                echo 'false';
            }
            
            mysqli_close($connect);
        }
    }

}else {
        echo 'false';

}