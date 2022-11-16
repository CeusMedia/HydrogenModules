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
/** @var array $readers */
/** @var bool $askForReady */

$iconSelect		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
$iconSend		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-envelope'] ).'&nbsp;';
$iconPrev		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconNext		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] ).'&nbsp;';

$listGroups		= '<div class="alert alert-danger">
	<strong>Keine verwendbare Testgruppe vorhanden.</strong><br/>
	Bitte zuerst eine Testgruppe anlegen!<br/>
	Diese Testgruppe muss Empfänger beinhalten und verwendbar sein.
</div>';
$disabled		= ' disabled="disabled"';
$list			= [];
foreach( $groups as $group ){
	if( (int) $group->status !== 1 )
		continue;
	if( (int) $group->type !== 1 )
		continue;
	$disabled	= '';
	$checkbox	= HtmlTag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'checked'	=> in_array( $group->newsletterGroupId, $groupIds ) ? 'checked' : NULL,
		'name'		=> 'groupIds[]',
		'value'		=> $group->newsletterGroupId,
	) );
	$title		= $checkbox.'&nbsp;'.$group->title.' ('.count( $group->readers ).')';
	$label		= HtmlTag::create( 'label', $title, ['class' => 'checkbox'] );
	$list[]		= $label;
}
if( $list ){
	$listGroups		= '
<div class="alert alert-info">
	<strong>Vor dem Versand an angemeldete Benutzer muss die Kampagne getestet werden.</strong><br/>
	<br/>
	Wähle hier eine (oder mehrere Testgruppen, falls vorhanden) aus.
	Die enthaltenen Benutzer werden dir zu Auswahl gestellt.
	Du kannst im nächsten Schritt bestimmen, an welche(n) Testbenutzer die Test-E-Mail tatsächlich gehen sollen.
</div>
<div class="row-fluid">
	<label for="input_groupIds">An alle Tester in den Gruppen <small class="muted">(Mehrfachauswahl ist möglich)</small></label>
	<div class="checkbox-list">'.join( $list ).'</div>
</div>';
}

$panelGroups	= '
<div class="content-panel content-panel-form">
	<h3>Testgruppen auswählen</h3>
	<div class="content-panel-inner">
		<form action="./work/newsletter/edit/'.$newsletterId.'" method="post">
			'.$listGroups.'
			<div class="buttonbar">
				<button type="submit" name="select" class="btn btn-primary" '.$disabled.'>'.$iconSelect.'auswählen</button>
			</div>
		</form>
	</div>
</div>';

$list		= '<div class="alert"><em><small class="not-muted">Keine Testgruppe gewählt.</small></em></div>';
$disabled	= ' disabled="disabled"';
if( $readers ){
	$list	= [];
	foreach( $readers as $reader ){
		$label	= $reader->firstname.' '.$reader->surname.' <small class="muted">&lt;'.$reader->email.'&gt;</small>';
		$list[]	= '<label class="checkbox"><input type="checkbox" name="readerIds[]" checked="checked" value="'.$reader->newsletterReaderId.'"/> '.$label.'</label>';
	}
	$list	= join( '', $list );
	$disabled	= '';
	$list		= '
<div class="content-panel content-panel-form">
	<h3>Empfänger bestätigen</h3>
	<div class="content-panel-inner">
<!--		<div class="alert alert-info">
			Der Newsletter wird direkt an die Testempfänger (ohne Newsletter- oder E-Mail-Queue) versendet.<br/>
			<strong>Dieser Vorgang kann nicht aufgehalten werden.</strong>
		</div>-->
		<form action="./work/newsletter/test/'.$newsletterId.'" method="post">
			<div class="row-fluid">
				<label>Leser in gewählten Gruppen</label>
				<div class="checkbox-list">'.$list.'</div>
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
		'.$list.'
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
