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

$listGroups		= HtmlTag::create( 'div', '<strong>'.$iconWarn.' Keine verwendbare Empfängerliste vorhanden.</strong><br/>
	Bitte zuerst eine <a href="./work/newsletter/group">Empfängerliste</a> anlegen!<br/>
	Diese Empfängerliste muss verwendbar sein und Empfänger sowie Test-Empfänger beinhalten.', [
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
	<h3>Empfängerlisten auswählen</h3>
	<div class="content-panel-inner">
		<form action="./work/newsletter/edit/'.$newsletterId.'" method="post">
			<div class="alert alert-info">
				<strong>Die Kampagne kann nun an reelle Benutzer versendet werden.</strong><br/>
				Wählen Sie hier eine oder mehrere Empfängerlisten aus, deren Benutzer die Kampagne empfangen sollen.<br/>
			</div>
			<div class="row-fluid">
				<label for="input_groupIds">Alle Leser in den Empfängerlisten <small class="muted">(Mehrfachauswahl ist möglich)</small></label>
				<div class="checkbox-list">'.$listGroups.'</div>
			</div>
			<div class="buttonbar">
				<div class="alert alert-info">
					<strong>Diese Auswahl löst den Versand noch nicht aus.</strong><br/>
					Im nächsten Schritt müssen die Empfänger erst noch bestätigt werden.
				</div>
				<button type="submit" name="select" class="btn btn-primary" '.$disabled.'>'.$iconSelect.'auswählen</button>
			</div>
		</form>
	</div>
</div>';

$disabled	= ' disabled="disabled"';

$list	= [];
if( $nrReaders || [] !== $readers ){
	if( $nrReaders && [] === $readers ) {
		$label	= 'An alle Abonnenten. <small class="muted">(Die vollständige Liste wird bei '.$nrReaders.' Empfängern nicht angezeigt.)</small>';
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
			$tester		= $reader->tester ? HtmlTag::create( 'span', 'Tester', ['class' => 'label label-info pull-right'] ) : '';
			$list[]	= HtmlTag::create( 'label', $input.'&nbsp;'.$label.$tester, ['class' => 'checkbox'] );
		}
	}

	$list	= join( '', $list );
	$disabled	= '';
	$list		= '
		<div class="content-panel content-panel-form">
			<h3>Empfänger bestätigen</h3>
			<div class="content-panel-inner">
				<form action="./work/newsletter/enqueue/'.$newsletterId.'" method="post">
					<input type="hidden" name="groupIds" value="'.join( ',', $groupIds ).'"/>
					<div class="row-fluid">
						<div class="span12">
<!--							<div class="alert alert-info">
								Das Absenden dieser Liste reiht die Newsletter in der Warteschlange des Versandsystems ein.<br/>
								Die E-Mails an die Leser werden dann automatisch verschickt.<br/>
							</div>-->
							<label>Leser in gewählten Empfängerlisten</label>
							<div class="checkbox-list">'.$list.'</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_sendLater" class="checkbox"><input type="checkbox" name="sendLater" id="input_sendLater" class="has-optionals" value="yes" data-animation="slide"/>&nbsp;nicht jetzt, sondern später senden</label>
						</div>
					</div>
					<div class="row-fluid optional sendLater sendLater-true" style="display: none">
						<div class="span3">
							<label for="input_sendAt_date">Senden am</label>
							<input type="date" name="sendAt_date" id="input_sendAt_date" class="span12"/>
						</div>
						<div class="span2">
							<label for="input_sendAt_time">um</label>
							<input type="time" name="sendAt_time" id="input_sendAt_time" class="span12"/>
						</div>
					</div>
					<div class="buttonbar">
						<div class="alert alert-info">
							<small>Der Newsletter wird für die ausgewählten Empfänger in der Newsletter-Queue eingereiht.<br/>
							Die E-Mails werden dann sukzessive erzeugt und <abbr title="in die E-Mail-Queue">zum Versand eingereiht</abbr>.<br/>
							Die Newsletter-E-Mails werden dann mit der Zeit ausgeliefert.</small>
						</div>
						<div class="alert alert-danger">
							<strong>'.$iconWarn.'&nbsp;Achtung:</strong> Dieser Vorgang kann nicht mehr unterbrochen werden.
						</div>
						<button type="submit" name="send" class="btn btn-primary"'.$disabled.'>'.$iconSend.'versenden</button>
					</div>
				</form>
			</div>
		</div>';
}
else
	$list		= '<div class="alert"><em class="not-muted">Noch keine Empfängerliste gewählt.</em></div>';

return '
<div class="row-fluid">
	<div class="span5">
		'.$panelGroups.'
	</div>
	<div class="span7">
		'.$list.'
	</div>
</div>';
