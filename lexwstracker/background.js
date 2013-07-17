//Copyright (c) 2011 The Chromium Authors. All rights reserved.
//Use of this source code is governed by a BSD-style license that can be
//found in the LICENSE file.

var currentUrl = "about:blank";
var noteExists = false;
var note = "";
var game_info = {};
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

var oWLStorage =  {

		DB_NAME : 'owlswgedb',
		DB_VERSION : 1,
		DB_NOTES_STORE_NAME : 'notes',
		DB_GCG_STORE_NAME : 'gcg',
		db : {},


		openDB : function(callback)  {
			var req = indexedDB.open(oWLStorage.DB_NAME, oWLStorage.DB_VERSION);
			req.onsuccess = function (evt) {

				oWLStorage.db = this.result;
				console.log("Open Succeeded");
				callback(true);

			};
			req.onerror = function (evt) {
				console.error("openDb:", evt.target.errorCode);
				callback(false);
			};

			req.onupgradeneeded = function (evt) {
				console.log("openDb.onupgradeneeded");
				oWLStorage.db = evt.currentTarget.result;
				var notes_store = evt.currentTarget.result.createObjectStore(
						oWLStorage.DB_NOTES_STORE_NAME, { keyPath: 'id', autoIncrement: true });

				notes_store.createIndex('recordid', 'recordid', { unique: true });
				notes_store.createIndex('profileid', 'profileid', { unique: false});
				notes_store.createIndex('game', 'game', { unique: false});
				notes_store.createIndex('gameid', 'gameid', { unique: false });					
				
				var gcg_store = evt.currentTarget.result.createObjectStore(
						oWLStorage.DB_GCG_STORE_NAME, { keyPath: 'id', autoIncrement: true });

				gcg_store.createIndex('recordid', 'recordid', { unique: true });
				gcg_store.createIndex('profileid', 'profileid', { unique: false});
				gcg_store.createIndex('game', 'game', { unique: false});
				gcg_store.createIndex('gameid', 'gameid', { unique: false });
				
				
				callback(true);
				
			};
		},

		/**
		 * @param {string} store_name
		 * @param {string} mode either "readonly" or "readwrite"
		 */
		getObjectStore : function (store_name, mode) {
			var tx = oWLStorage.db.transaction(store_name, mode);
			return tx.objectStore(store_name);
		},

		clearObjectStore: function (store_name) {
			var store = oWLStorage.getObjectStore(store_name, 'readwrite');
			var req = store.clear();
			req.onsuccess = function(evt) {
				oWLStorage.displayActionSuccess("Store cleared");	      
			};
			req.onerror = function (evt) {
				console.error("clearObjectStore:", evt.target.errorCode);
				oWLStorage.displayActionFailure(this.error);
			};
		},

		getNote: function (key, store, success_callback) {
			var req = store.get(key);
			req.onsuccess = function(evt) {
				var value = evt.target.result;
				if (value)
					success_callback(value.note);
			};
		},


		getGameInfo : function (key, store, success_callback) {
			var req = store.get(key);
			req.onsuccess = function(evt) {
				var value = evt.target.result;
				if (value)
					success_callback({gcg:value.gcg,screenshot:value.screenshot});
			};
		},

		/**
		 * @param {string} recordid
		 * @param {string} profileid
		 * @param {string} game
		 * @param {string} gameid
		 * @param {string} note
		 */
		addNote: function (recordid, profile, game, gameid,note,callback) {

			var obj = {recordid:recordid, profile:profile, game:game, gameid:gameid,note:note};

			var store = oWLStorage.getObjectStore(oWLStorage.DB_NOTES_STORE_NAME, 'readwrite');
			var req = store.index('recordid');
			req.get(recordid).onsuccess = function(evt) {
				var record = evt.target.result;
				var putReq;
				try {
					if (typeof evt.target.result == 'undefined') {
						putReq = store.put(obj);
					}
					else
					{
						record.note = note;						
						putReq = store.put(record);						
					}
					
				} catch (e) {
					callback(false);					
				}
				putReq.onsuccess = function (evt) {
					callback(true);
					
				};
				putReq.onerror = function() {
					console.error("addNote error", this.error);
					callback(false);
				};
			};		
			
		},


		/**
		 * @param {string} recordid
		 * @param {string} profileid
		 * @param {string} game
		 * @param {string} gameid
		 * @param {object} gcginfo
		 * @param {Blob=} screenshot
		 */
		addGCG: function (recordid, profile, game, gameid,objGCG,screenshot) {

			var obj = {recordid:recordid, profile:profile, game:game, gameid:gameid,gcginfo:gcginfo};

			var store = oWLStorage.getObjectStore(this.DB_GCG_STORE_NAME, 'readwrite');
			var req;
			try {
				req = store.put(obj);
			} catch (e) {
				oWLStorage.displayActionFailure(e);
				//throw e;
			}
			req.onsuccess = function (evt) {
				//console.log("Insertion in DB successful");
				oWLStorage.displayActionSuccess();
				//displayPubList(store);
			};
			req.onerror = function() {
				console.error("addPublication error", this.error);
				oWLStorage.displayActionFailure(this.error);
			};
		},

		/**
		 * @param {string} recordid
		 * @param {string} store_name
		 */
		deleteRecordFromStore: function (recordid,store_name) {
			var store = oWLStorage.getObjectStore(store_name, 'readwrite');
			var req = store.index('recordid');
			req.get(recordid).onsuccess = function(evt) {
				if (typeof evt.target.result == 'undefined') {
					oWLStorage.displayActionFailure("No matching record found");
					return;
				}
				deleteRecord(evt.target.result.id, store,store_name);
			};
			req.onerror = function (evt) {
				oWLStorage.displayActionFailure("Error Code: " + evt.target.errorCode);
			};
		},



		/**
		 * @param {number} key
		 * @param {IDBObjectStore=} store
		 * @param {string} store_name
		 */
		deleteRecord : function (key, store,store_name) {
			console.log("deletePublication:", arguments);

			if (typeof store == 'undefined')
				store = oWLStoragegetObjectStore(store_name, 'readwrite');

			// As per spec http://www.w3.org/TR/IndexedDB/#object-store-deletion-operation
			// the result of the Object Store Deletion Operation algorithm is
			// undefined, so it's not possible to know if some records were actually
			// deleted by looking at the request result.
			var req = store.get(key);
			req.onsuccess = function(evt) {
				var record = evt.target.result;
				console.log("record:", record);
				if (typeof record == 'undefined') {
					oWLStorage.displayActionFailure("No matching record found");
					return;
				}
				// Warning: The exact same key used for creation needs to be passed for
				// the deletion. If the key was a Number for creation, then it needs to
				// be a Number for deletion.
				req = store.delete(key);
				req.onsuccess = function(evt) {
					oWLStorage.displayActionSuccess("Deletion successful");					
				};
				req.onerror = function (evt) {
					console.error("deleteRecord:", evt.target.errorCode);
				};
			};
			req.onerror = function (evt) {
				console.error("deleteRecord:", evt.target.errorCode);
			};
		},

		/**
		 * @param {string} recordid
		 */
		getNoteByRecordId : function (recordid,callback) {
			var store = oWLStorage.getObjectStore(oWLStorage.DB_NOTES_STORE_NAME, 'readwrite');
			var req = store.index('recordid');
			req.get(recordid).onsuccess = function(evt) {
				if (typeof evt.target.result == 'undefined') {
					oWLStorage.displayActionFailure("No matching record found");
					callback("");
					return;
				}
				else
				{
					noteExists = true;
					note = evt.target.result.note;
					callback(note);
				}

			};
			req.onerror = function (evt) {
				console.error("getNoteByRecordId:", evt.target.errorCode);
				callback("");
			};
		},

		displayActionSuccess: function (msg) {
			msg = typeof msg != 'undefined' ? "Success: " + msg : "Success";
			//$('#msg').html('<span class="action-success">' + msg + '</span>');
			console.log(msg);
		},

		displayActionFailure: function (msg) {
			msg = typeof msg != 'undefined' ? "Failure: " + msg : "Failure";
			//$('#msg').html('<span class="action-failure">' + msg + '</span>');
			console.log(msg);
		}

}






