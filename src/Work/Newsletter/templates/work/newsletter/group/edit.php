<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var bool $tabbedLinks */

$tabsMain		= $tabbedLinks ? $this->renderMainTabs() : '';

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] ).'&nbsp;';
$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'&nbsp;';
$iconExport		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] ).'&nbsp;';

//  --  PANEL: READERS  --  //
$w			= (object) $words['edit_readers'];

$statusIcons	= [
	-1		=> 'remove',
	0		=> 'star',
	1		=> 'check',
];

$labelEmpty		= HtmlTag::create( 'em', $w->empty, ['class' => 'muted'] );
$listReaders	= HtmlTag::create( 'div', $labelEmpty, ['class' => 'alert alert-info'] );

if( $groupReaders ){
	$listReaders	= [];
	foreach( $groupReaders as $reader ){
		$iconStatus		= HtmlTag::create( 'i', "", ['class' => 'fa fa-fw fa-'.$statusIcons[$reader->status]] );
		$name			= HtmlTag::create( 'small', $reader->firstname.' '.$reader->surname );

		$label			= $iconStatus.'&nbsp;&lt;'.$reader->email.'&gt;';
		$urlReader		= './work/newsletter/reader/edit/'.$reader->newsletterReaderId;
		$urlRemove		= './work/newsletter/group/removeReader/'.$group->newsletterGroupId.'/'.$reader->newsletterReaderId;
		$attributes		= [
			'href'		=> $urlRemove,
			'class'		=> 'btn btn-mini btn-inverse',
		];
		$linkRemove		= HtmlTag::create( 'a', $iconRemove.$w->buttonRemove, $attributes );
		$linkRemove		= HtmlTag::create( 'div', $linkRemove, ['class' => 'pull-right'] );
		$linkReader		= HtmlTag::create( 'a', $label, ['href' => $urlReader] );
		$listReaders[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $linkReader.' '.$name, ['class' => ''] ),
			HtmlTag::create( 'td', $linkRemove, ['class' => ''] ),
		) );
	}
	$colgroup		= HtmlElements::ColumnGroup( '', '120px' );
	$tableHeads		= HtmlElements::TableHeads( ['Zugeordnete Leser'] );
	$thead			= HtmlTag::create( 'thead', $tableHeads );
	$tbody			= HtmlTag::create( 'tbody', $listReaders );
	$listReaders	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, [
		'class'	=> 'table table-condensed table-striped table-fixed'
	] );
}
$panelReaders	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner" id="group-reader-list">
		'.$listReaders.'
	</div>
</div>';

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
	$buttonExport	= HtmlTag::create( 'button', $iconExport.$w->buttonExport, array(
		'type'		=> 'button',
		'class'		=> 'btn btn-small not-btn-info disabled',
		'onclick'	=> 'alert("Exportieren von Kategorien ist in dieser Demo-Installation nicht möglich.")',
	) );

$buttonRemove	= HtmlTag::create( 'a', $iconRemove.$w->buttonRemove, array(
	'href'		=> './work/newsletter/group/remove/'.$groupId,
	'class'		=> 'btn btn-danger btn-small',
	'onclick'	=> "if(!confirm('Wirklich?')) return false;",
) );
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
