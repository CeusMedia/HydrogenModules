<?php
$modelBlock	= new Model_Form_Block( $env );

$iconBlock	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-square' ) );

$withinBlocks	= array();
$listBlocksWithin	= UI_HTML_Tag::create( 'p', '<em class="muted">Keine.</em>' );
$matches		= array();
preg_match_all( '/\[block_(\S+)\]/', $form->content, $matches );
if( isset( $matches[0] ) && count( $matches[0] ) ){
	$list	= array();
	foreach( array_keys( $matches[0] ) as $nr ){
		$item	= $modelBlock->getByIndex( 'identifier', $matches[1][$nr] );
		if( !$item )
			continue;
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
