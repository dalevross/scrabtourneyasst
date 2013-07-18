

var trackingGenerator = {

		g_playerid:'',
		g_game:'',
		g_gameid:'',
		g_opponentId:'',
		

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

			html=html+'<div class="heading">Your game with '+oppoName+' <span class="profile"><a href="http://facebook.com/'+oppoID+'" target="_blank">Facebook Profile</a></span></div>';

			var inbag = (tilecount>7)?tilecount-7:0;
			html = html + '<span style="font-weight:bold;">Tile Count: ' + tilecount + '</span><span> ('+ inbag + ' in bag)</span><br/>';
			for (var letter in left) {
				if(vowels.indexOf(letter)>-1) {vcnt=vcnt+left[letter];}
				else {
					if(letter!="blank") {ccnt=ccnt+left[letter];}else{bcnt=left[letter];}
				}
				if((index % 8)===0)
				{
					html = html + '<div style="float:left;padding:10px">';
				}
				html = html + '<div class="wrapper"><div   class="letter" title="Total: ' + dist[letter] +  '">' + letter + '</div><div class="count">' + left[letter] + '</div></div>';
				index++;
				if(((index)%8===0 && index!==0)|| index===Object.keys(left).length )
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

			}

			$dialog.html(html);

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
	$("#tabs").tabs();
	$("#saveButton").button();

	var bkg = chrome.extension.getBackgroundPage();


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
								$('#tabs .ui-tabs-nav li:nth-child(2) span').html("<img class='ui-icon ui-icon-locked'/>Notes");
								$(innerbody).html(note);
								editor.post();
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
			bkg.oWLStorage.addNote(recid, trackingGenerator.g_playerid, trackingGenerator.g_game, trackingGenerator.g_gameid,$.trim($("#tinyeditor").val(),trackingGenerator.g_opponentId),function(result){
				if(result)
				{
					if($.trim($("#tinyeditor").val())=="")
					{
						$('#tabs .ui-tabs-nav li:nth-child(2) span').html("Notes"); 
					}
					else
					{
						$('#tabs .ui-tabs-nav li:nth-child(2) span').html("<img class='ui-icon ui-icon-locked'/>Notes");								
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
				
				$('#tabs .ui-tabs-nav li:nth-child(2) span').html("<img class='ui-icon ui-icon-unlocked'/>Notes");								
				
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
	
	bindNoteChangeEvents();


});
