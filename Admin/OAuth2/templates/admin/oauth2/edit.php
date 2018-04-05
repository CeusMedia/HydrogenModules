<?php
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$iconActivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-toggle-on' ) );
$iconDeactivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-toggle-off' ) );


$form			= print_m( $provider, NULL, NULL, TRUE );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'		=> './admin/oauth2',
	'class'		=> 'btn',
) );
$buttonSave		= UI_HTML_Tag::create( 'a', $iconSave.' speichern', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
) );

$buttonActivate		= UI_HTML_Tag::create( 'a', $iconActivate.' aktivieren', array(
	'href'		=> './admin/oauth2/setStatus/'.$providerId.'/'.Model_Oauth_Provider::STATUS_ACTIVE,
	'class'		=> 'btn btn-small btn-success',
) );
$buttonDeactivate	= UI_HTML_Tag::create( 'a', $iconDeactivate.' deaktivieren', array(
	'href'		=> './admin/oauth2/setStatus/'.$providerId.'/'.Model_Oauth_Provider::STATUS_INACTIVE,
	'class'		=> 'btn btn-small btn-inverse',
) );

$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.' entfernen', array(
	'href'		=> './admin/oauth2/remove/'.$providerId,
	'class'		=> 'btn btn-danger btn-small',
) );

$hint	= UI_HTML_Tag::create( 'div', 'Anbieter-Bibliothek (Composer-Paket "'.$provider->composerPackage.'") ist installiert.', array( 'class' => 'alert alert-success' ) );
if( !$exists )
	$hint	= UI_HTML_Tag::create( 'div', 'Anbieter-Bibliothek existiert nicht. Bitte Composer-Paket "'.$provider->composerPackage.'" installiert!', array( 'class' => 'alert alert-important' ) );


return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Provider' ),
	UI_HTML_Tag::create( 'div', array(
		$hint,
		$form,
		UI_HTML_Tag::create( 'div', join( ' ', array(
			$buttonCancel,
			$buttonSave,
			$provider->status > 0 ? $buttonDeactivate : $buttonActivate,
			$buttonRemove,
		) ), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
