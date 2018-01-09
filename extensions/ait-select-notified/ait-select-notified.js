/**
* Module ait-select-notified
*
* @author      Raphaël Saget <r.saget@axelit.fr>
* @author      David Bontoux <d.bontoux@axelit.fr>
*/

function OnSelectNotified(sLogAttCode, sTicketID)
{

	$('#v_notified').html('<label style=\"display:inline-block; margin-left:5px;\">'+sLogAttCode+'</label>');

	$.post(AddAppContext(GetAbsoluteUrlModulesRoot()+'ait-select-notified/ajax.php'), { notified : sLogAttCode, id : sTicketID},
		function(data)
		{
			$('#v_notified').html('');
			$('#v_notified').html(data);
	});
}
