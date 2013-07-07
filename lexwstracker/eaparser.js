if(/http(s)?:\/\/scrabblefb-live2\.sn\.eamobile\.com\/live\/http(s)?\//.test(window.location.href)){
	
	chrome.runtime.onMessage.addListener(
	  function(request, sender, sendResponse) {
		  
	    if (request.command == "sendresults")
	    {
	    	  var used  = {"A":0,"B":0,"C":0,"D":0,"E":0,"F":0,"G":0,"H":0,"I":0,"J":0,"K":0,"L":0,"M":0,"N":0,"O":0,"P":0,"Q":0,"R":0,"S":0,"T":0,"U":0,"V":0,"W":0,"X":0,"Y":0,"Z":0,"blank":-26 };
	    	  $("div.tile.inactive").each(function (i) {
	    	        if ($(this).find("span.score_0").length > 0) {
	    	        	used["blank"]++;
	    	        } else {
	    	        	used[$(this).data("letter")]++;
	    	        }
	    	      });
	    	  
	    	  $("div.tile.active").each(function (i) {
	    	        if ($(this).find("span.score_0").length > 0) {
	    	        	used["blank"]++;
	    	        } else {
	    	        	used[$(this).data("letter")]++;
	    	        }
	    	      });
	       //var resptext = $("p.gameCardScore").first().text();
	       sendResponse({data: used});
	    }
  });
}