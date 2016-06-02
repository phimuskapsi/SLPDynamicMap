<?php
  add_shortcode('job_listing', 'job_listing_function');
	add_shortcode('get_location_boxes', 'get_location_boxes');
	add_shortcode('location_page', 'location_page');
	add_shortcode('get_state_list', 'get_state_list');
	add_shortcode('get_states', 'get_states_list');
	
  function get_location_boxes($atts){
		$a = shortcode_atts( array(
			'page' => ''
		), $atts );
		
		$page = $a['page'];
		
		if($page !== ""){
			$page = str_replace("-", " ", $page);
			$sql = "SELECT sl_store, sl_address, sl_address2, sl_city, sl_state, sl_zip, sl_country, sl_latitude, sl_longitude, sl_phone, sl_fax, sl_pages_url
					FROM wp_store_locator
					WHERE sl_state LIKE '%".$page."%' AND sl_tags NOT LIKE '%inactive%'";
			
			$sql_result = mysql_query($sql);
			$blocks = "";
			$dcol_cnt = 0;
			$drows = array();
			while($row = mysql_fetch_array($sql_result)){
				//drow and dcol are drawn cols and rows		
				$col = "";
				
				if($dcol_cnt % 2 === 0){
					// Then it's a left col
					$drow = array();
					$col = draw_location_column_box($row, "left");	
					$drow[] = $col;				
				} else {
					$col = draw_location_column_box($row, "right");		
					$drow[] = $col;
					$drows[] = $drow;
				}
	
				$dcol_cnt++;
			}
	
			// If there is an odd amount, add a blank col and finish the row.
			if($dcol_cnt % 2 === 1){
				$drow[] = do_shortcode("[one_half_last][/one_half_last]");
				$drows[] = $drow;
			}		
			
			for($d=0;$d<count($drows);$d++){
				$bld = $drows[$d];
				$blocks .= "<div class='dx_row'><div class='dx_full_col'>" . $bld[0] . $bld[1] . "</div></div>";
			}
			
			if($blocks === ""){
				$blocks = "Check Module Config. Location Name is missing or incorrect";
			}
		} else {
			$blocks = "Check Module Config. Location Name is missing or incorrect";
		}
		
		return $blocks;
	}
	
	function draw_location_column_box($row, $side){
		$block = "";
		$st = $row['sl_store'];
		$stcp = strpos($st, ",");
		$stpp = strpos($st, "(");
		
		$simple_store = $st;
		$content = "
			<p>
				<strong>
					<a href='" . $row['sl_pages_url'] . "' target='_blank'>" 
					 .	$simple_store .
					"</a>
				</strong>
			</p>
			<p>"
				 . $row['sl_address'] . 
			   "<br>"
				 . $row['sl_address2'] . 
			   "<br>"
				 . $row['sl_city'] . ", " . $row['sl_state'] . " " . $row['sl_zip'] 		   
				 . ($row['sl_phone'] === "" ? "" : "<br><br><strong>Phone: </strong>" . $row['sl_phone']) 
				 . ($row['sl_fax'] === "" ? "" : "<br><strong>Fax: </strong>" . $row['sl_fax']) .
			"</p>
		";
			
		if($side === "left"){
			$block .= do_shortcode("[one_half]" .  $content . "[/one_half]");
		} else {
			$block .= do_shortcode("[one_half_last]" .  $content . "[/one_half_last]");
		}
		
		return $block;
	}


	 
	
	add_action('MY_AJAX_HANDLER_draw_store', 'draw_store');
	add_action('MY_AJAX_HANDLER_nopriv_draw_store', 'draw_store');
	
	function draw_store(){
		global $wpdb;
		
		$state		  = $_POST['state_name'];
		$location_sql = "	SELECT  sl_store, sl_address, sl_address2, 
									sl_city, sl_state, sl_zip, sl_country, sl_latitude, 
									sl_longitude, sl_phone, sl_fax, sl_pages_url
							FROM wp_store_locator 
							WHERE sl_state = '" . $state . "' 
							ORDER BY sl_store";
					
		$location_pack = $wpdb -> get_results($location_sql, "ARRAY_A");
		
		switch($state){
			case "Georgia":
				$center = array("lat" => "33.040619", "lng" => "-83.643074");
			break;
			
			case "Idaho":
				$center = array("lat" => "44.240459", "lng" => "-114.478828");
			break;
			
			case "Indiana":
				$center = array("lat" => "39.849426", "lng" => "-86.258278");
			break;
			
			case "Mississippi":
				$center = array("lat" => "32.741646", "lng" => "-89.678696");
			break;
			
			case "North Carolina":
				$center = array("lat" => "35.630066", "lng" => "-79.806419");
			break;
			
			case "Oregon":
				$center = array("lat" => "44.572021", "lng" => "-122.070938");
			break;
			
			case "South Carolina":
				$center = array("lat" => "33.856892", "lng" => "-80.945007");
			break;
			
			case "Tennessee":
				$center = array("lat" => "35.747845", "lng" => "-86.692345");
			break;
			
			case "Utah":
				$center = array("lat" => "40.150032", "lng" => "-111.862434");
			break;
			
			case "Virginia":
				$center = array("lat" => "37.769337", "lng" => "-78.169968");
			break;
			
			case "Washington":
				$center = array("lat" => "47.400902", "lng" => "-121.490494");
			break;
		}

		if(count($location_pack) > 0){
			$head = "	<h2>" . $state . "</h2>	";
			$map_pins = array();
			$map_frame = "";
				
			foreach($location_pack as $lr){	
				$address = 	"<a href='" . $lr['sl_pages_url'] . "' target='_blank'>" 
								.	$lr['sl_store'] .
							"</a><br>" . 
							$lr['sl_address'] . ($lr['sl_address2'] !== "" ? "<br>" . $lr['sl_address2'] . "<br>" : "<br>") . 
							$lr['sl_city'] . ", " . $lr['sl_state'] . " " . $lr['sl_zip'];
				$map_pins[] = array("lat" => $lr['sl_latitude'], "lng" => $lr['sl_longitude'], "title" => $lr['sl_store'], "card" => $address, "center" => $center);
			}
			
			$map_frame = "<div id='" . $state . "-map' style='width:100%;height:400px;'></div>";	
			
			$map_lp_final = "	<div class='dx_row'>
									<div class='dx_full_col'>" . $map_frame . "</div>
								</div>";
			$loc_boxes = do_shortcode("[get_location_boxes page='" . $state . "']");			
			$blocks = $head . $map_lp_final . $loc_boxes;
		}
		
		$map_pack = array("pins" => $map_pins, "frame" => $blocks);
		echo json_encode($map_pack);
		wp_die();
	}
	
	function get_states_list(){
		$sql = "SELECT DISTINCT sl_state FROM wp_store_locator ORDER BY sl_state";
		$sql_result = mysql_query($sql);
		$blocks = "";
	  
		while($row = mysql_fetch_array($sql_result)){			
			$blocks .= $row['sl_state'] . ",";
				
		}
		return $blocks;
	}
	
	function get_state_list(){
		
	
		$sql = "SELECT DISTINCT sl_state FROM wp_store_locator ORDER BY sl_state";
		$sql_result = mysql_query($sql);
		$blocks = "<div class='dx_head_row'><div class='dx_head_col'><h2>Please Select a State:</h2></div></div>";
	  
		$cols = 0;
		$items = 0;
		$col  = "<div class='dx_quarter_col'>";
		$col_row = "<div class='dx_row_1'>";
		$open = TRUE;
		$blocks .= $col_row . $col;
		
		while($row = mysql_fetch_array($sql_result)){			
			$state = $row['sl_state'];

		
			if($state !== "" && $state !== NULL){
				$btn   = "	<div class=\"dynamix_button_container\">
								<a class=\"dynamix_button\" href=\"#state-display-block\" onclick=\"javascript:getStoreState(this);\">" . $state. "</a>
							</div>";
				
				
				if($items >= 3){
					$items = 0;				
					
					if($cols > 4){
						$blocks .= $btn . "</div></div><div class='dx_row'>"; // Add button, close column, close row, start new row
					} else {
						$blocks .= $btn . "</div>" . $col; // Add button, close column, and start another					
					}
									
					$cols++;
				} else {
					$blocks .= $btn; // add button and don't close column
					$items++;
				}
			}
		}
		
		
		$blocks .= "</div></div>";
		return $blocks . "<div class='dx_row'><div class='dx_full_col'><div id='state-display-block'></div></div></div>";
	}
	
	// Gravity Form + SLP = Location Drop-Down
	
	add_filter('gform_pre_render', 'get_centers');
	add_filter('gform_pre_validation', 'get_centers');
	add_filter('gform_pre_submission_filter', 'get_centers');
	add_filter('gform_admin_pre_render', 'get_centers');

	function get_centers($form){
		//throw new Exception(var_dump($form));
		//exit;
		
		if($form['title'] != "Contact Us") return $form;
		
		foreach($form['fields'] as &$field){

			if ($field['cssClass'] !== 'get_centers' ){
				continue;
			}
			
			$sql = "SELECT sl_id, sl_store, sl_email FROM wp_store_locator ORDER BY sl_store";
			$sql_result = mysql_query($sql);
			$choices = array();
			while($row = mysql_fetch_array($sql_result)){	
				$email = $row['sl_email'];
				if($email === '' || is_null($email)){
					$email = "info@orianna.com";
				}
				
				$choices[] = array('text' => $row['sl_store'], 'value' => $email);
			}
			
			//throw new Exception(var_dump($choices));
			//exit;

			$field['placeholder'] = 'Select a Center:';
			$field['choices'] = $choices;
		}
		return $form;
	}
