<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var object $application */
/** @var array<string,array<string|int,string>> $words */

$w	= (object) $words['view'];

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-trash'] );

$iconsStatus	= array(
	-1	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-trash'] ),
	0	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-stop'] ),
	1	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-play'] )
);
$iconsType	= array(
	0	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-world'] ),
	1	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-lock'] )
);

$data	= print_m( $application, NULL, NULL, TRUE );

$listAccessTokens	= '<div class="muted"><em><small>'.$w->noAccessTokens.'</small></em></div>';
if( !empty( $accessTokens ) ){
	$listAccessTokens	= [];
	foreach( $accessTokens as $token ){
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove.' '.$w->buttonRevoke, ['href' => './oauth/application/remove/'.$token->oauthApplicationId.'/access/'.$token->oauthAccessTokenId, 'class' => 'btn btn-mini btn-danger'] );
		$listAccessTokens[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $token->token ),
			HtmlTag::create( 'td', $buttonRemove ),
		] );
	}
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array( ) ) );
	$tbody		= HtmlTag::create( 'tbody', $listAccessTokens );
	$listAccessTokens	= HtmlTag::create( 'table', $thead.$tbody, ['class' => 'table table-striped table-condensed'] );
}

$listRefreshTokens	= '<div class="muted"><em><small>'.$w->noRefreshTokens.'</small></em></div>';
if( !empty( $refreshTokens ) ){
	$listRefreshTokens	= [];
	foreach( $refreshTokens as $token ){
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove.' '.$w->buttonRevoke, [
			'href'		=> './oauth/application/remove/'.$token->oauthApplicationId.'/refresh/'.$token->oauthRefreshTokenId,
			'class'		=> 'btn btn-mini btn-danger'
		] );
		$listRefreshTokens[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $token->token ),
/*			HtmlTag::create( 'td', $buttonRemove ),*/
		] );
	}
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array( ) ) );
	$tbody		= HtmlTag::create( 'tbody', $listRefreshTokens );
	$listRefreshTokens	= HtmlTag::create( 'table', $thead.$tbody, ['class' => 'table table-striped table-condensed'] );
}

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' '.$w->buttonCancel, ['href' => './oauth/application', 'class' => 'btn btn-small'] );
$buttonEdit		= HtmlTag::create( 'a', $iconEdit.' '.$w->buttonEdit, ['href' => './oauth/application/edit/'.$application->oauthApplicationId, 'class' => 'btn btn-small btn-primary'] );

$description	= strlen( trim( $application->description ) ) ? nl2br( $application->description ) : "-";

return '
<h3><span class="muted">'.$w->heading.': </span>'.$application->title.'</h3>
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
								<dt>'.$w->labelType.'</dt>
								<dd>'.$iconsType[$application->type].' '.$words['types'][$application->type].'</dd>
								<dt>'.$w->labelStatus.'</dt>
								<dd>'.$iconsStatus[$application->status].' '.$words['states'][$application->status].'</dd>
							</dl>
						</big>
						<dl class="dl-horizontal">
							<dt>'.$w->labelUrl.'</dt>
							<dd>'.HtmlTag::create( 'a', $application->url, ['href' => $application->url, 'target' => '_blank', 'class' => 'external'] ).'</dd>
							<dt>'.$w->labelClientId.'</dt>
							<dd>'.$application->clientId.'</dd>
							<dt>'.$w->labelClientSecret.'</dt>
							<dd>'.$application->clientSecret.'</dd>
						</dl>
						<dl class="dl-horizontal">
							<dt>'.$w->labelCreatedAt.'</dt>
							<dd>'.date( 'd.m.Y H:i', $application->createdAt ).'</dd>
							<dt>'.$w->labelModifiedAt.'</dt>
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
<h3>'.$w->headingTokens.'</h3>
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<div class="content-panel-inner">
				<h4>'.$w->headingAccessTokens.'</h4>
				'.$listAccessTokens.'
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="content-panel">
			<div class="content-panel-inner">
				<h4>'.$w->headingRefreshTokens.'</h4>
				'.$listRefreshTokens.'
			</div>
		</div>
	</div>
</div>
';
