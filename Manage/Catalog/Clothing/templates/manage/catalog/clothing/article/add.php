<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) ).'&nbsp;';
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) ).'&nbsp;';

$optCategoryId	= array();
foreach( $categoryMap as $item )
	$optCategoryId[$item->categoryId]	= $item->title;
$optCategoryId	= UI_HTML_Elements::Options( $optCategoryId);

return '
<div class="row-fluid">
	<div class="span12">
		<form action="./manage/catalog/clothing/article/add" method="post">
			<div class="content-panel">
				<h3>Neues Produkt</h3>
				<div class="content-panel-inner">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">Titel</label>
							'.UI_HTML_Tag::create( 'input', NULL, array(
								'type'		=> "text",
								'id'		=> "input_title",
								'name'		=> "title",
								'class'		=> "span12",
								'required'	=> "required",
							) ).'
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_form">Ausführung</label>
							'.UI_HTML_Tag::create( 'input', NULL, array(
								'type'		=> "text",
								'id'		=> "input_form",
								'name'		=> "form",
								'class'		=> "span12",
								'required'	=> "required",
							) ).'
						</div>
						<div class="span4">
							<label for="input_size">Größe</label>
							'.UI_HTML_Tag::create( 'input', NULL, array(
								'type'		=> "text",
								'id'		=> "input_size",
								'name'		=> "size",
								'class'		=> "span12",
								'required'	=> "required",
							) ).'
						</div>
						<div class="span4">
							<label for="input_price">Preis <small class="muted">(€€.¢¢)</small></label>
							'.UI_HTML_Tag::create( 'input', NULL, array(
								'type'		=> "text",
								'id'		=> "input_price",
								'name'		=> "price",
								'class'		=> "span12",
								'required'	=> "required",
							) ).'
						</div>
					</div>
					<div class="buttonbar">
						<a href="./manage/catalog/clothing/article" class="btn btn-small">'.$iconCancel.'abbrechen</a>
						<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'speichern</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>';
