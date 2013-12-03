<?php

$optGender	= UI_HTML_Elements::Options( $words['gender'], $request->get( 'gender' ) );

$w		= (object) $words['register'];
$user	= (object) $request->getAll();
$texts	= array( 'top', 'info', 'info.company', 'info.user', 'info.conditions', 'bottom' );
extract( $view->populateTexts( $texts, 'html/auth/register.' ) );

$formTerms	= '';
if( $view->hasContent( 'auth', 'tac', 'html/' ) ){
	$checkbox		= HTML::Checkbox( 'accept_tac', 1, FALSE );
	$contentTac		= $view->loadContent( 'auth', 'tac', 'html/' );
	if( $env->getModules()->has( 'UI_Helper_Content' ) ){
		$contentTac	= View_Helper_ContentConverter::render( $env, $contentTac );
		$contentTac	= HTML::DivClass( 'well well-small framed -column-clear -max -frame-white', $contentTac );
		$formTerms		= HTML::DivClass( 'row-fluid',
			$contentTac.
			HTML::Label( 'accept_tac', $checkbox."&nbsp;".$w->labelAccept, 'checkbox mandatory' )
		);
	}
	else{
		$formTerms		= HTML::DivClass( 'row-fluid',
			HTML::Label( 'conditions', $w->labelTerms, '' ).HTML::BR.
			HTML::Text( 'conditions', $contentTac, 'max monospace', 10, TRUE )
		).HTML::DivClass( 'row-fluid',
			HTML::Label( 'accept_tac', $checkbox."&nbsp;".$w->labelAccept, 'checkbox mandatory' )
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
		if($(this).data("last") != $(this).val()){
			$(this).data("last", $(this).val());
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
	<div class="row-fluid">
		<div class="span3">
			<label for="input_username" class="mandatory">'.$w->labelUsername.'</label>
			<input type="text" name="username" id="input_username" class="span12 -max mandatory" autocomplete="off" value="'.$request->get( 'username' ).'"/>
		</div>
		<div class="span3">
			<label for="input_password" class="mandatory">'.$w->labelPassword.'</label>
			<input type="text" name="password" id="input_password" class="span12 -max mandatory" autocomplete="off" value=""/>
		</div>
		<div class="span6">
			<label for="input_email" class="'.$mandatoryEmail.'">'.$w->labelEmail.'</label>
			<input type="text" name="email" id="input_email" class="span12 -max '.$mandatoryEmail.'" value="'.$request->get( 'email' ).'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span3">
			<label for="input_gender" class="">'.$w->labelGender.'</label>
			<select name="gender" id="input_gender" class="max">'.$optGender.'"</select>
		</div>
		<div class="span2">
			<label for="input_salutation" class="">'.$w->labelSalutation.'</label>
			<input type="text" name="salutation" id="input_salutation" class="span12 -max" value="'.$request->get( 'salutation' ).'"/>
		</div>
		<div class="span3">
			<label for="input_firstname" class="'.$mandatoryFirstname.'">'.$w->labelFirstname.'</label>
			<input type="text" name="firstname" id="input_firstname" class="span12 -max '.$mandatoryFirstname.'" value="'.$request->get( 'firstname' ).'"/>
		</div>
		<div class="span4">
			<label for="input_surname" class="'.$mandatorySurname.'">'.$w->labelSurname.'</label>
			<input type="text" name="surname" id="input_surname" class="span12 -max '.$mandatorySurname.'" value="'.$request->get( 'surname' ).'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span2">
			<label for="input_postcode" class="">'.$w->labelPostcode.'</label>
			<input type="text" name="postcode" id="input_postcode" class="span12 -max numeric" value="'.$request->get( 'postcode' ).'"/>
		</div>
		<div class="span4">
			<label for="input_city" class="">'.$w->labelCity.'</label>
			<input type="text" name="city" id="input_city" class="span12 -max" value="'.$request->get( 'city' ).'"/>
		</div>
		<div class="span4">
			<label for="input_street" class="">'.$w->labelStreet.'</label>
			<input type="text" name="street" id="input_street" class="span12 -max" value="'.$request->get( 'street' ).'"/>
		</div>
		<div class="span2">
			<label for="input_number" class="">'.$w->labelNumber.'</label>
			<input type="text" name="number" id="input_number" class="span12 -max numeric" value="'.$request->get( 'number' ).'"/>
		</div>
		'.$formTerms.'
	</ul>
	<div class="buttonbar">
		'.UI_HTML_Elements::Button( 'saveUser', $w->buttonSave, 'button save' ).'
	</div>
</fieldset>';

return $textTop.'
<form name="form_auth_register" action="./auth/register" method="post">
	<div class="column-left-66">
		'.$panelUser.'
	</div>
	<div class="column-left-33">
		'.$textInfo.'
	</div>
	<div class="column-clear"></div>
</form>';
?>
