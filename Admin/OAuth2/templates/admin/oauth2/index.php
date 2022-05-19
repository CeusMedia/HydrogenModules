<?php
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$helperTime		= new View_Helper_TimePhraser( $env );

$words	= array( 'statuses' => array(
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktivert',
) );

$table	= UI_HTML_Tag::create( 'div', 'Keine vorhanden.', array( 'class' => 'alert alert-info' ) );
if( $providers ){
	$rows	= [];
	foreach( $providers as $provider ){
		$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plug' ) ).'&nbsp;';
		if( $provider->icon )
			$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $provider->icon ) ).'&nbsp;';
		$label	= $icon.$provider->title;
		if( $this->env->getAcl()->has( 'admin/oauth2', 'edit' ) )
			$label	= UI_HTML_Tag::create( 'a', $label, array(
				'href'	=> './admin/oauth2/edit/'.$provider->oauthProviderId,
			) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
//			UI_HTML_Tag::create( 'td', $provider->oauthProviderId, array( 'style' => 'text-align: right' ) ),
			UI_HTML_Tag::create( 'td', $label ),
			UI_HTML_Tag::create( 'td', $helperTime->convert( max( $provider->createdAt, $provider->modifiedAt ), TRUE, 'vor' ) ),
			UI_HTML_Tag::create( 'td', $words['statuses'][$provider->status], array( 'style' => 'text-align: center; background-color: '.calculateColor( ( $provider->status + 1 ) / 2 ) ) ),
			UI_HTML_Tag::create( 'td', $provider->rank, array( 'style' => 'text-align: right' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
//		'50px',
		'',
		'120px',
		'140px',
		'60px',
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
//		UI_HTML_Tag::create( 'th', 'ID', array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'th', 'Anbieter' ),
		UI_HTML_Tag::create( 'th', 'geändert' ),
		UI_HTML_Tag::create( 'th', 'Zustand', array( 'style' => 'text-align: center' ) ),
		UI_HTML_Tag::create( 'th', 'Rang', array( 'style' => 'text-align: right' ) ),
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array(
		'class'	=> 'table table-striped table-fixed'
	) );
}

$buttonAdd	= '';
if( $this->env->getAcl()->has( 'admin/oauth2', 'add' ) )
	$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.' neuer Anbieter', array(
		'href'	=> './admin/oauth2/add',
		'class' => 'btn btn-success',
	) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/oauth2/' ) );

return $textTop.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Providers' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		UI_HTML_Tag::create( 'div', array(
			$buttonAdd,
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) ).$textBottom;

function calculateColor( float $ratio ): string
{
	$hue	= 255;
	$r		= $ratio < 0.5 ? 255 : round( ( 1 - $ratio ) * 2 * $hue );	//  calculate red channel
	$g		= $ratio > 0.5 ? 255 : round( $ratio * 2 * $hue );			//  calculate green channel
	$b		= 0;														//  calculate blue channel
	return sprintf( 'rgb(%d,%d,%d)', $r, $g, $b );						//  return RGB property value
}
