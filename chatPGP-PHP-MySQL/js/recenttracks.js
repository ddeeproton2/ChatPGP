void 0===window.centovacast&&(window.centovacast={});void 0===window.centovacast.options&&(window.centovacast.options={});
void 0===window.centovacast.loader&&(window.centovacast.loader={attempts:0,external_jquery:!1,loaded:!1,ready:!1,widget_definitions:{},url:"",load_script:function(b){var a=document.createElement("script");void 0!==a&&(a.setAttribute("type","text/javascript"),a.setAttribute("src",b),void 0!==a&&document.getElementsByTagName("head")[0].appendChild(a))},load_widget:function(b){b=this.widget_definitions[b];null===b.ref&&(b.ref=b.define(jQuery))},jq_loaded:function(){this.external_jquery||jQuery.noConflict();
jQuery.getJSONP=this.jq_get_jsonp;for(var b in this.widget_definitions)"string"===typeof b&&this.load_widget(b);this.loaded=!0;var a=this;jQuery(document).ready(function(){a.ready=!0;for(var b in a.widget_definitions)"function"===typeof a.widget_definitions[b].init&&a.widget_definitions[b].init(jQuery)})},check:function(){if("undefined"===typeof jQuery){var b=this;setTimeout(function(){b.check()},100);this.attempts++}else this.jq_loaded()},process_widget_element:function(b,a,c,d){b=jQuery(b);var e=
!1,f={},h,g,k;for(k in a)a.hasOwnProperty(k)&&(h=a[k],g=b.data(h),"undefined"!==typeof g?(f[h]=g,e=!0):f[h]="");g=b.prop("id");if(e)f.type=b.data("type");else{if("string"!==typeof g||g.substr(0,c.length+1)!==c+"_")return null;f.fromid=!0;f.originalid=g;c=g.substr(c.length+1);if(d){d=/^([a-z0-9]+)_/;e=d.exec(c);if(!e)return null;f.type=e[1];c=c.replace(d,"")}var l=null;for(k in a)a.hasOwnProperty(k)&&(h=a[k],null===l&&(l=h),d=new RegExp("_"+k+"-([^_]+)"),e=d.exec(c))&&(f[h]=e[1],c=c.replace(d,""));
f[l]=c;"string"===typeof f.mount&&(f.mount=f.mount.replace(/-/,"/"))}f.id=g;f.$el=b;return f},process_widget_elements:function(b,a,c,d){var e={},f=this;b.each(function(){var b=f.process_widget_element(this,a,c,d),g=""+b.username+b.mount;e[g]||(e[g]=jQuery.extend({},b),d&&(e[g].type=void 0),e[g].hastype=d,e[g].$el=d?{}:null);d?e[g].$el[b.type]=e[g].$el[b.type]?e[g].$el[b.type].add(b.$el[0]):b.$el:e[g].$el=e[g].$el?e[g].$el.add(b.$el[0]):b.$el});return{widget_data:e,get:function(a){return this.widget_data[a]?
this.widget_data[a]:void 0},get_property:function(a,b){return this.widget_data[a]&&this.widget_data[a][b]?this.widget_data[a][b]:void 0},get_element:function(a,b){return this.widget_data[a]?this.widget_data[a].hastype?this.widget_data[a].$el[b]?this.widget_data[a].$el[b]:jQuery():this.widget_data[a].$el?this.widget_data[a].$el:jQuery():void 0},set_element:function(a,b,c){this.widget_data[a].hastype?b&&b.length&&(this.widget_data[a].$el[b]=c):this.widget_data[a].$el=c},set_property:function(a,b,c){if(!this.widget_data[a])return!1;
this.widget_data[a][b]=c;return!0},each:function(a){for(var b in this.widget_data)"string"===typeof b&&a(b,this.widget_data[b])},each_element:function(a,b){if(this.widget_data[a].hastype)for(var c in this.widget_data[a].$el)"string"!==typeof c&&void 0!==c||b(this.widget_data[a].$el[c],c);else b(this.widget_data[a].$el)}}},init:function(){var b=document.getElementsByTagName("script"),b=b[b.length-1],b=void 0!==b.getAttribute.length?b.getAttribute("src"):b.getAttribute("src",2);b.match(/^https?:\/\//i)||
(b=window.location.href);this.url=b.replace(/(\.(?:[a-z]{2,}|[0-9]+)(\:[0-9]+)?\/).*$/i,"$1");(this.external_jquery="undefined"!==typeof jQuery)||this.load_script(this.url+"system/jquery.min.js");this.check()},add:function(b,a,c){this.widget_definitions[b]||(this.widget_definitions[b]={define:c,init:a,ref:null});this.loaded&&this.load_widget(b);this.ready&&a(jQuery)},jq_get_jsonp:function(b,a,c){return jQuery.ajax({type:"GET",url:b,data:a,success:c,dataType:"jsonp"})}},window.centovacast.loader.init());
window.centovacast.loader.add("recenttracks",function(b){b.extend(window.centovacast.recenttracks.settings,window.centovacast.options.recenttracks);window.centovacast.recenttracks.run()},function(b){window.centovacast.options.recenttracks=b.extend({},window.centovacast.options.recenttracks,window.centovacast.recenttracks?window.centovacast.recenttracks.config:null);b("<link/>",{rel:"stylesheet",type:"text/css",href:"https://adeweb.space:2199/"+"theme/widget_recenttracks.css"}).appendTo(b("body"));
return window.centovacast.recenttracks={pollcount:0,settings:{poll_limit:60,poll_frequency:6E4,track_limit:0,show_covers:1,scale_covers:1,buy_target:"_blank"},widgets:{},element_class:".cc_recenttracks_list",show_loading:function(a){a=this.widgets.get_element(a);var c=b("<div/>",{"class":"cc_recenttracks_throbber cctrack",css:{textAlign:"center",display:"none"},html:'<img src="'+"https://adeweb.space:2199/"+'system/images/ajax-loading.gif" align="absmiddle" />'});c.appendTo(a);c.fadeIn("fast")},
clear_loading:function(a){this.widgets.get_element(a).find(".cc_recenttracks_throbber").remove()},demote_first:function(a){a=this.widgets.get_element(a);a=b(".cctrack:first",a);a.find(".cctitle").removeClass("ccnowplaying");a=a.find(".cctime");a.removeClass("ccnowplaying");a.html(a.data("timestr"))},create_new:function(a,c,d){var e=b("<div/>");e.addClass("cctrack");var f=b("<div/>");f.addClass("ccdetails");if(window.centovacast.recenttracks.settings.show_covers){var h=b("<div/>");h.addClass("cccover");
var g=b("<img/>",{src:a.image});d.w&&g.attr("width",d.w);d.h&&g.attr("height",d.h);g.appendTo(h);h.appendTo(e);f.addClass("ccdetails_withcover")}d=b("<div/>",{html:a.title});d.addClass("cctitle");c&&d.addClass("ccnowplaying");d.appendTo(f);d=b("<div/>",{html:a.artist});d.addClass("ccartist");d.appendTo(f);a.album&&(d=b("<div/>",{html:a.album}),d.addClass("ccalbum"),d.appendTo(f));a.url&&b("<div/>",{html:"<a class='ccbuy' "+(""!==window.centovacast.recenttracks.settings.buy_target?'target="'+window.centovacast.recenttracks.settings.buy_target+
'" ':"")+"href='"+a.url+"'>"+window.lang.buyalbum+"</a>"}).appendTo(f);d=!1;c&&window.centovacast.streaminfo&&window.centovacast.streaminfo.state&&(d=window.centovacast.streaminfo.state.serverstate&&window.centovacast.streaminfo.state.sourceconn);d=b("<div/>",{html:d?window.lang.nowplaying:a.localtime});d.addClass("cctime");c&&(d.data("timestr",a.localtime),d.addClass("ccnowplaying"));d.appendTo(e);f.appendTo(e);window.centovacast.recenttracks.settings.show_covers&&b("<div/>",{css:{clear:"both",fontSize:"0px",
height:"0"}}).appendTo(e);return e},slide_to_next:function(a,b){a.slideDown("slow",function(){window.centovacast.recenttracks.next(b)})},animate_next:function(a){var b=this.widgets.get(a),d=b.pending_tracks.pop();if(d){d.time>b.since&&this.widgets.set_property(a,"since",d.time);var e=this.widgets.get_element(a);this.demote_first(a);b=this.create_new(d,!0,b.options);b.hide();b.prependTo(e);this.slide_to_next(b,a)}},animate_element_next:function(){var a=b(this),c=a.data("ccwidgetid");"string"===typeof c&&
(a.remove(),window.centovacast.recenttracks.animate_next(c))},next:function(a){var c=this.widgets.get(a);if(c.pending_tracks.length){var d=this.widgets.get_element(a);b(".cctrack",d).length>=c.tracklimit?b(".cctrack:last",d).data("ccwidgetid",a).fadeOut("slow",this.animate_element_next):this.animate_next(a)}},handle_json:function(a,c){c||(c=a.rid);var d=this.widgets.get(c);if(d){var e=this.widgets.get_element(c);if("error"===a.type||a.data&&a.data.length&&1===a.data.length)d=a?a.error?a.error:a.data[0]:
"No JSON object",e.empty(),d=b("<div />",{html:'<span title="'+d+'">Unavailable</span>'}),d.addClass("cctrack"),d.appendTo(e),this.clear_loading(c);else{var f=a.data[0],h=a.data[1],g=a.data[2],k=this.settings.track_limit?this.settings.track_limit:a.data[3],l=a.data[4];h&&e.html("");if(h){window.lang||(window.lang={});window.lang.buyalbum=g[0];window.lang.nowplaying=g[1];window.lang.notracks=g[2];this.widgets.set_property(c,"tracklimit",k);this.widgets.set_property(c,"options",l);e.hide();for(h=0;h<
f.length;h++)e.append(this.create_new(f[h],0===h,l)),f[h].time>d.since&&this.widgets.set_property(c,"since",f[h].time);f.length||(d=b("<div/>"),d.addClass("cctrack"),d.append(window.lang.notracks).appendTo(e));e.fadeIn("def")}else this.widgets[c].set_property("pending_tracks",f),this.next(c)}}},poll:function(a){var c=this.widgets.get(a);this.show_loading(a);var d="https://adeweb.space:2199/"+"external/rpc.php",c={m:"recenttracks.get",username:c.username,charset:c.charset,
mountpoint:c.mount,rid:a};this.settings.track_limit&&(c.limit=this.settings.track_limit);var e=this;b.getJSONP(d,c,function(b){b&&e.handle_json(b,a)})},poll_all:function(){var a=this;this.widgets.each(function(b){a.poll(b)});(0===this.settings.poll_limit||this.pollcount++<this.settings.poll_limit)&&setTimeout(function(){a.poll_all()},this.settings.poll_frequency)},run:function(){this.widgets=window.centovacast.loader.process_widget_elements(b(this.element_class),{username:"username",cs:"charset",
mp:"mount"},"cc_recenttracks",!1);this.poll_all()}}});