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

$roleMap	= array();
foreach( $roles as $role )
	$roleMap[$role->roleId] = $role->title;

$optStatus	= UI_HTML_Elements::Options( array_reverse( $words['status'], TRUE ), @$user->status );
$optRole	= UI_HTML_Elements::Options( array_reverse( $roleMap, TRUE ), @$user->roleId );
$optGender	= UI_HTML_Elements::Options( $words['gender'], $user->gender );

$panelAdd	= '
<form name="editUser" action="./admin/user/add" method="post">
	<fieldset>
		<legend class="icon user-add">'.$w->legend.'</legend>
		<ul class="input">
			<li class="column-left-50">
				<label for="email" class="mandatory">'.$w->labelEmail.'</label><br/>
				<input type="text" name="email" id="input_email" class="max mandatory" value="'.$user->email.'"/>
			</li>
			<li class="column-left-25">
				<label for="username" class="mandatory">'.$w->labelUsername.'</label><br/>
				<input type="text" name="username" id="input_username" class="max mandatory" value="'.$user->username.'"/>
			</li>
			<li class="column-left-25">
				<label for="password">'.$w->labelPassword.'</label><br/>
				<input type="password" name="password" id="input_password" class="max"/>
			</li>

			<li class="column-clear column-left-20">
				<label for="status" class="mandatory">'.$w->labelStatus.'</label><br/>
				<select name="status" id="input_status" class="max mandatory">'.$optStatus.'</select>
			</li>
			<li class="column-left-20">
				<label for="roleId" class="mandatory">'.$w->labelRole.'</label><br/>
				<select name="roleId" id="input_roleId" class="max mandatory">'.$optRole.'</select>
			</li>

			<li class="column-clear column-left-20">
				<label for="input_gender" class="">'.$w->labelGender.'</label><br/>
				<select name="gender" id="input_gender" class="max">'.$optGender.'"</select>
			</li>
			<li class="column-left-20">
				<label for="input_salutation" class="">'.$w->labelSalutation.'</label><br/>
				<input type="text" name="salutation" id="input_salutation" class="max" value="'.$user->salutation.'"/>
			</li>
			<li class="column-left-30">
				<label for="input_firstname" class="">'.$w->labelFirstname.'</label><br/>
				<input type="text" name="firstname" id="input_firstname" class="max" value="'.$user->firstname.'"/>
			</li>
			<li class="column-left-30">
				<label for="input_surname" class="">'.$w->labelSurname.'</label><br/>
				<input type="text" name="surname" id="input_surname" class="max" value="'.$user->surname.'"/>
			</li>

			<li class="column-clear column-left-20">
				<label for="input_postcode" class="">'.$w->labelPostcode.'</label><br/>
				<input type="text" name="postcode" id="input_postcode" class="max" value="'.$user->postcode.'"/>
			</li>
			<li class="column-left-30">
				<label for="input_city" class="">'.$w->labelCity.'</label><br/>
				<input type="text" name="city" id="input_city" class="max" value="'.$user->city.'"/>
			</li>
			<li class="column-left-30">
				<label for="input_street" class="">'.$w->labelStreet.'</label><br/>
				<input type="text" name="street" id="input_street" class="max" value="'.$user->street.'"/>
			</li>
			<li class="column-left-20">
				<label for="input_number" class="">'.$w->labelNumber.'</label><br/>
				<input type="text" name="number" id="input_number" class="max" value="'.$user->number.'"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './admin/user', $w->buttonCancel, 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'saveUser', $w->buttonSave, 'button save' ).'
		</div>
	</fieldset>
	<div style="clear: both"></div>
</form>
';

$panelInfo	= '';

return '
<div class="column-control">
	'.$panelInfo.'
</div>
<div class="column-main">
	'.$panelAdd.'
</div>
<div style="clear: both"></div>
';
?>
