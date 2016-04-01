<?php

$w				= (object) $words['index.filter'];

$optStatus		= array( '' => '- alle -' ) + $words['states'];
$optStatus		= UI_HTML_Elements::Options( $optStatus, $filterStatus );

$optCategory	= array( '' => '- alle -' );
foreach( $categories as $item )
	$optCategory[$item->categoryId]	= $item->title;
$optCategory	= UI_HTML_Elements::Options( $optCategory, $filterCategoryId );

return '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/blog/filter">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_categoryId">'.$w->labelCategoryId.'</label>
					<select name="categoryId" id="input_categoryId">'.$optCategory.'</select>
				</div>
			</div>
<!--			<div class="row-fluid">
				<div class="span12">
					<label for="input_"></label>
					<select name="" id="input_"></select>
				</div>
			</div>-->
			<div class="buttonbar">
				<button type="submit" name="filter" value="1" class="btn btn-primary">'.$w->buttonFilter.'</button>
				<a href="./manage/blog/filter/reset" class="btn btn-small btn-inverse">'.$w->buttonReset.'</a>
			</div>
		</form>
	</div>
</div>';
