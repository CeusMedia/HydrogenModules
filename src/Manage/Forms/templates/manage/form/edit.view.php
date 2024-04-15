<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,string|HtmlTag> $navButtons */

$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconPrev	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconNext	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] );
$iconBlock	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-square'] );

$listBlocksWithin	= HtmlTag::create( 'p', '<em class="muted">Keine.</em>' );
if( $blocksWithin = $this->getData( 'blocksWithin', [] ) ){
	$list	= [];
	foreach( $blocksWithin as $identifier => $item ){
		$link	= HtmlTag::create( 'a', $iconBlock.'&nbsp;'.$item->title, [
			'href'	=> './manage/form/block/edit/'.$item->blockId,
		], [
			'identifier'	=> $item->identifier,
		] );
		$list[]	= HtmlTag::create( 'li', $link );
	}
	if( $list )
		$listBlocksWithin	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
}

return '
<div class="row-fluid">
	<div class="span9">
		<div class="content-panel">
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span12" id="shadow-form"></div>
				</div>
				<div class="buttonbar">
					'.$navButtons['list'].'
					'.$navButtons['prevFacts'].'
<!--					'./*$navButtons['nextBlocks'].*/'-->
					'.$navButtons['nextContent'].'
				</div>
			</div>
		</div>
	</div>
	<div class="span3">
		<div class="content-panel" not-data-spy="affix" not-data-offset-top="0">
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span12" id="list-blocks-within">
						<h4>Verwendete Blöcke</h4>
						'.$listBlocksWithin.'
						<div class="row-fluid">
							<div class="span12">
								<label class="checkbox">
									<input type="checkbox" id="show-blocks"/>
									zeige Blöcke</label>
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
';
