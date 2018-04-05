<?php
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$form			= '';
$form			= UI_HTML_Tag::create( 'form', array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Titel', array( 'for' => 'input_' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
 				'type'		=> 'text',
				'name'		=> '',
				'id'		=> 'input_',
				'class'		=> 'span12',
				'value'		=> htmlentities( $provider->title, ENT_QUOTES, 'UTF-8' ),
				'disabled'	=> $provider->status > 0 ? 'disabled' : NULL,
			) ),
		), array( 'class' => 'span11' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Rang', array( 'for' => 'input_rank' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
 				'type'		=> 'text',
				'name'		=> 'rank',
				'id'		=> 'input_rank',
				'class'		=> 'span12',
				'value'		=> htmlentities( $provider->rank, ENT_QUOTES, 'UTF-8' ),
			) ),
		), array( 'class' => 'span1' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Client-ID', array( 'for' => 'input_clientId' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
 				'type'		=> 'text',
				'name'		=> 'clientId',
				'id'		=> 'input_clientId',
				'class'		=> 'span12',
				'value'		=> htmlentities( $provider->clientId, ENT_QUOTES, 'UTF-8' ),
			) ),
		), array( 'class' => 'span5' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Client-Secret', array( 'for' => 'input_clientSecret' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
 				'type'		=> 'text',
				'name'		=> 'clientSecret',
				'id'		=> 'input_clientSecret',
				'class'		=> 'span12',
				'value'		=> htmlentities( $provider->clientSecret, ENT_QUOTES, 'UTF-8' ),
			) ),
		), array( 'class' => 'span7' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Provider-Paket', array( 'for' => 'input_composerPackage' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
 				'type'		=> 'text',
				'name'		=> 'composerPackage',
				'id'		=> 'input_composerPackage',
				'class'		=> 'span12',
				'value'		=> htmlentities( $provider->composerPackage, ENT_QUOTES, 'UTF-8' ),
				'disabled'	=> $provider->status > 0 ? 'disabled' : NULL,
				'placeholder'	=> 'league/oauth2-...'
			) ),
		), array( 'class' => 'span5' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Provider-Klasse', array( 'for' => 'input_className' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
 				'type'		=> 'text',
				'name'		=> 'className',
				'id'		=> 'input_className',
				'class'		=> 'span12',
				'value'		=> htmlentities( $provider->className, ENT_QUOTES, 'UTF-8' ),
				'disabled'	=> $provider->status > 0 ? 'disabled' : NULL,
				'placeholder'	=> 'League\OAuth2\Client\Provider\...'
			) ),
		), array( 'class' => 'span7' ) ),
	), array( 'class' => 'row-fluid' ) ),
), array(
	'action'	=> './admin/oauth2/add',
	'method'	=> 'post',
) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'		=> './admin/oauth2',
	'class'		=> 'btn',
) );
$buttonSave		= UI_HTML_Tag::create( 'a', $iconSave.' speichern', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'neuer Anbieter' ),
	UI_HTML_Tag::create( 'div', array(
		$form,
		UI_HTML_Tag::create( 'div', join( ' ', array(
			$buttonCancel,
			$buttonSave,
		) ), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
