




var pageAjax = "ajax.php";
//var pageAjax = "https://tor2web.onionsearchengine.com/index.php?q=http%3A%2F%2Fmtyudj3rrgown4bti6zzrp3ezzdymrjirmq6yvbcgkojhqmnvz3w6uqd.onion%2Fchat%2Fajax.php";

var CHATPGP = {
    debug: false,
    _pgp: undefined,
    _user_session: undefined,
    _templates: undefined,
    _rooms: [],
    _users: [],
    _data: undefined, 
    _lastIdMessage: -1,
    _focused: true,
    socket: undefined,
    //_lastMessage: "",
    Start: function(){
            CHATPGP.Stop();
            
            CHATPGP.LoadProfile();
            CHATPGP.checkFocus();
            if(!CHATPGP._pgp || !CHATPGP._pgp.publicKey){
                var d = new Date();
                CHATPGP._pgp = PGP.generate();
                CHATPGP._pgp.id_publickey = d.getTime();
            }
            if(!CHATPGP._pgp || !CHATPGP._pgp.publicKey){
                console.log("publicKey is not set");
                return;
            }


            AJAX._timestamp = '';
            AJAX._type = "POST";
            // AJAX.OpenUrl_LongQuery: function(url, data, timeout, onread, onreaderror){
            AJAX.OpenUrl_LongQuery(
                pageAjax, 
                CHATPGP.setParamsServer(),
                ((4 * 60) + 45) * 1000, // Timeout request
                CHATPGP.onMessage,
                CHATPGP.onMessageError
            );
        
            //CHATPGP.bindOnMessage();


    },
    onMessage: function(data){
      console.log(data);
      CHATPGP.parseServerResponse(data);
      // set next request
      AJAX._data = CHATPGP.setParamsServer();
    },
    /*
    bindOnMessage: function(){
      CHATPGP.socket = io("//:24443");
      CHATPGP.socket.on('chat message', 'chatting', function(data){
        console.log("Refresh requested ");
         //CHATPGP.onMessage(JSON.parse(data));
            // Set request
            AJAX._type = "POST";

            // AJAX.OpenUrl_LongQuery: function(url, data, timeout, onread, onreaderror){
            AJAX.OpenUrl_LongQuery(
                    pageAjax, 
                    CHATPGP.setParamsServer(),
                    ((4 * 60) + 45) * 1000, // Timeout request
                    CHATPGP.onMessage,
                    CHATPGP.onMessageError
            );
       });

    },
    */
    SendMessage: function(id_room, message, ondone){
      if(CHATPGP._pgp == undefined || CHATPGP._pgp == "" || CHATPGP._pgp.publicKey == undefined){
      //if(CHATPGP._pgp == undefined || CHATPGP._pgp == ""){
              var d = new Date();
              CHATPGP._pgp = PGP.generate();
              CHATPGP._pgp.id_publickey = d.getTime();
              CHATPGP.SaveProfile();
      }

      var users = CHATPGP._data.users_connected;
      var msg = [];
      for(var i = 0; i < users.length; i++){
              msg.push({
                      message: PGP.encrypt(message, users[i].publickey), //CHATPGP._pgp.encrypt(message, users[i].publickey), // 
                      id_publickey: users[i].id_publickey,
                      user_name: users[i].user_name,
                      id_user: users[i].id_user
              });
      }
      
      AJAX.OpenUrl_ShortQuery(
              pageAjax, 
              {
                      "action":"sendMessage",
                      "params":{
                              "usersession": CHATPGP._user_session,
                              "messages": msg,
                              "id_room": id_room
                      }
              }, function(data){
                      console.log(data);
                      ondone(data);
              }
      );

    },
    onMessageError: function(data){
      console.log(data);
      /*
      //if(data.Error.toString().trim() === "create user fail"){
          console.log("[Autofix] set new ID");
          var d = new Date();
          CHATPGP._pgp = PGP.generate();
          CHATPGP._pgp.id_publickey = d.getTime();
          CHATPGP.SaveProfile();
          AJAX._timestamp = '';
          CHATPGP.LoadProfile();
      //}
      */
      
      //if(data.toString().match("Error")){
          AJAX._timestamp = '';
          CHATPGP.LoadProfile();
          //CHATPGP.SaveProfile();
      //}
      /*
          if(!CHATPGP._pgp){
              var d = new Date();
              CHATPGP._pgp = PGP.generate();
              CHATPGP._pgp.id_publickey = d.getTime();
          }
          if(!CHATPGP._pgp.publicKey){
              console.log("Error publicKey not set");
          }
      */  
    },
    Stop: function(){
            AJAX.Abort();
    },
    refreshProfile: function(user){
            $(".current_user_name").text(user.user_name);
    },
    findUserID: function(id){
        for(var i = 0; i < CHATPGP._users.length; i++){
            if(CHATPGP._users[i].id_user === id) return CHATPGP._users[i];
        }
        return false;
    },
    refreshUsersConnected: function(users){
        //console.log(users);
        if(CHATPGP._templates.users === undefined) {
            CHATPGP.LoadTemplates();
        }
        
        
        if(CHATPGP._templates.users === undefined) {
            console.log('Template is not defined!');
            return;
        }
        var template = CHATPGP._templates.users;
        // Detect new user and changes
        var list_id = [];
        for(var i = 0; i < users.length; i++){
            var oldUser = CHATPGP.findUserID(users[i].id_user);
            if(oldUser !== false){
                //var elem = $(".chat_wrapper_users .user_"+users[i].id_user+" .username");
                //if(elem.is(":visible")) { // not new user
                if(oldUser.user_name !== users[i].user_name) { // check changes
                    $(".chat_wrapper_users .user_"+users[i].id_user+" .username").text(users[i].user_name);
                }
            }else{ // new user
                $(".chat_wrapper_users").append(
                    template.replace('{username}', users[i].user_name)
                            .replace('{id_user}', users[i].id_user)
                            .replace('{id_user}', users[i].id_user)
                );
            }
            // Detection disconnected users
            list_id.push(users[i].id_user);
        }

        // Detect disconnected users
        for(var i = 0; i < CHATPGP._users.length; i++){
            //$(".chat_wrapper_users .id_user").each(function(){
            var id_user = CHATPGP._users[i].id_user;
            if(id_user === "{id_user}") {
                console.log('error id_user is not defined');
            }else{
                if(list_id.indexOf(id_user) === -1){ 
                    // user is disconnected
                    $(".chat_wrapper_users .user_"+id_user).remove();
                }					
            }
            //});
        }
        CHATPGP._users = users;
    },
    refreshMessages: function(messages, users_connected){

        for(var i = 0; i < messages.length; i++){
            //console.log(messages[i].message);
            //console.log(localStorage.getItem("privateKey"));
            //console.log(PGP.decrypt(messages[i].message, localStorage.getItem("privateKey")));
            var isNewMessage = false;
            if($(".idmessage_"+messages[i].id_message).length === 0){
                CHATPGP._lastIdMessage = messages[i].id_message;
                
                /*
                var username = messages[i].id_user_from;
                for(var j = 0; j < users_connected.length; j++){
                    if(users_connected[j].id_user == messages[i].id_user_from){
                        username = users_connected[j].user_name;
                    }
                }
                */
                
                isNewMessage = true;
                var html = `<div class=" media text-muted pt-3 idmessage_`+messages[i].id_message+`">
                    <img class="avatar mr-2" style="width:32px; height:32px;" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2232%22%20height%3D%2232%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2032%2032%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_164105ec91c%20text%20%7B%20fill%3A%23007bff%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A2pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_164105ec91c%22%3E%3Crect%20width%3D%2232%22%20height%3D%2232%22%20fill%3D%22%23007bff%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%2213.5%22%20y%3D%2216.9%22%3E32x32%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" data-holder-rendered="true">
                    <div class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
                        <strong class="text-gray-dark">

                            <div class="btn-group">
                                <button type="button" class="class btn btn-default" title="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <strong class="text-gray-dark">`+messages[i].user_name+`</strong>  
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="?action=add">Envoyer un message privé</a></li>
                                    <li><a href="?action=update&amp;id_name=1">Bloquer</a></li>
                                </ul>
                            </div>
                            <!--
                            <small title="12.11.2011">10:02</small> 
                            -->

                        </strong>
                        <span class="d-block">`+PGP.decrypt(messages[i].message, localStorage.getItem("privateKey"))+`</span>
                    </div>
                </div>`;
                $(".chat_wrapper_content .scrolly").append(html);
            }

        }
        if(isNewMessage){
            if(CHATPGP._focused === false){
                CHATPGP.alertAudio();
            }
            CHATPGP.resize();
            //$(".chat_wrapper_content .scrolly").scrollTop($(".chat_wrapper_content .scrolly")[0].scrollHeight + 500);
            setTimeout(function(){
                $(".chat_wrapper_content .scrolly").animate({ scrollTop: $(".chat_wrapper_content .scrolly")[0].scrollHeight + 500 }, 1000);
            },300);
            
        }
        /*
            "user_messages": [
                {
                    "id_message": "1",
                    "id_user_from": "6",
                    "id_user_to": "6",
                    "id_room": "0",
                    "date_send": "1650805664",
                    "date_expire": "1671456440",
                    "date_read": "0",
                    "id_publickey": "2147483647",
                    "message": "wUwDZ6QsNh4FiDsBAgCBtPVqHxpWfsNr8oQjGzCxG5MKM+vd4ebtkAFVEMf/\npTWwcFKwD7E5yXjKjQ/KCRtPCdaMwcPCZUyCw7XcRvZT0jwBgIPCnrWSQOhb\ndflFr7ROrrOpwrlSSD0CBSMus9mrvdcTaGw0e0MMn2scC2sMV7NW3A8YGXld\nxgkDdq4=\r\n=8f84\r\n"
                },
                {
                    "id_message": "2",
                    "id_user_from": "6",
                    "id_user_to": "6",
                    "id_room": "0",
                    "date_send": "1650805773",
                    "date_expire": "2147483647",
                    "date_read": "0",
                    "id_publickey": "2147483647",
                    "message": "wUwDZ6QsNh4FiDsBAf9YMMeagt2udcuaA2wQJFK0Fh4p/r94STtgwg07javu\nZdTNM3ZaJbMUsEpGB/lX8NZGGBp3lwFsjUQIoxDhdgCc0jwBF/diAB5OJDXF\n2Gcuj2/6lq/N5eXbh3DAANGSnqjU0rWOo0nQlEeg2MnkE04gtORW2lxTbh+/\n510dR5I=\r\n=oS8J\r\n"
                }
            ]

         */


    },
    setParamsServer: function(){
        CHATPGP.LoadProfile();
        return {
                "action":"get",
                "params":{
                        "usersession":  CHATPGP._user_session,
                        "publickey":    CHATPGP._pgp.publicKey,
                        "id_publickey": CHATPGP._pgp.id_publickey,
                        "user_rooms":   CHATPGP._rooms,
                        "lastIdMessage":   CHATPGP._lastIdMessage,
                        "user_avatar": "none"
                }
        };
    },
    LoadProfile: function(){
            
            /*
            // Get PGP Keys
            //CHATPGP._pgp = localStorage.getItem("pgp");
            if(CHATPGP._pgp == undefined && localStorage.getItem("pgpkeys") != undefined){
                CHATPGP._pgp = PGP.generate();
                CHATPGP._pgp.keys = localStorage.getItem("pgpkeys");
            }else{
            //if(CHATPGP._pgp.keys == undefined || CHATPGP._pgp == "" || CHATPGP._pgp.publicKey == undefined){
            //if(CHATPGP._pgp == undefined || CHATPGP._pgp == ""){
                    var d = new Date();
                    CHATPGP._pgp = PGP.generate();
                    CHATPGP._pgp.id_publickey = d.getTime();
                    CHATPGP.SaveProfile();
            }
            */


            /*
            if(localStorage.getItem("privateKey") !== undefined){
                CHATPGP._pgp.privateKey = localStorage.getItem("privateKey");
            }
            if(localStorage.getItem("publicKey") !== undefined){
                CHATPGP._pgp.publicKey = localStorage.getItem("publicKey");
            }
            if(localStorage.getItem("id_publickey") !== undefined){
                CHATPGP._pgp.id_publickey = localStorage.getItem("id_publickey");
            }
            */
            if(!CHATPGP._pgp){
                var d = new Date();
                CHATPGP._pgp = PGP.generate();
                CHATPGP._pgp.id_publickey = d.getTime();
            }
            if(!CHATPGP._pgp.publicKey){
                console.log("Error publicKey not set");
            }
            if(localStorage.getItem("privateKey") !== "" && localStorage.getItem("privateKey") !== "null"){
                CHATPGP._pgp.privateKey = localStorage.getItem("privateKey");
                CHATPGP._pgp.publicKey = localStorage.getItem("publicKey");
                CHATPGP._pgp.id_publickey = localStorage.getItem("id_publickey");
                CHATPGP._user_session = localStorage.getItem("user_session");
            }
            CHATPGP.SaveProfile();

    },
    SaveProfile: function(){
            /*
            if(CHATPGP._pgp.publicKey !== undefined) {
                localStorage.setItem("publicKey", CHATPGP._pgp.publicKey);
            }
            if(CHATPGP._pgp.privateKey !== undefined) {
                localStorage.setItem("privateKey", CHATPGP._pgp.privateKey);
            }
            if(CHATPGP._pgp.id_publickey !== undefined) {
                localStorage.setItem("id_publickey", CHATPGP._pgp.id_publickey);
            }
            if(CHATPGP._user_session !== undefined) {
                localStorage.setItem("user_session", CHATPGP._user_session);
            }
            */
            localStorage.setItem("publicKey", CHATPGP._pgp.publicKey);
            localStorage.setItem("privateKey", CHATPGP._pgp.privateKey);
            localStorage.setItem("id_publickey", CHATPGP._pgp.id_publickey);
            localStorage.setItem("user_session", CHATPGP._user_session);
    },
    ChangeProfile: function(params){
            params.usersession = localStorage.getItem("user_session");
            AJAX.OpenUrl_ShortQuery(
                    pageAjax, 
                    {
                            "action":"setProfile",
                            "params": params
                    }, function(data){
                            console.log(data);
                            CHATPGP.refreshProfile(data.user);
                            CHATPGP.refreshUsersConnected(data.users_connected);
                            CHATPGP.socket.emit('emitall', "", "", "set", "clienttype", "Gemini");
                    }
            );
    },
    Disconnect: function(params){
            console.log('Disconnect');
            AJAX.OpenUrl_ShortQuery(
                    pageAjax, 
                    {
                            "action":"disconnect",
                            "params":{
                                    "usersession": CHATPGP._user_session
                            }
                    }, function(data){
                            console.log(data);
                            CHATPGP.socket.emit('emitall', "", "", "set", "clienttype", "Gemini");
                    }
            );
    },

    LoadTemplates: function(){
            CHATPGP._templates = {
                    users: $(".chat_wrapper_users").html(),
                    messages: $(".chat_wrapper_content .scrolly").html()
            };
            $(".chat_wrapper_users").html("");
            //$(".chat_wrapper_content .scrolly").html("");
    },
    invitePrivateMessage: function(invite_id_user, ondone){
            AJAX.OpenUrl_ShortQuery(
                    pageAjax, 
                    {
                            "action":"openRoom",
                            "params":{
                                    "usersession": CHATPGP._user_session,
                                    "invite_id_user": invite_id_user
                            }
                    }, function(data){
                            ondone(data);
                    }
            );
    },
    bannUser: function(bann_id_user, ondone){
            AJAX.OpenUrl_ShortQuery(
                    pageAjax, 
                    {
                            "action":"bannUser",
                            "params":{
                                    "usersession": CHATPGP._user_session,
                                    "bann_id_user": bann_id_user
                            }
                    }, function(data){
                            ondone(data);
                            CHATPGP.socket.emit('emitall', "", "", "set", "clienttype", "Gemini");
                    }
            );
    },
    parseServerResponse: function(data){
        /*
        data = {
            "timestamp": "",
            "user": {
                "id_user": "7",
                "user_privilege": "0",
                "user_name": "guest_7",
                "user_email": "",
                "user_description": "",
                "user_allow_newsession": "1",
                "user_notify_typing": "1",
                "user_avatar": "",
                "id_publickey": "2147483647",
                "publickey": "-----BEGIN PGP PUBLIC KEY BLOCK-----\r\nVersion: OpenPGP.js v.1.20130420\r\nComment: http://openpgpjs.org\r\n\r\nxk0EYmVSIgEB/20a4qs9K5W6MfQ5BMlelVq2tGM9ENuwmk3C5yDWANM6DRSj\nSLD2yNjhYtxYIQ2qmPwKToVJiiuVMQVXr+MftssAEQEAAc0jUHJiRzBmZVBR\nd2V2T0todGwgeWRvcE1odmpmaTd6Rm5EeUXCXAQQAQgAEAUCYmVSIwkQ10IJ\nLCxK6+EAAOVTAf4pJI62VaDz5ZtW/aDOPzzaIY7YIDIaWiQUF8rlr9ZcotA8\nSJEvFos3jAVYHcjU8eKQ3rtJBjLarMPn3Ooj0ZyY\r\n=oTSq\r\n-----END PGP PUBLIC KEY BLOCK-----\r\n\r\n",
                "user_session": "092a91362597a0ff414661a6d2264ee2"
            },
            "rooms": [
                {
                    "id_room": "1",
                    "id_creator": "0",
                    "enabled": "1",
                    "name": "Le Tchat",
                    "crypted": "0",
                    "public": "1",
                    "id_roomsuser": "7",
                    "id_user": "7",
                    "user_privilege": "0",
                    "user_enabled": "1",
                    "user_name": "guest_7",
                    "user_pass": "",
                    "user_email": "",
                    "user_description": "",
                    "user_allow_newsession": "1",
                    "user_notify_typing": "1",
                    "user_avatar": "",
                    "id_publickey": "2147483647",
                    "publickey": "-----BEGIN PGP PUBLIC KEY BLOCK-----\r\nVersion: OpenPGP.js v.1.20130420\r\nComment: http://openpgpjs.org\r\n\r\nxk0EYmVSIgEB/20a4qs9K5W6MfQ5BMlelVq2tGM9ENuwmk3C5yDWANM6DRSj\nSLD2yNjhYtxYIQ2qmPwKToVJiiuVMQVXr+MftssAEQEAAc0jUHJiRzBmZVBR\nd2V2T0todGwgeWRvcE1odmpmaTd6Rm5EeUXCXAQQAQgAEAUCYmVSIwkQ10IJ\nLCxK6+EAAOVTAf4pJI62VaDz5ZtW/aDOPzzaIY7YIDIaWiQUF8rlr9ZcotA8\nSJEvFos3jAVYHcjU8eKQ3rtJBjLarMPn3Ooj0ZyY\r\n=oTSq\r\n-----END PGP PUBLIC KEY BLOCK-----\r\n\r\n"
                }
            ],
            "users_connected": [
                {
                    "id_user": "7",
                    "user_name": "guest_7",
                    "id_publickey": "2147483647",
                    "publickey": "-----BEGIN PGP PUBLIC KEY BLOCK-----\r\nVersion: OpenPGP.js v.1.20130420\r\nComment: http://openpgpjs.org\r\n\r\nxk0EYmVSIgEB/20a4qs9K5W6MfQ5BMlelVq2tGM9ENuwmk3C5yDWANM6DRSj\nSLD2yNjhYtxYIQ2qmPwKToVJiiuVMQVXr+MftssAEQEAAc0jUHJiRzBmZVBR\nd2V2T0todGwgeWRvcE1odmpmaTd6Rm5EeUXCXAQQAQgAEAUCYmVSIwkQ10IJ\nLCxK6+EAAOVTAf4pJI62VaDz5ZtW/aDOPzzaIY7YIDIaWiQUF8rlr9ZcotA8\nSJEvFos3jAVYHcjU8eKQ3rtJBjLarMPn3Ooj0ZyY\r\n=oTSq\r\n-----END PGP PUBLIC KEY BLOCK-----\r\n\r\n"
                }
            ],
            "user_messages": [
                {
                    "id_message": "1",
                    "id_user_from": "6",
                    "id_user_to": "6",
                    "id_room": "0",
                    "date_send": "1650805664",
                    "date_expire": "1671456440",
                    "date_read": "0",
                    "id_publickey": "2147483647",
                    "message": "wUwDZ6QsNh4FiDsBAgCBtPVqHxpWfsNr8oQjGzCxG5MKM+vd4ebtkAFVEMf/\npTWwcFKwD7E5yXjKjQ/KCRtPCdaMwcPCZUyCw7XcRvZT0jwBgIPCnrWSQOhb\ndflFr7ROrrOpwrlSSD0CBSMus9mrvdcTaGw0e0MMn2scC2sMV7NW3A8YGXld\nxgkDdq4=\r\n=8f84\r\n"
                },
                {
                    "id_message": "2",
                    "id_user_from": "6",
                    "id_user_to": "6",
                    "id_room": "0",
                    "date_send": "1650805773",
                    "date_expire": "2147483647",
                    "date_read": "0",
                    "id_publickey": "2147483647",
                    "message": "wUwDZ6QsNh4FiDsBAf9YMMeagt2udcuaA2wQJFK0Fh4p/r94STtgwg07javu\nZdTNM3ZaJbMUsEpGB/lX8NZGGBp3lwFsjUQIoxDhdgCc0jwBF/diAB5OJDXF\n2Gcuj2/6lq/N5eXbh3DAANGSnqjU0rWOo0nQlEeg2MnkE04gtORW2lxTbh+/\n510dR5I=\r\n=oS8J\r\n"
                }
            ]
        }
         */

        //console.log(data);
/*
        // check server answere
        if(data === undefined 
        || data.timestamp === undefined
        || data.timestamp === ""
        || !data.timestamp
        || data.user === undefined
        || data.user.user_session === undefined) {
                console.log("error server");
              
                setTimeout(function(){
                        console.log("restart connexion");
                        CHATPGP.Start();
                }, 5000);
              
                return;
        }
        */
        AJAX._timestamp = data.timestamp;
        CHATPGP._data = data;
        CHATPGP._user_session = data.user.user_session;
        CHATPGP.SaveProfile();
        CHATPGP.refreshProfile(data.user);
        CHATPGP.refreshUsersConnected(data.users_connected);
        CHATPGP.refreshMessages(data.user_messages, data.users_connected);
        
    },
    resize: function(){
        // Pour redimenssioner la vidéo au dessus du tchat
        //var h = $(window).innerHeight() - $(".chat_bottom_input").height() - $(".top_header").height()- 10 - 160;
        var h = $(window).innerHeight() - $(".chat_bottom_input").height() - $(".top_header").height()- 10 ;
        //console.log("resize to "+h);
        $(".chat_wrapper_content .scrolly").height(h);
        $(".chatPGP .chat_wrapper_users").height(h-50);
    },
    
    

    insertAtCaret: function(element, value) {
        var element_dom = $(element)[0];
        if (document.selection) {
            element_dom.focus();
            sel = document.selection.createRange();
            sel.text = value;
            return;
        } 
        if (element_dom.selectionStart || element_dom.selectionStart == "0") {
            var t_start = element_dom.selectionStart;
            var t_end = element_dom.selectionEnd;
            var val_start = element_dom.value.substring(value, t_start);
            var val_end = element_dom.value.substring(t_end, element_dom.value.length);
            element_dom.value = val_start + value + val_end;
        }else{
            element_dom.value += value;
        }
    },
    insertHTMLEditable: function(element, msg){
        $(element)[0].focus();
        document.execCommand("insertHTML", false, msg);
        //document.execCommand("insertImage", true, msg);
        //document.execCommand("InsertHTML", true, "yooo");
    },
    alertAudio: function(){
        const music = new Audio('audio/announcement-sound-4-21464.mp3');
        music.play();
        //music.loop =true;
        //music.playbackRate = 2;
        //music.pause();
    },
    checkFocus: function(){
        window.onblur = function() { CHATPGP._focused = false; };
        window.onfocus = function() { CHATPGP._focused = true; };
        document.onblur = window.onblur;
        document.focus = window.focus;
    },
    recording:{
        isRunning: false,
        datas:{
            chunks: [],
            blob: undefined,
            mediaRecorder: undefined,
            duration: undefined,
            stream: undefined,
            handleDuration: undefined,
            id_anim: undefined,
            canvas: undefined,
            canvasCtx: undefined
        },
        init:function(){
            if(navigator.mediaDevices === undefined || !navigator.mediaDevices.getUserMedia){
                console.log('getUserMedia not supported on your browser!');
                return false;
            }
            const constraints = { audio: true };
            navigator.mediaDevices.getUserMedia(constraints).then(CHATPGP.recording.onSucessInit, CHATPGP.recording.onErrorInit);  
            
            return true;
        },
        onSucessInit:function(stream){
            CHATPGP.recording.datas.stream = stream;
            CHATPGP.recording.datas.mediaRecorder = new MediaRecorder(CHATPGP.recording.datas.stream);         
            CHATPGP.recording.datas.mediaRecorder.ondataavailable = function(e) {
                CHATPGP.recording.datas.chunks.push(e.data);
            };
            CHATPGP.recording.datas.mediaRecorder.onstop = CHATPGP.recording.onStop;

        },
        onErrorInit:function(err) {
            console.log('The following error occured: ' + err);
        },
        start:function(){
            CHATPGP.recording.isRunning = true;
            CHATPGP.recording.datas.canvas = document.querySelector('.recording_pannel .audio_graph .audio_graph_flex .visualizer');
            CHATPGP.recording.datas.canvasCtx = CHATPGP.recording.datas.canvas.getContext("2d");
            CHATPGP.recording.visualize();
            CHATPGP.recording.datas.chunks = [];
            CHATPGP.recording.datas.mediaRecorder.start();
            console.log(CHATPGP.recording.datas.mediaRecorder.state);
            console.log("recorder started");
            CHATPGP.recording.datas.duration =  Date.now();
            CHATPGP.recording.datas.handleDuration = setInterval(CHATPGP.recording.tick, 1000);
        },
        stop:function(){
            CHATPGP.recording.datas.mediaRecorder.stop();
            console.log(CHATPGP.recording.datas.mediaRecorder.state);
            console.log("recorder stopped");
            var cancelAnimationFrame = window.cancelAnimationFrame || window.mozCancelAnimationFrame;
            cancelAnimationFrame(CHATPGP.recording.datas.id_anim);
            clearInterval(CHATPGP.recording.datas.handleDuration);
        },
        onStop:function(e){
            const soundClips = document.querySelector('.recording_pannel .clip .audio');
            const audio = document.createElement('audio');
            audio.setAttribute('controls', '');
            soundClips.innerHTML = "";
            soundClips.appendChild(audio);
            audio.controls = true;
            CHATPGP.recording.datas.blob = new Blob(CHATPGP.recording.datas.chunks, { 'type' : 'audio/ogg; codecs=opus' });
            CHATPGP.recording.datas.chunks = [];
            const audioURL = window.URL.createObjectURL(CHATPGP.recording.datas.blob);
            audio.src = audioURL;
            console.log("recorder stopped "+audioURL);
            CHATPGP.recording.isRunning = false;
        },
        send:function(url){
            function uploadToPHPServer(blob) {
                var file = new File([blob], 'msr-' + (new Date).toISOString().replace(/:|\./g, '-') + '.webm', {
                    type: 'video/webm'
                });

                // create FormData
                var formData = new FormData();
                formData.append('video-filename', file.name);
                formData.append('video-blob', file);

                           
                makeXMLHttpRequest(url, formData, function(responseText) {
                    //var downloadURL = 'https://path-to-your-server/uploads/' + file.name;
                    console.log('File uploade response :'+ responseText);
                    if(responseText !== ""){
                        console.log('File uploaded :'+ file.name);
                        $(".recording_pannel .clip .audio audio").prop("src", responseText);
                        var msg = $(".recording_pannel .clip .audio").html();
                        if(msg === "") return;
                        CHATPGP.SendMessage(
                            $(".chat_id_room").val(), 
                            msg,
                            function(data){
                                //console.log(data);
                                CHATPGP.parseServerResponse(data);
                                setTimeout(function(){
                                    $(".chat_wrapper_content .scrolly").animate({ scrollTop: $(".chat_wrapper_content .scrolly")[0].scrollHeight + 500 }, 1000);
                                },300);
                            }
                        );
                    }else{
                        console.log('Error uploade :'+ file.name);
                    }
                });
            }
            function makeXMLHttpRequest(url, data, callback) {
                var request = new XMLHttpRequest();
                request.onreadystatechange = function() {
                    if (request.readyState === 4 && request.status === 200) {
                        callback(request.responseText);
                    }
                };
                request.open('POST', url);
                request.send(data);

            }
            if(CHATPGP.recording.datas.blob !== undefined) uploadToPHPServer(CHATPGP.recording.datas.blob);
        },
        tick:function(){
            function addZero(n) {
                return ('0000'+n).match(/\d{2}$/);
            }
            var total_secondes = (Date.now()-CHATPGP.recording.datas.duration) / 1000;  
            let nb_jours = Math.floor(total_secondes / (60 * 60 * 24));
            var nb_heures = Math.floor((total_secondes - (nb_jours * 60 * 60 * 24)) / (60 * 60));
            var nb_minutes = Math.floor((total_secondes - ((nb_jours * 60 * 60 * 24 + nb_heures * 60 * 60))) / 60);
            var nb_secondes = Math.floor(total_secondes - ((nb_jours * 60 * 60 * 24 + nb_heures * 60 * 60 + nb_minutes * 60)));
            var dom_duration = document.querySelector('.recording_pannel .audio_graph .duration');
            dom_duration.innerHTML = addZero(nb_minutes)+":"+addZero(nb_secondes);
        },
        visualize: function() {
            if(!CHATPGP.recording.datas.audioCtx) {
              CHATPGP.recording.datas.audioCtx = new AudioContext();
            }

            const source = CHATPGP.recording.datas.audioCtx.createMediaStreamSource(CHATPGP.recording.datas.stream);

            const analyser = CHATPGP.recording.datas.audioCtx.createAnalyser();
            analyser.fftSize = 2048;
            const bufferLength = analyser.frequencyBinCount;
            const dataArray = new Uint8Array(bufferLength);

            source.connect(analyser);

            //analyser.connect(audioCtx.destination);

            draw();

            function draw() {
                const WIDTH = CHATPGP.recording.datas.canvas.width;
                const HEIGHT = CHATPGP.recording.datas.canvas.height;
                var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
                                    window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
                CHATPGP.recording.datas.id_anim = requestAnimationFrame(draw);

                analyser.getByteTimeDomainData(dataArray);

                CHATPGP.recording.datas.canvasCtx.fillStyle = 'rgb(255, 255, 255)';
                CHATPGP.recording.datas.canvasCtx.fillRect(0, 0, WIDTH, HEIGHT);

                CHATPGP.recording.datas.canvasCtx.lineWidth = 2;
                CHATPGP.recording.datas.canvasCtx.strokeStyle = 'rgb(0, 0, 0)';

                CHATPGP.recording.datas.canvasCtx.beginPath();

                let sliceWidth = WIDTH * 1.0 / bufferLength;
                let x = 0;


                for(let i = 0; i < bufferLength; i++) {

                  let v = dataArray[i] / 128.0;
                  let y = v * HEIGHT/2;
                  

                  if(i === 0) {
                        CHATPGP.recording.datas.canvasCtx.moveTo(x, y);
                  } else {
                        CHATPGP.recording.datas.canvasCtx.lineTo(x, y);
                  }

                  x += sliceWidth;
                }

                CHATPGP.recording.datas.canvasCtx.lineTo(CHATPGP.recording.datas.canvas.width, CHATPGP.recording.datas.canvas.height/2);
                CHATPGP.recording.datas.canvasCtx.stroke();

            }
        }


    }

};



class TProcessio{
 
    constructor(){
        //this.utilisateurs = [];
    }

    process(from, to, action, varname, value){
        console.log('TProcessio.process()',from, to, action, varname, value);
        AJAX._timestamp = '';
        AJAX._type = "POST";
        // AJAX.OpenUrl_LongQuery: function(url, data, timeout, onread, onreaderror){
        AJAX.OpenUrl_LongQuery(
            pageAjax, 
            CHATPGP.setParamsServer(),
            ((4 * 60) + 45) * 1000, // Timeout request
            CHATPGP.onMessage,
            CHATPGP.onMessageError
        );
    }
}





$(document).ready(function(){
    CHATPGP.debug = true;
    CHATPGP.LoadTemplates();
    CHATPGP.Start();

    CHATPGP.socket = io("//:24443");
    var p = new TProcessio();

    CHATPGP.socket.on('emitto', (from, to, action, varname, value) => {
        p.process(from, to, action, varname, value);
    });

    CHATPGP.socket.on('emitall', (from, to, action, varname, value) => {
        p.process(from, to, action, varname, value);
    });

    CHATPGP.socket.on('connect', function() {
        console.log('Connected to server!');
    });

    CHATPGP.socket.emit('join', "chatting");
    console.log("join chatting");
    //CHATPGP.socket.emit('emitall', "", "", "set", "clienttype", "Gemini");


    $(window).bind('online', function(e){
        CHATPGP.Start();
    });

    $(window).bind('offline', function(e){
        //CHATPGP.Stop();
        alert("You are offline");
    });

    $(window).bind('beforeunload', function(e){
        CHATPGP.Disconnect();
    });

    $(".btnConnect").click(function(){
        CHATPGP.Start();
    });

    $(".btnDisconnect").click(function(){
        CHATPGP.Stop();
        CHATPGP.Disconnect();
    });

    $(".btnConnect").click(function(){
        CHATPGP.Start();
    });

    $(".btnGoLoginForm").click(function(){
        $(".chatpage").hide(0, function(){
            $(".chat_login").show(0);
        });
    });

    $(".btnGoLoginVipForm").click(function(){
        $(".chatpage").hide(0, function(){
            $(".chat_login_vip").show(0);
        });
    });

    $(".btnConnect").click(function(){
        $(".chatpage").hide(0, function(){
            $(".chat_content,.chat_message").show(0);
        });
    });

    $(".btnSaveConfig").click(function(){
        $(".chatpage").hide(0, function(){
            $(".chat_content,.chat_message").show(0);
        });
    });

    $(".btnHome").click(function(){
        $(".chatpage").hide(0, function(){
            $(".chat_content,.chat_message").show(0);
        });
    });

    $(".client_message, .chat_message .client_message_html").keypress(function(e) {
        if(e.which === 13 && e.shiftKey === false) {
            $(".btnsendMessage").click();
            return false;
        }
    });

    /*
    $(".btnsendMessage").click(function(){
        console.log(".btnsendMessage");
        var msg = $(".client_message").val();
        $(".client_message").val("");

        if(msg.trim().toString().length === 0){
            return;
        }
        //CHATPGP._lastMessage = msg;
        CHATPGP.SendMessage(
            $(".chat_id_room").val(), 
            msg,
            function(data){
                //console.log(data);
                CHATPGP.parseServerResponse(data);

            }
        );
    });
    */
    $(".btnsendMessage").click(function(){
        console.log(".btnsendMessage ");
        var msg = $(".chat_message .client_message_html").html();
        $(".chat_message .client_message_html").html("");
        console.log(".btnsendMessage "+msg);
        /*
        if(msg === CHATPGP._lastMessage){
            return;
        }
         */
        if(msg.trim().toString().length === 0){
            return;
        }
        //CHATPGP._lastMessage = msg;
        CHATPGP.SendMessage(
            $(".chat_id_room").val(), 
            msg,
            function(data){
                CHATPGP.socket.emit('emitall', "", "", "set", "clienttype", "Gemini");
                //console.log(data);
                CHATPGP.parseServerResponse(data);
                setTimeout(function(){
                    $(".chat_wrapper_content .scrolly").animate({ scrollTop: $(".chat_wrapper_content .scrolly")[0].scrollHeight + 500 }, 1000);
                },300);
                
            }
        );
    });

    $(".chat_wrapper_users").on('click', '.btnInvitePrivateMessage', function(e) {
        var base = $(this).parent().parent().parent().parent();
        var invite_id_user = $("input.id_user", base).val();
        CHATPGP.invitePrivateMessage(invite_id_user, function(data){

        });
    }); 

    $(".chat_wrapper_users").on('click', '.btnBannUser', function(e) {
        var base = $(this).parent().parent().parent().parent();
        var bann_id_user = $("input.id_user", base).val();
        CHATPGP.bannUser(bann_id_user, function(data){

        });
    }); 

    $(window).resize(function(){
        CHATPGP.resize();
    });
    CHATPGP.resize();
    
    /*
    $(".chat_message .client_message_html").keypress(function(){
        console.log(window.getSelection().getRangeAt(0));
    });
    
    $(".chat_message .client_message_html").click(function(){
        console.log(window.getSelection().getRangeAt(0));
    });
    */
   
    $(".chatPGP .smileys span").click(function(){
        //console.log(".chatPGP .smileys span CLICK "+$(this).html());
        //CHATPGP.insertHTMLEditable(".chat_message .client_message_html", $("img",this).attr("src"));
        CHATPGP.insertHTMLEditable(".chat_message .client_message_html", $(this).html());
    });
    
    $(".btnToggleUsers").click(function(){
        console.log("btnToggleUsers");
        if($(this).is(":visible")){
           $(".chatPGP .chat_wrapper_content").css("padding-right","175px"); 
           //$(".chatPGP .chat_wrapper_content").animate({ "paddingright": "175px"}, 1000);
        }
        $(".chatPGP .chat_wrapper_users").toggle(function(){
            if($(this).is(":visible")){
                $(".btnToggleUsers").val(">");
            }else{
                $(".btnToggleUsers").val("<");
                $(".chatPGP .chat_wrapper_content").css("padding-right","0");
            }
        });
    });

    
    $(".btnToggleTheme").click(function(){
        console.log("click");
        if($("body").hasClass("black")){ 
            $("body").removeClass("black"); 
        }else{
            $("body").addClass("black");
        }
    });

    $(".btnRecord").click(function(){
        // Pas de plugin pour capturer l'audio (mauvais navigateur utilisé)
        if(CHATPGP.recording.init() === false){
            // TODO afficher l'aide
            return;
        }
        $(".smileys, .chat_message").fadeOut("normal", function(){
            $(".recording_pannel .audio_graph .duration").text("00:00");
            $(".recording_pannel .audio_graph").fadeIn("normal");
            

        });
    });
    $(".btnCancelvisualizer").click(function(){
        $(".recording_pannel .audio_graph").fadeOut("normal", function(){
            $(".smileys, .chat_message").fadeIn("normal");
            CHATPGP.resize();
        });
    });
   
    $(".btnStartRecording").click(function(){
        if(CHATPGP.recording.isRunning){
            CHATPGP.recording.stop();
            $(this).text("Démarrer");
            $(".recording_pannel .audio_graph").fadeOut("normal", function(){
                $(".recording_pannel .clip").fadeIn("normal");
            });
        }else{
            CHATPGP.recording.start();
            $(this).text("Arrêter");
        }
    });
    
    $(".btnCancelclip").click(function(){
        $(".recording_pannel .clip").fadeOut("normal", function(){
            $(".recording_pannel .audio_graph .duration").text("00:00");
            $(".recording_pannel .audio_graph").fadeIn("normal");
        });
    });

    $(".btnSendVocalMessage").click(function(){
        CHATPGP.recording.send("ajax.php?action=uploadAudio&usersession="+CHATPGP._user_session);
        $(".recording_pannel .clip").fadeOut("normal", function(){
            $(".btnCancelvisualizer").click();
        });
    });


    
});