<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<string,array<string,string>> $words */
/** @var array<object> $categories */
/** @var ?string $filterStatus */
/** @var ?string $filterCategoryId */

$w				= (object) $words['index.filter'];

$optStatus		= ['' => '- alle -'] + $words['states'];
$inputStatus	= HtmlTag::create( 'select', HtmlElements::Options( $optStatus, $filterStatus ), [
	'name'	=> 'status',
	'id'	=> 'input_status',
] );

$optCategory	= ['' => '- alle -'];
foreach( $categories as $item )
	$optCategory[$item->categoryId]	= $item->title;
$inputCategoryId	= HtmlTag::create( 'select', HtmlElements::Options( $optCategory, $filterCategoryId ), [
	'name'	=> 'categoryId',
	'id'	=> 'input_categoryId',
] );

return '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/blog/filter">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">'.$w->labelStatus.'</label>
					'.$inputStatus.'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_categoryId">'.$w->labelCategoryId.'</label>
					'.$inputCategoryId.'
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
