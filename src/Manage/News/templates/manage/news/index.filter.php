<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconFilter	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] );
$iconReset	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search-minus'] );

$w			= (object) $words['filter'];

$optStatus	= ['' => '- alle -'];
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $value;
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );


$buttonFilter	= HtmlTag::create( 'button', $iconFilter.'&nbsp;'.$w->buttonFilter, [
	'type'	=> 'submit',
	'name'	=> 'filter',
	'class'	=> 'btn btn-info',
] );

$buttonReset	= HtmlTag::create( 'a', $iconReset.'&nbsp;'.$w->buttonReset, [
	'href'	=> './manage/news/filter/reset',
	'class'	=> 'btn btn-inverse btn-small',
] );


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
			'.HtmlTag::create( 'div', join( '&nbsp;', [
				$buttonFilter,
				$buttonReset,
			] ), ['class' => 'buttonbar'] ).'
		</form>
	</div>
</div>';
