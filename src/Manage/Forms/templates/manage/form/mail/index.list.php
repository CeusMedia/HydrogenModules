<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var array<object> $mails List of form mails */
/** @var int $page */
/** @var int $pages */

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconView	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconForm	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-th'] );

$formats	= [
	0	=> 'nicht definiert',
	1	=> 'Text',
	2	=> 'HTML',
];

$roleTypeMap	= [
	Model_Form_Mail::ROLE_TYPE_NONE				=> 'keinen',
	Model_Form_Mail::ROLE_TYPE_CUSTOMER_ALL		=> 'Kunde',
	Model_Form_Mail::ROLE_TYPE_CUSTOMER_RESULT	=> 'Kunde: Ergebnis',
	Model_Form_Mail::ROLE_TYPE_CUSTOMER_REACT	=> 'Kunde: Reaktion',
	Model_Form_Mail::ROLE_TYPE_LEADER_ALL		=> 'Leiter',
	Model_Form_Mail::ROLE_TYPE_LEADER_RESULT	=> 'Leiter: Ergebnis',
	Model_Form_Mail::ROLE_TYPE_LEADER_REACT		=> 'Leiter: Reaktion',
	Model_Form_Mail::ROLE_TYPE_MANAGER_ALL		=> 'Manager',
	Model_Form_Mail::ROLE_TYPE_MANAGER_RESULT	=> 'Manager: Ergebnis',
	Model_Form_Mail::ROLE_TYPE_MANAGER_REACT	=> 'Manager: Reaktion',
];


$modelForm	= new Model_Form( $env );

$rows		= [];
foreach( $mails as $mail ){
	$linkView	= HtmlTag::create( 'a', $iconView.'&nbsp;anzeigen', [
		'href'	=> './manage/form/mail/view/'.$mail->mailId,
		'class'	=> 'btn btn-mini btn-info',
	] );
	$linkEdit	= HtmlTag::create( 'a', $mail->title, ['href' => './manage/form/mail/edit/'.$mail->mailId] );
	$nrForms	= $modelForm->countByIndex( 'customerMailId', $mail->mailId ) + $modelForm->countByIndex( 'managerMailId', $mail->mailId );
	$rows[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', HtmlTag::create( 'small', $mail->mailId ), ['style' => 'text-align: right'] ),
		HtmlTag::create( 'td', $linkEdit, ['class' => 'autocut'] ),
/*		HtmlTag::create( 'td', '<small><tt>'.$mail->identifier.'</tt></small>' ),*/
		HtmlTag::create( 'td', HtmlTag::create( 'small', $roleTypeMap[$mail->roleType] ) ),
		HtmlTag::create( 'td', $formats[$mail->format] ),
		HtmlTag::create( 'td', $nrForms, ['style' => 'text-align: right'] ),
		HtmlTag::create( 'td', $linkView ),
	) );
}
$colgroup	= HtmlElements::ColumnGroup( '40px', '', /*'30%',*/ '140px', '70px', '40px', '100px' );
$thead		= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
	HtmlTag::create( 'th', 'ID', ['style' => 'text-align: right'] ),
	HtmlTag::create( 'th', 'Titel' ),
/*	HtmlTag::create( 'th', 'Shortcode' ),*/
	HtmlTag::create( 'th', 'Nutzung' ),
	HtmlTag::create( 'th', 'Format' ),
	HtmlTag::create( 'th', HtmlTag::create( 'abbr', $iconForm, ['title' => 'Formulare'] ), ['style' => 'text-align: right'] ),
) ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-fixed table-striped table-condensed'] );

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;neue Formular-E-Mail', [
	'href'	=> './manage/form/mail/add',
	'class'	=> 'btn btn-success'
] );

$pagination	= new PageControl( './manage/form/mail', $page, $pages );

return '
<div class="content-panel">
	<h3>E-Mails</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$buttonAdd.'
			'.$pagination.'
		</div>
	</div>
</div>';
