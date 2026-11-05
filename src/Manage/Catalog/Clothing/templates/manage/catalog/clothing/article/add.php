<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';

$w	= (object) $words['add'];

$optCategoryId	= [];
foreach( $categoryMap as $item )
	$optCategoryId[$item->categoryId]	= $item->title;
$optCategoryId	= HtmlElements::Options( $optCategoryId);

return '
<div class="row-fluid">
	<div class="span12">
		<form action="./manage/catalog/clothing/article/add" method="post">
			<div class="content-panel">
				<h3>'.$w->heading.'</h3>
				<div class="content-panel-inner">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">'.$w->labelTitle.'</label>
							'.HtmlTag::create( 'input', NULL, [
								'type'		=> "text",
								'id'		=> "input_title",
								'name'		=> "title",
								'class'		=> "span12",
								'required'	=> "required",
							] ).'
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_form">'.$w->labelForm.'</label>
							'.HtmlTag::create( 'input', NULL, [
								'type'		=> "text",
								'id'		=> "input_form",
								'name'		=> "form",
								'class'		=> "span12",
								'required'	=> "required",
							] ).'
						</div>
						<div class="span4">
							<label for="input_size">'.$w->labelSize.'</label>
							'.HtmlTag::create( 'input', NULL, [
								'type'		=> "text",
								'id'		=> "input_size",
								'name'		=> "size",
								'class'		=> "span12",
								'required'	=> "required",
							] ).'
						</div>
						<div class="span4">
							<label for="input_price">'.$w->labelPrice.'</label>
							'.HtmlTag::create( 'input', NULL, [
								'type'		=> "text",
								'id'		=> "input_price",
								'name'		=> "price",
								'class'		=> "span12",
								'required'	=> "required",
							] ).'
						</div>
					</div>
					<div class="buttonbar">
						<a href="./manage/catalog/clothing/article" class="btn btn-small">'.$iconCancel.$w->buttonCancel.'</a>
						<button type="submit" name="save" class="btn btn-primary">'.$iconSave.$w->buttonSave.'</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>';
