<?php

$optGender	= UI_HTML_Elements::Options( $words['gender'], $request->get( 'gender' ) );

$contentTac	= "";
if( $view->hasContent( 'auth', 'tac' ) )
	$contentTac	= $view->loadContent( 'auth', 'tac' );

$w		= (object) $words['register'];
$user	= (object) $request->getAll();
$text	= $view->populateTexts( array( 'top', 'info', 'info.company', 'info.user', 'info.conditions', 'bottom' ), 'html/auth/register.' );

$panelUser	= '<fieldset>
	<legend class="register">'.$w->legend.'</legend>
	<ul class="input">
		<li class="column-left-50">
			<label for="input_email" class="mandatory">'.$w->labelEmail.'</label><br/>
			<input type="text" name="email" id="input_email" class="max mandatory" value="'.$request->get( 'email' ).'"/>
		</li>
		<li class="column-left-25">
			<label for="input_username" class="mandatory">'.$w->labelUsername.'</label><br/>
			<input type="text" name="username" id="input_username" class="max mandatory" value="'.$request->get( 'username' ).'"/>
		</li>
		<li class="column-left-25">
			<label for="input_password" class="mandatory">'.$w->labelPassword.'</label><br/>
			<input type="text" name="password" id="input_password" class="max mandatory" value=""/>
		</li>

		<li class="column-clear column-left-20">
			<label for="input_gender" class="">'.$w->labelGender.'</label><br/>
			<select name="gender" id="input_gender" class="max">'.$optGender.'"</select>
		</li>
		<li class="column-left-20">
			<label for="input_salutation" class="">'.$w->labelSalutation.'</label><br/>
			<input type="text" name="salutation" id="input_salutation" class="max" value="'.$request->get( 'salutation' ).'"/>
		</li>
		<li class="column-left-30">
			<label for="input_firstname" class="">'.$w->labelFirstname.'</label><br/>
			<input type="text" name="firstname" id="input_firstname" class="max" value="'.$request->get( 'firstname' ).'"/>
		</li>
		<li class="column-left-30">
			<label for="input_surname" class="">'.$w->labelSurname.'</label><br/>
			<input type="text" name="surname" id="input_surname" class="max" value="'.$request->get( 'surname' ).'"/>
		</li>

		<li class="column-clear column-left-20">
			<label for="input_postcode" class="">'.$w->labelPostcode.'</label><br/>
			<input type="text" name="postcode" id="input_postcode" class="max" value="'.$request->get( 'postcode' ).'"/>
		</li>
		<li class="column-left-30">
			<label for="input_city" class="">'.$w->labelCity.'</label><br/>
			<input type="text" name="city" id="input_city" class="max" value="'.$request->get( 'city' ).'"/>
		</li>
		<li class="column-left-30">
			<label for="input_street" class="">'.$w->labelStreet.'</label><br/>
			<input type="text" name="street" id="input_street" class="max" value="'.$request->get( 'street' ).'"/>
		</li>
		<li class="column-left-20">
			<label for="input_number" class="">'.$w->labelNumber.'</label><br/>
			<input type="text" name="number" id="input_number" class="max" value="'.$request->get( 'number' ).'"/>
		</li>
	</ul>
	<div class="buttonbar">
		'.UI_HTML_Elements::Button( 'saveUser', $w->buttonSave, 'button save' ).'
	</div>
</fieldset>
';

$panelConditions	= '';/*HTML::Fields(
	HTML::Legend( $w->legendFinish, 'register' ).
	HTML::UlClass( 'input',
		HTML::Li(
			HTML::Label( 'conditions', $w->labelTerms, '' ).HTML::BR.
			HTML::Text( 'conditions', $contentTac, 'max monospace', 10, TRUE )		
		).
		HTML::Li(
			HTML::Checkbox( 'accept_tac', 1, FALSE ).'&nbsp;'.
			HTML::Label( 'accept_tac', $w->labelAccept )
		)
	).
	HTML::Buttons(
		HTML::Button( 'saveUser', $w->buttonSave, 'button save' )
	)
);*/

return $text['top'].'
<form name="form_auth_register" action="./auth/register" method="post">
	<div class="column-left-66">
		'.$panelUser.'
	</div>
	<div class="column-left-33">
		'.$text['info'].'
	</div>
	<div class="column-clear"></div>
</form>';
?>