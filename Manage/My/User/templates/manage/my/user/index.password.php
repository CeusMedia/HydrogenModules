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

return '
<div class="content-panel">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">'.
		HTML::Form( './manage/my/user/password', 'my_user_password',
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span6',
					HTML::Label( 'passwordOld', $w->labelPassword, 'mandatory' ).
					'<input type="password" name="passwordOld" id="input_passwordOld" class="span12 mandatory" required value="" autocomplete="off"/>'
		//			HTML::Password( 'passwordOld', 'span12 mandatory' )
				).
				HTML::DivClass( 'span6',
					HTML::Label( 'passwordNew', $w->labelPasswordNew, 'mandatory' ).
					'<input type="password" name="passwordNew" id="input_passwordNew" class="span12 mandatory" required value="" autocomplete="off"/>'
		//			HTML::Input( 'passwordNew', NULL, 'span12 mandatory' )
				)
			).
			HTML::Buttons(
				UI_HTML_Elements::Button( 'savePassword', '<i class="icon-ok icon-white"></i> '.$w->buttonSave, 'btn btn-small btn-primary' )
			)
		).'
	</div>
</div>';
?>
