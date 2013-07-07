/**
 * FoxyOWLSTracker namespace.
 */
if ("undefined" == typeof(FoxyOWLSTracker)) {
  var FoxyOWLSTracker = {};
};

/**
 * Controls the browser overlay for the Hello World extension.
 */
FoxyOWLSTracker.BrowserOverlay = {
  /**
   * Says 'Hello' to the user.
   */
  sayHello : function(aEvent) {
    let stringBundle = document.getElementById("foxyowlstracker-string-bundle");
    let message = stringBundle.getString("foxyowlstracker.greeting.label");

    window.alert(message);
  }
};
