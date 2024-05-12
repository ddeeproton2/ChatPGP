 
	/* global AJAX */

		
		var AJAX = {
			_timerTimeoutHandle: undefined, 
			_isAborting: undefined, 
			_xhr: undefined, 
			_timestamp: undefined, 
			_url: undefined, 
			_data: undefined, 
			_timeout: undefined, 
			_onread: undefined, 
			_onreaderror: undefined, 
			_type: "POST",
			_isConnected: false,
			OpenUrl_ShortQuery: function(url, data, ondone){
				console.log(url);
				console.log(data);
				console.log(ondone);
				AJAX.events.debug("ShortQuery");
				AJAX.events.warn(data);
				//AJAX.events.warn(data.params);
                                
                $.ajax({
					type: 'POST',
					url: url,
					data: data,
					async: true,
					cache: false,
					beforeSend: function (request) {
						request.setRequestHeader("Authorization", "Negotiate");
					},
					success: function(res){
                                            try{
                                                    var r = JSON.parse(res);
                                                    AJAX.events.info(r);
                                                    ondone(r);
                                            }catch(e){
                                                    AJAX.events.error([
                                                            url,
                                                            e.message
                                                    ]);
                                                    AJAX.events.error(res);
                                            }
					},
					error: function(data){
                                            //alert("Network error");
					}
				});
                                /*
				$.get(url, data)
				.done(function(res){
					try{
						var r = JSON.parse(res);
						AJAX.events.info(r);
						ondone(r);
					}catch(e){
						AJAX.events.error([
							url,
							e.message
						]);
						AJAX.events.error(res);
					}
				})
				.fail(function(){
					//alert("Network error");
				})
				.always(function(){
					//alert( "finished" );
				});
                             
                                 */
			},
			OpenUrl_LongQuery: function(url, data, timeout, onread, onreaderror){
				if(AJAX._isConnected === true){
					setTimeout(function(){
						AJAX.OpenUrl_LongQuery(url, data, timeout, onread, onreaderror); 
					},100);
					return;
				}
				AJAX._isConnected = true;
				AJAX._url = url;
				AJAX._data = data;
				AJAX._timeout = timeout;
				AJAX._onread = onread;
				AJAX._onreaderror = onreaderror;
				if(navigator.onLine == false){
					AJAX.events.debug('navigator offline');
					//return;
				}
				if(timeout > 0){
					AJAX._timerTimeoutHandle = setTimeout(function(){
						AJAX.Abort();
						AJAX.events.debug('timeout');
						AJAX.server_reconnect();
					}, timeout);
				}
				//data._isAborting = false;
				data.timestamp = AJAX._timestamp;
				AJAX.events.debug("LongQuery");
				AJAX.events.warn(data);
				//AJAX.events.warn(data.params);
				AJAX._xhr = $.ajax({
					type: AJAX._type,
					url: url,
					data: data,
					async: true,
					cache: false,
					beforeSend: function (request) {
						request.setRequestHeader("Authorization", "Negotiate");
					},
					success: function(res){
						AJAX.ClearTimer();
						var isError = false;
						try{
							var r = JSON.parse(res);
							if(r != undefined && r.timestamp != undefined){
								AJAX._timestamp = r.timestamp;
								AJAX.events.info(r);
								onread(r);
							}else{
								onreaderror(res);
							}
						}catch(e){
							AJAX.events.error([
								url,
								e.message
							]);
							AJAX.events.error(res);
							isError = true;
							//debugger;
						}
						if(isError){
							AJAX.events.debug("Reconnexion in 10 sec...");
							setTimeout(AJAX.server_reconnect, 10000)
						}else{
							//AJAX.server_reconnect();
						}
                        AJAX._isConnected = false;
					},
					error: function(data){
						if(AJAX._isAborting === true) {
							AJAX.events.debug('clean close listening');
						}else{ 
							AJAX.events.debug("Connexion lost");
							AJAX.events.debug("Reconnexion in 5 sec...");
							setTimeout(AJAX.Reload, 5000);
						}
                        AJAX._isConnected = false;
					}
				});
			},
			server_reconnect: function(){
				//CHATPGP.Start;
				AJAX.OpenUrl_LongQuery(AJAX._url, AJAX._data, AJAX._timeout, AJAX._onread, AJAX._onreaderror);
			},
			Reload: function(){
				setTimeout(AJAX.server_reconnect, 1000);
			},
			ClearTimer: function(){
				if(AJAX._timerTimeoutHandle != 0) 
					clearTimeout(AJAX._timerTimeoutHandle);
			},
			Abort: function(){
				AJAX.events.debug("AjaxAbort");
				if(AJAX._xhr === undefined) {
					return;
				}
				AJAX._isAborting = true;
				AJAX._xhr.abort();
			},
			events: {
				debug: function(data){
					console.debug(data);
					AJAX.events.html(data);
				},
				warn: function(data){
					console.warn(data);
					AJAX.events.html(data);
				},
				info: function(data){
					console.info(data);
					AJAX.events.html(data);
				},
				error: function(data){
					console.error(data);
					AJAX.events.html(data);
				},
				html: function(data){
					if(!CHATPGP.debug) return;
					try{
						data = JSON.stringify(data, null, 4)
					}catch(e){
					}
                    if($(".debug pre").length == 0) return;
					$(".debug pre").append("<p>"+data+"</p>").scrollTop($(".debug pre")[0].scrollHeight);
				}
			}
			
		}