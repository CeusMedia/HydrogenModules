<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconSelect		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) ).'&nbsp;';
$iconSend		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-envelope' ) ).'&nbsp;';

$listGroups		= '<div class="alert alert-danger"><strong>Keine Testgruppe vorhanden.</strong><br/>Bitte zuerst eine Testgruppe mit Empfängern anlegen!</div>';
$disabled		= ' disabled="disabled"';
$list			= [];
foreach( $groups as $group ){
	if( (int) $group->status !== 1 )
		continue;
	$disabled	= '';
	$checkbox	= HtmlTag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'checked'	=> in_array( $group->newsletterGroupId, $groupIds ) ? 'checked' : NULL,
		'name'		=> 'groupIds[]',
		'value'		=> $group->newsletterGroupId,
	) );
	$title		= $checkbox.'&nbsp;'.$group->title.' ('.count( $group->readers ).')';
	$label		= HtmlTag::create( 'label', $title, array( 'class' => 'checkbox' ) );
	$list[]		= $label;
}
$listGroups		= $list ? join( '', $list ) : $listGroups;
$panelGroups	= '
<div class="content-panel content-panel-form">
	<h3>Empfängergruppen auswählen</h3>
	<div class="content-panel-inner">
		<form action="./work/newsletter/edit/'.$newsletterId.'" method="post">
			<div class="alert alert-info">
				<strong>Die Kampagne kann nun an reelle Benutzer versendet werden.</strong><br/>
				Wählen Sie hier eine oder <abbr title="Dazu Taste STRG drücken und mit der Maus auf die Gruppen klicken">mehrere</abbr> Gruppen aus, deren Benutzer die Kampagne empfangen sollen.<br/>
<!--				<br/>
				<small>Der Newsletter wird für die ausgewählten Empfänger in der Newsletter-Queue eingereiht.
				Diese Queue übergibt die E-Mails sukzessive an die E-Mail-Queue.
				Die Newsletter-E-Mails werden dann mit der Zeit ausgeliefert.</small>
				<br/>-->
			</div>
			<div class="row-fluid">
				<label for="input_groupIds">Alle Leser in den Gruppen <small class="muted">(Mehrfachauswahl ist möglich)</small></label>
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

$list		= '<div class="alert"><em class="not-muted">Keine Gruppe gewählt.</em></div>';
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
				<form action="./work/newsletter/enqueue/'.$newsletterId.'" method="post">
					<div class="row-fluid">
<!--						<div class="alert alert-info">
							Das Absenden dieser Liste reiht die Newsletter in der Warteschlange des Versandsystems ein.<br/>
							Die E-Mails an die Leser werden dann automatisch verschickt.<br/>
						</div>-->
						<label>Leser in gewählten Gruppen</label>
						<div class="checkbox-list">'.$list.'</div>
						<div class="buttonbar">
							<div class="alert alert-danger">
								<strong>Achtung:</strong> Dieser Vorgang kann nicht mehr unterbrochen werden.
							</div>
							<button type="submit" name="send" class="btn btn-primary"'.$disabled.'>'.$iconSend.'versenden</button>
						</div>
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
</div>';
