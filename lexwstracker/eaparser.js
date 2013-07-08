if(/http(s)?:\/\/scrabblefb-live2\.sn\.eamobile\.com\/live\/http(s)?\//.test(window.location.href)){
	
	chrome.runtime.onMessage.addListener(
	  function(request, sender, sendResponse) {
		  
	    if (request.command == "sendresults")
	    {
	    	  var used  = {};
	    	  var dist = {};
	    	  
	    	  //var longdict =;
	    	  
	    	  $("div.tileTrackingItem").each(function (i) {
	    		    var tileletter = $.trim($(this).find("span.tileTrackingLetter").eq(0).text());
	    		    var tilecount = $.trim($(this).find("span.tileTrackingCount").eq(0).text());
	    	        if (tileletter==="") {
	    	        	used["blank"]=0;
	    	        	dist["blank"]=parseInt(tilecount);
	    	        } else {
	    	        	used[tileletter]=0;
	    	        	dist[tileletter]=parseInt(tilecount);
	    	        }
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
	       sendResponse({used: used,dist: dist});
	    }
  });
}