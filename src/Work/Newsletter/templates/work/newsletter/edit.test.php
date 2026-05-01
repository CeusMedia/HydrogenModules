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

$listGroups		= HtmlTag::create( 'div', '<strong>'.$iconWarn.' Keine verwendbare Empfängerliste vorhanden.</strong><br/>
	Bitte zuerst eine <a href="./work/newsletter/group">Empfängerliste</a> anlegen!<br/>
	Diese Empfängerliste muss verwendbar sein und Empfänger sowie Test-Empfänger beinhalten.', [
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
	<strong>Vor dem Versand an die Empfänger muss die Kampagne getestet werden.</strong><br/>
	<br/>
	Wähle hier mindestens eine Empfängerliste aus.<br/>
	Die enthaltenen Test-Empfänger werden dir zu Auswahl gestellt.<br/>
	Du kannst im nächsten Schritt bestimmen, an welche<small>(n)</small> Test-Empfänger die Test-E-Mail tatsächlich gehen sollen.<br/>
</div>
<div class="row-fluid">
	<label for="input_groupIds">An alle Tester in den Empfängerlisten <small class="muted">(Mehrfachauswahl ist möglich)</small></label>
	<div class="checkbox-list">'.join( $list ).'</div>
</div>';
}

$panelGroups	= '
<div class="content-panel content-panel-form">
	<h3>Newsletter testen</h3>
	<div class="content-panel-inner">
		<form action="./work/newsletter/edit/'.$newsletterId.'" method="post">
			'.$listGroups.'
			<div class="buttonbar">
				<button type="submit" name="select" class="btn btn-primary" '.$disabled.'>'.$iconSelect.'auswählen</button>
			</div>
		</form>
	</div>
</div>';

$panelReaders	= '';
if( [] === $groupIds )
	$panelReaders	= '<div class="alert">Noch keine Empfängerliste<small>(n)</small> gewählt.</div>';
else
	$panelReaders	= '<div class="alert alert-error">'.$iconWarn.'&nbsp;Keine Test-Abonnenten vorhanden.</div>';

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
	<h3>Test-Empfänger bestätigen</h3>
	<div class="content-panel-inner">
<!--		<div class="alert alert-info">
			Der Newsletter wird direkt an die Testempfänger (ohne Newsletter- oder E-Mail-Queue) versendet.<br/>
			<strong>Dieser Vorgang kann nicht aufgehalten werden.</strong>
		</div>-->
		<form action="./work/newsletter/test/' .$newsletterId.'" method="post">
			<div class="row-fluid">
				<label>Test-Empfänger in gewählten Empfängerlisten</label>
				<div class="checkbox-list">'.join( '', $list ).'</div>
			</div>
			<div class="row-fluid">
				<div class="alert alert-warning">
					<p>
						<strong>Die Test-Newsletter-Mails werden sofort an die Empfänger versendet.</strong><br/>
					</p>
					<p>
						'.$iconInfo.'&nbsp;Das kann <abbr title="wenn mehrere Empfänger ausgewählt wurden">etwas dauern</abbr>, weil alle Schritte inklusive Mail-Versand <abbr title="alle Warteschlangen werden übersprungen">direkt ausgeführt</abbr> werden.<br/>
					</p>
					<p>
						<i class="fa fa-fw fa-thumbs-o-up"></i> Dafür sollten die Mails innerhalb weniger Sekunden ankommen.
					</p>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="send" class="btn btn-primary"'.$disabled.'>'.$iconSend.'versenden</button>
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
		<h3 id="myModalLabel">Bereit zum Versand?</h3>
	</div>
	<div class="modal-body">
		<p>
			Die Kampagne wurde testweise an die E-Mail-Adresse des Testbenutzers gesendet.
			Bitte schauen Sie in ihr E-Mail-Postfach nach dieser E-Mail.
		</p>
		<p>
			Wenn Sie mit dem Ergebnis zufrieden sind, können Sie <a href="./work/newsletter/setStatus/'.$newsletterId.'/'.Model_Newsletter::STATUS_READY.'?forwardTo=setContentTab/'.$newsletterId.'/4">zum Versand übergehen</a>.
		</p>
		<p>
			Anderenfalls nehmen Sie einfach <a href="./work/newsletter/setContentTab/'.$newsletterId.'/1">weitere Änderungen</a> vor.
		</p>
	</div>
	<div class="modal-footer">
		<a class="btn" href="./work/newsletter/setContentTab/'.$newsletterId.'/1">'.$iconPrev.'weitere Änderungen</a>
		<a class="btn btn-primary" href="./work/newsletter/setStatus/'.$newsletterId.'/'.Model_Newsletter::STATUS_READY.'?forwardTo=setContentTab/'.$newsletterId.'/4">'.$iconNext.'zum Versand übergehen</a>
	</div>
</div>
<script>
jQuery(document).ready(function(){
	if('.( (int) $askForReady ).'){
		jQuery("#model-askForReady").modal();
	}
});
</script>';
