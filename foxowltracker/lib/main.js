var data = require("sdk/self").data;
var tabs = require("sdk/tabs"); 


var tracker_diag = require("sdk/panel").Panel({
  width: 357,
  contentURL: data.url("popup.html"),
  contentScriptFile: [data.url("jquery-1.7.min.js"),data.url("popup.js")]
});

// Create a widget, and attach the panel to it, so the panel is
// shown when the user clicks the widget.
require("sdk/widget").Widget({
  label: "OWLS Tracker",
  id: "tracker-diag",
  contentURL: data.url("icon16.png"),
  panel: tracker_diag
});
 
// When the panel is displayed it generated an event called
// "show": we will listen for that event and when it happens,
// we will check the current tab to determine if it is trackable
// then send the data to the panel for display .
tracker_diag.on("show", function() {
var tab = tabs.activeTab;
if (((tab.url.indexOf('lexulous') > -1)||(tab.url.indexOf('wordscraper') > -1)|| (tab.url.indexOf('ea_scrabble_closed') > -1)|| (tab.url.indexOf('livescrabble') > -1))&&(tab.url.indexOf('apps.facebook.com') > -1)) {
	
	var applink = tab.url;
	var game = applink.match(/(lexulous|wordscraper|ea_scrabble_closed|livescrabble)/g);

	if (game === null) {

		tracker_diag.port.emit("message","Invalid link in address bar!");		

		return false;

	}
	
	if((game[0]=="ea_scrabble_closed")||(game[0]=="livescrabble"))
	{
		tracker_diag.port.emit("loading");
		//var data = require("sdk/self").data;
		var pageMod = require("sdk/page-mod");
		 
		pageMod.PageMod({
		  include: /http(s)?:\/\/scrabblefb-live2\.sn\.eamobile\.com\/live\/http(s)?\/.*/,
		  contentScriptFile: [data.url("jquery-1.7.min.js"),
		                      data.url("eaparser.js")],
		                      attachTo: "frame",
		                      onAttach: function(worker) {
		                    	    console.log("Attached");
		                    	    worker.port.emit("getUsedTiles");
		                    	    worker.port.on("gotUsedTiles", function(elementContent) {
		                    	    	tracker_diag.port.emit("usedtiles",elementContent);
		                    	    });
		                    	  }
		});
	}
  }	
  
});
 
