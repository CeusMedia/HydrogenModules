<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w				= (object) $words['register'];

$moduleConfig	= $config->getAll( 'module.resource_users.', TRUE );

$iconRegister	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$optGender		= HtmlElements::Options( $words['gender'], $user->get( 'gender' ) );

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


$list	= [];
foreach( $files as $file ){
	if( ( $html = getLegalFileContent( $env, $view, $file ) ) )
	if( $env->getModules()->has( 'UI_Helper_Content' ) )
		$html	= View_Helper_ContentConverter::render( $env, $html );
	$list[]		= $html;
}
$tacHtml	= join( '<hr/>', $list );

$formTerms	= '';
if( $tacHtml ){
	$formTerms		= HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row', array(
		HTML::DivClass( "bs2-span12 bs3-col-md-12 bs4-col-md-12", array(
			HTML::Label( 'conditions', $w->labelTerms, '' ),
			HtmlTag::create( 'div', $tacHtml, array(
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
		$iconUnbind			= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
		$assignedProvider	= $env->getSession()->get( 'auth_register_oauth_provider' );
		if( $assignedProvider ){
			$icon		= '';
			if( $assignedProvider->icon ){
				$icon		= HtmlTag::create( 'div', array(
					'<span class="fa-stack fa-2x" style=""><i class="fa fa-square-o fa-stack-2x"></i><i class="'.$assignedProvider->icon.' fa-stack-1x"></i></span>',
				), array( 'class' => 'bs2-span4 bs3-col-md-4 bs4-col-md-4', 'style' => 'text-align: center; font-size: 2em; padding-top: 0.75em;' ) );

			}
			$field		= HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'div', array(
						HtmlTag::create( 'div', array(
							HtmlTag::create( 'h5', 'Verknüpfung hergestellt' ),
							HtmlTag::create( 'p', join( '<br/>', array(
								'Ihr Benutzerkonto wird mit <strong>'.$assignedProvider->title.'</strong> verknüft sein. Sie können sich dann schneller einloggen.',
								'Einige Felder der Registrierung wurden nun bereits mit Vorschlägen gefüllt.',
								'',
							) ) ),
							HtmlTag::create( 'div', array(
								HtmlTag::create( 'a', $iconUnbind.'&nbsp;Verknüpfung aufheben', array(
									'href'	=> './auth/oauth2/unbind',
									'class'	=> 'btn btn-small not-btn-inverse'
								) ),
							) ),
						), array( 'class' => $icon ? 'bs2-span8 bs3-col-md-8 bs4-col-md-8' : 'bs2-span12 bs3-col-md-12 bs4-col-md-12' ) ),
						$icon,
					), array( 'class' => 'bs2-row-fluid bs3-row bs4-row' ) ),
				) ),
			), array( 'class' => 'alert alert-success' ) );
		}
		else{
			$helper		= new View_Helper_Oauth_ProviderButtons( $this->env );
			$helper->setDropdownLabel( 'weitere Anbieter' );
			$buttons	= $helper->setLinkPath( './auth/oauth2/register/' )->render();
			$field		=  array(
				HtmlTag::create( 'label', 'Registrieren mit' ),
				HtmlTag::create( 'div', HtmlTag::create( 'div', $buttons, array(
					'class' => 'bs2-span12 bs3-col-md-12 bs4-col-md-12'
				) ), array( 'class' => 'bs2-row-fluid bs3-row bs4-row' ) ),
			);
		}
		$fieldOauth2	= HtmlTag::create( 'div', array(
			HtmlTag::create( 'div', array(
				$field,
				HtmlTag::create( 'hr', NULL ),
			), array( 'class' => 'bs2-span12 bs3-col-md-12 bs4-col-md-12' ) ),
		), array( 'class' => 'bs2-row-fluid bs3-row bs4-row' ) );
	}
}

$formExtensions	= $view->renderRegisterFormExtensions();

$formUrl	= "./auth/local/register".( $from ? '?from='.$from : '' );
$panelUser	= HTML::DivClass( 'content-panel', array(
	HTML::H3( $w->heading ),
	HTML::DivClass( 'content-panel-inner', array(
		HTML::Form( $formUrl, "form_auth_register_user", array(
			$fieldOauth2,
			HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row', array(
				HTML::DivClass( 'bs2-span3 bs3-col-md-3 bs4-col-md-3', array(
					HTML::Label( "username", $w->labelUsername, "mandatory" ),
					HtmlTag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'username',
						'id'			=> 'input_username',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12 mandatory',
						'value'			=> $user->get( 'username' ),
						'required'		=> 'required',
						'autocomplete'	=> 'off'
					) )
				) ),
				HTML::DivClass( 'bs2-span3 bs3-col-md-3 bs4-col-md-3', array(
					HTML::Label( "password", HtmlTag::create( 'abbr', $w->labelPassword, array(
						'title'	=> sprintf( $w->labelPassword_title, $moduleConfig->get( 'password.length.min' ) )
					) ), "mandatory" ),
					HtmlTag::create( 'input', NULL, array(
						'type'			=> 'password',
						'name'			=> 'password',
						'id'			=> 'input_password',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12 mandatory',
						'value'			=> '',
						'required'		=> $moduleConfig->get( 'firstname.mandatory' ) ? 'required' : NULL,
						'autocomplete'	=> 'off'
					) )
				) ),
				HTML::DivClass( 'bs2-span6 bs3-col-md-6 bs4-col-md-6', array(
					HTML::Label( "email", $w->labelEmail, $moduleConfig->get( 'email.mandatory' ) ? 'mandatory' : '' ),
					HtmlTag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'email',
						'id'			=> 'input_email',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12 '.( $moduleConfig->get( 'email.mandatory' ) ? 'mandatory' : '' ),
						'value'			=> $user->get( 'email' ),
						'required'		=> $moduleConfig->get( 'email.mandatory' ) ? 'required' : NULL,
						'autocomplete'	=> 'email',
					) )
				) ),
			) ).
			HtmlTag::create( 'hr' ).
			HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row', array(
				HTML::DivClass( 'bs2-span3 bs3-col-md-3 bs4-col-md-3', array(
					HTML::Label( "gender", $w->labelGender ),
					HtmlTag::create( 'select', $optGender, array(
						'name'		=> "gender",
						'id'		=> "input_gender",
						'class'		=> "bs2-span12 bs3-col-md-12 bs4-col-md-12"
					) )
				) ),
				HTML::DivClass( 'bs2-span2 bs3-col-md-2 bs4-col-md-2', array(
					HTML::Label( "salutation", $w->labelSalutation ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'salutation',
						'id'		=> 'input_salutation',
						'class'		=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12',
						'value'		=> $user->get( 'salutation' ),
					) )
				) ),
				HTML::DivClass( 'bs2-span3 bs3-col-md-3 bs4-col-md-3', array(
					HTML::Label( "firstname", $w->labelFirstname, $moduleConfig->get( 'firstname.mandatory' ) ? 'mandatory' : '' ),
					HtmlTag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'firstname',
						'id'			=> 'input_firstname',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12 '.( $moduleConfig->get( 'firstname.mandatory' ) ? 'mandatory' : '' ),
						'value'			=> $user->get( 'firstname' ),
						'required'		=> $moduleConfig->get( 'firstname.mandatory' ) ? 'required' : NULL,
						'autocomplete'	=> 'given-name',
					) )
				) ),
				HTML::DivClass( 'bs2-span4 bs3-col-md-4 bs4-col-md-4', array(
					HTML::Label( "surname", $w->labelSurname, $moduleConfig->get( 'surname.mandatory' ) ? 'mandatory' : '' ),
					HtmlTag::create( 'input', NULL, array(
							'type'		=> 'text',
						'name'			=> 'surname',
						'id'			=> 'input_surname',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12 '.( $moduleConfig->get( 'surname.mandatory' ) ? 'mandatory' : '' ),
						'value'			=> $user->get( 'surname' ),
						'required'		=> $moduleConfig->get( 'surname.mandatory' ) ? 'required' : NULL,
						'autocomplete'	=> 'family-name',
					) )
				) ),
			) ).
			HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row', array(
				HTML::DivClass( 'bs2-span3 bs3-col-md-3 bs4-col-md-3', array(
					HTML::Label( "country", $w->labelCountry ),
					HtmlTag::create( 'select', HtmlElements::Options( $countries, $user->get( 'country' ) ), array(
						'name'			=> 'country',
						'id'			=> 'input_country',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12',
						'autocomplete'	=> 'country',
					) )
				) ),
				HTML::DivClass( 'bs2-span2 bs3-col-md-2 bs4-col-md-2', array(
					HTML::Label( "postcode", $w->labelPostcode ),
					HtmlTag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'postcode',
						'id'			=> 'input_postcode',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12',
						'value'			=> $user->get( 'postcode' ),
						'autocomplete'	=> 'postal-code',
					) )
				) ),
				HTML::DivClass( 'bs2-span3 bs3-col-md-3 bs4-col-md-3', array(
					HTML::Label( "city", $w->labelCity ),
					HtmlTag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'city',
						'id'			=> 'input_city',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12',
						'value'			=> $user->get( 'city' ),
						'autocomplete'	=> 'address-level2',
					) )
				) ),
				HTML::DivClass( 'bs2-span4 bs3-col-md-4 bs4-col-md-4', array(
					HTML::Label( "street", $w->labelStreet ),
					HtmlTag::create( 'input', NULL, array(
						'type'			=> 'text',
						'name'			=> 'street',
						'id'			=> 'input_street',
						'class'			=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12',
						'value'			=> $user->get( 'street' ),
						'autocomplete'	=> 'address-line1',
					) )
				) ),
			) ).
			HTML::HR.
			$formExtensions.
			$formTerms.
			HTML::DivClass( 'buttonbar', array(
				HtmlTag::create( 'button', $iconRegister.'&nbsp'.$w->buttonSave, array(
					'type'		=> 'submit',
					'id'		=> 'button_save',
					'class'		=> 'btn btn-primary btn-large save',
					'name'		=> 'save',
					'disabled'	=> 'disabled'
				) )
			) )
		) )
	) )
) );

$textTop	= $textTop ? HTML::DivClass( "auth-register-text-top", $textTop ) : '';
$textBottom	= $textTop ? HTML::DivClass( "auth-register-text-bottom", $textBottom ) : '';

if( strlen( trim( strip_tags( $textInfo ) ) ) ){
	return $textTop.
		HTML::DivClass( "bs2-row-fluid bs3-row bs4-row", array(
			HTML::DivClass( "bs2-span4 bs3-col-md-4 bs4-col-md-4", $panelUser ),
			HTML::DivClass( "bs2-span8 bs3-col-md-8 bs4-col-md-8", $textInfo ),
		) ).$textBottom;
}
return $panelUser;
?>
