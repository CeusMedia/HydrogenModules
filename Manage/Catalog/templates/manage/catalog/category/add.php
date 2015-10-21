<?php
$w	= (object) $words['add'];

$tabs		= $this->renderMainTabs();

$optParent	= array( 0 => '' );
foreach( $categories as $item )
	if( $item->parentId == 0 )
		$optParent[$item->categoryId]	= $item->label_de;
natcasesort( $optParent );
$optParent	= UI_HTML_Elements::Options( $optParent, (int) $category->parentId );

$optVisible	= $words['visible'];
$optVisible	= UI_HTML_Elements::Options( $optVisible, (int) $category->visible );

$panelList	= $view->loadTemplateFile( 'manage/catalog/category/list.php' );

return '
'.$tabs.'
<div class="row-fluid">
	<div class="span6">
		'.$panelList.'
	</div>
	<div class="span6">
		<div class="content-panel">
			<h4>'.$w->heading.'</h4>
			<div class="content-panel-inner form-changes-auto">
				<form action="manage/catalog/category/add/'.htmlentities( $category->parentId, ENT_QUOTES, 'UTF-8' ).'" method="post">
					<label for="input_parentId">'.$w->labelParentId.'</label>
					<select class="span12" name="parentId" id="input_parentId">'.$optParent.'</select>
					<label for="input_label_de">'.$w->labelLabel.'</label>
					<input class="span12" type="text" name="label_de" id="input_label_de" value="'.htmlentities( $category->label_de, ENT_QUOTES, 'UTF-8' ).'"/>
					<label for="input_label_former">'.$w->labelLabelFormer.'</label>
					<input class="span12" type="text" name="label_former" id="input_label_former" value="'.htmlentities( $category->label_former, ENT_QUOTES, 'UTF-8' ).'"/>
					<label for="input_publisher">'.$w->labelPublisher.'</label>
					<input class="span12" type="text" name="publisher" id="input_publisher" value="'.htmlentities( $category->publisher, ENT_QUOTES, 'UTF-8' ).'"/>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_issn">'.$w->labelIssn.'</label>
							<input class="span12" type="text" name="issn" id="input_issn" value="'.htmlentities( $category->issn, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span5">
							<label for="input_visible">'.$w->labelVisible.'</label>
							<select class="span12" name="visible" id="input_visible">'.$optVisible.'</select>
						</div>
						<div class="span1">
							<label for="input_rank">'.$w->labelRank.'</label>
							<input class="span12" type="text" name="rank" id="input_rank" value="'.htmlentities( $category->rank, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="buttonbar">
<!--						<a class="btn btn-small" href="./manage/catalog/category"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>-->
						<button type="submit" class="btn btn-primary" name="save"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
';
?>
