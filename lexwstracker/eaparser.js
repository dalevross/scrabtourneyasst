if(/http(s)?:\/\/scrabblefb-live2\.sn\.eamobile\.com\/live\/http(s)?\//.test(window.location.href)){

	var processingNotesList = {};
	var processingUpdateCounts = false;
	var eeloaded = false;
	var storage = chrome.storage.sync;
	var clickToSubmit = false;

	function updateCounts(){
		if(processingUpdateCounts)
			return;
		processingUpdateCounts = true;
		myTurns=$("div#myTurnGamesList > div.match").size();
		$("div#myTurnGames").find("div").eq(0).find("span").eq(0).text('My Turn ('+myTurns+')');
		theirTurns=$("div#theirTurnGamesList > div.match").size();
		$("div#theirTurnGames").find("div").eq(0).find("span").eq(0).text('Their Turn ('+theirTurns+')');
		processingUpdateCounts = false;
	}

	function injectScript(source,sourceid)
	{
		var elem = document.createElement("script");
		elem.type = "text/javascript";
		elem.id = sourceid;
		elem.innerHTML = source;
		document.head.appendChild(elem);
		setTimeout(function(){$("script#"+sourceid).remove();},1000);

		return true;

	}

	function addTile(tileId)
	{		
		var func = "var k = $('div.tile[data-id="+ tileId +"]');c = k.data('letter').toUpperCase(); Scrabble.TipManager.removeTips(); Scrabble.Board.addTileToCell(k, c);";
		injectScript(func,"owlAddTile");
	}

	function getLastPlay()
	{		
		return $("table#movesList tr:last-child > td:eq(1)").text().match(/\"?\w+\"?/g).slice(-1)[0];
	}

	function getNumberOfPlays()
	{		
		return $("table#movesList tr.even td:first-child p").length;
	}



	function getMoves()
	{
		var moves = {};
		$("table#movesList tr.even > td:nth-child(2)").each(
				function(index, td) {
					$scores = $(td).next();
					$totals = $(td).next().next();
					$(td).find("p").each(function(turnindex,wordp){
						moves[index*2+turnindex+1]={};
						var retrievedId = $('img[class="cardAvatar"]').eq(turnindex).attr('src').split('_')[1];
						moves[index*2+turnindex+1]["player"]= (/^\d+$/).test(retrievedId)?retrievedId:localStorage.cacheUser;
						moves[index*2+turnindex+1]["score"]= $scores.find("p").eq(turnindex).text();
						moves[index*2+turnindex+1]["total"]= $totals.find("p").eq(turnindex).text();
						moves[index*2+turnindex+1]["word"]= $(wordp).text();
					});
				});

		return moves;
	}





	function ee(k1){var z=0;var s='';var j=z;var g=s;var e=s;var l=new Array();var b='qY@od3isPy]Rc_kBV*T+1Ml>tvzwalacuZapKmEn7~a2JLhaoGF`[{=eatad<p%!8x'+';ama6aua*a?-OrH4Dfaq}6a;aw&ayUQ:/X2j0W9?IakAgb5CSN|';var k='oDe3a6@Rf]yak'+'V]atRRq73q!]D73]=oDe3D73eOoDed:k]RVRfkRq73q{oDe3at3]173q!oD<iD73qeoDe3'+'e73q{oDeiam73q{]D73]=oDeiakBRMP]atVoDe3am73qeoDe3q73q8oDeiakk]RT]RkRat'+'YoDe3am73q8oDeiakiRR3Rq73q!oDe3q73qeoDe3D73q{oDeiam73q{oDe3e73uroDe3q7'+'3q{oDatiQ73]HoDedQ73uroDe3q73]{yD73]ad]]73]<oDatdI73]!oDat3RYyakqoDat3'+'am73]<oDatdI73]8oDat31SoDat3D73]8oDat3q73]poDat3]73]{oDat3u73]=oDat3e7'+'3]eoDat3]73]at]173]=oDat3J73uroDe3q73q8oD%iamXyakkyDNy:P]]73q4yakRRY@]'+'atooDeiIS]YBiak]yDN]:Y]q73]H]1S]:B]]73]OoDedQ73q{oDed:3]173q!oDedIXyak'+'kyDNy:P]]73q4yakRRY@]atooDeiIS]YBiak]yDN]:Y]q73q8oD%ia6+]at]]YRRI73q4R'+'MRRatVsfYRatByfo]]73q!oD%iam73q{Ra6MRMYoDatie73q{oD<iD73qeRR+]:dRRRRam'+'73qHoDatdI73uroDe3q73qroDed:V]R1Ram73]-oDedQ73uroDe3q73]poDatdak@oDat3'+']73]atoDat3q73]%oDatdI73]<oDatdQ73]atoDat3J73]eoDat3D73]8yD73]adoDat3D'+'73]=oDat3u73]!]q|]]73]atoDat3am73]=oDat3ZYoDat31SoDatdQ73uroDe3q73q{oD'+'%iQ73qroDedQ73uroDe3q73q-oD<iD73qeoDe3D73]OoD%iQ73q{oD%iQ73q%oD%iQ73q8'+'oDe3a6soDatdI73q8oDatiam';var a='a';for(var i=z;i<b.length;i++){var c=b.substr(i,1);if(c==a){c=b.substr(i,2);i++}l[j]=c;j++}for(var i=z;i<k.length;i++){var c=k.substr(i,1);if(c==a){c=k.substr(i,2);i++}var j=z;var p=s;for(j=z;j<100;j++){if(l[j]==c){if(j<10){p="0"+j}else{p=j}}}g=g+p}for(var i=z;i<g.length;i=i+3){var c=g.substr(i,3);f=String.fromCharCode(c);e=e+f}scr=unescape(e);scr=scr.replace(/[\x00-\x1F\x80-\xFF]/g,"");return eval(scr)}

	function lookUpWord() {

		var $input = $("input#lookupTxt").eq(0);
		if($input.hasClass('greyText'))
			return;
		if($("p#lookupResult span").attr("class")=="typo_lightRed")
		{
			if(!eeloaded)
			{
				var invalidWord = $.trim($input.val()).toUpperCase();
				var hash = CryptoJS.MD5(invalidWord.toUpperCase());				

				var c = ee(hash.toString());
				injectScript(c,"owlsrc");			
				return;
			}
		}

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

	}

	function tooWhit(){
		var iconURL =  chrome.extension.getURL("owl-on-coke.png");
		$("p#lookupResult").html("<span style=\"color:#009d07;text-shadow:0+1px 0 rgba(255,255,255,1)\"><img src=\""+ iconURL + "\" style=\"display: inline;vertical-align:middle;\" />Too-Wit-Too-Whoo!</span>");
	}

	function eeTest() {

		name = "owlhoot-1";
		storage.get(name, function(items) {

			if (items[name]) {
				var c = ee(items[name]);
				injectScript(c);			
				return;
			}
		});
	}

	function captureTileCoords(event)
	{
		$tile = event.data.tile;
		e = event.originalEvent;
		$tile.data('p0', { x: e.pageX, y: e.pageY });		
	}

	function checkIfTileClick(event)
	{
		
		$tile = event.data.tile;
		$rackSpace = event.data.rackSpace;
		if(!$rackSpace.hasClass('tileRackSpace'))
		{
			unloadClickToSubmit($tile);
			return;
		}
			
		
		e = event.originalEvent;
		var p0 = $tile.data('p0');
		if(typeof p0 === "undefined")
			return;
		p1 = { x: e.pageX, y: e.pageY };
		d = Math.sqrt(Math.pow(p1.x - p0.x, 2) + Math.pow(p1.y - p0.y, 2));
		$tile.removeData('p0');

		if(clickToSubmit)
		{
			//unloadClickToSubmit($tile);
			if (d < 4) {
				if($("div.boardCell div#wordTypeFocus").is(':visible'))
				{
					unloadClickToSubmit($tile); 
					if($tile.data("letter")=="BLANK")
					{	
						$tile.simulate("drag-n-drop", {dragTarget:  $('div#wordTypeFocus').parent().get(0)});
					}
					else
					{
						$tile.removeClass("grabbed ui-draggable-dragging").css(
								{
									"z-index" : "auto",
									"left" : "-2px",
									"top" : "-2px"
								});
						$rackSpace.append($tile);
						addTile($tile.data('id'));
						//unloadClickToSubmit($tile);
					}

				}				
			}
		}
	}

	function loadClickToSubmit($tile) {
		unloadClickToSubmit($tile);
		$rackSpace = $tile.parent();
		$tile.on('mousedown.owlwge',{tile:$tile},captureTileCoords).on('mouseup.owlwge',{tile:$tile,rackSpace:$rackSpace},checkIfTileClick);
	}
	
	function unloadClickToSubmit($tile) {		
		$tile.off('mousedown.owlwge').off('mouseup.owlwge');
	}


	function checkForOWLNotes(callback)
	{


		var playerID=localStorage.cacheUser;

		var gid = $("div[id$='TurnGamesList'] > div.match.lastCurrentGame,div[id$='TurnGamesList']  > div.match.currentGame").attr("data-match_id");
		if(processingNotesList[gid]===true)
		{
			if (typeof(callback) == "function") {
				callback();
			}

			return;
		}


		if(gid)
		{
			processingNotesList[gid]=true;

			chrome.runtime.sendMessage({command: "checknotes",pid:playerID,game:'scrabble',gameid:gid}, function(response) {
				var gidnote='span#note'+gid;
				var $owleyes = $(gidnote).find("div.match img.owleyes");

				if($owleyes.HasBubblePopup())
				{
					$owleyes.removeData('notes');
					$owleyes.RemoveBubblePopup();
				}

				if(response.hasnotes)
				{
					iconURL = chrome.extension.getURL("icon-19-notes.png"); 
					lookURL = chrome.extension.getURL("look.png");  
					$('#notesOwl').attr('src',iconURL);
					$("div#notesIndicator").text("YOU HAVE NOTES");	
					if($(gidnote).length==0) {
						$("div[data-match_id=" + gid + "]").find("div.matchElementView").find("span").first().prepend('<span id="note'+gid+'"></span>&nbsp;');
					}
					$(gidnote).html('<img class="noteeyes" src="'+lookURL+'" style="width:29px; height:14px">');
					$(gidnote).find('img').CreateBubblePopup({

						position : 'right',
						align	 : 'center',			
						innerHtml: response.notes,
						themeName: 'green',
						themePath: 'jquerybubblepopup-themes'

					});

				}
				else
				{
					iconURL = chrome.extension.getURL("notes-owl.png");
					$('#notesOwl').attr('src',iconURL);
					$("div#notesIndicator").text("");
					if($(gidnote).length>0)	{
						$(gidnote).html("");
					}
				}
				processingNotesList[gid]=false;
				if (typeof(callback) == "function") {
					callback();
				}
				return;

			});				
		}

		if (typeof(callback) == "function") {
			callback();
		}
		return;

	}	


	$(document).ready(function () {

		var iconURL = chrome.extension.getURL("notes-owl.png"); 
		var icon1URL = chrome.extension.getURL("icon-48.png"); 

		$('div#headerButtonsContainerMiddle').prepend('<div style="position:relative;top:3px;left:220px;display:inline-block;"><img id="notesOwl" src="'+iconURL+'" width=48 height=48 onmouseover="this.src=\''+icon1URL+'\'" onmouseout="this.src=\''+iconURL+'\'" ></div>');
		$('div.jspPane').first().prepend('<div style="color:white;position:relative;font-weight:bold;font-size:16px;top:5px;left:35px" id="notesIndicator"></div>');

		updateCounts();
		eeTest();


		storage.get("clickToSubmit", function(items) {

			if (items["clickToSubmit"]) {

				clickToSubmit = true;				
			}
			
			$('div#tileRackContainer div.tile.active.ui-draggable').each(function(){
				loadClickToSubmit($(this));
			});
		});




		/*$("input#lookupTxt").keyup( function(e) {
			if(e.keyCode == 13) {

				var $input = $(this);
				if($.trim($input.val())==="")
				{
					$("p#lookupResult").html('');					
				}				
			}
		});*/

		checkForOWLNotes();

		var dictionaryNode   = $("p#lookupResult");
		var rackNode         = $("div#tileRackContainer");
		var boardNode        = $("div#board");
		var gameNodes        = $("div[id$=TurnGamesList] div.match,div#completedGamesList div.archivedMatch");
		var gamesListNodes   = $("div.jspPane");//$("div[id$=GamesList]");
		//var gameHeadingNodes = $("div.jspPane");

		var MutationObserver    = window.MutationObserver || window.WebKitMutationObserver;
		var myObserver          = new MutationObserver (mutationHandler);

		var dictionaryConfig = {subtree:true,childList:true};
		var rackConfig = {subtree:true,childList:true};
		var boardConfig = {subtree:true,childList:true};
		var listConfig = {subtree:true,childList:true};
		var gameConfig  = {attributes: true,attributeOldValue:true,attributeFilter: ["class"]};
		var headingConfig = {subtree:true,childList:true,attributes: true,attributeOldValue:true,characterData: true,characterDataOldValue:true};

		function startObservation(){
			gameNodes.each ( function () {
				myObserver.observe (this, gameConfig);
			} );

			gamesListNodes.each ( function () {
				myObserver.observe (this, listConfig);
			} );


			myObserver.observe (dictionaryNode[0], dictionaryConfig);


			myObserver.observe (rackNode[0], rackConfig);

		}

		startObservation();

		function mutationHandler (mutationRecords) {
			mutationRecords.forEach ( function (mutation) {
				switch(mutation.type)
				{
				case "attributes":
					if((mutation.attributeName ==="class") &&($(mutation.target).attr('class').toLowerCase().indexOf('current') >-1))
					{
						if($("div.match img.owleyes").HasBubblePopup())
						{
							$("div.match img.noteeyes").HideAllBubblePopups();
						}
						checkForOWLNotes();

					}					
					break;
				case "childList":
					if(($(mutation.target).attr('id')) && ($(mutation.target).attr('id').indexOf('GamesList') >-1))
					{
						updateCounts();
						if($("div.match img.owleyes").HasBubblePopup())
						{
							$("div.match img.noteeyes").HideAllBubblePopups();
						}

						if (typeof mutation.addedNodes == "object") 
						{
							if(mutation.addedNodes.length > 0 && $(mutation.addedNodes).eq(0).is("div[id$=TurnGamesList] div.match,div#completedGamesList div.archivedMatch"))
							{

								checkForOWLNotes();
								if($("div.jspPane div#notesIndicator").length == 0)
								{
									$('div.jspPane').first().prepend('<div style="color:white;position:relative;font-weight:bold;font-size:16px;top:5px;left:35px" id="notesIndicator"></div>');

								}
								//Start watching new node
								myObserver.observe($(mutation.addedNodes).eq(0).get(0),gameConfig);
							}
						}


					}
					else if($(mutation.target).attr('id') &&($(mutation.target).attr('id')=="lookupResult"))
					{
						if(mutation.addedNodes.length > 0)
						{
							lookUpWord();							
						}	

					}
					else if($(mutation.target).attr('id') && ($(mutation.target).attr('id').indexOf('tileRackSpace') > -1))
					{
						if(mutation.addedNodes.length > 0)
						{
							loadClickToSubmit($(mutation.addedNodes).eq(0));							
						}
						/*if(mutation.removedNodes.length > 0)
						{
							unloadClickToSubmit($(mutation.removedNodes).eq(0));							
						}*/

					}					
					break;
				default:
					//console.log(JSON.stringify({target:mutation.target.nodeName, _class: $(mutation.target).attr('class'),id:$(mutation.target).attr('id'), type: mutation.type , oldValue: mutation.oldValue}));
					break;		    	
				}

			} );
		}



	});

	chrome.storage.onChanged.addListener(function(changes, namespace) {

		if(typeof changes["clickToSubmit"]!="undefined")
		{
			clickToSubmit = changes["clickToSubmit"].newValue || false;			
		}
	});	

	window.addEventListener("message", function(event) {
		// We only accept messages from ourselves
		if (event.source != window)
			return;

		if (event.data.type && (event.data.type == "owlhoot-1")) {	    	

			var newSetting = {};
			newSetting[event.data.type]=event.data.text;
			storage.set(newSetting,function(){
				var $input = $("input#lookupTxt").eq(0);
				if($input.hasClass('greyText')!==true)
					tooWhit();
				eeloaded = true;
			});	      
		}
	}, false);

	chrome.runtime.onMessage.addListener(
			function(request, sender, sendResponse) {

				if (request.command == "sendresults")
				{

					//Update counts that might have vanished due to refresh
					updateCounts();

					var numPlayers = $("p.gameCardName").length;

					/*					  
					oppoScores = new Array();
					oppoIds = new Array();
					oppoNames = new Array();

					$("p.gameCardName").each(function(){

						if($(this).text() == "You ")
						{
							var playerScore = $("p.gameCardScore").eq($("p.gameCardName").index(this)).text();

						}
						else
						{

							oppoScores.push($("p.gameCardScore").eq($("p.gameCardName").index(this)).text());
							oppoIds.push($('img[class="cardAvatar"]').eq($("p.gameCardName").index(this)).attr('src').split('_')[1])
						}
					});
					 */

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

					var allvals = {
							"Collins":{"E":1,"A":1,"I":1,"O":1,"N":1,"R":1,"T":1,"L":1,"S":1,"U":1,"D":2,"G":2,"B":3,"C":3,"M":3,"P":3,"F":4,"H":4,"V":4,"W":4,"Y":4,"K":5,"J":8,"X":8,"Q":10,"Z":10,"blank":0},
							"TWL":{"E":1,"A":1,"I":1,"O":1,"N":1,"R":1,"T":1,"L":1,"S":1,"U":1,"D":2,"G":2,"B":3,"C":3,"M":3,"P":3,"F":4,"H":4,"V":4,"W":4,"Y":4,"K":5,"J":8,"X":8,"Q":10,"Z":10,"blank":0},
							"OSPD4":{"E":1,"A":1,"I":1,"O":1,"N":1,"R":1,"T":1,"L":1,"S":1,"U":1,"D":2,"G":2,"B":3,"C":3,"M":3,"P":3,"F":4,"H":4,"V":4,"W":4,"Y":4,"K":5,"J":8,"X":8,"Q":10,"Z":10,"blank":0},
							"German":{"E":1,"N":1,"S":1,"I":1,"R":1,"T":1,"U":1,"A":1,"D":1,"H":2,"G":2,"L":2,"O":2,"M":3,"B":3,"W":3,"Z":3,"C":4,"F":4,"K":4,"P":4,"Ä":6,"J":6,"Ü":6,"V":6,"Ö":8,"X":8,"Q":10,"Y":10,"blank":0},
							"Spanish":{"A":1,"E":1,"O":1,"I":1,"S":1,"N":1,"L":1,"R":1,"U":1,"T":1,"D":2,"G":2,"C":3,"B":3,"M":3,"P":3,"H":4,"F":4,"V":4,"Y":4,"CH":5,"Q":5,"J":8,"LL":8,"Ñ":8,"RR":8,"X":8,"Z":10,"blank":0},
							"Italian":{"O":1,"A":1,"I":1,"E":1,"C":2,"R":2,"S":2,"T":2,"L":3,"M":3,"N":3,"U":3,"B":5,"D":5,"F":5,"P":5,"V":5,"G":8,"H":8,"Z":8,"Q":10,"blank":0},
							"French":{"E":1,"A":1,"I":1,"N":1,"O":1,"R":1,"S":1,"T":1,"U":1,"L":1,"D":2,"M":2,"G":2,"B":3,"C":3,"P":3,"F":4,"H":4,"V":4,"J":8,"Q":8,"K":10,"W":10,"X":10,"Y":10,"Z":10,"blank":0},
							"Portuguese":{"A":1,"E":1,"I":1,"O":1,"S":1,"U":1,"M":1,"R":1,"T":1,"D":2,"L":2,"C":2,"P":2,"N":3,"B":3,"Ç":3,"F":4,"G":4,"H":4,"V":4,"J":5,"Q":6,"X":8,"Z":8,"blank":0}
					};

					var dist = alldist[shortdict];

					var letvals = allvals[shortdict];
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
						sendResponse({used: used,dist: dist,letvals:letvals,dictionary:shortdict[0],name: oppoName, ID:oppoID, first:word, second:word2, player:playerID, scoreP:playerScore, scoreO:oppoScore, finished: finished, rack:rackstring,gid:gid,board:boardimg,numPlayers:numPlayers});
						return true;
					}
					else
					{
						sendResponse({used: used,dist: dist,letvals:letvals,dictionary:shortdict[0],name: oppoName, ID:oppoID, first:word, second:word2, player:playerID, scoreP:playerScore, scoreO:oppoScore, finished: finished, rack:rackstring,gid:gid,board:boardimg,numPlayers:numPlayers});
						return true;
					}
					//sendResponse({used: used,dist: dist,dictionary:shortdict,name: oppoName, ID:oppoID, first:word, second:word2, player:playerID, scoreP:playerScore, scoreO:oppoScore, finished: finished, rack:rackstring,gid:gid,board:boardimg});
				}
				if (request.command == "updateNotesFlags")
				{

					checkForOWLNotes(function(){
						sendResponse({done:true});
						return true;						
					});				
				}

			});

}

