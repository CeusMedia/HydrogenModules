<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

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
$optProvider	= array( '' => '- keine -' );
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
			HtmlTag::create( 'label', 'Provider-Vorlage', array( 'for' => 'input_providerKey' ) ),
			HtmlTag::create( 'select', $optProvider, array(
				'name'			=> 'providerKey',
				'id'			=> 'input_providerKey',
				'class'			=> 'span12 has-optionals',
			) ),
		), array( 'class' => 'span3' ) ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Titel', array( 'for' => 'input_title', 'class' => 'required mandatory' ) ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'title',
				'id'			=> 'input_title',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->title, ENT_QUOTES, 'UTF-8' ),
				'required'		=> 'required',
			) ),
		), array( 'class' => 'span5' ) ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Icon', array( 'for' => 'input_icon' ) ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'icon',
				'id'			=> 'input_icon',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->icon, ENT_QUOTES, 'UTF-8' ),
				'placeholder'	=> 'fa fa-fw fa-plug',
			) ),
		), array( 'class' => 'span3 optional providerKey providerKey-' ) ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Rang', array( 'for' => 'input_rank' ) ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'rank',
				'id'			=> 'input_rank',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->rank, ENT_QUOTES, 'UTF-8' ),
			) ),
		), array( 'class' => 'span1' ) ),
	), array( 'class' => 'row-fluid' ) ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Client-ID', array( 'for' => 'input_clientId', 'class' => 'required mandatory' ) ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'clientId',
				'id'			=> 'input_clientId',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->clientId, ENT_QUOTES, 'UTF-8' ),
				'required'		=> 'required',
			) ),
		), array( 'class' => 'span5' ) ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Client-Secret', array( 'for' => 'input_clientSecret', 'class' => 'required mandatory' ) ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'clientSecret',
				'id'			=> 'input_clientSecret',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->clientSecret, ENT_QUOTES, 'UTF-8' ),
				'required'		=> 'required',
			) ),
		), array( 'class' => 'span7' ) ),
	), array( 'class' => 'row-fluid' ) ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Provider-Klasse', array( 'for' => 'input_className', 'class' => 'required mandatory' ) ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'className',
				'id'			=> 'input_className',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->className, ENT_QUOTES, 'UTF-8' ),
				'placeholder'	=> 'League\OAuth2\Client\Provider\...',
				'required'		=> 'required',
			) ),
		), array( 'class' => 'span7' ) ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Composer-Paket', array( 'for' => 'input_composerPackage' ) ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'composerPackage',
				'id'			=> 'input_composerPackage',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->composerPackage, ENT_QUOTES, 'UTF-8' ),
				'placeholder'	=> 'league/oauth2-...',
			) ),
		), array( 'class' => 'span5' ) ),
	), array( 'class' => 'row-fluid optional providerKey providerKey-' ) ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Provider-Optionen <small class="muted">(als JSON)</small>', array( 'for' => 'input_options', 'class' => '', 'title' => 'JSON-Objekt, wie {"key":"value"}' ) ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'options',
				'id'			=> 'input_options',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->options, ENT_QUOTES, 'UTF-8' ),
			) ),
		), array( 'class' => 'span5' ) ),
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'label', 'Privilegien <small class="muted">(kommagetrennt)</small>', array( 'for' => 'input_scopes' ) ),
			HtmlTag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'scopes',
				'id'			=> 'input_scopes',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->scopes, ENT_QUOTES, 'UTF-8' ),
			) ),
		), array( 'class' => 'span7' ) ),
	), array( 'class' => 'row-fluid optional providerKey providerKey-' ) ),
	HtmlTag::create( 'div', join( ' ', array(
		$buttonCancel,
		$buttonSave,
	) ), array( 'class' => 'buttonbar' ) ),
), array(
	'action'	=> './admin/oauth2/add',
	'method'	=> 'post',
) );

$panelForm	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'neuer Anbieter' ),
	HtmlTag::create( 'div', array(
		$form,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

$script	= '
	ModuleAdminOAuth2.setProviders('.json_encode( $providerMap ).');
	ModuleAdminOAuth2.init();';
$view->env->getPage()->runScript( $script );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/admin/oauth2/add/' ) );

return $textTop.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		$panelForm,
	), array( 'class' => 'span8' ) ),
	HtmlTag::create( 'div', array(
		$textInfo,
	), array( 'class' => 'span4' ) ),
), array( 'class' => 'row-fluid' ) ).$textBottom;
