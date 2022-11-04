<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$optProduct	= [];
foreach( $products as $item )
	$optProduct[$item->productId]	= $item->title;
$optProduct	= HtmlElements::Options( $optProduct, $product->productId );

$optStatus		= $words['states'];
$optStatus		= HtmlElements::Options( $optStatus, $license->status );

$optDuration	= $words['durations'];
$optDuration	= HtmlElements::Options( $optDuration, $license->duration );

$w	= (object) $words['add'];

$panelAdd	= '
<div class="content-panel content-panel-form">
	<h3><span class="muted">'.$product->title.': </span>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/catalog/provision/product/license/add/'.$product->productId.'" method="post">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $license->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
				<div class="span1">
					<label for="input_rank">'.$w->labelRank.'</label>
					<input type="text" name="rank" id="input_rank" class="span12" value="'.htmlentities( $license->rank, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_productId">'.$w->labelProductId.'</label>
					<select name="productId" id="input_productId" class="span12">'.$optProduct.'</select>
				</div>
				<div class="span3">
					<label for="input_duration">'.$w->labelDuration.'</label>
					<select name="duration" id="input_duration" class="span12">'.$optDuration.'</select>
				</div>
				<div class="span2">
					<label for="input_users">'.$w->labelUsers.'</label>
					<input type="text" name="users" id="input_users" class="span12" value="'.htmlentities( $license->users, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_price">'.$w->labelPrice.'</label>
					<input type="text" name="price" id="input_price" class="span12" value="'.htmlentities( $license->price, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">'.$w->labelDescription.'</label>
					<textarea name="description" id="input_description" class="span12 TinyMCE" data-tinymce-mode="minimal">'.htmlentities( $license->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/catalog/provision/product/edit/'.$product->productId.'" class="btn btn-small"><i class="icon-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';

$panelList	= $view->loadTemplateFile( 'manage/catalog/provision/product/index.list.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelList.'
	</div>
	<div class="span9">
		'.$panelAdd.'
	</div>
</div';
