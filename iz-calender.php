<?php 
/**
 * @package IZ Calender
 * @version 1.3
 */
/*
Plugin Name: IZ Calender
Plugin URI: http://wordpress.org/extend/plugins/izcalender/
Description: IZ Calendar is a user-friendly callender view of events also providing a clean admin interface which enables you to add, update, delete and manage events. Add the calender to your theme and view upcoming events. 
Author: Paul Cilliers
Version: 1.3
Author URI: www.intisul.co.za
License: GPLv2
*/
global $attooh_db_version;
$attooh_db_version = "1.0";

add_action('wp_print_styles', 'izcalender_add_stylesheet');
add_action('admin_menu', 'izcalender_menu');
add_action('wp_ajax_my_special_action', 'izcalender_events');
add_action('wp_ajax_my_date_format', 'izcalender_date_format');

if(is_admin()){
	add_action('wp_ajax_ui_list_events', 'izcalender_ui_list_events');
	add_action('wp_ajax_ui_event_description', 'izcalender_ui_event_description');
}
add_action('wp_ajax_nopriv_ui_list_events', 'izcalender_ui_list_events');
add_action('wp_ajax_nopriv_ui_event_description', 'izcalender_ui_event_description');

wp_enqueue_script('jquery');
wp_register_script('iz-calender-js', WP_PLUGIN_URL . '/izcalender/functions.js');
wp_enqueue_script('iz-calender-js');

function izcalender_add_stylesheet(){
	$myAdminStyleUrl = WP_PLUGIN_URL . '/izcalender/css/iz-calender-admin.css';
	wp_register_style('adminStyle', $myAdminStyleUrl);
	wp_enqueue_style( 'adminStyle');
	
	$izcalender_style = WP_PLUGIN_URL . '/izcalender/css/iz-calender.css';
	wp_register_style('izcalenderStyle', $izcalender_style);
	wp_enqueue_style( 'izcalenderStyle');
	
}
izcalender_add_stylesheet();

function izcalender_display_header($style = ''){
	$html = '
	<div id="iz-calender">
		<div id="iz-calender-header'.$style.'"></div>
		<div id="iz-calender-page'.$style.'">';
	echo $html;
}
function izcalender_display_footer($style = ''){
	$html = '
		</div><!--#iz-calender-page-->
		<div id="iz-calender-footer'.$style.'"></div>
	</div>';
	echo $html;
}
//creats "table prefix"_iz_calender
function izcalender_install () {
   global $wpdb;
   global $attooh_db_version;

   $table_name = $wpdb->prefix . "iz_calender";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name)
   	{
       $sql = "	CREATE TABLE `".$table_name."` (
				  `Id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `izc_event_year` int(4) DEFAULT NULL,
				  `izc_event_month` int(2) unsigned DEFAULT '0',
				  `izc_event_day` int(2) DEFAULT NULL,
				  `izc_event_time` time DEFAULT NULL,
				  `izc_event_name` varchar(50) DEFAULT NULL,
				  `izc_event_venue` varchar(50) DEFAULT NULL,
				  `izc_event_description` blob,
				  `izc_event_directions` blob,
				  `izc_event_status` varchar(15) DEFAULT NULL,
				  `izc_event_published` timestamp NULL DEFAULT NULL,
				  PRIMARY KEY (`Id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				
				INSERT INTO ".$table_name." (izc_event_year, izc_event_month, izc_event_day, izc_event_time, izc_event_name, izc_event_venue, izc_event_description)
				VALUES ('".date('Y')."','".date('n')."','".date('j')."', '".date('G').":".date('i').":00', 'Sample Event', 'To be announced', 'This is a sample event!')";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);

      $rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'name' => $welcome_name, 'text' => $welcome_text ) );
 
      add_option("attooh_db_version", $attooh_db_version);

   	}
}
//Ads admin menus
function izcalender_menu() {
	add_menu_page( 'Calender', 'IZ-Calender', 8, 'iz-calender', 'izcalender_list_events', WP_PLUGIN_URL . '/izcalender/images/izc-icon.png', 199 );
	add_submenu_page( 'iz-calender', 'Add Event','New Event', 8, 'add-event', 'izcalender_event');
}
//format timestamps to ie: "Tuesday the 12th of Oct, 2010 at 11:30"
function izcalender_date_format(){
		if($_POST['day']!='Select')
			{
			echo date("l",mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year']));
			echo ' the ';
			echo date("jS",mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year']));
			echo ' of ';
			echo date("M, Y",mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year']));
			if($_POST['time']!='Select:00')
				{
				echo ' at ';
				echo substr($_POST['time'],0,5);
				}
			}		
}
//function called for building the calender
function izcalender_event(){
	
	$months = array('','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	echo '	<form name="date_values">
				<input type="hidden" name="oldmonthvalue" value="calender-month-1" >
				<input type="hidden" name="oldyearvalue" value="'.date('Y').'" >
			</form>';
			
	if(!is_admin()) { echo '<div class="ui-iz-calender">'; }
	echo '<div id="iz-calender">';
	echo '<div class="wrap">';
	if(is_admin()) { echo '<h2>Add Event</h2>'; }
	
	echo '<div class="calender-months">';
	
	if(is_admin()) {
		echo '<div id="calender-select-year"><ul class="prev"><li><a href="javascript:changYear(\'prev\',\'admin\',\''.admin_url('admin-ajax.php').'\');">&lt;</a></li></ul><div class="calender-year" id="calender-year">'.date('Y').'</div><ul class="next"><li><a href="javascript:changYear(\'next\',\'admin\',\''.admin_url('admin-ajax.php').'\');">&gt;</a></li></ul></div>';
	}
	if(!is_admin()) {
		echo '<div id="calender-select-year"><ul class="prev"><li><a href="javascript:changYear(\'prev\',\'ui\',\''.admin_url('admin-ajax.php').'\');">&lt;</a></li></ul><div class="calender-year" id="calender-year">'.date('Y').'</div><ul class="next"><li><a href="javascript:changYear(\'next\',\'\',\''.admin_url('admin-ajax.php').'\');">&gt;</a></li></ul></div>';
	}
		
			for($i=1;$i<=4;$i++)
				{
					echo '<ul class="calender-month-row">';					
					
					for($j=1;$j<=3;$j++)
						{
							if($i==1){$k=0;}
							if($i==2){$k=3;}
							if($i==3){$k=6;}
							if($i==4){$k=9;}
							
							if(is_admin())
							{
								echo '<li id="calender-month-'.($k+$j).'" class="calender-month-'.($k+$j).'"><a href="javascript:showEvents(\'calender-month-'.($k+$j).'\',\''.($k+$j).'\',\''.$_REQUEST['p_id'].'\',\'admin\',\'\');">'.$months[$k+$j].'</a></li>';
							}
							if(!is_admin())
							{
								echo '<li id="calender-month-'.($k+$j).'" class="calender-month-'.($k+$j).'"><a href="javascript:showEvents(\'calender-month-'.($k+$j).'\',\''.($k+$j).'\',\''.$_REQUEST['p_id'].'\',\'ui\',\''.admin_url('admin-ajax.php').'\');">'.$months[$k+$j].'</a></li>';
							}
						}
					
					echo '</ul>';
				}
	
	echo '</div>';
	
	echo '<div id="calender-days" class="calender-days" style="display:none;">
				<div id="date-format" style="display:none;"></div>
				<form name="events">
					<input type="hidden" name="izc_event_day" value="">
					<input type="hidden" name="izc_event_month" value="">
					<input type="hidden" name="izc_event_year" value="'.date('Y').'">
				</form>
			</div>';
	
	echo '</div>';
	echo '</div>';
	if(!is_admin()) { echo '</div>';
	}
	else{
	
		if($_REQUEST['action']=='edit')
			{
			echo '	<script type="text/javascript">
						showEvents(\'calender-month-'.$_REQUEST['month'].'\',\''.$_REQUEST['month'].'\',\''.$_REQUEST['p_id'].'\',\''.admin.'\',\'\');
					</script>';
			}
	}
}
//user-interface listing events of seleted month
function izcalender_ui_list_events(){
		
		global $wpdb;
		
		$sgl = "SELECT * FROM " . $wpdb->prefix . "iz_calender WHERE izc_event_month=".$_POST['month']." AND izc_event_year=".$_POST['year'];
		$myrows = $wpdb->get_results($sgl);
		echo '<div id="ui-iz-calender-month">'.date("F, Y",mktime(0,0,0,$_POST['month']+1,$_POST['day'],$_POST['year'])).'</div>';
		
		echo '<ul>';
		if($myrows)
			{
			foreach($myrows as $row)
				{
					echo '<li><a href="javascript:getEventDescription(\''.$row->Id.'\',\''.admin_url('admin-ajax.php').'\');">'.date("jS",mktime(0,0,0,$_POST['month']+1,$row->izc_event_day,$_POST['year'])).' - '.$row->izc_event_name.'</a></li>';
				}
			}
		else
			{
				echo '<span style="font-weight:normal">no events sheduled.<span>';
			}
		echo '</ul>';
}
//displays event description
function izcalender_ui_event_description(){
	global $wpdb;
		
		$sgl = "SELECT * FROM " . $wpdb->prefix . "iz_calender WHERE Id=".$_POST['p_id'];
		$myrows = $wpdb->get_results($sgl);
		
		foreach($myrows as $row)
			{
				echo '<u><strong>'.$row->izc_event_name.'</strong></u><br />';
				echo '<strong>Where</strong>: '.$row->izc_event_venue.'<br />';
				echo '<strong>When</strong>: '.substr($row->izc_event_time,0,5).'<br />';
				echo $row->izc_event_description.'<br />';
			}
}
//Form for adding and updating events
function izcalender_events(){
	
	global $wpdb;
	$title = 'Insert ';
	$action = 'insert';
	$months  	= array('','January','February','March','April','May','June','July','August','September','October','November','December');
	$maxdays 	= array('','30','28','31','30','31','30','31','31','30','31','30','31');
	$time		= array('Select','05:00','05:30','06:00','06:30','07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00','20:30','21:00','21:30','22:00','22:30','23:00');
	
	if($_POST['edit']==1){
		
		$title = 'Update ';
		$action = 'edit';
		$sgl = "SELECT * FROM " . $wpdb->prefix . "iz_calender WHERE Id=".$_POST['p_id'];
		$myrows = $wpdb->get_results($sgl);
		
		foreach($myrows as $row)
			{
				$izc_event_name 		= $row->izc_event_name;
				$izc_event_venue 		= $row->izc_event_venue;
				$izc_event_description 	= $row->izc_event_description;
				$izc_event_day 			= $row->izc_event_day;
				$izc_event_month 		= $row->izc_event_month;
				$izc_event_year			= $row->izc_event_year;
				$izc_event_time			= $row->izc_event_time;
			}

	}
	
	echo '<div class="days">';
	echo '<form name="events" method="post" action="?page=iz-calender&action='.$action.'&p_id='.$_POST['p_id'].'">';
	echo '<input type="hidden" name="izc_event_month" value="'.$_POST['month'].'">';
	echo '<input type="hidden" name="izc_event_year" value="'.$_POST['year'].'">';
	echo '<h3>'.$months[$_POST['month']].'</h3>';
	echo '<div class="iz-calender-event-description">';
	echo '<label>Event Name:</label><br /><input type="text" name="izc_event_name" value="'.$izc_event_name.'"><br />';
	echo '<label>Event Venue:</label><br /><input type="text" name="izc_event_venue" value="'.$izc_event_venue.'"><br />';
	echo '<label>Event Description:</label><textarea name="izc_event_description" rows="9">'.$izc_event_description.'</textarea>';
	echo '</div>';
	echo '<div class="iz-calender-event-attributes">';
	echo '<label>Day:<label> <select name="izc_event_day" onChange="format_date(this.value,document.events.izc_event_year.value,\''.$_POST['month'].'\',document.events.izc_event_time.value);">';
			echo '<option value="Select">Select</option>';
			$add_attr ='';
			for($i=1;$i<=$maxdays[$_POST['month']];$i++)
				{		
					if($i==$izc_event_day){ $add_attr = 'selected'; } else { $add_attr = ''; }
					echo '<option value="'.$i.'" '.$add_attr.'>'.$i.'</option>';
				}
	echo '</select><br />';
	echo '<label>Time:</label><select name="izc_event_time" onChange="format_date(document.events.izc_event_day.value,document.events.izc_event_year.value,\''.$_POST['month'].'\',this.value);">';
			for($i=0;$i<count($time);$i++)
				{		
					if($time[$i]==substr($izc_event_time,0,5)){ $add_attr = 'selected'; } else { $add_attr = ''; }
					echo '<option value="'.$time[$i].':00" '.$add_attr.'>'.$time[$i].'</option>';
				}
	echo '</select>';
	echo '<div id="date-format"></div>';
	echo '<input type="submit" name="submit" value="'.$title.' Event" class="button-primary">';
	echo '</div>';
	echo '</div>';
	echo '</form>';
	echo '</div>';
	die();
	
}
//Show teasers of full event descriptions
function izcalender_view_excerpt($cotent){
	$words = str_word_count($cotent,1);
	for( $i = 0; $i<=count($words); $i++ )
		{
			$excerpt .= $words[$i].' ';
			if($i>7)
				{
					$add_ellipsis ='...';
					break;
				}
		}
	return $excerpt.$add_ellipsis;
}

function izcalender_list_events() {
	
	global $wpdb;
	izcalender_display_header('-list');
	
	//Check URL parameters and perform action
	if(isset($_REQUEST['action']))
		{
		if($_REQUEST['action']	=='trash')	{izcalender_trash ($_REQUEST['p_id']);}
		if($_REQUEST['action']	=='insert')	{izcalender_insert_update_events('insert');}
		if($_REQUEST['action']	=='edit')	{izcalender_insert_update_events('update',$_REQUEST['p_id']);}
		}
		
	if (!current_user_can('manage_options'))
		{
		wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		//Table headings
		echo '<div class="wrap">';
		echo '<h2>Events list</h2>';
		echo '<table class="iz-calender-list" width="100%" border="0" cellspacing="1" cellpadding="5">
				  <tr>
					<td width="20%" class="table-head"><strong>Event&nbsp;name</strong></td>
					<td width="20%" class="table-head"><strong>Venue</strong></td>
					<td width="30%" class="table-head"><strong>Description</strong></td>					
					<td width="30%" class="table-head"><strong>When</strong></td>
					<td width="10%" class="table-head"><strong>Edit</strong></td>
					<td width="10%" class="table-head"><strong>Trash</strong></td>
				  </tr>';
			  	 
		$sgl = "SELECT * FROM " . $wpdb->prefix . "iz_calender ORDER BY Id DESC";
		$myrows = $wpdb->get_results($sgl);
		//display events
		foreach($myrows as $row)
			{
				echo '<tr>';
				echo '<td><a href="?page=add-event&action=edit&month='.$row->izc_event_month.'&p_id='.$row->Id.'">'.$row->izc_event_name.'</a></td>';
				echo '<td>'.$row->izc_event_venue.'</td>';
				echo '<td>'.izcalender_view_excerpt($row->izc_event_description).'</td>';				
				echo '<td>'.date('l M j,Y',mktime(0,0,0,$row->izc_event_month,$row->izc_event_day,$row->izc_event_year)).' at '.substr($row->izc_event_time,0,5).'</td>';
				echo '<td><a href="?page=add-event&action=edit&month='.$row->izc_event_month.'&p_id='.$row->Id.'"><img src="'. WP_PLUGIN_URL . '/izcalender/images/edit.jpg" width="44" height="34" alt="Edit Product: '.$row->p_name.'" /></a></td>';
				echo '<td><a href="?page=iz-calender&action=trash&p_id='.$row->Id.'"><img src="'. WP_PLUGIN_URL . '/izcalender/images/trash.jpg" width="44" height="34" alt="Thrash Product: '.$row->p_name.'" /></a></td>';	
				echo '</tr>';			
			}
			
		echo '</table>';
		echo '</div>';
	//display plugin footer
	izcalender_display_footer('-list');
	
}
//function called by admin-interface to insert new, or update existing events
function izcalender_insert_update_events($action,$p_id =''){
	
	global $wpdb;
	$error = '';
	
	($_REQUEST['izc_event_name']!='') 		? $izc_event_name 		 = $_REQUEST['izc_event_name']		  : '';
	($_REQUEST['izc_event_venue']!='')		? $izc_event_venue		 = $_REQUEST['izc_event_venue'] 	  : '';
	($_REQUEST['izc_event_description']!='')? $izc_event_description = $_REQUEST['izc_event_description'] : '';
	($_REQUEST['izc_event_day']!='') 		? $izc_event_day		 = $_REQUEST['izc_event_day'] 		  : '';
	($_REQUEST['izc_event_month']!='') 		? $izc_event_month		 = $_REQUEST['izc_event_month'] 	  : '';
	($_REQUEST['izc_event_year']!='') 		? $izc_event_year		 = $_REQUEST['izc_event_year'] 	  	  : '';
	($_REQUEST['izc_event_time']!='') 		? $izc_event_time		 = $_REQUEST['izc_event_time'] 		  : '';

	if($action == 'update')
		{
			$update = $wpdb->update ( $wpdb->prefix . 'iz_calender', 
										array( 
												'izc_event_name'		=> $izc_event_name,
												'izc_event_venue' 		=> $izc_event_venue,
												'izc_event_description' => $izc_event_description,
												'izc_event_day' 		=> $izc_event_day,
												'izc_event_month' 		=> $izc_event_month,
												'izc_event_year' 		=> $izc_event_year,
												'izc_event_time' 		=> $izc_event_time
											 ), 
										array(	'Id' => $p_id),												 
										array( 	'%s', 
												'%s',
												'%s',
												'%d',
												'%d',
												'%d',
												'%s'
											 ), 
										array(  '%d')
									 );
			if($update)
				{
					echo('<div class="updated fade" id="message"><p>Event <strong> updated</strong>.</p></div>');
				}
			else
				{
					echo('<div class="error" id="message"><p>Failed! please try again.</p></div>');
				}
			}
	if($action == 'insert')
		{
		if($izc_event_name!='')
			{
			$sgl = "SELECT izc_event_name FROM " . $wpdb->prefix . "iz_calender WHERE izc_event_name='".$izc_event_name."'";
			$myrows = $wpdb->get_results($sgl);
			
			if($myrows)
				{
				foreach($myrows as $row)
					{
						echo('<div class="error" id="message"><p>Event "<strong>'.$row->izc_event_name.'</strong>" already exists.</p></div>');
					}
				}
			else
				{
				$insert = $wpdb->insert ( $wpdb->prefix . 'iz_calender', 
											array( 
													'izc_event_name'		=> $izc_event_name,
													'izc_event_venue' 		=> $izc_event_venue,
													'izc_event_description' => $izc_event_description,
													'izc_event_day' 		=> $izc_event_day,
													'izc_event_month' 		=> $izc_event_month,
													'izc_event_year' 		=> $izc_event_year,
													'izc_event_time' 		=> $izc_event_time
												 ), 
											array( 	'%s', 
													'%s',
													'%s',
													'%d',
													'%d',
													'%d',
													'%s'
											 	 ) 
										 );
				if($insert)
					{
						echo('<div class="updated fade" id="message"><p>Event <strong> added</strong>.</p></div>');
					}
				else
					{
						echo('<div class="error" id="message"><p>Failed! please try again.</p></div>');
					}
				}
			}
		}	
}
//Delete events
function izcalender_trash($delId){
	
	global $wpdb;
	$delete = $wpdb->query ( 'DELETE FROM '.$wpdb->prefix . 'iz_calender WHERE id='.$delId );
	
	if($delete)
		{
			echo('<div class="updated fade" id="message"><p>Event <strong> deleted</strong>.</p></div>');
		}
	else
		{
			echo('<div class="error" id="message"><p>Failed! please try again.</p></div>');
		}
}
//Below hook is called only on Plugin activation
register_activation_hook(__FILE__,'izcalender_install');
//funtion called by user-interface
function IZCalendar(){
	echo	'<div id="ui-izc">';
  	izcalender_event();
	echo 	'<div id="ui-iz-calender-events" class="ui-iz-calender-events" style="display:none; "></div>';
	echo	'<div id="ui-iz-calender-event-description"></div>';
	echo	'<div id="ui-iz-calender-month"></div>';
	echo	'</div>';
	echo 	'<script type="text/javascript">
						showEvents(\'calender-month-'.date('n').'\',\''.date('n').'\',\''.$_REQUEST['p_id'].'\',\'ui\',\''.admin_url('admin-ajax.php').'\');
			 </script>';
}


if(!function_exists('php_exec_pre')){
	function php_exec_pre($text) {
		$textarr = preg_split("/(<phpcode>.*<\\/phpcode>)/Us", $text, -1, PREG_SPLIT_DELIM_CAPTURE); // capture the tags as well as in between
		$stop = count($textarr);// loop stuff
		for ($phpexec_i = 0; $phpexec_i < $stop; $phpexec_i++) {
			$content = $textarr[$phpexec_i];
			if (preg_match("/^<phpcode>(.*)<\\/phpcode>/Us", $content, $code)) { // If it's a phpcode	
				$content = '[phpcode]' . base64_encode($code[1]) . '[/phpcode]';
			}
			$output .= $content;
		}
		return $output;
	}
}

### unmask code after balanceTags ###
if(!function_exists('php_exec_post')){
	function php_exec_post($text) {
		$textarr = preg_split("/(\\[phpcode\\].*\\[\\/phpcode\\])/Us", $text, -1, PREG_SPLIT_DELIM_CAPTURE); // capture the tags as well as in between
		$stop = count($textarr);// loop stuff
		for ($phpexec_i = 0; $phpexec_i < $stop; $phpexec_i++) {
			$content = $textarr[$phpexec_i];
			if (preg_match("/^\\[phpcode\\](.*)\\[\\/phpcode\\]/Us", $content, $code)) { // If it's a phpcode
				$content = '<phpcode>' . base64_decode($code[1]) . '</phpcode>';
			}
			$output .= $content;
		}
		return $output;
	}
}

### main routine ###
if(!function_exists('php_exec_process')){
	function php_exec_process($phpexec_text) {
		$phpexec_userdata = get_userdatabylogin(the_author('login',false));
		if($phpexec_userdata->user_level >= php_exec_getuserlevel()){
			$phpexec_doeval = true;
		}
	
		$phpexec_textarr = preg_split("/(<phpcode>.*<\\/phpcode>)/Us", $phpexec_text, -1, PREG_SPLIT_DELIM_CAPTURE); // capture the tags as well as in between
		$phpexec_stop = count($phpexec_textarr);// loop stuff
		for ($phpexec_i = 0; $phpexec_i < $phpexec_stop; $phpexec_i++) {
			$phpexec_content = $phpexec_textarr[$phpexec_i];
			if (preg_match("/^<phpcode>(.*)<\\/phpcode>/Us", $phpexec_content, $phpexec_code)) { // If it's a phpcode	
				$phpexec_php = $phpexec_code[1];
				if ($phpexec_doeval) {
					ob_start();
					eval("?>". $phpexec_php . "<?php ");
					$phpexec_output .= ob_get_clean();
				} else {
					$phpexec_output .= htmlspecialchars($phpexec_php);
				}
			} else {
				$phpexec_output .= $phpexec_content;
			}
		}
		return $phpexec_output;
	}
}

if(!function_exists('php_exec_getuserlevel')){
	function php_exec_getuserlevel(){
		if($level = get_option('php_exec_userlevel')){
			return $level;
		} else {
			return 9;
		}
	}
}

add_filter('content_save_pre', 'php_exec_pre', 29);
add_filter('content_save_pre', 'php_exec_post', 71);
add_filter('the_content', 'php_exec_process', 2);

add_filter('excerpt_save_pre', 'php_exec_pre', 29);
add_filter('excerpt_save_pre', 'php_exec_post', 71);
add_filter('the_excerpt', 'php_exec_process', 2);

?>