document.addEventListener('DOMContentLoaded', function () {

	var bkg = chrome.extension.getBackgroundPage();

	var version = chrome.runtime.getManifest().version;
	var notesTable;

	$("div#options-version").html("Version: " + version);
	$( "#dialog-confirm").dialog({ autoOpen: false });

	$("#accordion").accordion({
		collapsible : true,
		active : false,
		heightStyle:"content"
	});

	$("div#toolbar img#refreshButton").on('click',function(){
		window.location.reload();		
	});
	
	
	$("button#deleteAll,button#deleteSelected").button({icons : { primary: "ui-icon-trash" }}).css('display','none');

	$("button#deleteAll").click(function(){		
		var button = this;
		var $dialog = $( "#dialog-confirm" );
		$( "#dialog-confirm" ).eq(0).html("<img src='owl-on-coke.png' style='display: inline;vertical-align:middle;'/><span>Deleted notes cannot be recovered. Are you sure?</span>").dialog({
			resizable: false,
			draggable:false,
			height:'auto',
			modal: true,
			position: { my: "bottom center", at: "top center", of: button },
			buttons: {
				"Yes": function() {
					bkg.oWLStorage.addNote(noteid, null, null, null,"",null,function(result){
						if(result)
						{
							
							notesTable.fnDeleteRow( nRow );
							chrome.tabs.query({url:"*://apps.facebook.com/*",windowType:"normal"}, function (tabs) {
								$.each(tabs,function(index,tab){																	
									if (((tab.url.indexOf('lexulous') > -1 && tab.url.indexOf('gid') > -1 )||(tab.url.indexOf('wordscraper') > -1  && tab.url.indexOf('gid') > -1)|| (tab.url.indexOf('ea_scrabble_closed') > -1)|| (tab.url.indexOf('livescrabble') > -1))&&(tab.url.indexOf('apps.facebook.com') > -1)) {
										chrome.tabs.update(tab.id, {url: tab.url});
									}
								});
								$dialog.dialog("close");
								
							});				
							

						}								       			

					});		
				},
				"No": function() {
					$dialog.dialog( "close" );
				}
			}
			
		}).dialog("open");

		
	
	});

	var storage = chrome.storage.sync;

	$chkNotesFirst = $("div#settings input[name=notesFirst]");

	loadSettings();

	function htmlEntities(str) {
		return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}

	function loadSettings()
	{
		var done=false;
		var chkCount = $("div#settings input:checkbox").length;
		var chksLoaded = 0;

		var numCount = $("div#settings input[type=number]").length;
		var numsLoaded = 0;

		$("div#settings input:checkbox").each(function(){
			var $checkbox = $(this);
			var name = $checkbox.attr('name');
			//var origVal = (name=="notesFirst")?true:false;
			var origVal = false;

			storage.get(name, function(items) {

				if (items[name]) {
					$checkbox.prop('checked', items[name]);
					if(name=="useAllTilesLimit")
					{
						$("div#settings input[name=allTilesLimit]").prop('disabled', !$("div#settings input[name=useAllTilesLimit]").prop('checked'));
					}

					chksLoaded++;
					if(chksLoaded === chkCount && numsLoaded === numCount && done === false)
					{
						done = true;
						bindSettingsChangeEvents();						
					}		
				}
				else
				{
					/*if(name=="notesFirst")
					{
						$(this).prop('checked',true);
						origVal=true;
					}
					 */
					var newSetting = {};
					newSetting[name]=origVal;
					storage.set(newSetting, function(){
						if(name=="useAllTilesLimit")
						{
							$("div#settings input[name=allTilesLimit]").prop('disabled', !$("div#settings input[name=useAllTilesLimit]").prop('checked'));
						}

						chksLoaded++;
						if(chksLoaded === chkCount && numsLoaded === numCount && done === false)
						{
							done = true;
							bindSettingsChangeEvents();						
						}				

					});	    		    	
				}

			});
		});


		bkg.oWLStorage.openDB(function(result){
			if(result)
			{

				setTimeout(function(){
					bkg.oWLStorage.getAllNotes(function(notes){



						if(notes.length)
						{
							
							
							var notesObj = {};
							var aaData = new Array();
							$.each(notes,function(){
								var record = this;
								select = "<input class='selectrecord' type='checkbox' data-id='"+ record.recordid +"'/>";
								game = record.game.substring(0,1).toUpperCase() + record.game.substring(1);
								if(/(lexulous|wordscraper)/.test(record.game.toLowerCase()))
								{
									gameM = /(lexulous|wordscraper)/g.exec(record.game.toLowerCase());
									gameid = "<a href='https://apps.facebook.com/"+ gameM[0]+ "/?action=viewboard&gid="+ record.gameid + "&pid=2&showGameOver=' target='_blank'>" + record.gameid + "</a>";
								}
								else
								{

									gameid = "<span title='"+ record.gameid +"'>" + ((record.gameid.length > 10)?(record.gameid.substring(0,9)+ "..."): record.gameid) + "</span>";

								}

								notesObj[record.recordid]= record.note;

								opponent = "<a href=\"https://www.facebook.com/"+ record.opponent+ "\"><img src=\"https://graph.facebook.com/"+ record.opponent+"/picture?type=small\"/></a>";
								showNote = "<span class=\"showNote\"><img data-id=\""+  record.recordid +"\"  class=\"ui-icon ui-icon-comment\"/></span>";
								dateSaved = record.savedDate.toUTCString();
								deleteNote = "<button class=\"deleteNote\" data-id=\""+ record.recordid +"\">Delete</button>";
								aaData.push([select,game,gameid,opponent,showNote,dateSaved,deleteNote]);
							});
							//console.debug(JSON.stringify(notesObj));
							$('#notesContainer').html( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="notesTable" width="100%"></table>' );
							notesTable = $('#notesTable').dataTable( {
								"aaData": aaData,
								"aaSorting": [[ 5, "desc" ]],
								"fnRowCallback": function( nRow, aData, iDisplayIndex ) {									/* Append the grade to the default row class name */

									$('input.selectrecord',nRow).on('change',function(){
										someChecked = ($(".selectrecord:checked").length)>0;
										$("button#deleteSelected").css('display',(someChecked)?'block':'none');										
									});

									$('button.deleteNote', nRow).button({icons : { primary: "ui-icon-trash" }}).on('click',function(event){
										
										var noteid = $(this).data('id');
										var button = this;
										var $dialog = $( "#dialog-confirm" );
										$( "#dialog-confirm" ).eq(0).html("<img src='owl-on-coke.png' style='display: inline;vertical-align:middle;'/><span>Deleted notes cannot be recovered. Are you sure?</span>").dialog({
											resizable: false,
											draggable:false,
											height:'auto',
											modal: true,
											position: { my: "left center", at: "right center", of: button },
											buttons: {
												"Yes": function() {
													bkg.oWLStorage.addNote(noteid, null, null, null,"",null,function(result){
														if(result)
														{
															
															notesTable.fnDeleteRow( nRow );
															chrome.tabs.query({url:"*://apps.facebook.com/*",windowType:"normal"}, function (tabs) {
																$.each(tabs,function(index,tab){																	
																	if (((tab.url.indexOf('lexulous') > -1 && tab.url.indexOf('gid') > -1 )||(tab.url.indexOf('wordscraper') > -1  && tab.url.indexOf('gid') > -1)|| (tab.url.indexOf('ea_scrabble_closed') > -1)|| (tab.url.indexOf('livescrabble') > -1))&&(tab.url.indexOf('apps.facebook.com') > -1)) {
																		chrome.tabs.update(tab.id, {url: tab.url});
																	}
																});
																$dialog.dialog("close");
																
															});						
															

														}								       			

													});		
												},
												"No": function() {
													$dialog.dialog( "close" );
												}
											}
											
										}).dialog("open");


									});


								},
								"aoColumns": [
								              { "sTitle": "Select" },          
								              { "sTitle": "Game" },
								              { "sTitle": "GameId" },            
								              { "sTitle": "Opponent" },		            
								              { "sTitle": "View Note" },
								              { "sTitle": "Date Saved" },
								              { "sTitle": "Delete" }	
								              ],
								              "bJQueryUI": true,
								              "sPaginationType": "full_numbers"
							} );

							
							$('span.showNote img').each(function(){
								//console.debug(notesObj[$(this).data('id')]);
								$(this).CreateBubblePopup({

									position : 'right',
									align	 : 'center',													
									innerHtml: notesObj[$(this).data('id')],													
									themeName: 'green',
									themePath: 'jquerybubblepopup-themes',
									alwaysVisible:true
								});

							});
							$("button#deleteAll").show();
							notesObj=null;



						}
						else
						{
							$('#notesContainer').html( 'You have no saved notes' );

						}

					});




				},100);


			}
			else
			{

				$('#notesContainer').html( 'Error retrieving notes' );
			}

		});



		$("div#settings input[type=number]").each(function(){
			var $input = $(this);
			var name = $input.attr('name');
			var origVal = $(this).val();
			storage.get(name, function(items) {

				if (items[name]) {
					(function(n){
						$input.val(n);
						numsLoaded++;
						if(chksLoaded === chkCount && numsLoaded === numCount && done === false)
						{
							done = true;
							bindSettingsChangeEvents();
							callback();
						}			

					})(items[name]);
				}
				else
				{
					var newSetting = {};
					newSetting[name]=origVal;
					storage.set(newSetting,function(){
						numsLoaded++;
						if(chksLoaded === chkCount && numsLoaded === numCount && done === false)
						{
							done = true;
							bindSettingsChangeEvents();
							callback();
						}					

					});	    		    	
				}

			});
		});

		function bindSettingsChangeEvents(){
			$("div#settings input:checkbox").change(function(){		
				var name = $(this).attr('name');
				var newSetting = {};
				newSetting[name] = $(this).prop('checked');
				storage.set(newSetting);			

			});

			$("div#settings input[type=number]").change(function(){		
				var name = $(this).attr('name');
				var newSetting = {};
				newSetting[name] = $(this).val();
				storage.set(newSetting); 

			});	
		}				

	}


	chrome.storage.onChanged.addListener(function(changes, namespace) {

		//Check if settings were deleted and reload if that occurs
		var changeLen = Object.keys(changes).length;
		var changeCount = 0 ;
		for (key in changes) {
			var storageChange = changes[key];
			if(typeof storageChange.newValue != "undefined")
				return;

			changeCount++;
			if(changeCount==changeLen)
			{
				window.location.reload();
			}

		}
	});


	chrome.storage.onChanged.addListener(function(changes, namespace) {

		var newVal = false;

		if(typeof changes["useAllTilesLimit"]!="undefined")
		{
			var newVal = changes["useAllTilesLimit"].newValue || false;
			$("div#settings input[name=allTilesLimit]").prop('disabled', !newVal);
		}	

	});	



	chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
		if(request.command == 'triggerpopup')
		{
			$("#tabs" ).tabs( "option", "active", 1 );
		}
	});



});
