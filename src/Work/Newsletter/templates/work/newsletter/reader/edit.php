<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View_Work_Newsletter_Reader $view */
/** @var object $words */
/** @var object $reader */
/** @var bool $tabbedLinks */
/** @var array $groups */
/** @var array $readerLetters */
/** @var array $readerGroups */

$tabsMain		= $tabbedLinks ? $view->renderMainTabs( 'work/newsletter/reader' ) : '';

$optStatus	= HtmlElements::Options( (array) $words->states, $reader->status );
$optGender	= HtmlElements::Options( (array) $words->gender, $reader->gender );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
$iconConfirm	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-envelope-o'] ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'&nbsp;';

$urlCancel			= './work/newsletter/reader';
$urlConfirm			= './work/newsletter/reader/sendConfirmation/'.$reader->newsletterReaderId;
$urlRemove			= './work/newsletter/reader/remove/'.$reader->newsletterReaderId;
$labelButtonCancel	= $iconCancel.$words->edit->buttonCancel;
$labelButtonSave	= $iconSave.$words->edit->buttonSave;
$labelButtonConfirm	= '<strike>'.$iconConfirm.$words->edit->buttonConfirm.'</strike>';
$labelButtonRemove	= $iconRemove.$words->edit->buttonRemove;

$buttonCancel		= '<a class="btn btn-small" href="'.$urlCancel.'">'.$labelButtonCancel.'</a>';
$buttonSave			= '<button type="submit" class="btn btn-primary" name="save">'.$labelButtonSave.'</button>';
$buttonConfirm		= '<button disabled="disabled" type="button" class="btn btn-info" onclick="if(confirm(\'Wirklich?\'))document.location.href=\''.$urlConfirm.'\';">'.$labelButtonConfirm.'</button>';
$buttonRemove		= '<a class="btn btn-danger" onclick="if(!confirm(\'Wirklich?\')) return false;" href="'.$urlRemove.'">'.$labelButtonRemove.'</a>';

if( (int) $reader->status !== 0 )
	$buttonConfirm		= '<button disabled="disabled" type="button" class="btn btn-info">'.$labelButtonConfirm.'</button>';
//if( (int) $reader->status > 0 )
//	$buttonRemove		= '<button disabled="disabled" type="button" class="btn btn-danger">'.$labelButtonRemove.'</button>';

$panelDetailEditor	= '
			<div class="content-panel">
				<h3>Daten</h3>
				<div class="content-panel-inner">
					<form action="./work/newsletter/reader/edit/'.$reader->newsletterReaderId.'" method="post">
						<div class="row-fluid">
							<div class="span9">
								<label for="input_email" class="mandatory">'.$words->edit->labelEmail.'</label>
								<input type="text" name="email" id="input_email" class="span12" value="'.htmlentities( $reader->email, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
							</div>
							<div class="span3">
								<label for="input_status">'.$words->edit->labelStatus.'</label>
								<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span2">
								<label for="input_gender">'.$words->edit->labelGender.'</label>
								<select name="gender" id="input_gender" class="span12">'.$optGender.'</select>
							</div>
							<div class="span2">
								<label for="input_prefix">'.$words->edit->labelPrefix.'</label>
								<input type="text" name="prefix" id="input_prefix" class="span12" value="'.htmlentities( $reader->prefix, ENT_QUOTES, 'UTF-8' ).'"/>
							</div>
							<div class="span4">
								<label for="input_firstname" class="mandatory">'.$words->edit->labelFirstname.'</label>
								<input type="text" name="firstname" id="input_firstname" class="span12" value="'.htmlentities( $reader->firstname, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
							</div>
							<div class="span4">
								<label for="input_surname" class="mandatory">'.$words->edit->labelSurname.'</label>
								<input type="text" name="surname" id="input_surname" class="span12" value="'.htmlentities( $reader->surname, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span7">
								<label for="input_institution">'.$words->edit->labelInstitution.'</label>
								<input type="text" name="institution" id="input_institution" class="span12" value="'.htmlentities( $reader->institution, ENT_QUOTES, 'UTF-8' ).'"/>
							</div>
							<div class="span3">
								<label for="">registriert am</label>
								<input type="text" name="" id="input_registeredAt" class="span12 disabled readonly" readonly="readonly" value="'.date( 'd.m.Y', $reader->registeredAt ).'"/>
							</div>
							<div class="span2">
								<label for="">Testempfänger</label>
								'.HtmlTag::create( 'input', NULL, [
									'type'	=> 'checkbox',
									'name'	=> 'tester',
									'id'	=> 'input_tester',
									'value'	=> '1',
									'checked'	=> ( $reader->tester ?? FALSE ) ? 'checked' : NULL,
								] ).'
							</div>
						</div>
						<div class="row-fluid">
							<div class="buttonbar">
								'.$buttonCancel.'
								'.$buttonSave.'
								'.$buttonRemove.'
							</div>
						</div>
					</form>
				</div>
			</div>
';

$panelGroupEditor	= $view->loadTemplateFile( 'work/newsletter/reader/edit.groups.php' );
$panelLetterList	= $view->loadTemplateFile( 'work/newsletter/reader/edit.letters.php' );


extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/newsletter/reader/edit/', ['words' => $words, 'reader' => $reader] ) );

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
<!--	<a href="./work/newsletter/reader" class="btn btn-mini">'.$iconCancel.$words->edit->buttonList.'</a>-->
	'.$textAbove.'
	<div class="row-fluid">
		<div class="span8">
			'.$panelDetailEditor.'
			'.$panelLetterList.'
		</div>
		<div class="span4">
			'.$panelGroupEditor.'
		</div>
	</div>
</div>
'.$textBottom;
