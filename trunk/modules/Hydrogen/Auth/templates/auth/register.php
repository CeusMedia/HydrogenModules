<?php

$optGender	= UI_HTML_Elements::Options( $words['gender'], $request->get( 'gender' ) );

$w		= (object) $words['register'];
$user	= (object) $request->getAll();
$texts	= array( 'top', 'info', 'info.company', 'info.user', 'info.conditions', 'bottom' );
$text	= $view->populateTexts( $texts, 'html/auth/register.' );

$formTerms	= '';
if( $view->hasContent( 'auth', 'tac', 'html/' ) ){
	$contentTac		= $view->loadContent( 'auth', 'tac', 'html/' );
	if( $env->getModules()->has( 'UI_Helper_Content' ) ){
		$contentTac	= View_Helper_ContentConverter::render( $env, $contentTac );
		$contentTac	= HTML::DivClass( 'framed column-clear max', $contentTac );
		$formTerms		= HTML::LiClass( 'column-clear',
			$contentTac.
			HTML::Checkbox( 'accept_tac', 1, FALSE, 'mandatory' ).'&nbsp;'.
			HTML::Label( 'accept_tac', $w->labelAccept, 'mandatory' )
		);
	}
	else{
		$formTerms		= HTML::LiClass( 'column-clear',
			HTML::Label( 'conditions', $w->labelTerms, '' ).HTML::BR.
			HTML::Text( 'conditions', $contentTac, 'max monospace', 10, TRUE )
		).HTML::Li(
			HTML::Checkbox( 'accept_tac', 1, FALSE ).'&nbsp;'.
			HTML::Label( 'accept_tac', $w->labelAccept, 'mandatory' )
		);
	}
}

$mandatoryEmail		= $config->get( 'module.users.email.mandatory' ) ? 'mandatory' : '';
$mandatoryFirstname	= $config->get( 'module.users.firstname.mandatory' ) ? 'mandatory' : '';
$mandatorySurname	= $config->get( 'module.users.surname.mandatory' ) ? 'mandatory' : '';

$panelUser	= '
<style>
div.framed {
	border: 1px solid gray;
	border-radius: 0.4em;
	padding: 1em 2em;
	overflow: auto;
	height: 200px;
	}
input.state-good {
	background-color: #EFFFF7;
	}
input.state-bad {
	background-color: #FFDFDF;
	}
</style>
<script>
$(document).ready(function(){
	$("#input_username").keyup(function(){
		var lenMin = config.module_users_name_length_min;
		var lenMax = config.module_users_name_length_max;
		var length = $(this).val().length;
		$(this).removeClass("state-good").removeClass("state-bad");
		if(length && lenMin <= length && length <= lenMax ){
			$.ajax({
				url: "./auth/ajaxUsernameExists",
				method: "post",
				data: {username: $(this).val()},
				dataType: "json",
				context: this,
				success: function(response){
					$(this).addClass(response ? "state-bad" : "state-good");
				}
			});
		}
		
	});
	if($("#input_accept_tac.mandatory").size()){
		$("button.save").attr("disabled","disabled");
		$("#input_accept_tac").change(function(){
			if($(this).is(":checked"))
				$("button.save").removeAttr("disabled");
			else
				$("button.save").attr("disabled","disabled");			
		});
	}
});
</script>
<fieldset>
	<legend class="register">'.$w->legend.'</legend>
	<ul class="input">
		<li class="column-left-25">
			<label for="input_username" class="mandatory">'.$w->labelUsername.'</label><br/>
			<input type="text" name="username" id="input_username" class="max mandatory" autocomplete="off" value="'.$request->get( 'username' ).'"/>
		</li>
		<li class="column-left-25">
			<label for="input_password" class="mandatory">'.$w->labelPassword.'</label><br/>
			<input type="text" name="password" id="input_password" class="max mandatory" autocomplete="off" value=""/>
		</li>
		<li class="column-left-50">
			<label for="input_email" class="'.$mandatoryEmail.'">'.$w->labelEmail.'</label><br/>
			<input type="text" name="email" id="input_email" class="max '.$mandatoryEmail.'" value="'.$request->get( 'email' ).'"/>
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
			<label for="input_firstname" class="'.$mandatoryFirstname.'">'.$w->labelFirstname.'</label><br/>
			<input type="text" name="firstname" id="input_firstname" class="max '.$mandatoryFirstname.'" value="'.$request->get( 'firstname' ).'"/>
		</li>
		<li class="column-left-30">
			<label for="input_surname" class="'.$mandatorySurname.'">'.$w->labelSurname.'</label><br/>
			<input type="text" name="surname" id="input_surname" class="max '.$mandatorySurname.'" value="'.$request->get( 'surname' ).'"/>
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
		'.$formTerms.'
	</ul>
	<div class="buttonbar">
		'.UI_HTML_Elements::Button( 'saveUser', $w->buttonSave, 'button save' ).'
	</div>
</fieldset>';

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
