# ChatPGP
 Public Chat crypted with PGP. 
 
 This project is not ended. (Some buttons are here, but dosen't work).
 
 
# Installation

install :

- A web server "PHP" with "MySQL"

- A "NodeJS" server

Configure NodeJS Client in "chatPGP-PHP-MySQL/js/CHAT.js"

```javascript
CHATPGP.socket = io("//:24443");
```

Configure the NodeJS server in "chatPGP-NodeJS/!start.bat"

```bash
node app.js --https-port 13443 --http-port 13080 --ssl-key SSL/private-key.pem --ssl-cert SSL/certificate.pem --ssl-ca SSL/ca.pem
```
Erase and create new certificates before go in public ;)

Configure MySQL connexion to the server in "chatPGP-PHP-MySQL/ajax.php"

```php
$config = array(
	'mysql_address' => '127.0.0.1:53306',
	'mysql_username' => 'root',
	'mysql_password' => 'root',
	'mysql_database' => 'tchat1'
);
```

Create a MySQL Database from your phpmyadmin, with the name specified into "chatPGP-PHP-MySQL/ajax.php" (default "tchat1")

Put the folder "chatPGP-PHP-MySQL" in a public folder and rename it as you want or put the content in the root www :)

# How it works ?

All SQL tables will be created on the fly. if the database is well created.

So you can erase all tables (not the database). The chat will create the tables on the next request.

Each user clients, will create a PGP key on first connexion. Will share his public key. So messages sent before this first connexion will not be visible.

No account is requested to use this chat. Account created on the fly, if not cookie is present.

NodeJS server tell when something has changed on the chat (a new message, profil name, ...) and refresh the chat.

PHP and MySQL server will manage the data. 

The merging of NodeJS and PHP MySQL is done with JQuery in "chatPGP-PHP-MySQL/js/CHAT.js"


# Optional fonctionality 

- A vocal message is aviable. But, nothing is crypted there with PGP. Nothing to install. All is ready with the chat.

- You can add your video stream, on the top of the chat, if you uncomment and configure the tag <video ...></video> in "index.php" to use your stream server.

The stream server is aviable in the folder "nginx-1.7.11.3-Gryphon-RTMP-Server"

Then you can stream to it with OBS Studio https://obsproject.com


# For mobile phones

To make this chat aviable between mobiles, you can use tor server. 

Install Termux on Android, and then the servers : apache2, mysqld, node, tor, and talk with your friends :)

```bash
pkg update
pkg install proot-distro
proot-distro list
proot-distro install ubuntu-lts
proot-distro login ubuntu-lts
apt update -y && apt upgrade -y && apt install xfce4
service dbus start
termux-x11 :1 -xstartup "dbus-launch --exit-with-session xcfe4-session"

echo Start or install now "Termux:X11"
```
This command will download and install Ubuntu on your phone

Then you can go into your destktop with "Termux:X11" Android Application if you want to install apache2, mysqld, node, tor, with "apt install ..."

or stop here to continue only with command prompts without desktop

```bash
proot-distro login ubuntu-lts
```
