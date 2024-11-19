<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var View_Work_Newsletter_Group $view */
/** @var View_Work_Newsletter_Group $this */
/** @var object $words */
/** @var bool $tabbedLinks */
/** @var array<object> $groupReaders */
/** @var object $group */
/** @var int|string $groupId */
/** @var ?Logic_Limiter $limiter */

$tabsMain		= $tabbedLinks ? $this->renderMainTabs() : '';

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] ).'&nbsp;';
$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'&nbsp;';
$iconExport		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] ).'&nbsp;';

//  --  PANEL: FORM  --  //
$w			= (object) $words['edit'];

$optStatus	= HtmlElements::Options( $words['states'], $group->status );
$optType	= HtmlElements::Options( $words['types'], $group->type );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.$w->buttonCancel, [
	'href'		=> './work/newsletter/group',
	'class'		=> 'btn btn-small',
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.$w->buttonSave, [
	'type'		=> 'submit',
	'class'		=> 'btn btn-primary',
	'name'		=> 'save',
] );
$buttonExport	= HtmlTag::create( 'a', $iconExport.$w->buttonExport, [
	'href'		=> './work/newsletter/group/export/'.$groupId,
	'class'		=> 'btn btn-small not-btn-info',
] );

if( $limiter && $limiter->denies( 'Work.Newsletter.Group:allowExport' ) )
	$buttonExport	= HtmlTag::create( 'button', $iconExport.$w->buttonExport, [
		'type'		=> 'button',
		'class'		=> 'btn btn-small not-btn-info disabled',
		'onclick'	=> 'alert("Exportieren von Kategorien ist in dieser Demo-Installation nicht möglich.")',
	] );

$buttonRemove	= HtmlTag::create( 'a', $iconRemove.$w->buttonRemove, [
	'href'		=> './work/newsletter/group/remove/'.$groupId,
	'class'		=> 'btn btn-danger btn-small',
	'onclick'	=> "if(!confirm('Wirklich?')) return false;",
] );
if( !$groupReaders )
	$buttonRemove		= HtmlTag::create( 'a', $iconRemove.$w->buttonRemove, [
		'href'		=> './work/newsletter/group/remove/'.$groupId,
		'class'		=> 'btn btn-danger btn-small',
		'onclick'	=> "return false;",
		'disabled'	=> 'disabled',
	] );

$buttonReader	= HtmlTag::create( 'a', $iconAdd.$w->buttonReader, [
	'href'		=> './work/newsletter/reader/add/?groups[]='.$groupId,
	'class'		=> 'btn btn-success btn-small',
] );

$panelForm	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./work/newsletter/group/edit/'.$groupId.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $group->title, ENT_QUOTES, 'UTF-8' ).'"required/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_type">'.$w->labelType.'</label>
					<select name="type" id="input_type" class="span12" required>'.$optType.'</select>
				</div>
				<div class="span6">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
				'.$buttonRemove.'
				'.$buttonExport.'
				'.$buttonReader.'
			</div>
		</form>
	</div>
</div>';


//  --  PANEL: READERS  --  //

$helperReaders	= new View_Helper_Work_Newsletter_GroupReaders( $env );
$helperReaders->setGroup( $group );
$helperReaders->setReaders( $groupReaders );
$helperReaders->setWords( $words );
$panelReaders	= $helperReaders->render();



extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/newsletter/group/edit/', ['words' => $words, 'group' => $group] ) );

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	<!--<a href="./work/newsletter/group" class="btn btn-mini">'.$iconCancel.$w->buttonList.'</a>-->
	'.$textAbove.'
	<div class="row-fluid">
		<div class="span6">
			'.$panelForm.'
		</div>
		<div class="span6">
			'.$panelReaders.'
		</div>
	</div>
</div>
'.$textBottom;
