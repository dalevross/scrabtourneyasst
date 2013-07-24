if(/http(s)?:\/\/scrabblefb-live2\.sn\.eamobile\.com\/live\/http(s)?\//.test(window.location.href)){

	function updateCounts(){		
		myTurns=$("div#myTurnGamesList > div.match").size();
		$("div#myTurnGames").find("div").eq(0).find("span").eq(0).text('My Turn ('+myTurns+')');
		theirTurns=$("div#theirTurnGamesList > div.match").size();
		$("div#theirTurnGames").find("div").eq(0).find("span").eq(0).text('Their Turn ('+theirTurns+')');			
	}

	$(document).ready(function () {
		
		var iconURL = chrome.extension.getURL("notes-owl.png"); 
		$('div#headerButtonsContainerMiddle').append('<div style="color:darkgreen;position:relative;font-weight:bold;font-size:16px;top:3px;left:220px"><img src="'+iconURL+'"><div style="position:relative;top:-42px;left:60px">YOU HAVE NOTES</div></div>');

		var myTurns=0;
		if($("div#myTurnGamesList")) {
			myTurns=$("div#myTurnGamesList > div.match").size();
			$("div#myTurnGames").find("div").eq(0).find("span").eq(0).text('My Turn ('+myTurns+')');
		}
		var theirTurns=0;
		if($("div#theirTurnGamesList")) {
			theirTurns=$("div#theirTurnGamesList > div.match").size();
			$("div#theirTurnGames").find("div").eq(0).find("span").eq(0).text('Their Turn ('+theirTurns+')');
		}

		$("div#myTurnGamesList").on('DOMNodeInserted DOMNodeRemoved DOMSubtreeModified', function(event) {
			myTurns=$("div#myTurnGamesList > div.match").size();
			$("div#myTurnGames").find("div").eq(0).find("span").eq(0).text('My Turn ('+myTurns+')');

		});

		$("div#theirTurnGamesList").on('DOMNodeInserted DOMNodeRemoved DOMSubtreeModified', function(event) {
			theirTurns=$("div#theirTurnGamesList > div.match").size();
			$("div#theirTurnGames").find("div").eq(0).find("span").eq(0).text('Their Turn ('+theirTurns+')');
		});



		$("p#lookupResult").on('DOMNodeInserted', function(event) {
			if($("p#lookupResult span").attr("class")=="typo_lightRed")
			{
				return;
			}
			var $input = $("input#lookupTxt").eq(0);

			var longdict = $.trim($("span#wordsBtnImage").attr('title'));
			var shortdict = longdict.match(/(Collins|TWL|OSPD4|German|Spanish|Italian|French|Portuguese)/g);

			var langs={
					"Collins":"en",
					"TWL" :"en",
					"OSPD4" : "en",
					"French": "fr",
					"Italian":"it",
					"German": "de",
					"Portuguese":"pt",
					"Spanish": "es"
			};
			var lang=langs[shortdict];

			var validWord="";
			if($("p#lookupResult span").attr("class")=="typo_green") {
				validWord = $.trim($input.val()).toUpperCase();

				if(validWord == "") {
					$("p#lookupResult").html('');
				}
				else if($("p#lookupResult img#owl").length === 0) {
					iconURL =  chrome.extension.getURL("owl-lookup.png");
					$("p#lookupResult").find("span").html('<A href="http://google.com/search?hl='+lang+'&q=define:'+validWord+'" target="define" class="typo_green" style="text-decoration:underline"><img src="'+iconURL+'" align="left">'+validWord+'</a>');
				}

			}

		});
		
		$("input#lookupTxt").keyup( function(e) {
			if(e.keyCode == 13) {
				//Update counts that might have vanished due to refresh
				updateCounts();
				
				var $input = $(this);
				if($.trim($input.val())==="")
				{
					$("p#lookupResult").html('');					
				}				
			}
		});
	});

	chrome.runtime.onMessage.addListener(
			function(request, sender, sendResponse) {

				if (request.command == "sendresults")
				{

					//Update counts that might have vanished due to refresh
					updateCounts();

					if($("p.gameCardName").eq(0).text()=="You ") {
						var playerScore = $("p.gameCardScore").eq(0).text();
						var oppoScore = $("p.gameCardScore").eq(1).text(); 	
					}
					else {
						var playerScore = $("p.gameCardScore").eq(1).text();
						var oppoScore = $("p.gameCardScore").eq(0).text(); 		
					}

					if($("p.gameCardName").eq(0).text()=="You ") {
						var oppoName = $.trim($("p.gameCardName").eq(1).text());
						var oppoID=$('img[class="cardAvatar"]').eq(1).attr('src').split('_')[1];
					}
					else {
						var oppoName = $.trim($("p.gameCardName").eq(0).text());
						var oppoID=$('img[class="cardAvatar"]').eq(0).attr('src').split('_')[1];
					}

					var playerID=localStorage.cacheUser;

					var rack=new Array();
					var finished;

					if($("div#eog1Place img").length == 0)
					{
						finished = false;
					}
					else
					{
						var winnerId = $("div#eog1Place img").attr('src').split('_')[1];
						var winnerScore = $("div#eog1Place div.eogTotal div.value").text();
						var loserId = $("div#eog2Place img").attr('src').split('_')[1];
						var loserScore = $("div#eog2Place div.eogTotal div.value").text();

						if(winnerId===playerID)
						{
							finished = ((winnerScore == playerScore) && (loserId == oppoID) && (oppoScore==loserScore));
						}
						else if(winnerId===oppoID)
						{
							finished = ((winnerScore == oppoScore) && (loserId == playerID) && (playerScore==loserScore));
						}		

					}


					var word=$('tr[class="even"]').eq(0).find('td').eq(1).find('p').text().split('\"')[1];
					var word2=$('tr[class="even"]').eq(1).find('td').eq(1).find('p').text().split('\"')[1];

					var used  = {};


					var longdict = $.trim($("span#wordsBtnImage").attr('title'));
					var shortdict = longdict.match(/(Collins|TWL|OSPD4|German|Spanish|Italian|French|Portuguese)/g);

					var langs={
							"Collins":"en",
							"TWL" :"en",
							"OSPD4" : "en",
							"French": "fr",
							"Italian":"it",
							"German": "de",
							"Portuguese":"pt",
							"Spanish": "es"
					};
					var lang=langs[shortdict];


					var alldist = {
							"Collins":{"A":9,"B":2,"C":2,"D":4,"E":12,"F":2,"G":3,"H":2,"I":9,"J":1,"K":1,"L":4,"M":2,"N":6,"O":8,"P":2,"Q":1,"R":6,"S":4,"T":6,"U":4,"V":2,"W":2,"X":1,"Y":2,"Z":1,"blank":2},
							"TWL" :{"A":9,"B":2,"C":2,"D":4,"E":12,"F":2,"G":3,"H":2,"I":9,"J":1,"K":1,"L":4,"M":2,"N":6,"O":8,"P":2,"Q":1,"R":6,"S":4,"T":6,"U":4,"V":2,"W":2,"X":1,"Y":2,"Z":1,"blank":2},
							"OSPD4" :{"A":9,"B":2,"C":2,"D":4,"E":12,"F":2,"G":3,"H":2,"I":9,"J":1,"K":1,"L":4,"M":2,"N":6,"O":8,"P":2,"Q":1,"R":6,"S":4,"T":6,"U":4,"V":2,"W":2,"X":1,"Y":2,"Z":1,"blank":2},
							"French":{"A":9,"B":2,"C":2,"D":3,"E":15,"F":2,"G":2,"H":2,"I":8,"J":1,"K":1,"L":5,"M":3,"N":6,"O":6,"P":2,"Q":1,"R":6,"S":6,"T":6,"U":6,"V":2,"W":1,"X":1,"Y":1,"Z":1,"blank":2},
							"Italian":{"A":14,"B":3,"C":6,"D":3,"E":11,"F":3,"G":2,"H":2,"I":12,"L":5,"M":5,"N":5,"O":15,"P":3,"Q":1,"R":6,"S":6,"T":6,"U":5,"V":3,"Z":2,"blank":2},
							"German": {"A":5,"B":2,"C":2,"D":4,"E":15,"F":2,"G":3,"H":4,"I":6,"J":1,"K":2,"L":3,"M":4,"N":9,"O":3,"P":1,"Q":1,"R":6,"S":7,"T":6,"U":6,"V":1,"W":1,"X":1,"Y":1,"Z":1,"Ä":1,"Ö":1,"Ü":1,"blank":2},
							"Portuguese":{"A":14,"B":3,"C":4,"D":5,"E":11,"F":2,"G":2,"H":2,"I":10,"J":2,"L":5,"M":6,"N":4,"O":10,"P":4,"Q":1,"R":6,"S":8,"T":5,"U":7,"V":2,"X":1,"Z":1,"Ç":2,"blank":3},
							"Spanish": {"A":12,"B":2,"C":4,"D":5,"E":12,"F":1,"G":2,"H":2,"I":6,"J":1,"L":4,"M":2,"N":5,"O":9,"P":2,"Q":1,"R":5,"S":6,"T":4,"U":5,"V":1,"X":1,"Y":1,"Z":1,"CH":1,"LL":1,"Ñ":1,"RR":1,"blank":2}
					};

					var dist = alldist[shortdict];


					$.each( dist, function( key, value ) {
						used[key]=0;
					});



					$("div.tile.inactive").each(function (i) {
						if($(this).data("id")!="-1")
						{
							if ($(this).find("span.score_0").length > 0) {
								used["blank"]++;
							} else {
								used[$(this).data("letter")]++;
							}


						}
					});

					$("div.tile.active").each(function (i) {
						if (($(this).find("span.score_0").length > 0)&&($(this).data("id")!="-1")) {
							used["blank"]++;
							rack.push("?");
						} else {
							used[$(this).data("letter")]++;
							rack.push($(this).data("letter"));
						}
					});
					
					var gid = $("div[id$='TurnGamesList'] > div.match.lastCurrentGame,div[id$='TurnGamesList']  > div.match.currentGame").attr("data-match_id");

					rack.sort();
					var rackstring="";
					for(var i=0;i<rack.length;i++) {
						rackstring=rackstring+rack[i];
					}
					
					var boardimg;
					
					if(finished)
					{
						///TODO Send screen shot and game info
						
						/*html2canvas($('div#board'), {
							  logging:true,
							  proxy: 'https://scrabtourneyasst.herokuapp.com/scrabtourneyasst/h2cproxy.php',
							  onrendered: function(canvas) {
							    boardimg = canvas.toDataURL();
							    sendResponse({used: used,dist: dist,dictionary:shortdict,name: oppoName, ID:oppoID, first:word, second:word2, player:playerID, scoreP:playerScore, scoreO:oppoScore, finished: finished, rack:rackstring,gid:gid,board:boardimg});
							    return true;
							  }
						});
						*/
						sendResponse({used: used,dist: dist,dictionary:shortdict[0],name: oppoName, ID:oppoID, first:word, second:word2, player:playerID, scoreP:playerScore, scoreO:oppoScore, finished: finished, rack:rackstring,gid:gid,board:boardimg});
						return true;
					}
					else
					{
						sendResponse({used: used,dist: dist,dictionary:shortdict[0],name: oppoName, ID:oppoID, first:word, second:word2, player:playerID, scoreP:playerScore, scoreO:oppoScore, finished: finished, rack:rackstring,gid:gid,board:boardimg});
						return true;
					}
					//sendResponse({used: used,dist: dist,dictionary:shortdict,name: oppoName, ID:oppoID, first:word, second:word2, player:playerID, scoreP:playerScore, scoreO:oppoScore, finished: finished, rack:rackstring,gid:gid,board:boardimg});
				}
			});
}