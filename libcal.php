<?php
	
	// Local variables
	$libguidesDomain = ''; // Domain for LibGuides site (ex. http://mylibguides.edu)
	$apiID = ''; // LibCal API id
	$apiKey = ''; // LibCal API key
	
	// Avoid cross domain errors
	header("Access-Control-Allow-Origin: ".$libguidesDomain);
	
	// LibCal API: GET rooms - add room image urls to array
	$json = file_get_contents('https://api2.libcal.com/1.0/rooms?iid='.$apiID.'&key='.$apiKey);
	$obj = json_decode($json);
	$room = $obj->rooms;
	
	$imageArray = array();
	foreach($room as $value){
		
		$image = $value->image_url;
		
		if (substr( $image, 0, 2 ) === '//'){
			
			$image = 'https:' . $image;
			
		}
		
		$imageArray[$value->room_id] = $image;
		
	}
	
	// LibCal API: GET room_groups - add rooms to array
	$json = file_get_contents('https://api2.libcal.com/1.0/room_groups/?iid='.$apiID.'&key='.$apiKey);
	$obj = json_decode($json);
	$group = $obj->groups;
	
	$groupArray = array();
	foreach($group as $id){
		
		$groupID = $id->group_id;
		array_push($groupArray,$groupID);
		
	}
	
	// LibCal API: GET room_bookings_nickname - add events array
	$eventArray = array();
	foreach($groupArray as $groupID){
		
		$json = file_get_contents('https://api2.libcal.com/1.0/room_bookings_nickname/?iid='.$apiID.'&group_id='.$groupID.'&key='.$apiKey);
		
		$obj = json_decode($json,true);
		
		$timeslots = $obj->bookings->timeslots;
		
		if (!empty($obj[bookings][timeslots])) {
			
			foreach($obj[bookings][timeslots] as $bookingIndex => $booking){
				
				array_push($eventArray,$booking);
				
			}
			
		}
		
	}
	
	// Sort event data
	$sortRoom = array();
	foreach ($eventArray as $sortRoom){
		$sortRoomArray[] = $sortRoom['room_name'];
	}
	$sortStart = array();
	foreach ($eventArray as $sortStart){
		$sortStartArray[] = $sortStart['booking_start'];
	}
	$sortLabel = array();
	foreach ($eventArray as $sortLabel){
		$sortLabelArray[] = $sortLabel['room_name'];
	}
	array_multisort($sortRoomArray, SORT_ASC, $sortStartArray, SORT_ASC, $sortLabelArray, SORT_ASC, $eventArray);
	
	$sortArray = array();
	foreach($eventArray as $eventIndex => $event){
		
		$previousEvent = $eventArray[$eventIndex-1];
		$nextEvent = $eventArray[$eventIndex+1];
		
		if(($event[room_id] != $previousEvent[room_id]) || ($event[booking_label] != $previousEvent[booking_label])){
			
			$roomID = $event[room_id];
			$roomName = $event[room_name];
			$bookingLabel = $event[booking_label];
			$bookingStart = $event[booking_start];
			
			$date = new DateTime($bookingStart);
			$normStart = $date->format('h:iA');
			
			foreach($imageArray as $imageIndex => $image){
			
				if($imageIndex == $event[room_id]){
				
					$roomImage = $image;
				
				}
			
			}
			
			array_push($sortArray,$roomID,$roomName,$roomImage,$bookingLabel,$bookingStart,$normStart);
			
			
		}
		
		// Find first and last timeslot of each event for event begin/end times
		if(($event[room_id] != $nextEvent[room_id]) || ($event[booking_label] != $nextEvent[booking_label])){
			
			$bookingEnd = $event[booking_end];
			$bookingCreated = $event[booking_created];
			
			$date = new DateTime($bookingEnd);
			$normEnd = $date->format('h:iA');
			
			array_push($sortArray,$bookingEnd,$normEnd,$bookingCreated);
			
		}
		
	}
	
	// Format array for JSON output
	$newEventArray = array_chunk($sortArray, 9);
	
	// Create JSON output
	echo json_encode($newEventArray);
	
?>