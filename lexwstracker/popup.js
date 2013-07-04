

var trackingGenerator = {
	  getTilesLeft: function(applink) {

		var html = '';
		
		var $dialog;
				
		$dialog = $('#lexwstracker');
		
		var dist = {"A":9,"B":2,"C":2,"D":4,"E":12,"F":2,"G":3,"H":2,"I":9,"J":1,"K":1,"L":4,"M":2,"N":6,"O":8,"P":2,"Q":1,"R":6,"S":4,"T":6,"U":4,"V":2,"W":2,"X":1,"Y":2,"Z":1,"blank":2};
		var left = {};
		//var applink =  chrome.extension.getBackgroundPage().currentUrl;
		var game = applink.match(/(lexulous|wordscraper|ea_scrabble_closed|livescrabble)/g);

		if (game === null) {

			$dialog.html('Invalid link in address bar!');		

			return false;

		}
		var loadinghtml = '<div><span>Loading...</span><br/><img src="https://scrabtourneyasst.herokuapp.com/scrabtourneyasst/trackerloading.gif" /></div>';
		
		if((game[0]=="ea_scrabble_closed")||(game[0]=="livescrabble"))
		{
			$dialog.html(loadinghtml);
			 chrome.tabs.query({'active': true}, function (tabs) {
				 chrome.tabs.sendMessage(tabs[0].id, {command: "sendresults"}, function(response) {
				     var used = response.data;
				     var tilecount = 0;
				     for(var letter in used){
				    	    if((dist[letter]-used[letter])>0)
				    	    {
				    	    	left[letter] = dist[letter]-used[letter];
				    	    	tilecount+=dist[letter]-used[letter];
				    	    }
				    	}
				     var color = (game==="lexulous")?"#2BB0E8":"red";
				     var index = 0;
				     var html = '';
				     var inbag = (tilecount>7)?tilecount-7:0;
				     html = html + '<span style="font-weight:bold;">Tile Count: ' + tilecount + '</span><span> ('+ inbag + ' in bag)</span><br/>';
						for (var letter in left) {
							if((index % 8)===0)
							{
								html = html + '<div style="float:left;padding:10px">';
							}
							html = html + '<span style="color:' + color + '" title="Total: ' + dist[letter] +  '">' + letter + ' - ' + left[letter] + '</span><br/>';
							index++;
							if(((index)%8===0 && index!==0)|| index===Object.keys(left).length )
							{
								html = html + '</div>';
							}
						}
						
					//}
					
					//$html = $html . 'The tile tracker is currently down for maintenance. <br/>';
					
					

					html = html + '<div style="clear:both"/>';

					//html = html + '<span id="trackerstat">' . $status . '</span><br/><br/>';


					html = html + '<span>Brought to you by<br/><a href="http://www.facebook.com/lexandws?ref=ts" target="_blank" ><span style="text-decoration:underline;color:blue;">Lexulous/Wordscraper Tournaments</span></a></span>';
					
					html = html + '<br/><br/><span>Contact <a href="http://www.facebook.com/dvross" target="_blank" ><span style="text-decoration:underline;color:blue;">Dale V. Ross</span></a> for support or suggestions</span>';
					var d = new Date();

					var suffix = '<br/><span> Retrieved at ' + d.toLocaleString() + '</span>';

					html = html + suffix;
					
					 $dialog.html(html);
					  });
				});
		
		}
		else
		{
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
	
	
	
			
	
	
			$dialog.html(loadinghtml);
	
	
	
			$.ajax({url : 'https://scrabtourneyasst.herokuapp.com/scrabtourneyasst/scrabtiletracker.php?callback=?',
	
						context : this,
	
						data : (params),
	
						dataType : "jsonp",
	
						success : function(data) {					
	
							var d = new Date();
	
							var suffix = '<br/><span> Retrieved at ' + d.toLocaleString() + '</span>';
	
							html = data['html'] + suffix;
	
							$dialog.html(html);
	
						},
	
						failure : function(jqXHR, textStatus, errorThrown) {
	
							$dialog.html(textStatus);
	
							document.body.style.cursor = 'default';							
							
							return false;
	
						}
	
					});
		}
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
