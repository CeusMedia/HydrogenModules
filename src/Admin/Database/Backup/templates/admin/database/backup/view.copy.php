<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $backup */
/** @var string $currentCopyPrefix */

$w				= (object) $words['view.copy'];

$iconRestore	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cog'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$buttonCreateCopy	= HtmlTag::create( 'a', $iconRestore.' '.$w->buttonCreate, [
	'href'	=> './admin/database/backup/copy/create/'.$backup->id,
	'class'	=> 'btn btn-primary'
] );
$facts					= $w->description;
$buttonActivateCopy		= '';
$buttonDeactivateCopy	= '';
$buttonRemoveCopy		= '';
if( !empty( $backup->comment['copyPrefix'] ) ){
	$buttonCreateCopy		= '';
	$buttonActivateCopy		= HtmlTag::create( 'a', $iconRemove.'&nbsp; '.$w->buttonActivate, [
		'href'	=> './admin/database/backup/copy/activate/'.$backup->id,
		'class'	=> 'btn btn-success'
	] );
	$buttonRemoveCopy	= HtmlTag::create( 'a', $iconRemove.' '.$w->buttonRemove, [
		'href'	=> './admin/database/backup/copy/drop/'.$backup->id,
		'class'	=> 'btn btn-danger'
	] );
	$facts	= '
		<dl class="dl-horizontal">
			<dt>'.$w->labelPrefix.'</dt>
			<dd>'.$backup->comment['copyPrefix'].'</dd>
			<dt>'.$w->labelCreatedAt.'</dt>
			<dd>'.date( 'Y-m-d H:i:s', (float) $backup->comment['copyTimestamp'] ).'</dd>
		</dl>';
	if( $backup->comment['copyPrefix'] === $currentCopyPrefix ){
		$buttonActivateCopy		= '';
		$buttonRemoveCopy		= '';
		$buttonDeactivateCopy	= HtmlTag::create( 'a', $iconRemove.'&nbsp; '.$w->buttonDeactivate, [
			'href'	=> './admin/database/backup/copy/deactivate/'.$backup->id,
			'class'	=> 'btn btn-inverse'
		] );
	}
}

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.$facts.'
			</div>
		</div>
		<div class="buttonbar">
			'.$buttonCreateCopy.'
			'.$buttonActivateCopy.'
			'.$buttonDeactivateCopy.'
			'.$buttonRemoveCopy.'
		</div>
	</div>
</div>';
