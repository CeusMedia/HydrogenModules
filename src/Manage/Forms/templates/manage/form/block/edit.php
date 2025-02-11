<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var Entity_Form_Block $block */
/** @var array<Entity_Form> $withinForms */
/** @var array<Entity_Form_Block> $withinBlocks */

//$modelForm	= new Model_Form( $env );
$modelBlock	= new Model_Form_Block( $env );

$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconView	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconBlock	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-square'] );
$iconForm	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-th'] );

$listWithinForms	= HtmlTag::create( 'p', '<em class="muted">Keine.</em>' );
if( $withinForms ){
	$list	= [];
	foreach( $withinForms as $item ){
		$link	= HtmlTag::create( 'a', $iconForm.'&nbsp;'.$item->title, [
			'href'	=> './manage/form/edit/'.$item->formId,
		] );
		$list[]	= HtmlTag::create( 'li', $link );
	}
	$listWithinForms	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
}

$listWithinBlocks	= HtmlTag::create( 'p', '<em class="muted">Keine.</em>' );
if( $withinBlocks ){
	$list	= [];
	foreach( $withinBlocks as $item ){
		$link	= HtmlTag::create( 'a', $iconBlock.'&nbsp;'.$item->title, [
			'href'	=> './manage/form/block/edit/'.$item->blockId,
		] );
		$list[]	= HtmlTag::create( 'li', $link );
	}
	$listWithinBlocks	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
}

$listBlocksWithin	= HtmlTag::create( 'p', '<em class="muted">Keine.</em>' );
$matches		= [];
preg_match_all( '/\[block_(\S+)\]/', $block->content, $matches );
if( isset( $matches[0] ) && count( $matches[0] ) ){
	$list	= [];
	foreach( array_keys( $matches[0] ) as $nr ){
		$item	= $modelBlock->getByIndex( 'identifier', $matches[1][$nr] );
		if( !$item )
			continue;
		$link	= HtmlTag::create( 'a', $iconBlock.'&nbsp;'.$item->title, [
			'href'	=> './manage/form/block/edit/'.$item->blockId,
		] );
		$list[]	= HtmlTag::create( 'li', $link );
	}
	if( $list )
		$listBlocksWithin	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
}

$env->getPage()->js->addScriptOnReady('FormEditor.applyAceEditor("#input_content");');

return '
<h2><span class="muted">Block:</span> '.$block->title.'</h2>
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./manage/form/block/edit/'.$block->blockId.'" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $block->title, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="span6">
					<label for="input_identifier" class="mandatory">Shortcode</label>
					<input type="text" name="identifier" id="input_identifier" class="span12" value="'.htmlentities( $block->identifier, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
			</div>
			<div class="row-fluid" style="margin-bottom: 1em">
				<div class="span12">
					<label for="input_content">Inhalt</label>
					<textarea name="content" id="input_content" class="span12" rows="25">'.htmlentities( $block->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/form/block" class="btn">'.$iconList.' zur Liste</a>
				<a href="./manage/form/block/view/'.$block->blockId.'" class="btn btn-info">'.$iconView.' anzeigen</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.' speichern</button>
				'.HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', [
					'href'		=> ( $withinForms || $withinBlocks ) ? NULL : './manage/form/block/remove/'.$block->blockId,
					'class'		=> 'btn btn-danger',
					'disabled'	=> ( $withinForms || $withinBlocks ) ? 'disabled' : NULL,
					'onclick'	=> 'return confirm("Wirklich ?");',
				] ).'
			</div>
		</form>
	</div>
</div>
<hr/>
<div class="content-panel">
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				<h4>Verwendung in Formularen</h4>
				'.$listWithinForms.'
			</div>
			<div class="span3">
				<h4>Verwendung in Blöcken</h4>
				'.$listWithinBlocks.'
			</div>
			<div class="span3">
				<h4>Verwendete Blöcke</h4>
				'.$listBlocksWithin.'
			</div>
		</div>
	</div>
</div>
';
