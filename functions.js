function format_date(day,year,month,time){
		var mydate = {
					action: 'my_date_format',
					day: day,
					month: month,
					year: year,
					time: time
				   };
		var obj = document.getElementById('date-format');
		jQuery.post(ajaxurl, mydate, function(response)
			{
			lastChar = response.length;
			obj.innerHTML = response.substr(0,lastChar-1);
			}); 
}
function getEvent(month,p_id){
		if(p_id!='')
			{
			var data = {
					action: 'my_special_action',
					month: month,
					year: document.date_values.oldyearvalue.value,
					edit:1,
					p_id: p_id
				   };
			}
		else
			{
		var data = {
					action: 'my_special_action',
					year: document.date_values.oldyearvalue.value,
					month: month
				   };
			}
		var obj = document.getElementById('calender-days');
		jQuery.post(ajaxurl, data, function(response)
			{
			obj.innerHTML = response;
			}); 				
}
function getEventDescription(p_id,ajax_url){
		var data = {
					action: 'ui_event_description',
					p_id: p_id
				   };
		var obj = document.getElementById('ui-iz-calender-event-description');
		jQuery.post(ajax_url, data, function(response)
			{
			lastChar = response.length;
			obj.innerHTML = response.substr(0,lastChar-1);
			}); 			
}
function getEventUI(month,ajax_url){
	
			var data = {
					action: 'ui_list_events',
					month: month,
					year: document.date_values.oldyearvalue.value
				   };
		var obj = document.getElementById('ui-iz-calender-events');
		
		jQuery.post(ajax_url, data, function(response)
			{
					lastChar = response.length;
					obj.innerHTML = response.substr(0,lastChar-1);
			}); 				
}
function showEvents(element,month,p_id,interface,ajax_url){	

		jQuery('.'+document.date_values.oldmonthvalue.value, '.wrap').removeClass('active');
		jQuery('.' + element, '.wrap').addClass('active');		
		
		document.date_values.oldmonthvalue.value = element;	
		
		if(interface=='admin')
			{
			getEvent(month,p_id);	
			jQuery('.calender-days', '.wrap').slideUp(200);
			jQuery('.calender-days', '.wrap').slideDown(300);	
			}
		if(interface=='ui')
			{
			getEventUI(month,ajax_url);
			jQuery('.ui-iz-calender-events').slideUp(200);
			jQuery('.ui-iz-calender-events').slideDown(300);
			var e_des = document.getElementById('ui-iz-calender-event-description'); e_des.innerHTML = '';
			
			}				
					
}
function changYear(check,interface,ajax_url){
	var setyear = '';
	if(check == 'next')
		{
		setyear = parseInt(document.events.izc_event_year.value) + 1;
		var obj = document.getElementById('calender-year');
		obj.innerHTML = setyear;
		document.date_values.oldyearvalue.value = setyear;
		document.events.izc_event_year.value = setyear;
		}
	if(check == 'prev')
		{
		setyear = parseInt(document.events.izc_event_year.value) - 1;
		var obj = document.getElementById('calender-year');
		obj.innerHTML = setyear;
		document.date_values.oldyearvalue.value = setyear;
		document.events.izc_event_year.value = setyear;
		}
	if(interface=='admin')
		{
		format_date(document.events.izc_event_day.value,setyear,document.events.izc_event_month.value,document.events.izc_event_time.value);
		}
	else
		{
		
		var e_des = document.getElementById('ui-iz-calender-event-description'); e_des.innerHTML = '';
		var e_month = document.getElementById('ui-iz-calender-month'); e_month.innerHTML = '';
		var e_events = document.getElementById('ui-iz-calender-events'); e_events.innerHTML = '';
		jQuery('.'+document.date_values.oldmonthvalue.value, '.wrap').removeClass('active');
		}
}