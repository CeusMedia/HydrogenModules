<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,array<string,string>> $words */
/** @var object $reader */

$status	= $words['reader-states'][$reader->status];

$panelReader	= '
<h4>Ihre Daten</h4>
<dl class="dl-horizontal">
	<dt>E-Mail-Adresse</dt>
	<dd><span>'.$reader->email.'</span></dd>
	<dt>Vor- und Nachname</dt>
	<dd>'.$reader->firstname.' '.$reader->surname.'</dd>
	<dt>Institution</dt>
	<dd>'.( $reader->institution ?: '-' ).'</dd>
	<dt>Registriert am/um</dt>
	<dd>'.date( 'd.m.Y H:i', $reader->registeredAt ).'</dd>
	<dt>active</dt>
	<dd>'.$status.'</dd>
</dl>
';

#print_m( $subscriptions );
#die;

$readerGroups	= [];
foreach( $subscriptions as $subscription )
	$readerGroups[]	= $subscription->newsletterGroupId;

$list	= [];
foreach( $groups as $group ){
	$checkbox	= HtmlTag::create( 'input', NULL, [
		'type'		=> 'checkbox',
		'name'		=> 'groups[]',
		'value'		=> $group->newsletterGroupId,
		'checked'	=> in_array( $group->newsletterGroupId, $readerGroups ) ? 'checked' : NULL
	] );
	$label	= HtmlTag::create( 'label', $checkbox.'&nbsp;'.$group->title, ['class' => 'checkbox'] );
	$list[]	= HtmlTag::create( 'li', $label );
}
$list	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );

$panelGroups	= '<h4>Ihre Themen</h4>
'.$list;

return '
<h3>Ihre Eintragung beim Newsletter</h3>
<div class="row-fluid">
	<div class="span6">
		Sie haben sich am Newsletter registriert. Hier können Sie:
		<ul>
			<li>ihre Registrierungsdaten einsehen</li>
			<li>Themen abonnieren, über die sie informiert werden wollen</li>
			<li>E-Mails zu Themen abbstellen</li>
			<li>ihre Registrierung auflösen, falls keine Interesse mehr besteht</li>
		</ul>
		<br/>
		<small class="muted">
			<b>Hinweise nach Teledienstdatenschutzgesetz (TDDSG):</b>
			<ul>
				<li>Nach der Anmeldung erhalten Sie eine Bestätigung des Abonnements per E-Mail.</li>
				<li>Ihre Daten werden keinen Dritten zugänglich gemacht.</li>
				<li>Personenbezogene Daten werden ausschließlich für den Versand des Newsletters verwendet.</li>
				<li>Ihre Daten werden nach Wegfall dieser Zweckbestimmung von uns gelöscht.</li>
				<li>Sie können jederzeit Ihr Newsletter-Abonnement löschen.</li>
			</ul>
		</small>
	</div>
	<div class="span6">
		<form action="./info/newsletter/edit/'.$readerId.'/'.$letterId.'/'.$key.'" method="post">
			'.$panelReader.'
			'.$panelGroups.'
			<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i>&nbsp;speichern</button>
			<a href="#" class="btn btn-small btn-danger"><i class="icon-remove icon-white"></i>&nbsp;komplett abmelden</a>
		</form>
	</div>
</div>';
