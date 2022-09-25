<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['edit'];

$tabs		= $this->renderMainTabs();
$list		= $this->renderList( $authors, $author->authorId );

$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$optGender	= array( /*$words['gender']*/ );
$optGender	= HtmlElements::Options( $optGender/*, $author->gender*/ );

$image		= "images/no_author.png";
if( $author->image ){
	$image	= 'file/bookstore/author/'.$author->image;
}
$image	= HtmlTag::create( 'img', NULL, array( 'src' => $image, 'class' => 'img-polaroid' ) );

$buttonRemoveImage	= "";
if( $author->image ){
	$urlRemoveImage	= './manage/catalog/bookstore/author/removeImage/'.$author->authorId;
	$attributes		= array(
		'title'		=> $w->buttonRemoveImage,
		'type'		=> "button",
		'class'		=> "btn btn-danger",
		'onclick'	=> "document.location.href='".$urlRemoveImage."';"
	);
	$buttonRemoveImage	= HtmlTag::create( 'button', $iconRemove, $attributes );
}

$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, array(
	'href'		=> './manage/catalog/bookstore/author/remove/'.$author->authorId,
	'disabled'	=> $articles ? 'disabled' : NULL,
	'class'		=> "btn btn-small btn-danger",
	'onclick'	=> "if(!confirm('Wirklich?')) return false",
) );

$helperUpload	= new View_Helper_Input_File( $env );
$helperUpload->setName( 'image' );
$helperUpload->setLabel( '<i class="icon-folder-open icon-white"></i>' );
$helperUpload->setRequired( FALSE );

return '
<div class="content-panel">
	<!--<h4>'.$w->heading.'</h4>-->
	<div class="content-panel-inner form-changes-auto">
		<form action="./manage/catalog/bookstore/author/edit/'.$author->authorId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span6">
					<div class="row-fluid">
						<div class="span4">
							<label for="input_gender">'.$w->labelGender.'</label>
							<select disabled="disabled" class="span12" name="gender" id="input_gender">'.$optGender.'</select>
						</div>
						<div class="span8">
							<label for="input_firstname">'.$w->labelFirstname.'</label>
							<input class="span12" type="text" name="firstname" id="input_firstname" value="'.htmlentities( $author->firstname, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_surname">'.$w->labelLastname.'</label>
							<input class="span12" type="text" name="lastname" id="input_lastname" value="'.htmlentities( $author->lastname, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span10">
							<label for="input_image">'.$w->labelImage.'</label>
							'.$helperUpload->render().'
						</div>
						<div class="span2 pull-right">
							<label>&nbsp;</label>
							'.$buttonRemoveImage.'
						</div>
					</div>
				</div>
				<div class="span6">
					<div style="text-align: center">
						'.$image.'
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<label for="input_institution">'.$w->labelReference.'</label>
				<input class="span12" type="text" name="reference" id="input_reference" value="'.htmlentities( $author->reference, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="row-fluid">
				<label for="input_institution">'.$w->labelInstitution.'</label>
				<input disabled="disabled" class="span12" type="text" name="institution" id="input_institution" value="'.htmlentities( ""/*$author->institution*/, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="row-fluid">
				<label for="input_description">'.$w->labelDescription.'</label>
				<textarea class="span12" type="text" name="description" id="input_description" rows="6">'.htmlentities( $author->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
			</div>
			<div class="buttonbar">
<!--				<a class="btn btn-small" href="./manage/catalog/bookstore/author"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>-->
				<button type="submit" class="btn btn-primary" name="save"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
				<a href="'.$frontend->getUri().'catalog/bookstore/author/'.$author->authorId.'" class="btn btn-small btn-info" target="_blank"><i class="icon icon-eye-open icon-white"></i> '.$w->buttonView.'</a>
				'.$buttonRemove.'
			</div>
		</form>
	</div>
</div>
';
?>
