<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index'];

$optStatus	= array(
	0	=> 'nicht sichtbar',
	1	=> 'sichtbar',
);

$buttonAdd	= HtmlTag::create( 'a', '<i class="icon-plus icon-white"></i> neuer Eintrag', array(
	'href'	=> './manage/testimonial/add',
	'class'	=> 'btn btn-small btn-success',
) );

$list	= '<div class="muted"><em>Keine gefunden.</em></div>';
if( $testimonials ){
	$list	= [];
	foreach( $testimonials as $testimonial ){
		$link	= HtmlTag::create( 'a', $testimonial->title, array(
			'href'	=> './manage/testimonial/edit/'.$testimonial->testimonialId,
		) );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $testimonial->username ),
			HtmlTag::create( 'td', $optStatus[$testimonial->status] ),
			HtmlTag::create( 'td', date( 'd.m.Y H:i', $testimonial->timestamp ) ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( ['', '20%', '15%', '20%'] );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array(
		'Überschrift', 'Autor', 'Zustand', 'Zeitpunkt'
	) ) );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
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

