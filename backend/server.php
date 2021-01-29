<?php
require('./authentication.php');
require('./stores/stores.php');
require('connection.php');
require('./users.php');
require('./products.php');
require('./orders.php');
require('./tags.php');
require('./images.php');
require('./categories.php');

header( 'Content-Type: application/json' );

$availableMethods = [
	'products',
	'stores',
	'users',
	'news',
	'orders',
	'tags',
	'images',
	'tests',
	'categories'
];

$resourceType = $_GET['resource_type'];

if ( !in_array( $resourceType, $availableMethods ) ) {
	http_response_code( 400 );
	echo json_encode(
		[
			'error' => "$resourceType is un unkown",
		]
	);
	
	die;
}

$resourceId = array_key_exists('resource_id', $_GET ) ? $_GET['resource_id'] : '';
$resourceSub = array_key_exists('resource_sub', $_GET ) ? $_GET['resource_sub'] : '';
$resourceSub_sub = array_key_exists('resource_sub_sub', $_GET ) ? $_GET['resource_sub_sub'] : '';

$method = $_SERVER['REQUEST_METHOD'];

$connect = connection();

switch ( strtoupper( $method ) ) {
	case 'GET':
		if ( in_array( $resourceType, $availableMethods)) {

			if ( !empty( $resourceId ) ) {

				$id = number_format($resourceId);

			}

			$user_id = number_format($_SERVER['HTTP_X_USER_ID']);

			if ("users" == $resourceType){
				if ( !empty( $resourceId ) ){
					authentication();

					http_response_code( 200 );

					echo json_encode( retrieveUser($connect,$id,$user_id) );

					mysqli_close($connect);
					exit;

				}else{			
					http_response_code( 200 );

					echo retrieveAllUsers($connect);

					mysqli_close($connect);
					exit;
				}
			}

			if ("products" == $resourceType){
				if ( !empty( $resourceId ) ){

					if ( "store" == $resourceId ){

						if ( !empty($resourceSub) ){
							http_response_code( 200 );

							$store_id = number_format($resourceSub);

							echo retrieveAllProductsShop($connect,$store_id);
						}else{
							http_response_code( 403 );

							echo json_encode(
								[
									"error"=>"You must enter a store ID"
								]
							);
						}

					}else{

						http_response_code( 200 );

						echo json_encode( retrieveProduct($connect,$id) );

						mysqli_close($connect);
						exit;
					}

				}else{

					http_response_code( 200 );
				
					echo retrieveAllProducts($connect);

					mysqli_close($connect);
					exit;
				}
			}

			if ("stores" == $resourceType){
				if ( !empty( $resourceId ) ){
					//authentication();

					http_response_code( 200 );

					echo json_encode( retrieveStore($connect,$id) );

					mysqli_close($connect);
					exit;

				}else{

					http_response_code( 200 );

					echo retrieveAllStores($connect);

					mysqli_close($connect);

					exit;
				}

			}

			if ("orders" == $resourceType){
				if ( !empty( $resourceId ) ){
					authentication();

					http_response_code( 200 );

					echo json_encode( retrieveOrder($connect,$id,$user_id) );

					mysqli_close($connect);
					exit;

				}else{
					authentication();

					http_response_code( 200 );

					echo retrieveAllOrders($connect,$user_id);

					mysqli_close($connect);

					exit;
				}

			}

			if ("tags" == $resourceType){
				if ( !empty( $resourceId ) ){
					if ('all' == $resourceId){
						http_response_code( 200 );

						echo retrieveAllTags($connect);

						mysqli_close($connect);
						exit;

					}else{
						authentication();

						http_response_code( 200 );

						echo json_encode( retrieveTag($connect,$id,$user_id) );

						mysqli_close($connect);
						exit;
					}

				}else{
					authentication();

					http_response_code( 200 );

					echo retrieveStoreTags($connect,$user_id);

					mysqli_close($connect);

					exit;
				}

			}

			if ("images" == $resourceType){
				if ( !empty( $resourceId ) ){
					if ('all' == $resourceId){
						http_response_code( 200 );

						echo retrieveAllImages($connect);

						mysqli_close($connect);
						exit;

					}else{
						//authentication();

						http_response_code( 200 );

						echo json_encode( retrieveImage($connect,$id) );

						mysqli_close($connect);
						exit;
					}

				}else{
					authentication();

					http_response_code( 200 );

					echo retrieveStoreImages($connect,$user_id);

					mysqli_close($connect);

					exit;
				}

			}

			if ("categories" == $resourceType){
				if ( !empty( $resourceId ) ){

					http_response_code( 200 );

					echo json_encode( retrieveCategory($connect,$id) );

					mysqli_close($connect);
					exit;

				}else{

					http_response_code( 200 );

					echo retrieveAllCategories($connect);

					mysqli_close($connect);

					exit;
				}

			}
		}else{
			http_response_code( 404 );

			echo json_encode(
				[
					"error" => "Endpoint not fund in ".$method."method ",
				]
			);

			die;
		}
		
		break;

	case 'POST':
		if ( in_array( $resourceType, $availableMethods)) {

			$user_id = number_format($_SERVER['HTTP_X_USER_ID']);

			if ("users" == $resourceType){				
				http_response_code( 200 );

				echo createUser($connect);

				mysqli_close($connect);
				exit;
			}

			if ("products" == $resourceType){
				authentication();

				http_response_code( 200 );
			
				echo createProduct($connect,$user_id);

				mysqli_close($connect);
				exit;
			}

			if ("stores" == $resourceType){
				authentication();

				http_response_code( 200 );

				echo createStore($connect,$user_id);

				mysqli_close($connect);

				exit;

			}

			if ("orders" == $resourceType){
				authentication();

				http_response_code( 200 );

				echo createOrder($connect);

				mysqli_close($connect);

				exit;

			}

			if ("tags" == $resourceType){
				authentication();

				http_response_code( 200 );

				echo createTag($connect,$user_id);

				mysqli_close($connect);

				exit;

			}

			if ("images" == $resourceType){
				authentication();

				http_response_code( 200 );

				echo createImage($connect,$user_id);

				mysqli_close($connect);

				exit;

			}

			if ("categories" == $resourceType){
				authentication();

				http_response_code( 200 );

				echo createCategory($connect,$user_id);

				mysqli_close($connect);

				exit;

			}
		}else{
			http_response_code( 404 );

			echo json_encode(
				[
					"error" => "Endpoint not fund in ".$method."method ",
				]
			);

			die;
		}
		break;

	case 'PUT':
		authentication();		
		if ( in_array( $resourceType, $availableMethods)) {

			if ( !empty($resourceId) ) {

				$id = number_format($resourceId);
				$user_id = number_format($_SERVER['HTTP_X_USER_ID']);

				if ("users" == $resourceType){
				
					http_response_code( 200 );

					echo updateUser($connect,$id,$user_id);

					mysqli_close($connect);
					exit;
				}

				if ("products" == $resourceType){				
					http_response_code( 200 );
				
					echo updateProduct($connect,$id,$user_id);

					mysqli_close($connect);
					exit;
				}

				if ("stores" == $resourceType){

					http_response_code( 200 );

					echo updateStore($connect,$id,$user_id);

					mysqli_close($connect);

					exit;

				}

				if ("orders" == $resourceType){

					http_response_code( 200 );

					echo updateOrder($connect,$id,$user_id);

					mysqli_close($connect);

					exit;

				}

				if ("tags" == $resourceType){

					http_response_code( 200 );

					echo updateTag($connect,$id,$user_id);

					mysqli_close($connect);

					exit;

				}

				if ("images" == $resourceType){

					http_response_code( 200 );

					echo updateImage($connect,$id,$user_id);

					mysqli_close($connect);

					exit;

				}

				if ("categories" == $resourceType){

					http_response_code( 200 );

					echo updateCategory($connect,$id,$user_id);

					mysqli_close($connect);

					exit;

				}
			}else{
				echo json_encode(
					[
						"error" => "You must enter a ID"
					]
				);
			}
		}else{
			http_response_code( 404 );

			echo json_encode(
				[
					"error" => "Endpoint not fund in ".$method."method ",
				]
			);

			die;
		}

		break;
	case 'DELETE':
		authentication();
		if ( in_array( $resourceType, $availableMethods)) {

			if ( !empty($resourceId) ) {

				$id = number_format($resourceId);
				$user_id = number_format($_SERVER['HTTP_X_USER_ID']);

				if ("users" == $resourceType){				
					http_response_code( 200 );

					echo deleteUser($connect,$id,$user_id);

					mysqli_close($connect);
					exit;
				}

				if ("products" == $resourceType){				
					http_response_code( 200 );
				
					echo deleteProduct($connect,$id,$user_id);

					mysqli_close($connect);
					exit;
				}

				if ("stores" == $resourceType){

					http_response_code( 200 );

					echo deleteStore($connect,$id,$user_id);

					mysqli_close($connect);

					exit;

				}

				if ("orders" == $resourceType){

					http_response_code( 200 );

					echo deleteOrder($connect,$id,$user_id);

					mysqli_close($connect);

					exit;

				}

				if ("tags" == $resourceType){

					http_response_code( 200 );

					echo deleteTag($connect,$id,$user_id);

					mysqli_close($connect);

					exit;

				}

				if ("images" == $resourceType){

					http_response_code( 200 );

					echo deleteImage($connect,$id,$user_id);

					mysqli_close($connect);

					exit;

				}

				if ("categories" == $resourceType){

					http_response_code( 200 );

					echo deleteCategory($connect,$id,$user_id);

					mysqli_close($connect);

					exit;

				}

			}else{
				echo json_encode(
					[
						"error" => "You must enter a ID"
					]
				);
			}
		}else{
			http_response_code( 404 );

			echo json_encode(
				[
					"error" => "Endpoint not fund in ".$method."method ",
				]
			);

			die;
		}

		break;

	default:
	http_response_code( 404 );

		echo json_encode(
			[
				'error' => $method.' not yet implemented',
			]
		);

		break;
}