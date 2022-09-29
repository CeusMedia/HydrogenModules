<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $provider */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconActivate	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-toggle-on'] );
$iconDeactivate	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-toggle-off'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zurück', array(
	'href'		=> './admin/oauth2',
	'class'		=> 'btn',
) );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' speichern', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
) );

$buttonActivate		= HtmlTag::create( 'a', $iconActivate.' aktivieren', array(
	'href'		=> './admin/oauth2/setStatus/'.$providerId.'/'.Model_Oauth_Provider::STATUS_ACTIVE,
	'class'		=> 'btn btn-small btn-success',
) );

$buttonDeactivate	= HtmlTag::create( 'a', $iconDeactivate.' deaktivieren', array(
	'href'		=> './admin/oauth2/setStatus/'.$providerId.'/'.Model_Oauth_Provider::STATUS_INACTIVE,
	'class'		=> 'btn btn-small btn-inverse',
) );

$buttonRemove	= HtmlTag::create( 'button', $iconRemove.' entfernen', array(
	'type'		=> 'button',
	'class'		=> 'btn btn-danger btn-small',
	'disabled'	=> 'disabled',
) );

if( 0 && !$exists){
	$buttonActivate		= HtmlTag::create( 'button', $iconActivate.' aktivieren', array(
		'type'		=> 'button',
		'class'		=> 'btn btn-small btn-success disabled',
		'title'		=> 'Kann nicht aktiviert werden. Etwas stimmt noch nicht.',
		'onclick'	=> 'alert(\'Kann nicht aktiviert werden. Etwas stimmt noch nicht.\');',
	) );
}

if( $provider->status != Model_Oauth_Provider::STATUS_ACTIVE )
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.' entfernen', array(
		'href'		=> './admin/oauth2/remove/'.$providerId,
		'class'		=> 'btn btn-danger btn-small',
	) );

$form	= HtmlTag::create( 'form', array(
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Titel', ['for' => 'input_title', 'class' => 'required mandatory'] ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'title',
				'id'			=> 'input_title',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->title, ENT_QUOTES, 'UTF-8' ),
				'required'		=> 'required',
				'disabled'		=> $provider->status > 0 ? 'disabled' : NULL,
			) ),
		), ['class' => 'span7'] ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Icon', ['for' => 'input_icon'] ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'icon',
				'id'			=> 'input_icon',
				'class'			=> 'span12',
				'placeholder'	=> 'fa fa-fw fa-plug',
				'value'			=> htmlentities( $provider->icon, ENT_QUOTES, 'UTF-8' ),
				'disabled'		=> $provider->status > 0 ? 'disabled' : NULL,
			) ),
		), ['class' => 'span4'] ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Rang', ['for' => 'input_rank'] ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'rank',
				'id'			=> 'input_rank',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->rank, ENT_QUOTES, 'UTF-8' ),
			) ),
		), ['class' => 'span1'] ),
	), ['class' => 'row-fluid'] ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Client-ID', ['for' => 'input_clientId', 'class' => 'required mandatory'] ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'clientId',
				'id'			=> 'input_clientId',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->clientId, ENT_QUOTES, 'UTF-8' ),
				'required'		=> 'required',
			) ),
		), ['class' => 'span5'] ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Client-Secret', ['for' => 'input_clientSecret', 'class' => 'required mandatory'] ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'clientSecret',
				'id'			=> 'input_clientSecret',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->clientSecret, ENT_QUOTES, 'UTF-8' ),
				'required'		=> 'required',
			) ),
		), ['class' => 'span7'] ),
	), ['class' => 'row-fluid'] ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Provider-Klasse', ['for' => 'input_className', 'class' => 'required mandatory'] ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'className',
				'id'			=> 'input_className',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->className, ENT_QUOTES, 'UTF-8' ),
				'placeholder'	=> 'League\OAuth2\Client\Provider\...',
				'required'		=> 'required',
				'disabled'		=> $provider->status > 0 ? 'disabled' : NULL,
			) ),
		), ['class' => 'span7'] ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Composer-Paket', ['for' => 'input_composerPackage'] ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'composerPackage',
				'id'			=> 'input_composerPackage',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->composerPackage, ENT_QUOTES, 'UTF-8' ),
				'placeholder'	=> 'league/oauth2-...',
				'disabled'		=> $provider->status > 0 ? 'disabled' : NULL,
			) ),
		), ['class' => 'span5'] ),
	), ['class' => 'row-fluid'] ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Provider-Optionen <small class="muted">(als JSON)</small>', ['for' => 'input_options', 'class' => '', 'title' => 'JSON-Objekt, wie {"key":"value"}'] ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'options',
				'id'			=> 'input_options',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->options, ENT_QUOTES, 'UTF-8' ),
			//	'disabled'		=> $provider->status > 0 ? 'disabled' : NULL,
			) ),
		), ['class' => 'span5'] ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Privilegien <small class="muted">(kommagetrennt)</small>', ['for' => 'input_scopes'] ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'scopes',
				'id'			=> 'input_scopes',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->scopes, ENT_QUOTES, 'UTF-8' ),
			//	'disabled'		=> $provider->status > 0 ? 'disabled' : NULL,
			) ),
		), ['class' => 'span7'] ),
	), ['class' => 'row-fluid optional providerKey providerKey-'] ),
	HtmlTag::create( 'div', join( ' ', array(
		$buttonCancel,
		$buttonSave,
		$provider->status > 0 ? $buttonDeactivate : $buttonActivate,
		$buttonRemove,
	) ), ['class' => 'buttonbar'] ),
), array(
	'action'	=> './admin/oauth2/edit/'.$providerId,
	'method'	=> 'post',
) );


$hint	= HtmlTag::create( 'div', 'Anbieter-Bibliothek (Composer-Paket "'.$provider->composerPackage.'") ist installiert.', ['class' => 'alert alert-success'] );
$hint	= '';
if( !$exists )
	$hint	= HtmlTag::create( 'div', 'Anbieter-Bibliothek existiert nicht. Bitte Composer-Paket <strong><tt>'.$provider->composerPackage.'</tt></strong> installieren!', ['class' => 'alert alert-important'] );

$env->getPage()->runScript( 'ModuleAdminOAuth2.init()' );

$panelForm	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Provider' ),
	HtmlTag::create( 'div', array(
		$hint,
		$form,
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

[$textTop, $textInfo, $textBottom] = $view->populateTexts( ['top', 'info', 'bottom'], 'html/admin/oauth2/edit/' );

return $textTop.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		$panelForm,
	), ['class' => 'span8'] ),
	HtmlTag::create( 'div', array(
		$textInfo,
	), ['class' => 'span4'] ),
), ['class' => 'row-fluid'] ).$textBottom;
