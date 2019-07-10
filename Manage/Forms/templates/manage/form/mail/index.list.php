<?php
$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconForm	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-th' ) );

$formats	= array(
	0	=> 'nicht definiert',
	1	=> 'Text',
	2	=> 'HTML',
);

$roleTypeMap	= array(
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
);


$modelForm	= new Model_Form( $env );

$rows		= array();
foreach( $mails as $mail ){
	$linkView	= UI_HTML_Tag::create( 'a', $iconView.'&nbsp;anzeigen', array(
		'href'	=> './manage/form/mail/view/'.$mail->mailId,
		'class'	=> 'btn btn-mini btn-info',
	) );
	$linkEdit	= UI_HTML_Tag::create( 'a', $mail->title, array( 'href' => './manage/form/mail/edit/'.$mail->mailId ) );
	$nrForms	= $modelForm->countByIndex( 'customerMailId', $mail->mailId ) + $modelForm->countByIndex( 'managerMailId', $mail->mailId );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $mail->mailId ), array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'td', $linkEdit, array( 'class' => 'autocut' ) ),
/*		UI_HTML_Tag::create( 'td', '<small><tt>'.$mail->identifier.'</tt></small>' ),*/
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $roleTypeMap[$mail->roleType] ) ),
		UI_HTML_Tag::create( 'td', $formats[$mail->format] ),
		UI_HTML_Tag::create( 'td', $nrForms, array( 'style' => 'text-align: right' ) ),
		UI_HTML_Tag::create( 'td', $linkView ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( '40px', '', /*'30%',*/ '140px', '70px', '40px', '100px' );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
	UI_HTML_Tag::create( 'th', 'ID', array( 'style' => 'text-align: right' ) ),
	UI_HTML_Tag::create( 'th', 'Titel' ),
/*	UI_HTML_Tag::create( 'th', 'Shortcode' ),*/
	UI_HTML_Tag::create( 'th', 'Nutzung' ),
	UI_HTML_Tag::create( 'th', 'Format' ),
	UI_HTML_Tag::create( 'th', UI_HTML_Tag::create( 'abbr', $iconForm, array( 'title' => 'Formulare' ) ), array( 'style' => 'text-align: right' ) ),
) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed table-striped table-condensed' ) );

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Formular-E-Mail', array(
	'href'	=> './manage/form/mail/add',
	'class'	=> 'btn btn-success'
) );

$pagination	= new \CeusMedia\Bootstrap\PageControl( './manage/form/mail', $page, $pages );

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