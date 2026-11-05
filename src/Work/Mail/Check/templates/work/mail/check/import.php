<?php

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var string[] $columns */
/** @var string|NULL $name */
/** @var int|NULL $size */
/** @var int|NULL $count */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['import'];

$tabs	= View_Work_Mail_Check::renderTabs( $env, 'import' );

if( !empty( $type ) ){
	$optColumn	= [];
	foreach( $columns as $column )
		$optColumn[$column]	= $column;
	$optColumn	= HtmlElements::Options( $optColumn, $columns[0] );

	return $tabs.'
<div class="content-panel">
	<h3>'.$w->headingStep2.'</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/check/import" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span9">
					<div class="row-fluid">
						<div class="span6">
							<label for="input_file">'.$w->labelFile.'</label>
							<input type="text" name="file" id="input_file" value="'.htmlentities( $name, ENT_QUOTES, 'UTF-8' ).'" class="span12" disabled="disabled"/>
						</div>
						<div class="span3">
							<label for="input_format">'.$w->labelFormat.'</label>
							<input type="text" name="format" id="input_format" value="'.$type.'" class="span12" disabled="disabled"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_group">'.$w->labelGroup.'</label>
							<input type="text" name="group" id="input_group" value="" class="span12"/>
						</div>
						<div class="span3">
							<label for="input_size">'.$w->labelSize.'</label>
							<input type="text" name="size" id="input_size" value="'.UnitFormater::formatBytes( $size ).'" class="span12" disabled="disabled"/>
						</div>
						<div class="span3">
							<label for="input_count">'.$w->labelCount.'</label>
							<input type="text" name="count" id="input_count" value="'.$count.'" class="span12" disabled="disabled"/>
						</div>
					</div>
				</div>
				<div class="span3">
					<label for="input_column">'.$w->labelColumn.'</label>
					<select name="column" id="input_column" class="span12" size="4">'.$optColumn.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./work/mail/check/import/abort" class="btn btn-small"><i class="fa fa-fw fa-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-upload icon-white"></i>&nbsp;'.$w->buttonUpload.'</button>
			</div>
		</form>
	</div>
</div>';
}
else{
	$helperUpload	= new View_Helper_Input_File( $env );
	$helperUpload->setName( 'file' )->setLabel( $w->labelUpload )->setRequired( TRUE );
	return $tabs.'
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>'.$w->headingStep1.'</h3>
			<div class="content-panel-inner">
				<form action="./work/mail/check/import" method="post" enctype="multipart/form-data">
					'.$helperUpload->render().'
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-primary"><i class="icon-upload icon-white"></i>&nbsp;'.$w->buttonUpload.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
}
