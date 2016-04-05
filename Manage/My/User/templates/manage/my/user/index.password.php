<?php

//  --  PANEL: PASSWORD  --  //
$w	= (object) $words['password'];
$env->page->js->addUrl( 'http://cdn.ceusmedia.de/js/jquery/pstrength/2.1.0.min.js' );
$script		= '
$(document).ready(function(){
	if('.$pwdMinLength.'||'.$pwdMinStrength.'){
		$("#input_passwordNew").pstrength({
			minChar: '.$pwdMinLength.',
			displayMinChar: false,//'.$pwdMinLength.',
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
$env->page->js->addScript( $script );

return HTML::DivClass( 'content-panel content-panel-form', array(
	UI_HTML_Tag::create( 'h4', $w->legend ),
	HTML::DivClass( 'content-panel-inner',
		HTML::Form( './manage/my/user/password', 'my_user_password', array(
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					HTML::Label( 'passwordOld', $w->labelPasswordOld, 'mandatory', $w->labelPasswordOld_title ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> "password",
						'name'			=> "passwordOld",
						'id'			=> "input_passwordOld",
						'class'			=> "span11 mandatory",
						'required'		=> 'required',
						'value'			=> "",
						'autocomplete'	=> "off"
					) ),
				) )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					HTML::Label( 'passwordNew', $w->labelPasswordNew, 'mandatory', sprintf( $w->labelPasswordNew_title, $pwdMinLength ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> "password",
						'name'			=> "passwordNew",
						'id'			=> "input_passwordNew",
						'class'			=> "span11 mandatory",
						'required'		=> 'required',
						'value'			=> "",
						'autocomplete'	=> "off"
					) ),
				) )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					HTML::Label( 'passwordConfirm', $w->labelPasswordConfirm, 'mandatory', $w->labelPasswordConfirm_title ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> "password",
						'name'			=> "passwordConfirm",
						'id'			=> "input_passwordConfirm",
						'class'			=> "span11 mandatory",
						'required'		=> 'required',
						'value'			=> "",
						'autocomplete'	=> "off"
					) ),
				) )
			),
			HTML::Buttons(
				UI_HTML_Elements::Button( 'savePassword', '<i class="icon-ok icon-white"></i> '.$w->buttonSave, 'btn btn-small btn-primary' )
			)
		), array( 'autocomplete' => 'off' ) )
	) )
).'
<style>
#password-strength {
	width: 90% !important;
	margin-top: 0px;
	margin-bottom: 0.5em;
	background-color: #EFEFEF;
	border: 1px solid #CFCFCF;
	font-size: 0.9em;
	overflow: hidden;
	min-height: 1em;
	border-radius: 0.6em;
	}
#password-strength .password-strength-bar {
	width: 100% !important;
	padding-top: 0.1em;
	text-align: center;
	font-size: 0.9em;
	color: rgba( 0,0,0,0.75);
	opacity: 1 !important;
	}

</style>
';
?>
