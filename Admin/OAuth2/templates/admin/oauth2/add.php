<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $providers */
/** @var object $provider */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zurück', array(
	'href'		=> './admin/oauth2',
	'class'		=> 'btn',
) );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' speichern', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
) );

$providerMap	= [];
$optProvider	= ['' => '- keine -'];
foreach( $providersIndex as $indexItem ){
	$key	= preg_replace( '~/~', '__', $indexItem->package );
	$providerMap[$key]	= $indexItem;
	foreach( $providers as $hasItem )
		if( $indexItem->package === $hasItem->composerPackage )
			continue 2;
	$optProvider[$key]	= $indexItem->title;
}
$optProvider	= HtmlElements::Options( $optProvider );

$form			= HtmlTag::create( 'form', array(
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Provider-Vorlage', ['for' => 'input_providerKey'] ),
			HtmlTag::create( 'select', $optProvider, array(
				'name'			=> 'providerKey',
				'id'			=> 'input_providerKey',
				'class'			=> 'span12 has-optionals',
			) ),
		), ['class' => 'span3'] ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Titel', ['for' => 'input_title', 'class' => 'required mandatory'] ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'title',
				'id'			=> 'input_title',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->title, ENT_QUOTES, 'UTF-8' ),
				'required'		=> 'required',
			) ),
		), ['class' => 'span5'] ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Icon', ['for' => 'input_icon'] ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'icon',
				'id'			=> 'input_icon',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->icon, ENT_QUOTES, 'UTF-8' ),
				'placeholder'	=> 'fa fa-fw fa-plug',
			) ),
		), ['class' => 'span3 optional providerKey providerKey-'] ),
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
			) ),
		), ['class' => 'span5'] ),
	), ['class' => 'row-fluid optional providerKey providerKey-'] ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Provider-Optionen <small class="muted">(als JSON)</small>', ['for' => 'input_options', 'class' => '', 'title' => 'JSON-Objekt, wie {"key":"value"}'] ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'options',
				'id'			=> 'input_options',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->options, ENT_QUOTES, 'UTF-8' ),
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
			) ),
		), ['class' => 'span7'] ),
	), ['class' => 'row-fluid optional providerKey providerKey-'] ),
	HtmlTag::create( 'div', join( ' ', array(
		$buttonCancel,
		$buttonSave,
	) ), ['class' => 'buttonbar'] ),
), array(
	'action'	=> './admin/oauth2/add',
	'method'	=> 'post',
) );

$panelForm	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'neuer Anbieter' ),
	HtmlTag::create( 'div', array(
		$form,
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

$script	= '
	ModuleAdminOAuth2.setProviders('.json_encode( $providerMap ).');
	ModuleAdminOAuth2.init();';
$env->getPage()->runScript( $script );

[$textTop, $textInfo, $textBottom] = $view->populateTexts( ['top', 'info', 'bottom'], 'html/admin/oauth2/add/' );

return $textTop.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		$panelForm,
	), ['class' => 'span8'] ),
	HtmlTag::create( 'div', array(
		$textInfo,
	), ['class' => 'span4'] ),
), ['class' => 'row-fluid'] ).$textBottom;
