# ChatPGP
 Public Chat crypted with PGP. 
 
 This project is not ended. Some buttons are here, but dosen't work.

#Installation

- A web server "PHP" with "MySQL"

- A "NodeJS" server

Configure NodeJS Client in "chatPGP-PHP-MySQL/js/CHAT.js"

Configure the NodeJS server in "chatPGP-NodeJS/!start.bat"

Configure MySQL in "chatPGP-PHP-MySQL/ajax.php"

Create a Database, with the name specified into "chatPGP-PHP-MySQL/ajax.php" (default "tchat1")
___________________

How it works

All SQL tables will be created on the fly. if the database is well created.

So you can erase all tables (not the database). The chat will create the tables on the next request.

Each user clients, will create a PGP key on first connexion. Will share his public key. So messages sent before this first connexion will not be visible.

No account is requested to use this chat. Account created on the fly, if not cookie is present.

NodeJS server tell when something has changed on the chat (a new message, profil name, ...) and refresh the chat.

PHP and MySQL server will manage the data. 

The merging of NodeJS and PHP MySQL is done with JQuery in "chatPGP-PHP-MySQL/js/CHAT.js"

________________

Optional fonctionality 

- A vocal message is aviable. But, nothing is crypted there with PGP.

- You can add your video stream, on the top of the chat, if you uncomment and configure the tag <video ...></video> in "index.php" to use your stream server.
