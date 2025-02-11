<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $rights */

if( !in_array( 'scan', $rights ) )
	return '';

$iconRefresh	= HtmlTag::create( 'i', '', ['class' => 'icon-repeat'] );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h4', 'Dateien scannen' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'p', 'Wurden neue Dateien oder Ordner per FTP hochgeladen?
			Damit diese hier aufgelistet werden, müssen Sie den gesamten Dateienordner scannen.' ),
		HtmlTag::create( 'div', [
			HtmlTag::create( 'a', $iconRefresh.' nach neuen Dateien scannen', [
				'href'	=> './info/file/scan',
				'class'	=> 'btn btn-mini',
			] ),
		], ['class' => 'buttonbar'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );
