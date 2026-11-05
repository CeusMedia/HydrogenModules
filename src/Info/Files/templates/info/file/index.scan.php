<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $rights */
/** @var array<array<string,string>> $words */

if( !in_array( 'scan', $rights ) )
	return '';

$iconRefresh	= HtmlTag::create( 'i', '', ['class' => 'icon-repeat'] );
$w				= (object) $words['scan'];

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h4', $w->heading ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'p', $w->description ),
		HtmlTag::create( 'div', [
			HtmlTag::create( 'a', $iconRefresh.' '.$w->buttonScan, [
				'href'	=> './info/file/scan',
				'class'	=> 'btn btn-mini',
			] ),
		], ['class' => 'buttonbar'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );
