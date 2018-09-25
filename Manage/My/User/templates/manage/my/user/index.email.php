<?php

//  --  PANEL: PASSWORD  --  //
$w	= (object) $words['email'];

if( !$env->getConfig()->get( 'module.manage_my_user.email.changeable' ) )
	return '';

extract( $view->populateTexts( array( 'panel.email.above', 'panel.email.below', 'panel.email.info' ), 'html/manage/my/user/' ) );

$hasInfo	= strlen( trim( strip_tags( $textPanelEmailInfo ) ) );

return HTML::DivClass( 'content-panel content-panel-form', array(
	UI_HTML_Tag::create( 'h4', $w->heading ),
	HTML::DivClass( 'content-panel-inner',
		HTML::Form( './manage/my/user/email', 'my_user_email', array(
			HTML::DivClass( 'row-fluid', array(
				HTML::DivClass( $hasInfo ? 'span6' : 'span12', array(
					HTML::DivClass( 'row-fluid',
						HTML::DivClass( 'span12', array(
							HTML::Label( 'email', $w->labelEmailOld ),
							UI_HTML_Tag::create( 'input', NULL, array(
								'type'			=> "text",
								'class'			=> "span11",
								'disabled'		=> 'disabled',
								'readonly'		=> 'readonly',
								'value'			=> htmlentities( $user->email, ENT_QUOTES, 'UTF-8' ),
							) ),
						) )
					),
					HTML::DivClass( 'row-fluid',
						HTML::DivClass( 'span12', array(
							HTML::Label( 'email', $w->labelEmailNew, 'mandatory', $w->labelEmailNew_title ),
							UI_HTML_Tag::create( 'input', NULL, array(
								'type'			=> "text",
								'name'			=> "email",
								'id'			=> "input_email",
								'class'			=> "span11 mandatory",
								'required'		=> 'required',
								'value'			=> '',
								'autocomplete'	=> "off"
							) ),
						) )
					),
					HTML::DivClass( 'row-fluid',
						HTML::DivClass( 'span12', array(
							HTML::Label( 'password', $w->labelPassword, 'mandatory', $w->labelPassword_title ),
							UI_HTML_Tag::create( 'input', NULL, array(
								'type'			=> "password",
								'name'			=> "password",
								'id'			=> "input_password_email",
								'class'			=> "span11 mandatory",
								'required'		=> 'required',
								'value'			=> '',
								'autocomplete'	=> "off"
							) ),
						) )
					),
				) ),
				$hasInfo ? HTML::DivClass( 'span6', $textPanelEmailInfo ) : '',
			) ),
			HTML::Buttons( array(
				UI_HTML_Elements::Button( 'save', '<i class="icon-ok icon-white"></i> '.$w->buttonSave, 'btn btn-primary' )
			) )
		), array( 'autocomplete' => 'off' ) )
	) )
);
?>
