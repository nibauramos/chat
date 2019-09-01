<?php
require ('config.php');

$room = $_GET ['room'];
$room=substr($room, 0,32);

$msg = $_GET ['msg'];
$msg=substr($msg, 0,1024);
$linger = array ('l_linger' => 0, 'l_onoff' => 1);


// Enviar para o daemon
$socket = socket_create ( AF_INET, SOCK_STREAM, SOL_TCP );

socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));
socket_set_option($socket,SOL_SOCKET, SO_SNDTIMEO, array("sec"=>1, "usec"=>0));


if ($socket === false) {
	echo "socket_create() failed: reason: " . socket_strerror ( socket_last_error () ) . "\n";
	socket_set_option($socket, SOL_SOCKET, SO_LINGER, $linger);
	socket_close($socket);
	die ( 1 );
}

$result = @socket_connect ( $socket, $SERVER_IP, $SERVER_PORT );



if ($result === false) {
	echo "socket_connect() failed.\nReason: ($result) " . socket_strerror ( socket_last_error ( $socket ) ) . "\n";
	socket_set_option($socket, SOL_SOCKET, SO_LINGER, $linger);
	socket_close($socket);
	die ( 2 );
}

socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));
socket_set_option($socket,SOL_SOCKET, SO_SNDTIMEO, array("sec"=>1, "usec"=>0));


$toSend = $room . ";" . $msg;
socket_write ( $socket, $toSend, strlen ( $toSend ) );

// Receber a listagem
while ( $out = @socket_read ( $socket, 2048 ) ) {
	// devolver para o cliente
	echo $out;
}
//socket_set_option($socket, SOL_SOCKET, SO_LINGER, $linger);
socket_close ( $socket );

?>
