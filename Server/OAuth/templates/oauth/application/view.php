<?php

$iconsStatus	= array(
	-1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-removed' ) ),
	0	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-stop' ) ),
	1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-play' ) )
);
$iconsType	= array(
	0	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-world' ) ),
	1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-lock' ) )
);


$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$data	= print_m( $application, NULL, NULL, TRUE );

$listAccessTokens	= '<div class="muted"><em><small>Keine Access-Tokens aktiv.</small></em></div>';
if( !empty( $accessTokens ) ){
	$listAccessTokens	= array();
	foreach( $accessTokens as $token ){
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.' revoke', array( 'href' => './oauth/application/remove/'.$token->oauthApplicationId.'/access/'.$token->oauthAccessTokenId, 'class' => 'btn btn-mini btn-danger' ) );
		$listAccessTokens[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $token->token ),
			UI_HTML_Tag::create( 'td', $buttonRemove ),
		) );
	}
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( ) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $listAccessTokens );
	$listAccessTokens	= UI_HTML_Tag::create( 'table', $thead.$tbody, array( 'class' => 'table table-striped table-condensed' ) );
}

$listRefreshTokens	= '<div class="muted"><em><small>Keine Refresh-Tokens aktiv.</small></em></div>';
if( !empty( $refreshTokens ) ){
	$listRefreshTokens	= array();
	foreach( $refreshTokens as $token ){
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.' revoke', array(
			'href'		=> './oauth/application/remove/'.$token->oauthApplicationId.'/refresh/'.$token->oauthRefreshTokenId,
			'class'		=> 'btn btn-mini btn-danger'
		) );
		$listRefreshTokens[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $token->token ),
/*			UI_HTML_Tag::create( 'td', $buttonRemove ),*/
		) );
	}
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( ) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $listRefreshTokens );
	$listRefreshTokens	= UI_HTML_Tag::create( 'table', $thead.$tbody, array( 'class' => 'table table-striped table-condensed' ) );
}

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil icon-white' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array( 'href' => './oauth/application', 'class' => 'btn btn-small' ) );
$buttonEdit		= UI_HTML_Tag::create( 'a', $iconEdit.' bearbeiten', array( 'href' => './oauth/application/edit/'.$application->oauthApplicationId, 'class' => 'btn btn-small btn-primary' ) );

$description	= strlen( trim( $application->description ) ) ? nl2br( $application->description ) : "-";

return '
<h3><span class="muted">Applikation: </span>'.$application->title.'</h3>
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span6">
						<big>
							<dl class="dl-horizontal">
								<dt></dt>
								<dd></dd>
								<dt>Vertraulichkeit</dt>
								<dd>'.$iconsType[$application->type].' '.$words['types'][$application->type].'</dd>
								<dt>Zustand</dt>
								<dd>'.$iconsStatus[$application->status].' '.$words['states'][$application->status].'</dd>
							</dl>
						</big>
						<dl class="dl-horizontal">
							<dt>Client-URL</dt>
							<dd>'.UI_HTML_Tag::create( 'a', $application->url, array( 'href' => $application->url, 'target' => '_blank', 'class' => 'external' ) ).'</dd>
							<dt>Client-ID</dt>
							<dd>'.$application->clientId.'</dd>
							<dt>Client-Secret</dt>
							<dd>'.$application->clientSecret.'</dd>
						</dl>
						<dl class="dl-horizontal">
							<dt>erstellt</dt>
							<dd>'.date( 'd.m.Y H:i', $application->createdAt ).'</dd>
							<dt>zuletzt geändert</dt>
							<dd>'.( $application->modifiedAt ? date( 'd.m.Y H:i', $application->modifiedAt ) : '-' ).'</dd>
						</dl>
					</div>
					<div class="span6">
						'.$description.'
					</div>
				</div>
				<div class="buttonbar">
					'.$buttonCancel.'
					'.$buttonEdit.'
				</div>
			</div>
		</div>
	</div>
</div>
<h3>Tokens</h3>
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<div class="content-panel-inner">
				<h4>Access-Tokens</h4>
				'.$listAccessTokens.'
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="content-panel">
			<div class="content-panel-inner">
				<h4>Refresh-Tokens</h4>
				'.$listRefreshTokens.'
			</div>
		</div>
	</div>
</div>
';
