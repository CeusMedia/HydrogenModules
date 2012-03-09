<?php
$optStatus		= $words['status'] + array( '_selected' => $data['status'] );

$jsHelper		= CMF_Hydrogen_View_Helper_JavaScript::getInstance();
$jsHelper->addUrl( 'http://js.ceusmedia.de/jquery/pstrength/2.1.0.min.js', TRUE );

$script		= '
$(document).ready(function(){
	if('.$pwdMinLength.'||'.$pwdMinStrength.'){
		$("form :input#password").pstrength({
			minChar: '.$pwdMinLength.',
			displayMinChar: '.$pwdMinLength.',
			minCharText:  "'.$words['pstrength']['mininumLength'].'",
			verdicts:	[
				"'.$words['pstrength']['verdict-1'].'",
				"'.$words['pstrength']['verdict-2'].'",
				"'.$words['pstrength']['verdict-3'].'",
				"'.$words['pstrength']['verdict-4'].'",
				"'.$words['pstrength']['verdict-5'].'"
			],
			colors: ["#f00", "#f60", "#cc0", "#3c0", "#3f0"]
		});
	}
});
';
$jsHelper->addScript( $script );

$optRole		= array();
foreach( $roles as $role )
	$optRole[$role->roleId]	= $role->title;
$optRole['_selected']	= @$data['roleId'];

return '
<div class="column-main">
	<form name="editUser" action="./admin/user/add" method="post">
		<fieldset>
			<legend>'.$words['add']['legend'].'</legend>
			<ul class="input">
				<li>
					<label for="username">'.$words['add']['labelUsername'].'</label><br/>
					'.UI_HTML_Elements::Input( 'username', $data['username'], 'm' ).'
				</li>
	<!--			<li>
					<label for="email">'.$words['add']['labelEmail'].'</label><br/>
					'.UI_HTML_Elements::Input( 'email', $data['email'], 'l' ).'
				</li>-->
				<li>
					<label for="password">'.$words['add']['labelPassword'].'</label><br/>
					'.UI_HTML_Elements::Password( 'password', 'm' ).'
				</li>
				<li>
					<label for="status">'.$words['add']['labelStatus'].'</label><br/>
					'.UI_HTML_Elements::Select( 'status', $optStatus, 'm' ).'
				</li>
				<li>
					<label for="roleId">'.$words['add']['labelRole'].'</label><br/>
					'.UI_HTML_Elements::Select( 'roleId', $optRole, 'm' ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './admin/user', $words['add']['buttonCancel'], 'button cancel' ).'
				'.UI_HTML_Elements::Button( 'saveUser', $words['add']['buttonSave'], 'button save' ).'
			</div>
		</fieldset>
		<div style="clear: both"></div>
	</form>
</div>
';
?>
