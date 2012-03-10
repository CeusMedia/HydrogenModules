<?php
$w				= (object) $words['add'];

$optStatus		= $words['status'] + array( '_selected' => $user->status );

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
foreach( array_reverse( $roles ) as $role )
	$optRole[$role->roleId]	= $role->title;
$optRole['_selected']	= @$user->roleId;

return '
<div class="column-main">
	<form name="editUser" action="./admin/user/add" method="post">
		<fieldset>
			<legend>'.$w->legend.'</legend>
			<ul class="input">
				<li>
					<label for="username">'.$w->labelUsername.'</label><br/>
					'.UI_HTML_Elements::Input( 'username', $user->username, 'm' ).'
				</li>
	<!--			<li>
					<label for="email">'.$w->labelEmail.'</label><br/>
					'.UI_HTML_Elements::Input( 'email', $user->email, 'l' ).'
				</li>-->
				<li>
					<label for="password">'.$w->labelPassword.'</label><br/>
					'.UI_HTML_Elements::Password( 'password', 'm' ).'
				</li>
				<li>
					<label for="status">'.$w->labelStatus.'</label><br/>
					'.UI_HTML_Elements::Select( 'status', $optStatus, 'm' ).'
				</li>
				<li>
					<label for="roleId">'.$w->labelRole.'</label><br/>
					'.UI_HTML_Elements::Select( 'roleId', $optRole, 'm' ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './admin/user', $w->buttonCancel, 'button cancel' ).'
				'.UI_HTML_Elements::Button( 'saveUser', $w->buttonSave, 'button save' ).'
			</div>
		</fieldset>
		<div style="clear: both"></div>
	</form>
</div>
';
?>
