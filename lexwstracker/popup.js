

var trackingGenerator = {
	  getTilesLeft: function(applink) {

		var html = '';
		
		var $dialog;
				
		$dialog = $('#lexwstracker');
		
		

		//var applink =  chrome.extension.getBackgroundPage().currentUrl;
		var game = applink.match(/(lexulous|wordscraper)/g);

		if (game === null) {

			$dialog.html('Invalid link in address bar!');		

			return false;

		}

		var gid = /gid=(\d+)/g.exec(applink);

		if (gid === null) {

			$dialog.html('Invalid game link!');		

			return false;

		}

		var pid = /pid=(\d)/g.exec(applink);
		var password = /password=(\w+)/g.exec(applink);

		if ((pid === null) || (password === null)) {

			params = {gid : gid[1],game : game[0],version : 2};

		} else {

			params = {gid : gid[1],game : game[0],pid : pid[1],password : password[1],version : 2};

		}



		var loadinghtml = '<div><span>Loading...</span><br/><img src="https://scrabtourneyasst.herokuapp.com/scrabtourneyasst/trackerloading.gif" /></div>';



		$dialog.html(loadinghtml);



		$.ajax({url : 'https://scrabtourneyasst.herokuapp.com/scrabtourneyasst/scrabtiletracker.php?callback=?',

					context : this,

					data : (params),

					dataType : "jsonp",

					success : function(data) {					

						var d = new Date();

						var suffix = '<br/><span> Retrieved at ' + d.toLocaleString() + '</span>';

						html = data['html'] + suffix;

						$dialog.html(html).effect('pulsate',{},500,releaseFocus);

					},

					failure : function(jqXHR, textStatus, errorThrown) {

						$dialog.html(textStatus);

						document.body.style.cursor = 'default';
						
						$(iframeRef).focus();

						return false;

					}

				});
		
		return true;

	}
};

// Run our kitten generation script as soon as the document's DOM is ready.
document.addEventListener('DOMContentLoaded', function () {
  chrome.tabs.query({'active': true}, function (tabs) {
			var applink = tabs[0].url;
			trackingGenerator.getTilesLeft(applink);
		});
		
		
});
