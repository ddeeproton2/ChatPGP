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

- A vocal message is aviable. But not protected with PGP. 

- You can add your video stream, on the top of the chat, if you uncomment and configure the tag <video ...></video> in "index.php" to use your stream server.

The stream server is aviable in the folder "nginx-1.7.11.3-Gryphon-RTMP-Server"

Then you can stream to it with OBS Studio https://obsproject.com


# For Android

## Android - install Termux

To make this chat aviable between mobiles, you can use tor server. 

Install Termux on Android, and then the servers : apache2, mysqld, node, tor, and chat your friends.

To start, you need Ubuntu on your Termux :
```bash
pkg update
pkg install proot-distro
proot-distro list
proot-distro install ubuntu-lts
proot-distro login ubuntu-lts
apt update -y && apt upgrade -y
```

To start Ubuntu on the next restart of termux

```bash
proot-distro login ubuntu-lts
```

## Android - install Termux:X11

To install a desktop

```bash
proot-distro login ubuntu-lts
apt install xfce4
service dbus start
termux-x11 :1 -xstartup "dbus-launch --exit-with-session xcfe4-session"

echo Start or install now "Termux:X11"
```
and connect to the desktop with "termux-x11" Android application.

and then ask to ChatGPT or other AI how to install apache2 (with SSL), mysqld, node and tor :)

## Android - Auto start desktop with Termux, and Termux:X11 applications

if you want to start the desktop with Termux.

exit termux with exit if you was connected

Edit your .bashrc 

```bash
nano .bashrc
```

Add this at the end

```bash
proot-distro login ubuntu-lts
```

Control + S to save
F2 to exit

Log into Ubuntu again

```bash
proot-distro login ubuntu-lts
```

Edit the .bashrc of Ubuntu
```bash
nano .bashrc
```

Add this at the end 
```bash

processus="xfce4-session"
# if the desktop is not running
if ! pgrep -x "$processus" > /dev/null; then

  # start audio. Comment this if no audio needed for gain performances
  pulseaudio --start     --load="module-native-protocol-tcp auth-ip-acl=127.0.0.1 auth-anonymous=1"     --exit-idle-time=-1

  service dbus start
  rm -rf /tmp/.X*
  termux-x11 :1 -xstartup "dbus-launch --exit-with-session xfce4-session" &

#else
  #echo "The processs $processus is running."
fi

```

Control + S to save
F2 to exit

And then restart to test
