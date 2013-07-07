
var lexwstracker = document.getElementById("lexwstracker");
var $dialog = $('#lexwstracker');
var loadinghtml = '<div><span>Loading...</span><br/><img src="trackerloading.gif" /></div>';

self.port.on("message", function onMessage(message) {
	$dialog.html(message);
});

self.port.on("loading", function onLoading() {
	$dialog.html(loadinghtml);
});

self.port.on(
				"usedtiles",
				function onGotTiles(usedtiles) {
					var dist = {
						"A" : 9,
						"B" : 2,
						"C" : 2,
						"D" : 4,
						"E" : 12,
						"F" : 2,
						"G" : 3,
						"H" : 2,
						"I" : 9,
						"J" : 1,
						"K" : 1,
						"L" : 4,
						"M" : 2,
						"N" : 6,
						"O" : 8,
						"P" : 2,
						"Q" : 1,
						"R" : 6,
						"S" : 4,
						"T" : 6,
						"U" : 4,
						"V" : 2,
						"W" : 2,
						"X" : 1,
						"Y" : 2,
						"Z" : 1,
						"blank" : 2
					};
					var left = {};

					var used = usedtiles;
					var tilecount = 0;
					for ( var letter in used) {
						if ((dist[letter] - used[letter]) > 0) {
							left[letter] = dist[letter] - used[letter];
							tilecount += dist[letter] - used[letter];
						}
					}
					var color = (game === "lexulous") ? "#2BB0E8" : "red";
					var index = 0;
					var html = '';
					var inbag = (tilecount > 7) ? tilecount - 7 : 0;
					html = html
							+ '<span style="font-weight:bold;">Tile Count: '
							+ tilecount + '</span><span> (' + inbag
							+ ' in bag)</span><br/>';
					for ( var letter in left) {
						if ((index % 8) === 0) {
							html = html
									+ '<div style="float:left;padding:10px">';
						}
						html = html + '<span style="color:' + color
								+ '" title="Total: ' + dist[letter] + '">'
								+ letter + ' - ' + left[letter]
								+ '</span><br/>';
						index++;
						if (((index) % 8 === 0 && index !== 0)
								|| index === Object.keys(left).length) {
							html = html + '</div>';
						}
					}

					// }

					// $html = $html . 'The tile tracker is currently down for
					// maintenance. <br/>';

					html = html + '<div style="clear:both"/>';

					// html = html + '<span id="trackerstat">' . $status .
					// '</span><br/><br/>';

					html = html
							+ '<span>Brought to you by<br/><a href="http://www.facebook.com/lexandws?ref=ts" target="_blank" ><span style="text-decoration:underline;color:blue;">Lexulous/Wordscraper Tournaments</span></a></span>';

					html = html
							+ '<br/><br/><span>Contact <a href="http://www.facebook.com/dvross" target="_blank" ><span style="text-decoration:underline;color:blue;">Dale V. Ross</span></a> for support or suggestions</span>';
					var d = new Date();

					var suffix = '<br/><span> Retrieved at '
							+ d.toLocaleString() + '</span>';

					html = html + suffix;

					$dialog.html(html);
					

				});