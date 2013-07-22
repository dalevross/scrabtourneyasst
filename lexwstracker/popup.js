

var trackingGenerator = {

		g_playerid:'',
		g_game:'',
		g_gameid:'',
		g_opponentId:'',
		g_screenshot:'',

		loadToDialog: function($dialog,response,game){

			var used = response.used;
			var dist = response.dist;
			var oppoName = response.name;
			var oppoID= response.ID;
			var word=response.first;
			var dictionary=response.dictionary;
			var playerId=response.player;
			var playerScore=response.scoreP;
			var oppoScore=response.scoreO;
			var finished = response.finished;
			var rack = response.rack;
			var gameid = response.gid;

			trackingGenerator.g_playerid = response.player;			
			trackingGenerator.g_gameid = response.gid;
			trackingGenerator.g_game = game;
			trackingGenerator.g_opponentId = response.ID;
			trackingGenerator.g_screenshot = response.board;

			var left = {};
			var tilecount = 0;
			var vowels = "AEIOU";
			var vcnt=0;var ccnt=0;var bcnt=0;

			if(!word) {word=response.second;}

			for(var letter in used){
				if((dist[letter]-used[letter])>0)
				{
					left[letter] = dist[letter]-used[letter];
					tilecount+=dist[letter]-used[letter];
				}
			}

			var index = 0;
			var html = '';

			var heading='Your game with '+oppoName+' <div class="profile"><a href="http://facebook.com/'+oppoID+'" target="_blank">Facebook Profile</a></div>';
			$('div#heading').html(heading);

			var inbag = (tilecount>7)?tilecount-7:0;
			html = html + '<span style="font-weight:bold;">Tile Count: ' + tilecount + '</span><span> ('+ inbag + ' in bag)</span><br/>';

			var showAllTiles = $("div#settings input[name=allTiles]").prop('checked');
			var showTotals = $("div#settings input[name=showTotals]").prop('checked');
			var distinguishVowels = $("div#settings input[name=distinguishVowels]").prop('checked');
			var useAllTilesLimit = $("div#settings input[name=useAllTilesLimit]").prop('checked');
			var allTilesLimit = $("div#settings input[name=allTilesLimit]").val();


			if(showAllTiles && useAllTilesLimit)
			{
				showAllTiles = 	(tilecount > allTilesLimit);			
			}

			var numTiles = (showAllTiles)?Object.keys(dist).length:Object.keys(left).length;


			for (var letter in dist) {
				if(!showAllTiles && !(left[letter]))
					continue;


				if(left[letter])
				{
					if(vowels.indexOf(letter)>-1) 
					{
						vcnt=vcnt+left[letter];
					}
					else if(letter!="blank")
					{
						ccnt=ccnt+left[letter];
					}
					else
					{
						bcnt=left[letter];
					}
				}

				if((index % 8)===0)
				{
					html = html + '<div style="float:left;padding:10px">';
				}
				html = html + '<div class="wrapper' + ((showAllTiles && !(left[letter]))?' depleted':'') +'"><div   class="letter' + ((vowels.indexOf(letter)>-1 && distinguishVowels)?' vowel':'') +'" title="Total: ' + dist[letter] +  '">' + letter + '</div><div class="count">' + (left[letter] || '0') + ((showTotals)?('/' + dist[letter].toString()):'') + '</div></div>';
				index++;
				if(((index)%8===0 && index!==0)|| index === numTiles)
				{
					html = html + '</div>';
				}
			}



			html = html + '<div style="clear:both"/><div class="vccnt">Vowels: '+vcnt+', Consonants: '+ccnt+', Blanks: '+bcnt+'.<BR>Your sorted rack: <b>'+rack+'</b></div>';

			var d = new Date();

			var suffix = '<span style="font-size:10px"> Retrieved at ' + d.toLocaleString() + '</span>';

			html = html + suffix;

			/*
			 chrome.tabs.captureVisibleTab( function (dataURL){
				html = html + '<br/>' + '<a href="' + dataURL  + '" target="_blank">Download Screen Shot</a>' + '<br/>';
			}
			);*/

			if(playerId=='593170373'||playerId=='712117020') {
				if(finished) {
					html=html + '<div class="sendScore"><form action="http://moltengold.com/cgi-bin/scrabble/extn.pl" method="post" target="scoring"> <input name="playerScore" value="'+ playerScore +'" type="hidden" > <input name="oppoScore" value="'+ oppoScore +'" type="hidden" >  <input name="playerId" value="'+playerId+'" type="hidden"> <input name="oppoId" value="'+oppoID+'" type="hidden">  <input name="dictionary" value="'+dictionary+'" type="hidden"> <input name="word" value="'+ word +'" type="hidden" > <input name="app" value="Scrabble" type="hidden"> Save final scores in Facebook Scrabble League <input type="submit" value="Save"></form></div>';
				}
				else if(word) {

					html=html + '<div class="sendWord"><form action="http://moltengold.com/cgi-bin/scrabble/extn.pl" method="post" target="scoring"> <input name="word" value="'+ word +'" type="hidden" > <input name="playerId" value="'+playerId+'" type="hidden"> <input name="oppoId" value="'+oppoID+'" type="hidden">  <input name="dictionary" value="'+dictionary+'" type="hidden"> <input name="app" value="Scrabble" type="hidden"> Record first word ('+word+') in Facebook Scrabble League <input type="submit" value="Send"></form></div>';
				}


				$dialog.html(html);
				$("input[type=submit]").button();
				var $ajaxresult;
				if (!$('#ajaxresult').is(':data(dialog)')) {

					$ajaxresult = $('<div id="ajaxresult" ></div>').html('').dialog({autoOpen : false,title : 'Score Manager',
						width : 250,
						modal:true
					});

				} else {
					$ajaxresult = $('#ajaxresult');
				}

				var params;
				
				var uGame = game.substring(0,1).toUpperCase() + game.substring(1);
				//Override click for send event.				
				$("div[class^=send] input[type=submit]").on('click',function(evt){				
					evt.preventDefault();
					if(finished)
					{
						params = {playerScore:playerScore,oppoScore:oppoScore,playerId:playerId,oppoId:oppoID,dictionary:dictionary[0],word:word,app:uGame,gameid:gameid};
						
					}
					else
					{
						params = {word:word,playerId:playerId,oppoId:oppoID,dictionary:dictionary[0],app:uGame,gameid:gameid};
					}
					
					var loadinghtml = '<div><span>Loading...</span><br/><img src="trackerloading.gif" /></div>';

					if ($ajaxresult.dialog('isOpen')) {

						$ajaxresult.html(loadinghtml);

					} else {

						$ajaxresult.html(loadinghtml).dialog('open');

					}
					var url = 'http://moltengold.com/cgi-bin/scrabble/extn.pl';
					
					$.ajax({url : url,//+ '?callback=?',
						context : this,
						data : (params),
						dataType : "html",
						type:"POST",
						success : function(data) {		
							$ajaxresult.html(data);							
							return true;
						},
						failure : function(jqXHR, textStatus, errorThrown) {
							$ajaxresult.html(textStatus);
							return false;

						}
					});

				});
			}
			else
			{
				$dialog.html(html);				
			}



		},
		getTilesLeft: function(applink,callback) {

			var html = '';

			var $dialog;

			$dialog = $('#lexwstracker');

			var dist = {"A":9,"B":2,"C":2,"D":4,"E":12,"F":2,"G":3,"H":2,"I":9,"J":1,"K":1,"L":4,"M":2,"N":6,"O":8,"P":2,"Q":1,"R":6,"S":4,"T":6,"U":4,"V":2,"W":2,"X":1,"Y":2,"Z":1,"blank":2};

			// var applink = chrome.extension.getBackgroundPage().currentUrl;
			var game = applink.match(/(lexulous|wordscraper|ea_scrabble_closed|livescrabble)/g);

			if (game === null) {

				$dialog.html('Invalid link,' + applink +',found in address bar!');		

				return false;

			}
			var loadinghtml = '<div><span>Loading...</span><br/><img src="trackerloading.gif" /></div>';

			if((game[0]=="ea_scrabble_closed")||(game[0]=="livescrabble"))
			{
				game = 'scrabble';

				$dialog.html(loadinghtml);
				chrome.tabs.query({'active': true, 'currentWindow':true}, function (tabs) {
					chrome.tabs.sendMessage(tabs[0].id, {command: "sendresults"}, function(response) {
						var used = response.used;
						var dist = response.dist;
						trackingGenerator.loadToDialog($dialog,response,game);
						callback();
					});
				});

			}
			else
			{


				var gid = /gid=(\d+)/g.exec(applink);

				trackingGenerator.g_game = game[0];
				trackingGenerator.g_gameid = gid[1];




				if (gid === null) {

					$dialog.html('Invalid game link!');		

					return false;

				}



				var pid = /pid=(\d)/g.exec(applink);
				var password = /password=(\w+)/g.exec(applink);

				if ((pid === null) || (password === null)) {

					params = {gid : gid[1],game : game[0],version : 2,extension:1};

				} else {

					params = {gid : gid[1],game : game[0],pid : pid[1],password : password[1],version : 2,extension:1};

				}






				$dialog.html(loadinghtml);



				$.ajax({url : 'https://scrabtourneyasst.herokuapp.com/scrabtourneyasst/scrabtiletracker.php?callback=?',

					context : this,

					data : (params),

					dataType : "jsonp",

					success : function(data) {					

						var d = new Date();

						var suffix = '<br/><span style="font-size:10px"> Retrieved at ' + d.toLocaleString() + '</span>';

						html = data['html'] + suffix;
						trackingGenerator.g_playerid = data['id'];
						trackingGenerator.g_opponentId = data['oppid'];
						$dialog.html(html);
						callback();

					},

					failure : function(jqXHR, textStatus, errorThrown) {

						$dialog.html(textStatus);

						return false;

					}

				});
			}
			return true;

		},

		getStorageRecordId : function (userid,game,gameid)
		{
			return userid + '_' + game + '_'+ gameid;

		}
};


//Run our tile tracker script as soon as the document's DOM is ready.
document.addEventListener('DOMContentLoaded', function () {

	var bkg = chrome.extension.getBackgroundPage();

	$("#tabs").tabs();
	$("ul.ui-widget-header").removeClass(' ui-corner-all').css({ 'border' : 'none', 'border-bottom' : '1px solid #d4ccb0'});
	$("#saveButton").button();
	$("#saveButton").hide();

	$("div#toolbar img#closeButton").on('click',function(){
		window.close();		
	});

	$("div#toolbar img#refreshButton").on('click',function(){
		window.location.reload();		
	});


	var storage = chrome.storage.sync;

	$chkNotesFirst = $("div#settings input[name=notesFirst]");

	loadSettings();


	var editor = new TINY.editor.edit('editor', {
		id: 'tinyeditor',
		width: 320,
		height: 250,
		cssclass: 'tinyeditor',
		css:'body{background-color:#ffffff}', 
		controlclass: 'tinyeditor-control',
		rowclass: 'tinyeditor-header',
		dividerclass: 'tinyeditor-divider',
		controls: ['bold', 'italic', 'underline', 'strikethrough',
		           '|', 'outdent', 'indent', '|','undo', 'redo','unformat','n'
		           , 'size', '|', 'image', 'link', 'unlink'],
		           footer: false,
		           xhtml: false,
		           bodyid: 'editor'		
	});	


	var innerbody = editor.i.contentWindow.document.body;


	chrome.tabs.query({'active': true,'currentWindow':true}, function (tabs) {
		var applink = tabs[0].url;
		trackingGenerator.getTilesLeft(applink,function(){
			var recid = trackingGenerator.getStorageRecordId(trackingGenerator.g_playerid,trackingGenerator.g_game,trackingGenerator.g_gameid);

			$(innerbody).attr('contenteditable',false);
			$("div#notestatus").html('<span>Loading...</span><br/><img src="note-loading.gif" />');
			unbindNoteChangeEvents();

			bkg.oWLStorage.openDB(function(result){
				if(result)
				{

					setTimeout(function(){
						bkg.oWLStorage.getNoteByRecordId(recid,function(note){		
							if($.trim(note)!="")
							{
								$('#tabs .ui-tabs-nav li:nth-child(2) span').html("<img class='ui-icon ui-icon-comment'/>Notes");
								$(innerbody).html(note);//.append('<img src="' +  trackingGenerator.g_screenshot + '" />');
								editor.post();
								if($chkNotesFirst.prop('checked'))
								{
									$("#tabs" ).tabs( "option", "active", 1 );
								}
							}
							else
							{
								$('#tabs .ui-tabs-nav li:nth-child(2) span').html("Notes");

							}

							$(innerbody).attr('contenteditable',true);
							$("div#notestatus").html('');
							bindNoteChangeEvents();
						});
					},1000);


				}
				else
				{

					$(innerbody).attr('contenteditable',true);
					$("div#notestatus").html('');
					bindNoteChangeEvents();
				}

			});



		});
	});


	$('button#saveButton').click(function(){

		teval = $.trim($("#tinyeditor").val());
		if(teval != $(innerbody).html())
		{
			$(innerbody).attr('contenteditable',false);
			$("div#notestatus").html('<span>Saving...</span><br/><img src="note-loading.gif" />');
			unbindNoteChangeEvents();

			editor.post();
			var recid = trackingGenerator.getStorageRecordId(trackingGenerator.g_playerid,trackingGenerator.g_game,trackingGenerator.g_gameid);
			bkg.oWLStorage.addNote(recid, trackingGenerator.g_playerid, trackingGenerator.g_game, trackingGenerator.g_gameid,$.trim($("#tinyeditor").val()),trackingGenerator.g_opponentId,function(result){
				if(result)
				{
					$('#saveButton').hide();
					if(($.trim($("#tinyeditor").val())=="")||($.trim($("#tinyeditor").val())=="<br>"))
					{
						$('#tabs .ui-tabs-nav li:nth-child(2) span').html("Notes");
					}
					else{
						$('#tabs .ui-tabs-nav li:nth-child(2) span').html("<img class='ui-icon ui-icon-comment'/>Notes");
					}
				}

				$(innerbody).attr('contenteditable',true);
				$("div#notestatus").html('');
				bindNoteChangeEvents();

			});


		}

	});

	var delay = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();	

	function registerNoteChange()
	{

		delay(function(){
			teval = $.trim($("#tinyeditor").val());
			if(teval != $(innerbody).html())
			{			
				$('#saveButton').show();
				//$('#saveWarning').effect('shake');

			}	
		}, 500 );
	}	

	function bindNoteChangeEvents(){
		$('div.tinyeditor').on('mouseup',registerNoteChange);
		$(innerbody).on("paste keyup mouseup",registerNoteChange );
	}

	function unbindNoteChangeEvents(){
		$('div.tinyeditor').off('mouseup',registerNoteChange);
		$(innerbody).off("paste keyup mouseup",registerNoteChange );
	}

	function loadSettings()
	{

		$("div#settings input:checkbox").each(function(){
			var $checkbox = $(this);
			var name = $checkbox.attr('name');
			var origVal = $checkbox.prop('checked');
			storage.get(name, function(items) {

				if (items[name]) {
					$checkbox.prop('checked', items[name]);	    	
				}
				else
				{
					var newSetting = {};
					newSetting[name]=origVal;
					storage.set(newSetting);	    		    	
				}
				if(name=="useAllTilesLimit")
				{
					$("div#settings input[name=allTilesLimit]").prop('disabled', !$("div#settings input[name=useAllTilesLimit]").prop('checked'));
				}
			});
		});

		$("div#settings input[type=number]").each(function(){
			var $input = $(this);
			var name = $input.attr('name');
			var origVal = $(this).val();
			storage.get(name, function(items) {

				if (items[name]) {
					(function(n){
						$input.val(n);
					})(items[name]);
				}
				else
				{
					var newSetting = {};
					newSetting[name]=origVal;
					storage.set(newSetting);	    		    	
				}
			});
		});


		$("div#settings input:checkbox").change(function(){		
			var name = $(this).attr('name');
			var newSetting = {};
			newSetting[name] = $(this).prop('checked');
			storage.set(newSetting);
			
			if(name=='useAllTilesLimit')
			{	    	
				$("div#settings input[name=allTilesLimit]").prop('disabled', !$(this).prop('checked'));
			}			

		});

		$("div#settings input[type=number]").change(function(){		
			var name = $(this).attr('name');
			var newSetting = {};
			newSetting[name] = $(this).val();
			storage.set(newSetting); 

		});		

	}

	bindNoteChangeEvents();


});
