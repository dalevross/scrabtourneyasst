// Copyright (c) 2011 The Chromium Authors. All rights reserved.
// Use of this source code is governed by a BSD-style license that can be
// found in the LICENSE file.

var currentUrl = "about:blank";
// Called when the url of a tab changes.
function checkForValidUrl(tabId, changeInfo, tab) {
  // If the letter 'g' is found in the tab's URL...
  if (((tab.url.indexOf('lexulous') > -1)||(tab.url.indexOf('wordscraper') > -1)|| (tab.url.indexOf('ea_scrabble_closed') > -1))&&(tab.url.indexOf('apps.facebook.com') > -1)) {
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


// Listen for any changes to the URL of any tab.
chrome.tabs.onUpdated.addListener(checkForValidUrl);


