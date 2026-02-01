<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var object $words */
/** @var object $reader */
/** @var array $groups */
/** @var array $readerGroups */

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$statusIcons	= [
	-1		=> 'remove',
	0		=> 'star',
	1		=> 'ok',
];

$optGroup	= [];
foreach( $groups as $group )
	if( !array_key_exists( $group->newsletterGroupId, $readerGroups ) )
		$optGroup[$group->newsletterGroupId]	= $group->title;
$hideGroupAdd	= count( $optGroup ) ? '' : 'style="display: none"';
$optGroup	= HtmlElements::Options( $optGroup, array_keys( $readerGroups ) );

$listGroups	= HtmlTag::create( 'div', 'Keine Empfängerlisten zugewiesen.', ['class' => 'alert alert-info'] );
if( $readerGroups ){
	$listGroups	= [];
	foreach( $readerGroups as $readerGroup ){
		$label			= $readerGroup->title;
		$urlRemove		= './work/newsletter/reader/removeGroup/'.$reader->newsletterReaderId.'/'.$readerGroup->newsletterGroupId;
		$iconStatus		= HtmlTag::create( 'i', "", ['class' => 'icon-'.$statusIcons[$readerGroup->status]] );
		$attributes		= [
			'href'		=> $urlRemove,
			'class'		=> 'btn btn-mini btn-inverse',
		];
		$linkRemove		= HtmlTag::create( 'a', $iconRemove, $attributes );
		$linkRemove		= HtmlTag::create( 'div', $linkRemove, ['class' => 'pull-right'] );
		$urlGroup		= './work/newsletter/group/edit/'.$readerGroup->newsletterGroupId;
		$linkGroup		= HtmlTag::create( 'a', /*$iconStatus.' '.*/$label, ['href' => $urlGroup] );

		$listGroups[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $linkGroup, ['class' => ''] ),
			HtmlTag::create( 'td', $linkRemove, ['class' => ''] ),
		] );
	}
	$colgroup		= HtmlElements::ColumnGroup( "", "35px" );
	$tableHeads		= HtmlElements::TableHeads( ['Zugewiesene Empfängerlisten', ''] );
	$thead			= HtmlTag::create( 'thead', $tableHeads );
	$tbody			= HtmlTag::create( 'tbody', $listGroups );
	$listGroups		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, [
		'class'	=> "table table-condensed table-striped table-fixed"
	] );
}

return '
			<div class="content-panel">
				<h3>Empfängerlisten</h3>
				<div class="content-panel-inner">
					'.$listGroups.'
					<div class="row-fluid" '.$hideGroupAdd.'>
						<hr style="margin: -3px 0 6px 0"/>
						<form action="./work/newsletter/reader/addGroup/'.$reader->newsletterReaderId.'" method="post">
							<div class="span9">
								<select name="groupId" class="span12">'.$optGroup.'</select>
							</div>
							<div class="span3">
								<button type="submit" name="save" class="btn btn-success">'.$iconAdd.'</button>
							</div>
						</form>
					</div>
				</div>
			</div>
';