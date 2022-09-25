<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

//  --  PANEL: PASSWORD  --  //
$w	= (object) $words['email'];

if( !$env->getConfig()->get( 'module.manage_my_user.email.changeable' ) )
	return '';

extract( $view->populateTexts( array( 'panel.email.above', 'panel.email.below', 'panel.email.info' ), 'html/manage/my/user/' ) );

$hasInfo	= strlen( trim( strip_tags( $textPanelEmailInfo ) ) );

return HTML::DivClass( 'content-panel content-panel-form', array(
	HtmlTag::create( 'h4', $w->heading ),
	HTML::DivClass( 'content-panel-inner',
		HTML::Form( './manage/my/user/email', 'my_user_email', array(
			HTML::DivClass( 'row-fluid', array(
				HTML::DivClass( $hasInfo ? 'span6' : 'span12', array(
					HTML::DivClass( 'row-fluid',
						HTML::DivClass( 'span12', array(
							HTML::Label( 'email', $w->labelEmailOld ),
							HtmlTag::create( 'input', NULL, array(
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
							HtmlTag::create( 'input', NULL, array(
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
				) ),
				$hasInfo ? HTML::DivClass( 'span6', $textPanelEmailInfo ) : '',
			) ),
			HTML::Buttons( array(
				HtmlTag::create( 'small', $w->labelPasswordCurrent_title, array( 'class' => 'not-muted' ) ),
				HTML::DivClass( 'row-fluid',
					HTML::DivClass( 'span6', array(
						HTML::DivClass( 'input-prepend input-append',
							HTML::SpanClass( 'add-on', '<i class="fa fa-fw fa-lock"></i>' ).
							HtmlTag::create( 'input', '', array(
								'type'			=> 'password',
								'name'			=> 'password',
								'id'			=> 'input_password',
								'class'			=> 'span7',
								'required'		=> 'required',
								'autocomplete'	=> 'current-password',
								'placeholder'	=> $w->labelPasswordCurrent,
							) ).
							HtmlElements::Button( 'saveUser', '<i class="fa fa-fw fa-check"></i> '.$w->buttonSave, 'btn btn-primary' )
						)
					) )
				)
			) )
		), array( 'autocomplete' => 'off' ) )
	) )
);
?>
