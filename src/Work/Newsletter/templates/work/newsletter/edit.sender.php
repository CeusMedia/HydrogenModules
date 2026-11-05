<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var array $groups */
/** @var array $groupIds */
/** @var string $newsletterId */
/** @var int $nrReaders */
/** @var array $readers */

$iconSelect		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
$iconSend		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-envelope'] ).'&nbsp;';
$iconWarn		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-warning'] ).'&nbsp;';
$iconInfo		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-info-circle'] ).'&nbsp;';

$w				= (object) $words->edit_sender;

$listGroups		= HtmlTag::create( 'div', '<strong>'.$iconWarn.' '.$w->alertNoGroupsTitle.'</strong><br/>
	'.$w->alertNoGroupsText, [
	'class'	=> "alert alert-danger",
] );

$disabled		= ' disabled="disabled"';
$list			= [];
foreach( $groups as $group ){
	if( (int) $group->status !== 1 )
		continue;
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
$listGroups		= $list ? join( '', $list ) : $listGroups;
$panelGroups	= '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./work/newsletter/edit/'.$newsletterId.'" method="post">
			<div class="alert alert-info">
				<strong>'.$w->alertInfoTitle.'</strong><br/>
				'.$w->alertInfoText.'<br/>
			</div>
			<div class="row-fluid">
				<label for="input_groupIds">'.$w->labelGroups.' <small class="muted">'.$w->labelGroupsHint.'</small></label>
				<div class="checkbox-list">'.$listGroups.'</div>
			</div>
			<div class="buttonbar">
				<div class="alert alert-info">
					<strong>'.$w->alertSelectTitle.'</strong><br/>
					'.$w->alertSelectText.'
				</div>
				<button type="submit" name="select" class="btn btn-primary" '.$disabled.'>'.$iconSelect.$w->buttonSelect.'</button>
			</div>
		</form>
	</div>
</div>';

$disabled	= ' disabled="disabled"';

$list	= [];
if( $nrReaders || [] !== $readers ){
	if( $nrReaders && [] === $readers ) {
		$label	= $w->labelAllReaders.' <small class="muted">'.sprintf( $w->labelAllReadersHint, $nrReaders ).'</small>';
		$input	= HtmlTag::create( 'input', NULL, [
			'type'		=> 'checkbox',
			'name'		=> 'readerIds[]',
			'checked'	=> 'checked',
			'value'		=> '*',
		] );
		$list[]	= HtmlTag::create( 'label', $input.'&nbsp;'.$label, ['class' => 'checkbox'] );
	}
	else if( [] !== $readers ){
		foreach( $readers as $reader ){
			$label	= $reader->firstname.' '.$reader->surname.' <small class="muted">&lt;'.$reader->email.'&gt;</small>';
			$input	= HtmlTag::create( 'input', NULL, [
				'type'		=> 'checkbox',
				'name'		=> 'readerIds[]',
				'checked'	=> 'checked',
				'value'		=> $reader->newsletterReaderId,
			] );
			$tester		= $reader->tester ? HtmlTag::create( 'span', $w->labelTester, ['class' => 'label label-info pull-right'] ) : '';
			$list[]	= HtmlTag::create( 'label', $input.'&nbsp;'.$label.$tester, ['class' => 'checkbox'] );
		}
	}

	$list	= join( '', $list );
	$disabled	= '';
	$list		= '
		<div class="content-panel content-panel-form">
			<h3>'.$w->headingReaders.'</h3>
			<div class="content-panel-inner">
				<form action="./work/newsletter/enqueue/'.$newsletterId.'" method="post">
					<input type="hidden" name="groupIds" value="'.join( ',', $groupIds ).'"/>
					<div class="row-fluid">
						<div class="span12">
<!--							<div class="alert alert-info">
								Das Absenden dieser Liste reiht die Newsletter in der Warteschlange des Versandsystems ein.<br/>
								Die E-Mails an die Leser werden dann automatisch verschickt.<br/>
							</div>-->
							<label>'.$w->labelReaders.'</label>
							<div class="checkbox-list">'.$list.'</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_sendLater" class="checkbox"><input type="checkbox" name="sendLater" id="input_sendLater" class="has-optionals" value="yes" data-animation="slide"/>&nbsp;'.$w->labelSendLater.'</label>
						</div>
					</div>
					<div class="row-fluid optional sendLater sendLater-true" style="display: none">
						<div class="span3">
							<label for="input_sendAt_date">'.$w->labelSendAtDate.'</label>
							<input type="date" name="sendAt_date" id="input_sendAt_date" class="span12"/>
						</div>
						<div class="span2">
							<label for="input_sendAt_time">'.$w->labelSendAtTime.'</label>
							<input type="time" name="sendAt_time" id="input_sendAt_time" class="span12"/>
						</div>
					</div>
					<div class="buttonbar">
						<div class="alert alert-info">
							<small>'.$w->alertEnqueueInfo.'</small>
						</div>
						<div class="alert alert-danger">
							<strong>'.$iconWarn.'&nbsp;'.$w->alertWarningLabel.'</strong> '.$w->alertWarningText.'
						</div>
						<button type="submit" name="send" class="btn btn-primary"'.$disabled.'>'.$iconSend.$w->buttonSend.'</button>
					</div>
				</form>
			</div>
		</div>';
}
else
	$list		= '<div class="alert"><em class="not-muted">'.$w->alertNoGroupSelected.'</em></div>';

return '
<div class="row-fluid">
	<div class="span5">
		'.$panelGroups.'
	</div>
	<div class="span7">
		'.$list.'
	</div>
</div>';
