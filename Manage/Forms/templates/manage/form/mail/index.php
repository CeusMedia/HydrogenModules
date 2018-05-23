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

$modelForm	= new Model_Form( $env );

$rows		= array();
foreach( $mails as $mail ){
	$linkView	= UI_HTML_Tag::create( 'a', $iconView.'&nbsp;anzeigen', array(
		'href'	=> './manage/form/mail/view/'.$mail->mailId,
		'class'	=> 'btn btn-mini btn-info',
	) );
	$linkEdit	= UI_HTML_Tag::create( 'a', $mail->title, array( 'href' => './manage/form/mail/edit/'.$mail->mailId ) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $linkEdit ),
		UI_HTML_Tag::create( 'td', '<small><tt>'.$mail->identifier.'</tt></small>' ),
		UI_HTML_Tag::create( 'td', $formats[$mail->format] ),
		UI_HTML_Tag::create( 'td', $modelForm->countByIndex( 'mailId', $mail->mailId ) ),
		UI_HTML_Tag::create( 'td', $linkView ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( '', '30%', '120px', '40px', '120px' );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Titel', 'Shortcode', 'Format', UI_HTML_Tag::create( 'abbr', $iconForm, array( 'title' => 'Formulare' ) ) ) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed table-striped table-condensed' ) );

$heading	= UI_HTML_Tag::create( 'h2', 'Mails' );

$linkAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Mail', array(
	'href'	=> './manage/form/mail/add',
	'class'	=> 'btn btn-success'
) );
return $heading.$table.$linkAdd;
