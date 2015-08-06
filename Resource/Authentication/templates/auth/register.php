<?php

$w		= (object) $words['register'];

$iconRegister	= HTML::Icon( 'plus', TRUE );

$optGender		= UI_HTML_Elements::Options( $words['gender'], $user->get( 'gender' ) );

$texts	= array( 'top', 'info', 'info.company', 'info.user', 'info.conditions', 'bottom' );
extract( $view->populateTexts( $texts, 'html/auth/register/' ) );

$formTerms	= '';
$tacHtml	= '';
$tacFile	= 'html/auth/tac';
if( $env->getModules()->has( 'UI_Markdown') && $view->hasContentFile( $tacFile.'.md' ) ){
	$tacMarkdown	= $view->loadContentFile( $tacFile.'.md' );
	$tacHtml		= View_Helper_Markdown::transformStatic( $env, $tacMarkdown );
}
else if( $view->hasContentFile( $tacFile.'.html' ) ){
	$tacHtml		= $view->loadContentFile( $tacFile.'.html' );
	if( $env->getModules()->has( 'UI_Helper_Content' ) )
		$tacHtml	= View_Helper_ContentConverter::render( $env, $tacHtml );
}
if( $tacHtml ){
	$formTerms		= HTML::DivClass( 'row-fluid', array(
		HTML::DivClass( "span12", array(
			HTML::Label( 'conditions', $w->labelTerms, '' ),
			UI_HTML_Tag::create( 'div', $tacHtml, array(
				'class'	=> 'framed monospace',
				'id'	=> 'input_conditions',
			) )
		) ),
		HTML::DivClass( "span12", array(
			HTML::Label( 'accept_tac', array(
				HTML::Checkbox( 'accept_tac', 1, FALSE ),
				$w->labelAccept,
			), 'checkbox mandatory' )
		) )
	) );
}

$moduleConfig	= $config->getAll( 'module.resource_users.', TRUE );
//print_m( $moduleConfig->getAll() );die;

$env->getPage()->js->addScriptOnReady('Auth.initUserRegistration();');
$env->getPage()->css->theme->addUrl( 'module.resource.auth.css' );

$panelUser	= HTML::DivClass( 'content-panel', array(
	HTML::H3( $w->heading ),
	HTML::DivClass( 'content-panel-inner',
		HTML::DivClass( 'row-fluid', array(
			HTML::DivClass( 'span3', array(
				HTML::Label( "username", $w->labelUsername, "mandatory" ),
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'			=> 'text',
					'name'			=> 'username',
					'id'			=> 'input_username',
					'class'			=> 'span12 mandatory',
					'value'			=> $user->get( 'username' ),
					'required'		=> 'required',
					'autocomplete'	=> 'off'
				) )
			) ),
			HTML::DivClass( 'span3', array(
				HTML::Label( "password", new UI_HTML_Tag( 'abbr', $w->labelPassword, array(
					'title'	=> sprintf( $w->labelPassword_title, $moduleConfig->get( 'password.length.min' ) )
				) ), "mandatory" ),
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'			=> 'password',
					'name'			=> 'password',
					'id'			=> 'input_password',
					'class'			=> 'span12 mandatory',
					'value'			=> '',
					'required'		=> $moduleConfig->get( 'firstname.mandatory' ) ? 'required' : NULL,
					'autocomplete'	=> 'off'
				) )
			) ),
			HTML::DivClass( 'span6', array(
				HTML::Label( "email", $w->labelEmail, $moduleConfig->get( 'email.mandatory' ) ? 'mandatory' : '' ),
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'text',
					'name'		=> 'email',
					'id'		=> 'input_email',
					'class'		=> 'span12 '.( $moduleConfig->get( 'email.mandatory' ) ? 'mandatory' : '' ),
					'value'		=> $user->get( 'email' ),
					'required'	=> $moduleConfig->get( 'email.mandatory' ) ? 'required' : NULL,
				) )
			) ),
		) ).
		HTML::DivClass( 'row-fluid', array(
			HTML::DivClass( 'span3', array(
				HTML::Label( "gender", $w->labelGender ),
				UI_HTML_Tag::create( 'select', $optGender, array(
					'name'		=> "gender",
					'id'		=> "input_gender",
					'class'		=> "span12"
				) )
			) ),
			HTML::DivClass( 'span2', array(
				HTML::Label( "salutation", $w->labelSalutation ),
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'text',
					'name'		=> 'salutation',
					'id'		=> 'input_salutation',
					'class'		=> 'span12',
					'value'		=> $user->get( 'salutation' ),
				) )
			) ),
			HTML::DivClass( 'span3', array(
				HTML::Label( "firstname", $w->labelFirstname, $moduleConfig->get( 'firstname.mandatory' ) ? 'mandatory' : '' ),
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'text',
					'name'		=> 'firstname',
					'id'		=> 'input_firstname',
					'class'		=> 'span12 '.( $moduleConfig->get( 'firstname.mandatory' ) ? 'mandatory' : '' ),
					'value'		=> $user->get( 'firstname' ),
					'required'	=> $moduleConfig->get( 'firstname.mandatory' ) ? 'required' : NULL,
				) )
			) ),
			HTML::DivClass( 'span4', array(
				HTML::Label( "surname", $w->labelSurname, $moduleConfig->get( 'surname.mandatory' ) ? 'mandatory' : '' ),
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'text',
					'name'		=> 'surname',
					'id'		=> 'input_surname',
					'class'		=> 'span12 '.( $moduleConfig->get( 'surname.mandatory' ) ? 'mandatory' : '' ),
					'value'		=> $user->get( 'surname' ),
					'required'	=> $moduleConfig->get( 'surname.mandatory' ) ? 'required' : NULL,
				) )
			) ),
		) ).
		HTML::DivClass( 'row-fluid', array(
			HTML::DivClass( 'span3', array(
				HTML::Label( "street", $w->labelStreet ),
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'text',
					'name'		=> 'street',
					'id'		=> 'input_street',
					'class'		=> 'span12',
					'value'		=> $user->get( 'street' ),
				) )
			) ),
			HTML::DivClass( 'span2', array(
				HTML::Label( "number", $w->labelNumber ),
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'text',
					'name'		=> 'number',
					'id'		=> 'input_number',
					'class'		=> 'span12',
					'value'		=> $user->get( 'number' ),
				) )
			) ),
			HTML::DivClass( 'span2', array(
				HTML::Label( "postcode", $w->labelPostcode ),
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'text',
					'name'		=> 'postcode',
					'id'		=> 'input_postcode',
					'class'		=> 'span12',
					'value'		=> $user->get( 'postcode' ),
				) )
			) ),
			HTML::DivClass( 'span3', array(
				HTML::Label( "city", $w->labelCity ),
				UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'text',
					'name'		=> 'city',
					'id'		=> 'input_city',
					'class'		=> 'span12',
					'value'		=> $user->get( 'city' ),
				) )
			) ),
		) ).
		HTML::HR.
		$formTerms.
		HTML::DivClass( 'buttonbar', array(
			UI_HTML_Tag::create( 'button', $iconRegister.'&nbsp'.$w->buttonSave, array(
				'type'		=> 'submit',
				'id'		=> 'button_save',
				'class'		=> 'btn btn-primary save',
				'name'		=> 'save',
				'disabled'	=> 'disabled'
			) )
		) )
	) )
);

$formUrl	= "./auth/register".( $from ? '?from='.$from : '' );
return $textTop.
HTML::Form( $formUrl, "form_auth_register_user",
	HTML::DivClass( 'row-fluid', array(
		HTML::DivClass( 'span8', $panelUser ),
		HTML::DivClass( 'span4', $textInfo ),
	) )
).$textBottom;
?>
