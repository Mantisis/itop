// (c) Combodo SARL 2011 - 2016

function OnSelectNotified(sLogAttCode, sTicketID)
{

	$('#v_notified').html('<label style=\"display:inline-block; margin-left:5px;\">'+sLogAttCode+'</label>');

	$.post(AddAppContext(GetAbsoluteUrlModulesRoot()+'ait-select-notified/ajax.php'), { notified : sLogAttCode, id : sTicketID}, 
		function(data)
		{
			$('#v_notified').html('');
			$('#v_notified').html(data);
			/*
			for(var i= 0; i < data.length; i++)
			{
				console.log(data[i]);
				var checkbox = document.createElement("input");
				checkbox.type = "checkbox";    // make the element a checkbox
				//checkbox.name = "slct[]";      // give it a name we can check on the server side
				checkbox.value = true;
				checkbox.id = "rand"+data[i];
				$('#v_notified').append(checkbox);

				var label = document.createElement('label')
				label.htmlFor = "rand"+data[i];
				label.append(document.createTextNode(data[i]));			}*/
	
	});
	//$('#field_2_public_log div.caselog_input_header').append('<div id=\"select_sender_value')
	/*if ($('#precanned_button').attr('disabled')) return; // Disabled, do nothing
	if ($('#precanned_dlg').length == 0)
	{
		$('body').append('<div id="precanned_dlg"></div>');
	}
	$('#precanned_button').attr('disabled', 'disabled');
	$('#v_precanned').html('<img src="../images/indicator.gif" />');

	oWizardHelper.UpdateWizard();
	var theMap = { 'json': oWizardHelper.ToJSON(),
			   operation: 'select_precanned',
			   log_attcode: sLogAttCode
			 };
	
	// Run the query and get the result back directly in HTML
	$.post( AddAppContext(GetAbsoluteUrlModulesRoot()+'precanned-replies/ajax.php'), theMap, 
		function(data)
		{
			var dlg = $('#precanned_dlg');
			dlg.html(data);
			dlg.dialog({ width: 'auto', height: 'auto', autoOpen: false, modal: true, title: Dict.S('UI:Dlg-PickAReply'), resizeStop: function(event, ui) { PrecannedUpdateSizes(); }, close: function() {OnClosePrecannedReply(sLogAttCode);} });
			var data_area = $('#dr_precanned_select');
			data_area.css('max-height', (0.5*$(document).height())+'px'); // Stay within the document's boundaries
			data_area.css('overflow', 'auto'); // Stay within the document's boundaries
			dlg.dialog('open');
			PrecannedDoSearch(sLogAttCode);
			$('#precanned_select').resize(function() { PrecannedUpdateSizes(); });
		},
		'html'
	);*/
}