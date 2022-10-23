<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$w	= (object) $words['edit'];

$optStatus	= $words['states'];
$optStatus	= HtmlElements::Options( $optStatus, $product->status );


$panelList		= $view->loadTemplateFile( 'manage/catalog/provision/product/index.list.php' );
$panelLicenses	= $view->loadTemplateFile( 'manage/catalog/provision/product/license/index.list.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelList.'
	</div>
	<div class="span9">
		<div class="content-panel content-panel-form">
			<h3><span class="muted">'.$w->heading.': </span>'.$product->title.'</h3>
			<div class="content-panel-inner">
				<form action="./manage/catalog/provision/product/edit/'.$product->productId.'" method="post">
					<div class="row-fluid">
						<div class="span5">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $product->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span4">
							<label for="input_url">'.$w->labelUrl.'</label>
							<input type="text" name="url" id="input_url" class="span12" value="'.htmlentities( $product->url, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span2">
							<label for="input_status">'.$w->labelStatus.'</label>
							<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
						</div>
						<div class="span1">
							<label for="input_rank">'.$w->labelRank.'</label>
							<input type="text" name="rank" id="input_rank" class="span12" value="'.htmlentities( $product->rank, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_description">'.$w->labelDescription.'</label>
							<textarea name="description" id="input_description" class="span12 TinyMCE" data-tinymce-mode="minimal" rows="5">'.htmlentities( $product->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="buttonbar">
						<a href="./manage/catalog/provision/product" class="btn btn-small"><i class="icon-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</a>
						<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
					</div>
				</form>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6">
				'.$panelLicenses.'
			</div>
		</div>
	</div>
</div>
';
