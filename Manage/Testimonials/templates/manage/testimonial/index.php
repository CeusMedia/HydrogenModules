<?php
$w	= (object) $words['index'];

$optStatus	= array(
	0	=> 'nicht sichtbar',
	1	=> 'sichtbar',
);

$buttonAdd	= UI_HTML_Tag::create( 'a', '<i class="icon-plus icon-white"></i> neuer Eintrag', array(
	'href'	=> './manage/testimonial/add',
	'class'	=> 'btn btn-small btn-success',
) );

$list	= '<div class="muted"><em>Keine gefunden.</em></div>';
if( $testimonials ){
	$list	= array();
	foreach( $testimonials as $testimonial ){
		$link	= UI_HTML_Tag::create( 'a', $testimonial->title, array(
			'href'	=> './manage/testimonial/edit/'.$testimonial->testimonialId,
		) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $testimonial->username ),
			UI_HTML_Tag::create( 'td', $optStatus[$testimonial->status] ),
			UI_HTML_Tag::create( 'td', date( 'd.m.Y H:i', $testimonial->timestamp ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '', '20%', '15%', '20%' ) );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Überschrift', 'Autor', 'Zustand', 'Zeitpunkt'
	) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

$panelList	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
		'.$buttonAdd.'
	</div>
</div>';


return '
<div class="row-fluid">
	<div class="span12">
		'.$panelList.'
	</div>
</div>';

?>
