<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ).'&nbsp;';

$optStatus		= array(
	-2		=> 'deaktiviert',
	-1		=> 'versteckt',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
);
$optStatus	= UI_HTML_Elements::Options( $optStatus, $category->status );

$panelFacts		= '
<div class="content-panel">
	<h3>Kategorie</h3>
	<div class="content-panel-inner">
		<form action="./manage/catalog/clothing/category/edit/'.$category->categoryId.'" method="post">
			<div class="row-fluid">
				<div class="span10">
					<label for="input_title">Titel</label>
					'.HtmlTag::create( 'input', NULL, array(
						'type'		=> "text",
						'id'		=> "input_title",
						'name'		=> "title",
						'class'		=> "span12",
						'required'	=> "required",
						'value'		=> $category->title
					) ).'
				</div>
				<div class="span2">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">Beschreibung</label>
					<textarea name="description" id="input_description" class="span12" rows="6">'.$category->description.'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/catalog/clothing/category" class="btn btn-small">'.$iconCancel.'abbrechen</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'speichern</button>
			</div>
		</form>
	</div>
</div>';

return '
<div class="row-fluid">
	<div class="span8">
		'.$panelFacts.'
	</div>
	<div class="span4">
		...
	</div>
</div>';
