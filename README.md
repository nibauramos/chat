# chat

simple chat in HTML/JS/PHP

## Intro

This was the result of an afternoon just testing a concept for a "database less" chat system. I post this here ofering no waranties..and very little testing :)

## Concept

The logic behind this is to have a chat system accessible on a simple web page, that allows for the creation of rooms, however the server has no daemon, no database, no user registry..nothing.. It is meant to run on a "per-request"..accepting messages and forwarding them to whoever is on a chatroom, and... forgetting the messages shortly after... messages are delivered to whoever is on the room and when forgotten everything just "disapears".

This was meant as a no-history, no-tracking, "no-nothing" chat system

## Server

Considering that there is no daemon and no database system, what happens is that when you access the index.html#room-name this starts a few actions on your javascript, and one of them is a setInterval that attempts to call a PHP script that is "the server". Whenever a client is able to successfully call this "php server script" (only one instance of this script is allowed to run at a given time), the PHP process on the server opens a local TCP port so that it can deal with receving messages from other clients and forwarding them to connected clients. If for any reason this client "dies" any other connected client will take its place and launch the server again..if no client exists for this then no server needs to be running.

A small configuration can be found in config.php

## Client

The client is a simples HTML / JS webpage, that allows for a few client side configurations (like activating bgimages, sound notifications, etc.. all "command line stile" like old irc clients). There is a small routine to cypher messages but this is all done client side... you set a key, javascript then starts encrypting, and sending the encrypted result to the server. The server is not aware of any type of encryption...it just forwards.. and any client that has the same key will decrypt...all others will get garbled text.

You should access with http://your-server-ip/#room-name

Use:
/help - to see available client side commands

## Security

Not enough tests where made...not enough reviews where made... this was just a fun afternoon... use the code at your own risc.. trust it in the same way :) modify it anyway it pleases you

## Install

Just drop the code on any folder accessible through your php enabled web server and point your browser to http://your-server-ip/#desired-room-name

Any other client that access the same room will be able to receive messages.
