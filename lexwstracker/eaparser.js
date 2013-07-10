if(/http(s)?:\/\/scrabblefb-live2\.sn\.eamobile\.com\/live\/http(s)?\//.test(window.location.href)){

	$(document).ready(function () {


		$("input#lookupTxt").keyup( function(e) {
			if(e.keyCode == 13) {
				var $input = $(this);

				setTimeout(function(){

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
						validWord=$.trim($input.val());
						
						if($("p#lookupResult img#owl").length === 0) {
							iconURL =  chrome.extension.getURL("owl-lookup.png");
							$("p#lookupResult span").html('<a href="http://google.com/search?hl='+lang+'&q=define:'+validWord+'" target="define"><img id="owl" src="'+iconURL+'" align="left"> '+validWord+'</a>');
						}
					}

				},50);
			}
		});
	});

	chrome.runtime.onMessage.addListener(
			function(request, sender, sendResponse) {

				if (request.command == "sendresults")
				{

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
							finished = ((winnerScore == playerScore) && (loserId == oppoID) && (oppoScore==loserScore))
						}
						else if(winnerId===oppoID)
						{
							finished = ((winnerScore == oppoScore) && (loserId == oppoScore) && (oppoScore==playerScore))
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

					var validWord="";
					if($("p#lookupResult").find("span").attr("class")=="typo_green") {
						validWord=$("p#lookupResult").find("span").text();
						if(validWord.indexOf('Define')==-1) {
							iconURL =  chrome.extension.getURL("owl-lookup.png");
							$("p#lookupResult").find("span").html('<A href="http://google.com/search?hl='+lang+'&q=define:'+validWord+'" target="define"><img src="'+iconURL+'" align="left"> '+validWord+'</a>');
						}
					}
					else {
						if($("p#lookupResult").find("span").attr("class")=="typo_lightRed") {
							// do nothing
						}
						else {
							$("p#lookupResult").html('<span class="typo_lightRed">Click the owl for definitions of valid words</span>');
						}
					}

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
						} else {
							used[$(this).data("letter")]++;
						}
					});
					//var resptext = $("p.gameCardScore").first().text();
					sendResponse({used: used,dist: dist,dictionary:shortdict,name: oppoName, ID:oppoID, first:word, second:word2, player:playerID, scoreP:playerScore, scoreO:oppoScore, finished: finished, defineMe:validWord});
				}
			});
}