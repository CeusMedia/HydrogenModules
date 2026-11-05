<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,string|HtmlTag> $navButtons */
/** @var array<string,array<string,string>> $words */

$w			= (object) $words['edit.blocks.within'];

/**
 *	@deprecated		shown in view tab
 *	@todo			remove with references in module config
*/

$blocksWithin		= $this->getData( 'blocksWithin', [] );
$iconBlock			= new Icon( 'square' );
$listBlocksWithin	= HtmlTag::create( 'p', '<em class="muted">'.$w->noBlocks.'</em>' );

if( $blocksWithin ){
	$list	= [];
	foreach( $blocksWithin as $identifier => $item ){
		$link	= HtmlTag::create( 'a', $iconBlock.'&nbsp;'.$item->title, [
			'href'	=> './manage/form/block/edit/'.$item->blockId,
		] );
		$list[]	= HtmlTag::create( 'li', $link );
	}
	if( $list )
		$listBlocksWithin	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
}

return '
<div class="content-panel">
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				<h4>'.$w->headingBlocks.'</h4>
				'.$listBlocksWithin.'
			</div>
		</div>
		<div class="buttonbar">
			'.$navButtons['list'].'
			'.$navButtons['prevView'].'
			'.$navButtons['nextContent'].'
		</div>
	</div>
</div>
';
