<?php

//  --  PANEL: PASSWORD  --  //
$w	= (object) $words['password'];
$env->page->js->addUrl( 'http://js.ceusmedia.de/jquery/pstrength/2.1.0.min.js' );
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
$env->page->js->addScript( $script );

return HTML::DivClass( 'content-panel content-panel-form', array(
	UI_HTML_Tag::create( 'h4', $w->legend ),
	HTML::DivClass( 'content-panel-inner',
		HTML::Form( './manage/my/user/password', 'my_user_password', array(
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span8', array(
					HTML::Label( 'passwordOld', $w->labelPasswordOld, 'mandatory', $w->labelPasswordOld_title ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> "password",
						'name'			=> "passwordOld",
						'id'			=> "input_passwordOld",
						'class'			=> "span12 mandatory",
//						'required'		=> 'required',
						'value'			=> "",
						'autocomplete'	=> "off"
					) ),
				) )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span8', array(
					HTML::Label( 'passwordNew', $w->labelPasswordNew, 'mandatory', sprintf( $w->labelPasswordNew_title, $pwdMinLength ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> "password",
						'name'			=> "passwordNew",
						'id'			=> "input_passwordNew",
						'class'			=> "span12 mandatory",
//						'required'		=> 'required',
						'value'			=> "",
						'autocomplete'	=> "off"
					) ),
				) )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span8', array(
					HTML::Label( 'passwordConfirm', $w->labelPasswordConfirm, 'mandatory', $w->labelPasswordConfirm_title ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> "password",
						'name'			=> "passwordConfirm",
						'id'			=> "input_passwordConfirm",
						'class'			=> "span12 mandatory",
//						'required'		=> 'required',
						'value'			=> "",
						'autocomplete'	=> "off"
					) ),
				) )
			),
			HTML::Buttons(
				UI_HTML_Elements::Button( 'savePassword', '<i class="icon-ok icon-white"></i> '.$w->buttonSave, 'btn btn-small btn-primary' )
			)
		) )
	) )
);
?>
