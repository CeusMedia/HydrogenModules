<?php
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$iconActivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-toggle-on' ) );
$iconDeactivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-toggle-off' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'		=> './admin/oauth2',
	'class'		=> 'btn',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
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

$buttonRemove	= UI_HTML_Tag::create( 'button', $iconRemove.' entfernen', array(
	'type'		=> 'button',
	'class'		=> 'btn btn-danger btn-small',
	'disabled'	=> 'disabled',
) );

if(0 && !$exists){
	$buttonActivate		= UI_HTML_Tag::create( 'button', $iconActivate.' aktivieren', array(
		'type'		=> 'button',
		'class'		=> 'btn btn-small btn-success disabled',
		'title'		=> 'Kann nicht aktiviert werden. Etwas stimmt noch nicht.',
		'onclick'	=> 'alert(\'Kann nicht aktiviert werden. Etwas stimmt noch nicht.\');',
	) );
}

if( $provider->status != Model_Oauth_Provider::STATUS_ACTIVE )
	$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.' entfernen', array(
		'href'		=> './admin/oauth2/remove/'.$providerId,
		'class'		=> 'btn btn-danger btn-small',
	) );

$form			= UI_HTML_Tag::create( 'form', array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Titel', array( 'for' => 'input_title', 'class' => 'required mandatory' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'title',
				'id'			=> 'input_title',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->title, ENT_QUOTES, 'UTF-8' ),
				'required'		=> 'required',
				'disabled'		=> $provider->status > 0 ? 'disabled' : NULL,
			) ),
		), array( 'class' => 'span7' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Icon', array( 'for' => 'input_icon' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'icon',
				'id'			=> 'input_icon',
				'class'			=> 'span12',
				'placeholder'	=> 'fa fa-fw fa-plug',
				'value'			=> htmlentities( $provider->icon, ENT_QUOTES, 'UTF-8' ),
				'disabled'		=> $provider->status > 0 ? 'disabled' : NULL,
			) ),
		), array( 'class' => 'span4' ) ),
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
				'disabled'		=> $provider->status > 0 ? 'disabled' : NULL,
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
				'disabled'		=> $provider->status > 0 ? 'disabled' : NULL,
			) ),
		), array( 'class' => 'span5' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Provider-Optionen <small class="muted">(als JSON)</small>', array( 'for' => 'input_options', 'class' => '', 'title' => 'JSON-Objekt, wie {"key":"value"}' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'options',
				'id'			=> 'input_options',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->options, ENT_QUOTES, 'UTF-8' ),
			//	'disabled'		=> $provider->status > 0 ? 'disabled' : NULL,
			) ),
		), array( 'class' => 'span5' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Privilegien <small class="muted">(kommagetrennt)</small>', array( 'for' => 'input_scopes' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'			=> 'text',
				'name'			=> 'scopes',
				'id'			=> 'input_scopes',
				'class'			=> 'span12',
				'value'			=> htmlentities( $provider->scopes, ENT_QUOTES, 'UTF-8' ),
			//	'disabled'		=> $provider->status > 0 ? 'disabled' : NULL,
			) ),
		), array( 'class' => 'span7' ) ),
	), array( 'class' => 'row-fluid optional providerKey providerKey-' ) ),
	UI_HTML_Tag::create( 'div', join( ' ', array(
		$buttonCancel,
		$buttonSave,
		$provider->status > 0 ? $buttonDeactivate : $buttonActivate,
		$buttonRemove,
	) ), array( 'class' => 'buttonbar' ) ),
), array(
	'action'	=> './admin/oauth2/edit/'.$providerId,
	'method'	=> 'post',
) );


$hint	= UI_HTML_Tag::create( 'div', 'Anbieter-Bibliothek (Composer-Paket "'.$provider->composerPackage.'") ist installiert.', array( 'class' => 'alert alert-success' ) );
$hint	= '';
if( !$exists )
	$hint	= UI_HTML_Tag::create( 'div', 'Anbieter-Bibliothek existiert nicht. Bitte Composer-Paket <strong><tt>'.$provider->composerPackage.'</tt></strong> installieren!', array( 'class' => 'alert alert-important' ) );

$view->env->getPage()->runScript( 'ModuleAdminOAuth2.init()' );

$panelForm	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Provider' ),
	UI_HTML_Tag::create( 'div', array(
		$hint,
		$form,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/admin/oauth2/edit/' ) );

return $textTop.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		$panelForm,
	), array( 'class' => 'span8' ) ),
	UI_HTML_Tag::create( 'div', array(
		$textInfo,
	), array( 'class' => 'span4' ) ),
), array( 'class' => 'row-fluid' ) ).$textBottom;
