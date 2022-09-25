<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['password'];

$iconCancel	= HTML::Icon( 'arrow-left' );
$iconSend	= HTML::Icon( 'envelope', TRUE );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
	$iconSend		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
}

$buttonCancel		= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, array(
	'href'		=> './auth/local',
	'class'		=> 'btn',
) );
$buttonSave			= HtmlTag::create( 'button', $iconSend.'&nbsp;'.$w->buttonSend, array(
	'type'		=> 'submit',
	'id'		=> 'button_save',
	'class'		=> 'btn btn-primary',
	'name'		=> 'sendPassword',
	'disabled'	=> 'disabled',
) );
$buttonSaveBlock	= HtmlTag::create( 'button', $iconSend.'&nbsp;'.$w->buttonSend, array(
	'type'		=> 'submit',
	'id'		=> 'button_save',
	'class'		=> 'btn btn-primary btn-block',
	'name'		=> 'sendPassword',
	'disabled'	=> 'disabled',
) );

$labelEmail	= $w->labelEmail;
if( !empty( $w->labelEmail_info ) )
	$labelEmail	= HtmlTag::create( 'abbr', $labelEmail, array( 'title' => $w->labelEmail_info ) );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/local/password/' ) );

$panelPassword	= HTML::DivClass( 'content-panel content-panel-form', array(
	HTML::H3( $w->heading ),
	HTML::DivClass( 'content-panel-inner', array(
		HtmlTag::create( 'form', array(
			HTML::DivClass( 'row-fluid', array(
				HTML::DivClass( 'bs2-span12 bs3-col-md-12 bs4-col-md-12', array(
					HtmlTag::create( 'label',$labelEmail, array(
						'for'			=> 'input_password_email',
						'class'			=> 'mandatory'
					) ),
					HtmlTag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'password_email',
						'id'			=> 'input_password_email',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12 mandatory',
						'value'			=> htmlentities( $password_email, ENT_QUOTES, 'UTF-8' ),
						'autocomplete'	=> 'email',
					) )
				) )
			) ),
		/*	HTML::DivClass( 'buttonbar buttonbar-blocks', $buttonSave ),*/
			HTML::DivClass( 'buttonbar', join( ' ', array(
				$buttonCancel,
				$buttonSave,
			) ) ),
		), array(
			'action'	=> './auth/local/password',
			'method'	=> 'post',
		) )
	) ),
) );


if( strlen( trim( strip_tags( $textInfo ) ) ) ){
	return $textTop.
		HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row', array(
			HTML::DivClass( 'bs2-span4 bs3-col-md-4 bs4-col-md-4', $panelPassword ),
			HTML::DivClass( 'bs2-span8 bs3-col-md-8 bs4-col-md-8', $textInfo ),
		) ).$textBottom;
}

if( strlen( trim( strip_tags( $textTop ) ) ) || strlen( trim( strip_tags( $textBottom ) ) ) ){
	return $textTop.$panelLogin.$textBottom;
}

$env->getPage()->addBodyClass( 'auth-centered' );
return HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', $panelPassword, array( 'class' => 'centered-pane' ) )
), array( 'class' => 'centered-pane-container' ) );
?>
