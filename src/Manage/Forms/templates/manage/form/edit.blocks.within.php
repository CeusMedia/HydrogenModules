<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/**
 *	@deprecated		shown in view tab
 *	@todo			remove with references in module config
*/

$blocksWithin		= $this->getData( 'blocksWithin', [] );
$iconBlock			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-square'] );
$listBlocksWithin	= HtmlTag::create( 'p', '<em class="muted">Keine.</em>' );

if( $blocksWithin ){
	$list	= [];
	foreach( $blocksWithin as $identifier => $item ){
		$link	= HtmlTag::create( 'a', $iconBlock.'&nbsp;'.$item->title, array(
			'href'	=> './manage/form/block/edit/'.$item->blockId,
		) );
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
				<h4>Verwendete Blöcke</h4>
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
