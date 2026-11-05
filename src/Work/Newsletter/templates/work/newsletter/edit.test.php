<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var object $newsletter */
/** @var array $groups */
/** @var array $groupIds */
/** @var string $newsletterId */
/** @var array<int, Entity_Newsletter_Reader> $readers */
/** @var array<int, Entity_Newsletter_Reader> $testers */
/** @var bool $askForReady */

$iconSelect		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
$iconSend		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-envelope'] ).'&nbsp;';
$iconPrev		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconNext		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] ).'&nbsp;';
$iconWarn		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-warning'] ).'&nbsp;';
$iconInfo		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-info-circle'] ).'&nbsp;';

$w				= (object) $words->edit_test;

$listGroups		= HtmlTag::create( 'div', '<strong>'.$iconWarn.' '.$w->alertNoGroupsTitle.'</strong><br/>
	'.$w->alertNoGroupsText, [
	'class'	=> "alert alert-danger",
] );

$disabled	= ' disabled="disabled"';
$list		= [];
foreach( $groups as $group ){
	if( Model_Newsletter_Group::STATUS_USABLE !== (int) $group->status )
		continue;
//	if( Model_Newsletter_Group::TYPE_TEST !== (int) $group->type )
//		continue;
	$disabled	= '';
	$count		= count( $group->readers );
	$checkbox	= HtmlTag::create( 'input', NULL, [
		'type'		=> 'checkbox',
		'checked'	=> in_array( $group->newsletterGroupId, $groupIds ) ? 'checked' : NULL,
		'name'		=> 'groupIds[]',
		'value'		=> $group->newsletterGroupId,
		'disabled'	=> 0 === $count ? 'disabled' : NULL,
	] );
	$title		= $checkbox.'&nbsp;'.$group->title.' ('.$count.')';
	$label		= HtmlTag::create( 'label', $title, ['class' => 'checkbox'] );
	$list[]		= $label;
}
if( [] !== $list ){
	$listGroups		= '
<div class="alert alert-info">
	<strong>'.$w->alertInfoTitle.'</strong><br/>
	<br/>
	'.$w->alertInfoText.'<br/>
</div>
<div class="row-fluid">
	<label for="input_groupIds">'.$w->labelGroups.' <small class="muted">'.$w->labelGroupsHint.'</small></label>
	<div class="checkbox-list">'.join( $list ).'</div>
</div>';
}

$panelGroups	= '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./work/newsletter/edit/'.$newsletterId.'" method="post">
			'.$listGroups.'
			<div class="buttonbar">
				<button type="submit" name="select" class="btn btn-primary" '.$disabled.'>'.$iconSelect.$w->buttonSelect.'</button>
			</div>
		</form>
	</div>
</div>';

$panelReaders	= '';
if( [] === $groupIds )
	$panelReaders	= '<div class="alert">'.$w->alertNoGroupSelected.'</div>';
else
	$panelReaders	= '<div class="alert alert-error">'.$iconWarn.'&nbsp;'.$w->alertNoTesters.'</div>';

$disabled		= ' disabled="disabled"';
if( $testers ){
	$list	= [];
	foreach( $testers as $reader ){
		$label	= $reader->firstname.' '.$reader->surname.' <small class="muted">&lt;'.$reader->email.'&gt;</small>';
		$input	= HtmlTag::create( 'input', NULL, [
			'type'		=> 'checkbox',
			'name'		=> 'readerIds[]',
			'checked'	=> 'checked',
			'value'		=> $reader->newsletterReaderId,
		] );
		$list[]	= HtmlTag::create( 'label', $input.'&nbsp;'.$label, ['class' => 'checkbox'] );
	}
	$disabled		= '';

	$panelReaders	= '
<div class="content-panel content-panel-form" xmlns="http://www.w3.org/1999/html">
	<h3>'.$w->headingTesters.'</h3>
	<div class="content-panel-inner">
<!--		<div class="alert alert-info">
			Der Newsletter wird direkt an die Testempfänger (ohne Newsletter- oder E-Mail-Queue) versendet.<br/>
			<strong>Dieser Vorgang kann nicht aufgehalten werden.</strong>
		</div>-->
		<form action="./work/newsletter/test/' .$newsletterId.'" method="post">
			<div class="row-fluid">
				<label>'.$w->labelTesters.'</label>
				<div class="checkbox-list">'.join( '', $list ).'</div>
			</div>
			<div class="row-fluid">
				<div class="alert alert-warning">
					<p>
						<strong>'.$w->alertSendNow.'</strong><br/>
					</p>
					<p>
						'.$iconInfo.'&nbsp;'.$w->alertSendDuration.'<br/>
					</p>
					<p>
						<i class="fa fa-fw fa-thumbs-o-up"></i> '.$w->alertSendFast.'
					</p>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="send" class="btn btn-primary"'.$disabled.'>'.$iconSend.$w->buttonSend.'</button>
			</div>
		</form>
	</div>
</div>';
}

return '
<div class="row-fluid">
	<div class="span5">
		'.$panelGroups.'
	</div>
	<div class="span7">
		'.$panelReaders.'
	</div>
</div>
<div id="model-askForReady" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="myModalLabel">'.$w->modalHeading.'</h3>
	</div>
	<div class="modal-body">
		<p>
			'.$w->modalTextSent.'
		</p>
		<p>
			'.sprintf( $w->modalTextReady, './work/newsletter/setStatus/'.$newsletterId.'/'.Model_Newsletter::STATUS_READY.'?forwardTo=setContentTab/'.$newsletterId.'/4' ).'
		</p>
		<p>
			'.sprintf( $w->modalTextChanges, './work/newsletter/setContentTab/'.$newsletterId.'/1' ).'
		</p>
	</div>
	<div class="modal-footer">
		<a class="btn" href="./work/newsletter/setContentTab/'.$newsletterId.'/1">'.$iconPrev.$w->modalButtonChanges.'</a>
		<a class="btn btn-primary" href="./work/newsletter/setStatus/'.$newsletterId.'/'.Model_Newsletter::STATUS_READY.'?forwardTo=setContentTab/'.$newsletterId.'/4">'.$iconNext.$w->modalButtonReady.'</a>
	</div>
</div>
<script>
jQuery(document).ready(function(){
	if('.( (int) $askForReady ).'){
		jQuery("#model-askForReady").modal();
	}
});
</script>';
