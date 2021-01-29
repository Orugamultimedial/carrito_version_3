<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$matches=[];
if(preg_match('/\/([^\/]+)\/([^\/]+)\/([^\/]+)\/([^\/]+)/',$_SERVER["REQUEST_URI"],$matches))
{
    $_GET['resource_type']=$matches[1];    
    $_GET['resource_id']=$matches[2];
    $_GET['resource_sub']=$matches[3];
    $_GET['resource_sub_sub']=$matches[4];

    error_log(print_r($matches,1));
    require 'server.php';
}elseif(preg_match('/\/([^\/]+)\/([^\/]+)\/([^\/]+)/',$_SERVER["REQUEST_URI"],$matches))
{
    $_GET['resource_type']=$matches[1];    
    $_GET['resource_id']=$matches[2];
    $_GET['resource_sub']=$matches[3];
    error_log(print_r($matches,1));
    require 'server.php';
}else if(preg_match('/\/([^\/]+)\/([^\/]+)\/?/',$_SERVER["REQUEST_URI"],$matches))
{
    $_GET['resource_type']=$matches[1];    
    $_GET['resource_id']=$matches[2];       
    error_log(print_r($matches,1));
    require 'server.php';
}else if(preg_match('/\/([^\/]+)\/?/',$_SERVER["REQUEST_URI"],$matches))
{
    $_GET['resource_type']=$matches[1];        
    error_log(print_r($matches,1));
    require 'server.php';
}else
{
    error_log('No matches');
    http_response_code(404);
}

?>