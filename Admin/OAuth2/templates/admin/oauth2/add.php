<?php
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'		=> './admin/oauth2',
	'class'		=> 'btn',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
) );

$providerMap	= array();
$optProvider	= array( '' => '- keine -' );
foreach( $providersIndex as $indexItem ){
	$key	= preg_replace( '~/~', '__', $indexItem->package );
	$providerMap[$key]	= $indexItem;
	foreach( $providers as $hasItem )
		if( $indexItem->package === $hasItem->composerPackage )
			continue 2;
	$optProvider[$key]	= $indexItem->title;
}
$optProvider	= UI_HTML_Elements::Options( $optProvider );

$form			= UI_HTML_Tag::create( 'form', array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Provider-Vorlage', array( 'for' => 'input_providerKey' ) ),
			UI_HTML_Tag::create( 'select', $optProvider, array(
				'name'			=> 'providerKey',
				'id'			=> 'input_providerKey',
				'class'			=> 'span12 has-optionals',
			) ),
		), array( 'class' => 'span3' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Titel', array( 'for' => 'input_title', 'class' => 'required mandatory' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'title',
				'id'			=> 'input_title',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->title, ENT_QUOTES, 'UTF-8' ),
				'required'		=> 'required',
			) ),
		), array( 'class' => 'span5' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Icon', array( 'for' => 'input_icon' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'icon',
				'id'			=> 'input_icon',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->icon, ENT_QUOTES, 'UTF-8' ),
				'placeholder'	=> 'fa fa-fw fa-plug',
			) ),
		), array( 'class' => 'span3 optional providerKey providerKey-' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Rang', array( 'for' => 'input_rank' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'rank',
				'id'			=> 'input_rank',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->rank, ENT_QUOTES, 'UTF-8' ),
			) ),
		), array( 'class' => 'span1' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Client-ID', array( 'for' => 'input_clientId', 'class' => 'required mandatory' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'clientId',
				'id'			=> 'input_clientId',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->clientId, ENT_QUOTES, 'UTF-8' ),
				'required'		=> 'required',
			) ),
		), array( 'class' => 'span5' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Client-Secret', array( 'for' => 'input_clientSecret', 'class' => 'required mandatory' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'clientSecret',
				'id'			=> 'input_clientSecret',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->clientSecret, ENT_QUOTES, 'UTF-8' ),
				'required'		=> 'required',
			) ),
		), array( 'class' => 'span7' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Provider-Klasse', array( 'for' => 'input_className', 'class' => 'required mandatory' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'className',
				'id'			=> 'input_className',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->className, ENT_QUOTES, 'UTF-8' ),
				'placeholder'	=> 'League\OAuth2\Client\Provider\...',
				'required'		=> 'required',
			) ),
		), array( 'class' => 'span7' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Composer-Paket', array( 'for' => 'input_composerPackage' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'composerPackage',
				'id'			=> 'input_composerPackage',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->composerPackage, ENT_QUOTES, 'UTF-8' ),
				'placeholder'	=> 'league/oauth2-...',
			) ),
		), array( 'class' => 'span5' ) ),
	), array( 'class' => 'row-fluid optional providerKey providerKey-' ) ),
	UI_HTML_Tag::create( 'div', join( ' ', array(
		$buttonCancel,
		$buttonSave,
	) ), array( 'class' => 'buttonbar' ) ),
), array(
	'action'	=> './admin/oauth2/add',
	'method'	=> 'post',
) );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/admin/oauth2/add/' ) );

$panelForm	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'neuer Anbieter' ),
	UI_HTML_Tag::create( 'div', array(
		$form,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

$script	= '<script>
var oauth2ProviderMap = '.json_encode( $providerMap ).';
jQuery(document).ready(function(){
	jQuery("#input_providerKey").on("input", function(){
		var providerKey = jQuery(this).val();
		var title, icon, className, package;
		if(providerKey){
			title = oauth2ProviderMap[providerKey].title;
			icon = oauth2ProviderMap[providerKey].icon;
			className = oauth2ProviderMap[providerKey].class;
			package = oauth2ProviderMap[providerKey].package;
		}
		jQuery("#input_title").val(title);
		jQuery("#input_icon").val(icon);
		jQuery("#input_className").val(className);
		jQuery("#input_composerPackage").val(package);
	})
});
</script>';

return $textTop.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		$panelForm,
	), array( 'class' => 'span8' ) ),
	UI_HTML_Tag::create( 'div', array(
		$textInfo,
	), array( 'class' => 'span4' ) ),
), array( 'class' => 'row-fluid' ) ).$textBottom.$script;
