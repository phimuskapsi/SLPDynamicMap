function getStoreState(ele){
	var state = ele.innerHTML;
	if(state.length > 0){
		draw_store_info(state);
	}
};

var $map = null, $markers = new Array();
	
function draw_store_info(state_name){
			
	var data = {
		action: 'draw_store',
		state_name: state_name
	};
	
	var ajaxurl = "/wp-content/themes/Divi/php/divi-ajax.php";
	jQuery.post(ajaxurl, data, function(response){
		
		try{
			var data_pack = JSON.parse(response);
		} catch(e){
			alert(e.message);
		}
		
		// No error, gtg.
		var $sdb = jQuery("#state-display-block"), $map_ele = state_name + "-map", 					
					$map_center, $map_marker, $pin, $pin_position;
		
		if($sdb.text().length === 0){
			$sdb.append(data_pack.frame);
		}
		
		if($markers.length > 0){
			for(var mk=0;mk<$markers.length;mk++){
				$markers[mk].setMap(null);
			}
			$markers = new Array();
		}
		
		for(var pc=0;pc<data_pack.pins.length;pc++){
			$pin = data_pack.pins[pc];
						
			if(pc===0){
				if($map === null){					
					$map = new google.maps.Map(document.getElementById($map_ele), {
						zoom: 6,
						center: new google.maps.LatLng($pin.center.lat, $pin.center.lng)
					});
				} else {
					$map.setCenter(new google.maps.LatLng($pin.center.lat, $pin.center.lng));
					
				}
			} 	
			
			$iw = new google.maps.InfoWindow({content: $pin.card});
			
			$map_marker = new google.maps.Marker({
				position: new google.maps.LatLng($pin.lat, $pin.lng),
				map: $map,
				title: $pin.title,
				html: $pin.card,
				infowindow: $iw
			});			
				
			google.maps.event.addListener($map_marker, 'click', function(){
				this.infowindow.open($map, this);
			});
			
			$markers.push($map_marker);
		}			
		
	});
};
		
