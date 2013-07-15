//Copyright (c) 2011 The Chromium Authors. All rights reserved.
//Use of this source code is governed by a BSD-style license that can be
//found in the LICENSE file.

var currentUrl = "about:blank";
//Called when the url of a tab changes.
function checkForValidUrl(tabId, changeInfo, tab) {
	// If the letter 'g' is found in the tab's URL...
	if (((tab.url.indexOf('lexulous') > -1 && tab.url.indexOf('gid') > -1 )||(tab.url.indexOf('wordscraper') > -1  && tab.url.indexOf('gid') > -1)|| (tab.url.indexOf('ea_scrabble_closed') > -1)|| (tab.url.indexOf('livescrabble') > -1))&&(tab.url.indexOf('apps.facebook.com') > -1)) {
		// ... show the page action.
		chrome.pageAction.show(tabId);

	}
	else
	{
		chrome.pageAction.hide(tabId);
	}
};

chrome.pageAction.onClicked.addListener(function(tab) {
	currentUrl = tab.url;
});


//Listen for any changes to the URL of any tab.
chrome.tabs.onUpdated.addListener(checkForValidUrl);

(function () {

	const DB_NAME = 'owlswgedb';
	const DB_VERSION = 1; // Use a long long for this value (don't use a float)
	const DB_NOTES_STORE_NAME = 'notes';
	const DB_GCG_STORE_NAME = 'gcg';
	
	var db;

	function openDb() {
	    var req = indexedDB.open(DB_NAME, DB_VERSION);
	    req.onsuccess = function (evt) {
	     
	      db = this.result;
	      
	    };
	    req.onerror = function (evt) {
	      console.error("openDb:", evt.target.errorCode);
	    };
	    
	    req.onupgradeneeded = function (evt) {
	        console.log("openDb.onupgradeneeded");
	        var notes_store = evt.currentTarget.result.createObjectStore(
	        		DB_NOTES_STORE_NAME, { keyPath: 'id', autoIncrement: true });

	        notes_store.createIndex('recordid', 'recordid', { unique: true });
	        notes_store.createIndex('profileid', 'profileid', { unique: false});
	        notes_store.createIndex('game', 'game', { unique: false});
	        notes_store.createIndex('gameid', 'gameid', { unique: false });
	        
	        var gcg_store = evt.currentTarget.result.createObjectStore(
	        		DB_NOTES_GCG_NAME, { keyPath: 'id', autoIncrement: true });

	        gcg_store.createIndex('recordid', 'recordid', { unique: true });
	        gcg_store.createIndex('profileid', 'profileid', { unique: false});
	        gcg_store.createIndex('game', 'game', { unique: false});
	        gcg_store.createIndex('gameid', 'gameid', { unique: false });
	      };
	}
	
	/**
	   * @param {string} store_name
	   * @param {string} mode either "readonly" or "readwrite"
	   */
	  function getObjectStore(store_name, mode) {
	    var tx = db.transaction(store_name, mode);
	    return tx.objectStore(store_name);
	  }

	  function clearObjectStore(store_name) {
	    var store = getObjectStore(store_name, 'readwrite');
	    var req = store.clear();
	    req.onsuccess = function(evt) {
	      displayActionSuccess("Store cleared");	      
	    };
	    req.onerror = function (evt) {
	      console.error("clearObjectStore:", evt.target.errorCode);
	      displayActionFailure(this.error);
	    };
	  }

	  function getNote(key, store, success_callback) {
	    var req = store.get(key);
	    req.onsuccess = function(evt) {
	      var value = evt.target.result;
	      if (value)
	        success_callback(value.note);
	    };
	  }
	  
	  /**
	   * @param {string} recordid
	   * @param {string} profileid
	   * @param {string} game
	   * @param {string} gameid
	   * @param {string} note
	   */
	  function addNote(recordid, profile, game, gameid,note) {
	    
	    var obj = {recordid:recordid, profile:profile, game:game, gameid:gameid,note:note};
	    
	    var store = getObjectStore(DB_NOTES_STORE_NAME, 'readwrite');
	    var req;
	    try {
	      req = store.put(obj);
	    } catch (e) {
	      displayActionFailure(e);
	      //throw e;
	    }
	    req.onsuccess = function (evt) {
	      //console.log("Insertion in DB successful");
	      displayActionSuccess();
	      //displayPubList(store);
	    };
	    req.onerror = function() {
	      console.error("addPublication error", this.error);
	      displayActionFailure(this.error);
	    };
	  }
	  
	  
	  /**
	   * @param {string} recordid
	   * @param {string} profileid
	   * @param {string} game
	   * @param {string} gameid
	   * @param {object} gcginfo
	   */
	  function addGCG(recordid, profile, game, gameid,objGCG) {
	    
	    var obj = {recordid:recordid, profile:profile, game:game, gameid:gameid,gcginfo:gcginfo};
	    
	    var store = getObjectStore(DB_GCG_STORE_NAME, 'readwrite');
	    var req;
	    try {
	      req = store.put(obj);
	    } catch (e) {
	      displayActionFailure(e);
	      //throw e;
	    }
	    req.onsuccess = function (evt) {
	      //console.log("Insertion in DB successful");
	      displayActionSuccess();
	      //displayPubList(store);
	    };
	    req.onerror = function() {
	      console.error("addPublication error", this.error);
	      displayActionFailure(this.error);
	    };
	  }
	  
	  /**
	   * @param {string} recordid
	   * @param {string} store_name
	   */
	  function deleteRecordFromStore(recordid,store_name) {
	    var store = getObjectStore(store_name, 'readwrite');
	    var req = store.index('recordid');
	    req.get(recordid).onsuccess = function(evt) {
	      if (typeof evt.target.result == 'undefined') {
	        displayActionFailure("No matching record found");
	        return;
	      }
	      deleteRecord(evt.target.result.id, store,store_name);
	    };
	    req.onerror = function (evt) {
	    	displayActionFailure("Error Code: " + evt.target.errorCode);
	    };
	  }
	  
	  /**
	   * @param {number} key
	   * @param {IDBObjectStore=} store
	   * @param {string} store_name
	   */
	  function deleteRecord(key, store,store_name) {
	    console.log("deletePublication:", arguments);

	    if (typeof store == 'undefined')
	      store = getObjectStore(store_name, 'readwrite');

	    // As per spec http://www.w3.org/TR/IndexedDB/#object-store-deletion-operation
	    // the result of the Object Store Deletion Operation algorithm is
	    // undefined, so it's not possible to know if some records were actually
	    // deleted by looking at the request result.
	    var req = store.get(key);
	    req.onsuccess = function(evt) {
	      var record = evt.target.result;
	      console.log("record:", record);
	      if (typeof record == 'undefined') {
	        displayActionFailure("No matching record found");
	        return;
	      }
	      // Warning: The exact same key used for creation needs to be passed for
	      // the deletion. If the key was a Number for creation, then it needs to
	      // be a Number for deletion.
	      req = store.delete(key);
	      req.onsuccess = function(evt) {
	        console.log("evt:", evt);
	        console.log("evt.target:", evt.target);
	        console.log("evt.target.result:", evt.target.result);
	        console.log("delete successful");
	        displayActionSuccess("Deletion successful");
	        displayPubList(store);
	      };
	      req.onerror = function (evt) {
	        console.error("deletePublication:", evt.target.errorCode);
	      };
	    };
	    req.onerror = function (evt) {
	      console.error("deletePublication:", evt.target.errorCode);
	      };
	  }




})();



