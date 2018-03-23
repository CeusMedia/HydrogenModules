<?php

//  --  PANEL: USERNAME  --  //
$w	= (object) $words['username'];

if( 0 && !$env->getConfig()->get( 'module.manage_my_user.username.changeable' ) )
	return '';

extract( $view->populateTexts( array( 'panel.username.above', 'panel.username.below', 'panel.username.info' ), 'html/manage/my/user/' ) );

return HTML::DivClass( 'content-panel content-panel-form', array(
	UI_HTML_Tag::create( 'h4', $w->heading ),
	HTML::DivClass( 'content-panel-inner', array(
		HTML::Form( './manage/my/user/username', 'my_user_username', array(
			HTML::DivClass( 'row-fluid', array(
				HTML::DivClass( 'span6', array(
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
					HTML::DivClass( 'row-fluid',
						HTML::DivClass( 'span12', array(
							HTML::Label( 'username', $w->labelPassword, 'mandatory', $w->labelPassword_title ),
							UI_HTML_Tag::create( 'input', NULL, array(
								'type'			=> "password",
								'name'			=> "password",
								'id'			=> "input_password_username",
								'class'			=> "span11 mandatory",
								'required'		=> 'required',
								'value'			=> '',
								'autocomplete'	=> "off"
							) ),
						) )
					),
				) ),
				HTML::DivClass( 'span6', $textPanelUsernameInfo ),
			) ),
			HTML::Buttons( array(
				UI_HTML_Elements::Button( 'save', '<i class="icon-ok icon-white"></i> '.$w->buttonSave, 'btn btn-primary' )
			) )
		) )
	) )
) );
?>
