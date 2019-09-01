<?php

/*
SERVER_IP is not your public ip (it might be..but not necessarily)
This is the IP where the TCP socket for client-client communication inside your webserver
will take place. I recommend leaving this as 127.0.0.1
*/
$SERVER_IP = '127.0.0.1';

/*
SERVER_PORT is the TCP port that the server script will launch to comunicate messages between clients
*/
$SERVER_PORT = '1026';

/*
Messages with more thatn MAX_TIME seconds are "forgotten" from the server...dont lower this too
much or some clients might not get messages, but keep it "short" for privacy
*/
$MAX_TIME=15; 