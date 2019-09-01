<?php
require ('config.php');

$linger = array ('l_linger' => 0, 'l_onoff' => 1);


error_reporting ( E_ALL );

/* Allow the script to hang around waiting for connections. */
set_time_limit ( 60 );

/*
 * Turn on implicit output flushing so we see what we're getting as it comes in.
 */
ob_implicit_flush ();

$rooms = [ ];

if (($sock = socket_create ( AF_INET, SOCK_STREAM, SOL_TCP )) === false) {
	echo json_encode ( [ 
			1,
			"socket_create() failed: reason: " . socket_strerror ( socket_last_error () ) 
	] );
	die ( 1 );
}

if (@socket_bind ( $sock, $SERVER_IP, $SERVER_PORT ) === false) {
	echo json_encode ( [ 
			2,
			"socket_bind() failed: reason: " . socket_strerror ( socket_last_error ( $sock ) ) 
	] );
	socket_set_option($sock, SOL_SOCKET, SO_LINGER, $linger);
	socket_close($sock);
	die ( 2 );
}

if (socket_listen ( $sock, 5 ) === false) {
	echo json_encode ( [ 
			3,
			"socket_listen() failed: reason: " . socket_strerror ( socket_last_error ( $sock ) ) 
	] );
	socket_set_option($sock, SOL_SOCKET, SO_LINGER, $linger);
	socket_close($sock);
	die ( 3 );
}

do {
	error_log('Waiting for new client');
	if (($msgsock = socket_accept ( $sock )) === false) {
		echo json_encode ( [ 
				4,
				"socket_accept() failed: reason: " . socket_strerror ( socket_last_error ( $sock ) ) 
		] );
		error_log('Error on client socket');
		socket_set_option($msgsock, SOL_SOCKET, SO_LINGER, $linger);
		socket_close($msgsock);
		break;
	}
	
	socket_set_option ( $msgsock, SOL_SOCKET, SO_RCVTIMEO, array (
			"sec" => 1,
			"usec" => 0 
	) );
	socket_set_option ( $msgsock, SOL_SOCKET, SO_SNDTIMEO, array (
			"sec" => 1,
			"usec" => 0 
	) );
	
	if (false === ($rawMessage = socket_read ( $msgsock, 2048 ))) {
		socket_set_option($msgsock, SOL_SOCKET, SO_LINGER, $linger);
		socket_close ( $msgsock );
		error_log('Error reading from client 1');
		continue;
	}
	
	$separadorPos = strpos ( $rawMessage, ';' );
	if ($separadorPos === false) {
		socket_set_option($msgsock, SOL_SOCKET, SO_LINGER, $linger);
		socket_close ( $msgsock );
		error_log('No separator');
		continue;
	}
	
	$requestedRoom = trim ( substr ( $rawMessage, 0, $separadorPos ) );
	if (strlen($requestedRoom)>32){ //hack para shutdown
		socket_set_option($msgsock, SOL_SOCKET, SO_LINGER, $linger);
		socket_close ( $msgsock );
		error_log('Server instructed to terminate');
		break;
	}
	$msg = trim ( substr ( $rawMessage, $separadorPos + 1, strlen ( $rawMessage ) - strlen ( $requestedRoom ) ) );
	
	if (strlen ( $requestedRoom ) <= 0) {
		socket_set_option($msgsock, SOL_SOCKET, SO_LINGER, $linger);
		socket_close ( $msgsock );
		error_log('No room was requested');
		continue;
	}
	
	error_log('Received ('.$msg.') for '.$requestedRoom);
	// ver se o room já existe
	if (! array_key_exists ( $requestedRoom, $rooms )) {
		echo 'created: ' . $requestedRoom . "\n";
		// Não existia criamos o quarto
		$rooms [$requestedRoom] = array ();
	} else {
		echo 'already exists: ' . $requestedRoom . "\n";
	}
	
	// Limpar todos os quartos de mensagens com mais do x segundos
	foreach ( $rooms as $key => $value ) {
		for($i = 0; $i < count ( $rooms [$key] ); $i ++) {
			if ($rooms [$key] [$i] [0] + $MAX_TIME*1000 < microtime(true)*1000) {
				array_splice ( $rooms [$key], $i, 1 );
				$i --;
			}
		}
	}
	
	// Registar a frase enviada
	if (strlen ( $msg ) > 0) {
		array_push ( $rooms [$requestedRoom], array (
				round(microtime(true)*1000),
				$msg 
		) );
	}
	
	// Retornar todas as mensagens ainda no quarto
	$toSend = json_encode ( $rooms [$requestedRoom] );
	socket_write ( $msgsock, $toSend, strlen ( $toSend ) );
	socket_set_option($msgsock, SOL_SOCKET, SO_LINGER, $linger);
	socket_close ( $msgsock );
	error_log('closed socket...all done');
} while ( true );
socket_set_option($sock, SOL_SOCKET, SO_LINGER, $linger);
socket_close ( $sock );
error_log('Server terminated');
?>
