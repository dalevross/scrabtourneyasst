var ttimer = null;

//iframe canvas variable
var iframeRef = document.getElementById("iframe_canvas"); 

function loadjQueryui() {

	if (typeof jQuery.ui == 'undefined') {

		loadR3Script(
				"https://scrabtourneyasst.herokuapp.com/scrabtourneyasst/js/jquery-ui-1.8.20.custom.min.js",
				function() {
					if(getTilesLeft())
					{
						ttimer = setInterval("getTilesLeft()",60000);
					}
				});
	} else {
		if(getTilesLeft())
		{
			ttimer = setInterval("getTilesLeft()",60000);
		}
		
	}
}

function getTilesLeft() {

	var html = '';
	var applink = $(location).attr('href');
	if (applink === "") {
		stopTimer();
		alert('Invalid link in address bar!');		
		return false;
	}
	var game = applink.match(/(lexulous|wordscraper)/g);
	if (game === null) {
		stopTimer();
		alert('Invalid link in address bar!');		
		return false;
	}
	var gid = /gid=(\d+)/g.exec(applink);
	if (gid === null) {
		stopTimer();
		alert('Invalid game link!');		
		return false;
	}
	var pid = /pid=(\d)/g.exec(applink);
	var password = /password=(\w+)/g.exec(applink);
	if ((pid === null) || (password === null)) {
		params = {
			gid : gid[1],
			game : game[0],
			version : 2
		};
	} else {
		params = {
			gid : gid[1],
			game : game[0],
			pid : pid[1],
			password : password[1],
			version : 2
		};
	}
	document.body.style.cursor = 'wait';
	var $gis = $('div#pagelet_canvas_content');
	
	//var popposition = {top:424,left:$gis.offset().left + $gis.outerWidth() - 34};
	var popposition = $gis.offset().left + $gis.outerWidth() - 34 + ' ' + 424;
	var $dialog;
	if (!$('#lexwstracker').is(':data(dialog)')) {
		$dialog = $('<div id="lexwstracker" ></div>').html('').dialog({autoOpen : false,
					title : 'Tiles Remaining',
					width : 300,
//					create: function(event){										
//						$(event.target).parent().css('position', 'fixed');
//				    },									
					close: function(event, ui){										
				        stopTimer();
				    },					
				    position: ['right','top']
				});
	} else {
		$dialog = $('#lexwstracker');
	}
	var loadinghtml = '<div><span>Loading...</span><br/><img src="https://scrabtourneyasst.herokuapp.com/scrabtourneyasst/trackerloading.gif" /></div>';
	if ($dialog.dialog('isOpen')) {
		$dialog.html(loadinghtml);
	} else {
		$dialog.html(loadinghtml).dialog('open');
	}
	$.ajax({url : 'https://scrabtourneyasst.herokuapp.com/scrabtourneyasst/scrabtiletracker.php?callback=?',
				context : this,
				data : (params),
				dataType : "jsonp",
				success : function(data) {					
					var d = new Date();
					var suffix = '<br/><span> Retrieved at ' + d.toLocaleString() + '</span>';
					html = data['html'] + suffix;
					var background = 'https://scrabtourneyasst.herokuapp.com/scrabtourneyasst/' + game + 'board-smt.png';
					//$dialog.html(html).css('background-image','url('+ background + ')' ).effect('pulsate',{},500,releaseFocus);					
					$dialog.html(html).effect('pulsate',{},500,releaseFocus);
					document.body.style.cursor = 'default';
					
				},
				failure : function(jqXHR, textStatus, errorThrown) {
					alert(textStatus);
					document.body.style.cursor = 'default';
					stopTimer();
					$(iframeRef).focus();
					return false;
				}
			});
	
	
	document.body.style.cursor = 'default';	
	return true;
}

function loadR3Script(url, callback) {
	var script = document.createElement("script");
	script.type = "text/javascript";
	if (script.readyState) {
		script.onreadystatechange = function() {
			if (script.readyState == "loaded" || script.readyState == "complete") {
				script.onreadystatechange = null;
				callback();
			}
		};
	} else {
		script.onload = function() {
			callback();
		};
	}
	script.src = url;
	document.getElementsByTagName("head")[0].appendChild(script);
}

function runTileTracker()
{
	stopTimer();
	if (typeof jQuery == 'undefined') {
		loadR3Script("https://scrabtourneyasst.herokuapp.com/scrabtourneyasst/js/jquery-1.7.min.js",
				function() {
	
					$('head').append('<link rel="stylesheet" href="https://scrabtourneyasst.herokuapp.com/scrabtourneyasst/css/smoothness/jquery-ui-1.8.20.custom.css" type="text/css" />');
					loadjQueryui();
				});
	} else {
		loadjQueryui();
	}
}

function stopTimer()
{
	if(ttimer){
	       window.clearInterval(ttimer);
	       ttimer = null;
	}

}

function releaseFocus() {
	setTimeout(function() {
		$(iframeRef).focus();
	}, 1000);
}