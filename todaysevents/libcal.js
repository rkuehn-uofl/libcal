// libcal.js
// Include this script and '<div id="events"></div>' on destination LibGuides page

$(function(){
	
	
	var jsonURL = ''; // Local url for libcal.php (ex. http://mylocalserver.edu/libcal.php)
	
	$.getJSON(jsonURL, function(data) {
		
		$('#events').empty();
		
		$.each(data, function(indexEvent, event){
				
			if (event[0] == event[0]){
				
				$('#events').append('<div id="' + event[0] + '"></div>');
				
				if ($('h3.' + event[0]).length == 0){
					
					$('#' + event[0]).append('<h3 class="' + event[0] + '">' + event[1] + '</h3>');
					
				}
				
				$('#' + event[0]).append('<div class="' + event[0] + '"><li>' + event[3] + '     ' + event[5] + '-' + event[7] + '</li></div>');
				
			}
			
		});
		
	});
	
});