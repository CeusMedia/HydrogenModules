<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $providers */

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$helperTime		= new View_Helper_TimePhraser( $env );

$words	= array( 'statuses' => array(
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
) );

$table	= HtmlTag::create( 'div', 'Keine vorhanden.', ['class' => 'alert alert-info'] );
if( $providers ){
	$rows	= [];
	foreach( $providers as $provider ){
		$icon	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plug'] ).'&nbsp;';
		if( $provider->icon )
			$icon	= HtmlTag::create( 'i', '', ['class' => $provider->icon] ).'&nbsp;';
		$label	= $icon.$provider->title;
		if( $env->getAcl()->has( 'admin/oauth2', 'edit' ) )
			$label	= HtmlTag::create( 'a', $label, array(
				'href'	=> './admin/oauth2/edit/'.$provider->oauthProviderId,
			) );
		$rows[]	= HtmlTag::create( 'tr', array(
//			HtmlTag::create( 'td', $provider->oauthProviderId, ['style' => 'text-align: right'] ),
			HtmlTag::create( 'td', $label ),
			HtmlTag::create( 'td', $helperTime->convert( max( $provider->createdAt, $provider->modifiedAt ), TRUE, 'vor' ) ),
			HtmlTag::create( 'td', $words['statuses'][$provider->status], ['style' => 'text-align: center; background-color: '.calculateColor( ( $provider->status + 1 ) / 2 )] ),
			HtmlTag::create( 'td', $provider->rank, ['style' => 'text-align: right'] ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( array(
//		'50px',
		'',
		'120px',
		'140px',
		'60px',
	) );
	$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
//		HtmlTag::create( 'th', 'ID', ['style' => 'text-align: right'] ),
		HtmlTag::create( 'th', 'Anbieter' ),
		HtmlTag::create( 'th', 'geändert' ),
		HtmlTag::create( 'th', 'Zustand', ['style' => 'text-align: center'] ),
		HtmlTag::create( 'th', 'Rang', ['style' => 'text-align: right'] ),
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], array(
		'class'	=> 'table table-striped table-fixed'
	) );
}

$buttonAdd	= '';
if( $env->getAcl()->has( 'admin/oauth2', 'add' ) )
	$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' neuer Anbieter', array(
		'href'	=> './admin/oauth2/add',
		'class' => 'btn btn-success',
	) );

[$textTop, $textBottom] = $view->populateTexts( ['top', 'bottom'], 'html/admin/oauth2/' );

return $textTop.HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Providers' ),
	HtmlTag::create( 'div', array(
		$table,
		HtmlTag::create( 'div', array(
			$buttonAdd,
		), ['class' => 'buttonbar'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] ).$textBottom;

function calculateColor( float $ratio ): string
{
	$hue	= 255;
	$r		= $ratio < 0.5 ? 255 : round( ( 1 - $ratio ) * 2 * $hue );	//  calculate red channel
	$g		= $ratio > 0.5 ? 255 : round( $ratio * 2 * $hue );			//  calculate green channel
	$b		= 0;														//  calculate blue channel
	return sprintf( 'rgb(%d,%d,%d)', $r, $g, $b );						//  return RGB property value
}
