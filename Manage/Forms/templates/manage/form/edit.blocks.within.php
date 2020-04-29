<?php
/**
 *	@deprecated		shown in view tab
 *	@todo			remove with references in module config
*/

$blocksWithin		= $this->getData( 'blocksWithin', array() );
$iconBlock			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-square' ) );
$listBlocksWithin	= UI_HTML_Tag::create( 'p', '<em class="muted">Keine.</em>' );

if( $blocksWithin ){
	$list	= array();
	foreach( $blocksWithin as $identifier => $item ){
		$link	= UI_HTML_Tag::create( 'a', $iconBlock.'&nbsp;'.$item->title, array(
			'href'	=> './manage/form/block/edit/'.$item->blockId,
		) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	if( $list )
		$listBlocksWithin	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled' ) );
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
?>
