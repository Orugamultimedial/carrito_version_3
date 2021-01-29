<?php 
 function authentication(){
	if ( !array_key_exists( 'HTTP_X_TOKEN', $_SERVER ) 
		|| !array_key_exists( 'HTTP_X_USER_ID', $_SERVER ) ) {
		http_response_code( 403 );

		echo json_encode(
			[
				"error" => "You must a token"
			]
		);

		die;
	}

	$url = 'http://localhost:8001';

	// Inicio la llamada a CURL
	$ch = curl_init( $url );

	// Informo el encabezado
	curl_setopt( 
		$ch, 
		CURLOPT_HTTPHEADER, 
		[
			"X-Token: {$_SERVER['HTTP_X_TOKEN']}",
			"X-User-Id: {$_SERVER['HTTP_X_USER_ID']}"
		]
	);

	curl_setopt( 
		$ch, 
		CURLOPT_RETURNTRANSFER, 
		true 
	);

	$res = curl_exec( $ch );

	if ( curl_errno($ch) != 0 ) {

		http_response_code( 403 );

		die ( curl_error($ch) );
	}

	if ( $res !== 'true' ) {
		http_response_code( 403 );
		/*echo json_encode(
			[
				"error" => "Acceso denegado",
			]
		);*/
		echo json_encode($res);
		
		die;
	}
}
?>