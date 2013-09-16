<?php
$w			= (object) $words['add'];

$tabs		= $this->renderMainTabs();
$list		= $this->renderList( $authors, $author->author_id );

$optGender	= array( /*$words['gender']*/ );
$optGender	= UI_HTML_Elements::Options( $optGender/*, $author->gender*/ );

return '
'.$tabs.'
<div class="row-fluid">
	<div class="span4">
		<a class="btn btn-small btn-primary disabled" href="./manage/catalog/author/add"><i class="icon-plus icon-white"></i></a>
		<input type="text" placeholder="Suchen..." id="input_search">
		'.$list.'
	</div>
	<div class="span8">
		<form action="./manage/catalog/author/add" method="post">
			<h3>'.$w->heading.'</h3>
			<div class="row-fluid">
				<div class="span2">
					<label for="input_gender">'.$w->labelGender.'</label>
					<select disabled="disabled" class="span12" name="gender" id="input_gender">'.$optGender.'</select>
				</div>
				<div class="span4">
					<label for="input_firstname">'.$w->labelFirstname.'</label>
					<input class="span12" type="text" name="firstname" id="input_firstname" value="'.htmlentities( $author->firstname, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span6">
					<label for="input_surname">'.$w->labelLastname.'</label>
					<input class="span12" type="text" name="lastname" id="input_lastname" value="'.htmlentities( $author->lastname, ENT_QUOTES, 'UTF-8' ).'"/>
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
				<a class="btn btn-small" href="./manage/catalog/author"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
				<button type="submit" class="btn btn-small btn-success" name="save"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
';
?>
