<?php

$optStatus		= array( '' => 'alle' );
foreach( $words['states'] as $key => $label )
	$optStatus[$key]	= $label;
$optStatus		= UI_HTML_Elements::Options( $optStatus, $filterStatus );

$iconFilter	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) ).'&nbsp;';
$iconReset	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) ).'&nbsp;';

return '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		<form action="./work/newsletter/group/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_query">Suchwort</label>
					<input type="text" name="query" id="input_query" class="span12" value="'.htmlentities( $filterQuery, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-group">
					<button type="submit" name="filter" class="btn btn-small btn-info">'.$iconFilter.'suchen</button>
					<a href="./work/newsletter/group/filter/reset" class="btn btn-small btn-inverse">'.$iconReset.'alle</a>
				</div>
			</div>
		</form>
	</div>
</div>';
