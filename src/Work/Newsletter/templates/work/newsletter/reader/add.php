<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var object $reader */
/** @var array<object> $groups */
/** @var bool $tabbedLinks */
/** @var int $totalReaders */
/** @var ?Logic_Limiter $limiter */

$tabsMain		= $tabbedLinks ? $this->renderMainTabs() : '';

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';

$optStatus	= $words->states;
unset( $optStatus[-2] );
unset( $optStatus[-1] );
//$optStatus	= array_reverse( $optStatus );
$optStatus	= HtmlElements::Options( $optStatus, Model_Newsletter_Reader::STATUS_REGISTERED );

$optGender	= HtmlElements::Options( $words->gender, $reader->gender );

$listGroups	= [];
foreach( $groups as $group ){
	$checkbox	= HtmlTag::create( 'input', NULL, [
		'type'	=> 'checkbox',
		'name'	=> 'groupIds[]',
		'value'	=> $group->newsletterGroupId,
		'checked'	=> in_array( $group->newsletterGroupId, $selectedGroups ) ? 'checked' : NULL,
	] );
	$label	= $checkbox.'&nbsp;'.$group->title;
	$listGroups[]	= HtmlTag::create( 'label', $label, ['class' => 'checkbox'] );
}
$listGroups	= join( $listGroups );

$nextActions	= [];
if( $limiter && $limiter->denies( 'Work.Newsletter.Reader:maxItems', $totalReaders + 2 ) )
	$nextActions[]	='<label class="radio muted"><input type="radio" name="nextAction" value="add" disabled="disabled">&nbsp;einen weiteren Leser hinzufügen</label>';
else
	$nextActions[]	='<label class="radio"><input type="radio" name="nextAction" value="add">&nbsp;einen weiteren Leser hinzufügen</label>';

$nextActions[]	='<label class="radio"><input type="radio" name="nextAction" value="edit" checked="checked">&nbsp;diesen Leser bearbeiten</label>';
$nextActions[]	='<label class="radio"><input type="radio" name="nextAction" value="index">&nbsp;zurück zur Liste</label>';
$nextActions	= join( $nextActions );

extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/newsletter/reader/add/', ['words' => $words] ) );

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
	<div class="row-fluid">
		<div class="span8">
			<div class="content-panel">
				<h4>Daten</h4>
				<div class="content-panel-inner">
					<form action="./work/newsletter/reader/add" method="post">
						<div class="row-fluid">
							<div class="span6">
								<label for="input_email" class="mandatory">'.$words->add->labelEmail.'</label>
								<input type="text" name="email" id="input_email" class="span12" value="'.htmlentities( $reader->email, ENT_QUOTES, 'UTF-8' ).'" required/>
							</div>
							<div class="span6">
								<label for="input_institution">'.$words->add->labelInstitution.'</label>
								<input type="text" name="institution" id="input_institution" class="span12" value="'.htmlentities( $reader->institution, ENT_QUOTES, 'UTF-8' ).'"/>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span2">
								<label for="input_gender">'.$words->add->labelGender.'</label>
								<select name="gender" id="input_gender" class="span12">'.$optGender.'</select>
							</div>
							<div class="span2">
								<label for="input_prefix">'.$words->add->labelPrefix.'</label>
								<input type="text" name="prefix" id="input_prefix" class="span12" value="'.htmlentities( $reader->prefix, ENT_QUOTES, 'UTF-8' ).'"/>
							</div>
							<div class="span4">
								<label for="input_firstname" class="mandatory">'.$words->add->labelFirstname.'</label>
								<input type="text" name="firstname" id="input_firstname" class="span12" value="'.htmlentities( $reader->firstname, ENT_QUOTES, 'UTF-8' ).'" required/>
							</div>
							<div class="span4">
								<label for="input_surname" class="mandatory">'.$words->add->labelSurname.'</label>
								<input type="text" name="surname" id="input_surname" class="span12" value="'.htmlentities( $reader->surname, ENT_QUOTES, 'UTF-8' ).'" required/>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span3">
								<label for="input_status">'.$words->add->labelStatus.'</label>
								<select name="status" id="input_status" class="span12 has-optionals">'.$optStatus.'</select>
							</div>
							<div class="span9">
								<br/>
								<label class="checkbox">
									<input type="checkbox" name="inform" id="input_inform" class="optional status status-1" checked="checked"/>
									<input type="checkbox" name="inform" id="input_inform_display" class="optional status status-0" checked="checked" disabled="disabled"/>
									<span>neuen Abonnenten mit E-Mail informieren</span>
								</label>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12">
								<h4>Gruppenzuordnung:</h4>
								'.$listGroups.'
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12">
								<h4>Anschließend:</h4>
								'.$nextActions.'
							</div>
						</div>
						<div class="row-fluid">
							<div class="buttonbar">
								<a class="btn btn-small" href="./work/newsletter/reader">'.$iconCancel.'zurück</span></a>
								<button type="submit" class="btn btn-primary" name="save">'.$iconSave.'speichern</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
'.$textBottom;
