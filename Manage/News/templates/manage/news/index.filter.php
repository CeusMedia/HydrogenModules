<?php

$iconFilter	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );

$w			= (object) $words['filter'];

$pagination	= new CMM_Bootstrap_PageControl( './manage/news', $pageNr, ceil( $total / $limit ) );

$optStatus	= array( '' => '- alle -' );
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $value;
$optStatus	= UI_HTML_Elements::Options( $optStatus, $filterStatus );


$buttonFilter	= UI_HTML_Tag::create( 'button', $iconFilter.'&nbsp;'.$w->buttonFilter, array(
	'type'	=> 'submit',
	'name'	=> 'filter',
	'class'	=> 'btn btn-info',
) );

$buttonReset	= UI_HTML_Tag::create( 'a', $iconReset.'&nbsp;'.$w->buttonReset, array(
	'href'	=> './manage/news/filter/reset',
	'class'	=> 'btn btn-inverse btn-small',
) );


return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/news/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_query">'.$w->labelTitle.'</label>
					<input type="text" name="query" id="input_query" value="'.htmlentities( $filterQuery, ENT_QUOTES, 'UTF-8' ).'" class="span12"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			'.UI_HTML_Tag::create( 'div', join( '&nbsp;', array(
				$buttonFilter,
				$buttonReset,
			) ), array( 'class' => 'buttonbar' ) ).'
		</form>
	</div>
</div>';
?>
