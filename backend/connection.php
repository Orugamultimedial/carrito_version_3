<?php
function testConnection(){
	$host='127.0.0.1';
	$user= 'root';
	$pass= '';
	$db= 'carrito';

	$enlace = mysqli_connect($host, $user, $pass, $db);

	if (!$enlace) {
		echo "Error: Could not connect to MySQL." . PHP_EOL;
		echo "Debugging error: " . mysqli_connect_errno() . PHP_EOL;
		echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
		exit;
	}
	
	echo "Success: A proper connection to MySQL was made!" . PHP_EOL;
	echo "host information: " . mysqli_get_host_info($enlace) . PHP_EOL;
    
    mysqli_close($enlace);
	exit;
}
function Connection(){
	$host='127.0.0.1';
	$user= 'root';
	$pass= '';
	$db= 'carrito';

	$enlace = mysqli_connect($host, $user, $pass, $db);

	if (!$enlace) {
		echo "Error: Could not connect to MySQL." . PHP_EOL;
		echo "Debugging error: " . mysqli_connect_errno() . PHP_EOL;
		echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
		exit;
	}

	return $enlace;

}