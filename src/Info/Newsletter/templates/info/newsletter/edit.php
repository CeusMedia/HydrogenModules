<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,array<string,string>> $words */
/** @var object $reader */
/** @var array $subscriptions */
/** @var array $groups */
/** @var int|string $readerId */
/** @var int|string $letterId */

$w		= (object) $words['edit'];
$status	= $words['reader-states'][$reader->status];

$panelReader	= '
<h4>'.$w->headingReader.'</h4>
<dl class="dl-horizontal">
	<dt>'.$w->labelEmail.'</dt>
	<dd><span>'.$reader->email.'</span></dd>
	<dt>'.$w->labelName.'</dt>
	<dd>'.$reader->firstname.' '.$reader->surname.'</dd>
	<dt>'.$w->labelInstitution.'</dt>
	<dd>'.( $reader->institution ?: '-' ).'</dd>
	<dt>'.$w->labelRegisteredAt.'</dt>
	<dd>'.date( 'd.m.Y H:i', $reader->registeredAt ).'</dd>
	<dt>'.$w->labelStatus.'</dt>
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

$panelGroups	= '<h4>'.$w->headingGroups.'</h4>
'.$list;

return '
<h3>'.$w->heading.'</h3>
<div class="row-fluid">
	<div class="span6">
		'.$w->textIntro.'
		<ul>
			<li>'.$w->textOption1.'</li>
			<li>'.$w->textOption2.'</li>
			<li>'.$w->textOption3.'</li>
			<li>'.$w->textOption4.'</li>
		</ul>
		<br/>
		<small class="muted">
			<b>'.$w->headingPrivacy.'</b>
			<ul>
				<li>'.$w->textPrivacy1.'</li>
				<li>'.$w->textPrivacy2.'</li>
				<li>'.$w->textPrivacy3.'</li>
				<li>'.$w->textPrivacy4.'</li>
				<li>'.$w->textPrivacy5.'</li>
			</ul>
		</small>
	</div>
	<div class="span6">
		<form action="./info/newsletter/edit/'.$readerId.'/'.$letterId.'/'.$key.'" method="post">
			'.$panelReader.'
			'.$panelGroups.'
			<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
			<a href="#" class="btn btn-small btn-danger"><i class="icon-remove icon-white"></i>&nbsp;'.$w->buttonUnregister.'</a>
		</form>
	</div>
</div>';
