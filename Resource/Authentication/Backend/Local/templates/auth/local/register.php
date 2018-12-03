<?php

$w				= (object) $words['register'];

$moduleConfig	= $config->getAll( 'module.resource_users.', TRUE );

$iconRegister	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$optGender		= UI_HTML_Elements::Options( $words['gender'], $user->get( 'gender' ) );

$texts	= array( 'top', 'info', 'info.company', 'info.user', 'info.conditions', 'bottom' );
extract( $view->populateTexts( $texts, 'html/auth/local/register/' ) );

$files		= array(
	'html/auth/local/tac',
	'html/auth/local/privacy',
);

function reduceContentFile( $view, $fileName ){
	$contentFile	= $view->getContentUri( $fileName );
	if( !file_exists( $contentFile ) )
		return '';
	$content	= FS_File_Reader::load( $contentFile );
	$content	= preg_replace( "/<!--(.|\s)*?-->/", "", $content );
	return $content;
}

function getLegalFileContent( $env, $view, $legal ){
	if( ( $html = reduceContentFile( $view, $legal.'.html' ) ) )
		return $html;
	if( ( $markdown = reduceContentFile( $legal.'.md' ) ) )
		return View_Helper_Markdown::transformStatic( $env, $markdown );
}


$list	= array();
foreach( $files as $file ){
	if( ( $html = getLegalFileContent( $env, $view, $file ) ) )
	if( $env->getModules()->has( 'UI_Helper_Content' ) )
		$html	= View_Helper_ContentConverter::render( $env, $html );
	$list[]		= $html;
}
$tacHtml	= join( '<hr/>', $list );

$formTerms	= '';
if( $tacHtml ){
	$formTerms		= HTML::DivClass( 'row-fluid', array(
		HTML::DivClass( "span12", array(
			HTML::Label( 'conditions', $w->labelTerms, '' ),
			UI_HTML_Tag::create( 'div', $tacHtml, array(
				'class'	=> 'framed monospace',
				'id'	=> 'input_conditions',
			) ),
			HTML::Label( 'accept_tac', array(
				HTML::Checkbox( 'accept_tac', 1, FALSE ),
				$w->labelAccept,
			), 'checkbox mandatory' ),
		) ),
	) );
}

$fieldOauth2	= '';
if( isset( $useOauth2 ) && $useOauth2 ){
	$helper		= new View_Helper_Oauth_ProviderButtons( $this->env );
	if( $helper->count() ){
		$iconUnbind			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
		$assignedProvider	= $env->getSession()->get( 'auth_register_oauth_provider' );
		if( $assignedProvider ){
			$icon		= '';
			if( $assignedProvider->icon ){
				$icon		= UI_HTML_Tag::create( 'div', array(
					'<span class="fa-stack fa-2x" style=""><i class="fa fa-square-o fa-stack-2x"></i><i class="'.$assignedProvider->icon.' fa-stack-1x"></i></span>',
				), array( 'class' => 'span4', 'style' => 'text-align: center; font-size: 2em; padding-top: 0.75em;' ) );

			}
			$field		= UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'h5', 'Verknüpfung hergestellt' ),
							UI_HTML_Tag::create( 'p', join( '<br/>', array(
								'Ihr Benutzerkonto wird mit <strong>'.$assignedProvider->title.'</strong> verknüft sein. Sie können sich dann schneller einloggen.',
								'Einige Felder der Registrierung wurden nun bereits mit Vorschlägen gefüllt.',
								'',
							) ) ),
							UI_HTML_Tag::create( 'div', array(
								UI_HTML_Tag::create( 'a', $iconUnbind.'&nbsp;Verknüpfung aufheben', array(
									'href'	=> './auth/oauth2/unbind',
									'class'	=> 'btn btn-small not-btn-inverse'
								) ),
							) ),
						), array( 'class' => $icon ? 'span8' : 'span12' ) ),
						$icon,
					), array( 'class' => 'row-fluid' ) ),
				) ),
			), array( 'class' => 'alert alert-success' ) );
		}
		else{
			$helper		= new View_Helper_Oauth_ProviderButtons( $this->env );
			$helper->setDropdownLabel( 'weitere Anbieter' );
			$buttons	= $helper->setLinkPath( './auth/oauth2/register/' )->render();
			$field		=  array(
				UI_HTML_Tag::create( 'label', 'Registrieren mit' ),
				UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'span12' ) ), array( 'class' => 'row-fluid' ) ),
			);
		}
		$fieldOauth2	= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				$field,
				UI_HTML_Tag::create( 'hr', NULL ),
			), array( 'class' => 'span12' ) ),
		), array( 'class' => 'row-fluid' ) );
	}
}

$formExtensions	= $view->renderRegisterFormExtensions();

$panelUser	= HTML::DivClass( 'content-panel', array(
	HTML::H3( $w->heading ),
	HTML::DivClass( 'content-panel-inner', array(
			$fieldOauth2,
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
					HTML::Label( "password", UI_HTML_Tag::create( 'abbr', $w->labelPassword, array(
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
						'type'			=> 'text',
						'name'			=> 'email',
						'id'			=> 'input_email',
						'class'			=> 'span12 '.( $moduleConfig->get( 'email.mandatory' ) ? 'mandatory' : '' ),
						'value'			=> $user->get( 'email' ),
						'required'		=> $moduleConfig->get( 'email.mandatory' ) ? 'required' : NULL,
						'autocomplete'	=> 'email',
					) )
				) ),
			) ).
			UI_HTML_Tag::create( 'hr' ).
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
						'type'			=> 'text',
						'name'			=> 'firstname',
						'id'			=> 'input_firstname',
						'class'			=> 'span12 '.( $moduleConfig->get( 'firstname.mandatory' ) ? 'mandatory' : '' ),
						'value'			=> $user->get( 'firstname' ),
						'required'		=> $moduleConfig->get( 'firstname.mandatory' ) ? 'required' : NULL,
						'autocomplete'	=> 'given-name',
					) )
				) ),
				HTML::DivClass( 'span4', array(
					HTML::Label( "surname", $w->labelSurname, $moduleConfig->get( 'surname.mandatory' ) ? 'mandatory' : '' ),
					UI_HTML_Tag::create( 'input', NULL, array(
							'type'		=> 'text',
						'name'			=> 'surname',
						'id'			=> 'input_surname',
						'class'			=> 'span12 '.( $moduleConfig->get( 'surname.mandatory' ) ? 'mandatory' : '' ),
						'value'			=> $user->get( 'surname' ),
						'required'		=> $moduleConfig->get( 'surname.mandatory' ) ? 'required' : NULL,
						'autocomplete'	=> 'family-name',
					) )
				) ),
			) ).
			HTML::DivClass( 'row-fluid', array(
				HTML::DivClass( 'span3', array(
					HTML::Label( "country", $w->labelCountry ),
					UI_HTML_Tag::create( 'select', UI_HTML_Elements::Options( $countries, $user->get( 'country' ) ), array(
						'name'			=> 'country',
						'id'			=> 'input_country',
						'class'			=> 'span12',
						'autocomplete'	=> 'country',
					) )
				) ),
				HTML::DivClass( 'span2', array(
					HTML::Label( "postcode", $w->labelPostcode ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'postcode',
						'id'			=> 'input_postcode',
						'class'			=> 'span12',
						'value'			=> $user->get( 'postcode' ),
						'autocomplete'	=> 'postal-code',
					) )
				) ),
				HTML::DivClass( 'span3', array(
					HTML::Label( "city", $w->labelCity ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'city',
						'id'			=> 'input_city',
						'class'			=> 'span12',
						'value'			=> $user->get( 'city' ),
						'autocomplete'	=> 'address-level2',
					) )
				) ),
				HTML::DivClass( 'span4', array(
					HTML::Label( "street", $w->labelStreet ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'street',
						'id'			=> 'input_street',
						'class'			=> 'span12',
						'value'			=> $user->get( 'street' ),
						'autocomplete'	=> 'address-line1',
					) )
				) ),
			) ).
			HTML::HR.
			$formExtensions.
			$formTerms.
			HTML::DivClass( 'buttonbar', array(
				UI_HTML_Tag::create( 'button', $iconRegister.'&nbsp'.$w->buttonSave, array(
					'type'		=> 'submit',
					'id'		=> 'button_save',
					'class'		=> 'btn btn-primary btn-large save',
					'name'		=> 'save',
					'disabled'	=> 'disabled'
				) )
			) )
		) )
	) );

$formUrl	= "./auth/local/register".( $from ? '?from='.$from : '' );

$textTop	= $textTop ? HTML::DivClass( "auth-register-text-top", $textTop ) : '';
$textBottom	= $textTop ? HTML::DivClass( "auth-register-text-bottom", $textBottom ) : '';

if( strlen( trim( strip_tags( $textInfo ) ) ) ){
	return $textTop.
		HTML::DivClass( "bs2-row-fluid bs3-row bs4-row", array(
			HTML::DivClass( "bs2-span4 bs3-col-md-4 bs2-col-md-4", array(
				HTML::Form( $formUrl, "form_auth_register_user", $panelUser ),
			) ),
			HTML::DivClass( "bs2-span8 bs3-col-md-8 bs4-col-md-8", $textInfo ),
		) ).$textBottom;
}
return $panelUser;
?>
