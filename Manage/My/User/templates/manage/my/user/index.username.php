<?php

//  --  PANEL: USERNAME  --  //
$w	= (object) $words['username'];

if( !$env->getConfig()->get( 'module.manage_my_user.username.changeable' ) )
	return '';

return HTML::DivClass( 'content-panel content-panel-form', array(
	UI_HTML_Tag::create( 'h4', $w->heading ),
	HTML::DivClass( 'content-panel-inner', array(
		HTML::Form( './manage/my/user/username', 'my_user_username', array(
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					HTML::Label( 'email', $w->labelUsernameOld ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> "text",
						'class'			=> "span11 ",
						'disabled'		=> 'disabled',
						'readonly'		=> 'readonly',
						'value'			=> htmlentities( $user->username, ENT_QUOTES, 'UTF-8' ),
					) ),
				) )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					HTML::Label( 'username', $w->labelUsernameNew, 'mandatory', $w->labelUsernameNew_title ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> "text",
						'name'			=> "username",
						'id'			=> "input_username",
						'class'			=> "span11 mandatory",
						'required'		=> 'required',
						'value'			=> '',
						'autocomplete'	=> "off"
					) ),
				) )
			),
			HTML::Buttons( array(
				UI_HTML_Tag::create( 'small', 'Änderung bitte mit dem Passwort bestätigen.', array( 'class' => 'not-muted' ) ),
				HTML::DivClass( 'row-fluid',
					HTML::DivClass( 'span12', array(
						HTML::DivClass( 'input-prepend input-append',
							HTML::SpanClass( 'add-on', '<i class="not-icon-lock fa fa-fw fa-lock"></i>' ).
							'<input type="password" name="password" id="input_password_username" class="span5" required placeholder="'.$w->labelPassword.'" value="" autocomplete="off"/>'.
							UI_HTML_Elements::Button( 'save', '<i class="icon-ok icon-white"></i> '.$w->buttonSave, 'btn btn-primary' )
						)
					) )
				)
			) )
		) )
	) )
) );
?>
