<?php

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$rows	= array();
foreach( $templates as $template ){
	$title	= $template->title;
	$title	= UI_HTML_Tag::create( 'a', $template->title, array(
		'href'	=> './admin/mail/template/edit/'.$template->mailTemplateId,
		'class'	=> 'autocut',
	) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $title ),
		UI_HTML_Tag::create( 'td', date( 'd.m.Y H:i', $template->createdAt ) ),
		UI_HTML_Tag::create( 'td', date( 'd.m.Y H:i', $template->modifiedAt ) ),
	) );
}
$tableHeads	= UI_HTML_Elements::tableHeads( array(
	$words['index']['headTitle'],
	$words['index']['headCreated'],
	$words['index']['headModified']
) );

$table	= UI_HTML_Tag::create( 'table', array(
	UI_HTML_Elements::ColumnGroup( array( '', '140', '140' ) ),
	UI_HTML_Tag::create( 'thead', $tableHeads ),
	UI_HTML_Tag::create( 'tbody', $rows ),
), array( 'class' => 'table table-fixed' ) );

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], array(
	'href'	=> './admin/mail/template/add',
	'class'	=> 'btn btn-success',
) );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>'.$words['index']['heading'].'</h3>
			<div class="content-panel-inner">
				'.$table.'
				<div class="buttonbar">
					'.$buttonAdd.'
				</div>
			</div>
		</div>
	</div>
</div>';

?>
