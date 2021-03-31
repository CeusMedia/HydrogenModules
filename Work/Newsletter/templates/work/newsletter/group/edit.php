<?php
$tabsMain		= $tabbedLinks ? $this->renderMainTabs() : '';

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) ).'&nbsp;';
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) ).'&nbsp;';
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) ).'&nbsp;';
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ).'&nbsp;';
$iconExport		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) ).'&nbsp;';

//  --  PANEL: READERS  --  //
$w			= (object) $words['edit_readers'];

$statusIcons	= array(
	-1		=> 'remove',
	0		=> 'star',
	1		=> 'check',
);

$labelEmpty		= UI_HTML_Tag::create( 'em', $w->empty, array( 'class' => 'muted' ) );
$listReaders	= UI_HTML_Tag::create( 'div', $labelEmpty, array( 'class' => 'alert alert-info' ) );

if( $groupReaders ){
	$listReaders	= array();
	foreach( $groupReaders as $reader ){
		$iconStatus		= UI_HTML_Tag::create( 'i', "", array( 'class' => 'fa fa-fw fa-'.$statusIcons[$reader->status] ) );
		$name			= UI_HTML_Tag::create( 'small', $reader->firstname.' '.$reader->surname );

		$label			= $iconStatus.'&nbsp;&lt;'.$reader->email.'&gt;';
		$urlReader		= './work/newsletter/reader/edit/'.$reader->newsletterReaderId;
		$urlRemove		= './work/newsletter/group/removeReader/'.$group->newsletterGroupId.'/'.$reader->newsletterReaderId;
		$attributes		= array(
			'href'		=> $urlRemove,
			'class'		=> 'btn btn-mini btn-inverse',
		);
		$linkRemove		= UI_HTML_Tag::create( 'a', $iconRemove.$w->buttonRemove, $attributes );
		$linkRemove		= UI_HTML_Tag::create( 'div', $linkRemove, array( 'class' => 'pull-right' ) );
		$linkReader		= UI_HTML_Tag::create( 'a', $label, array( 'href' => $urlReader ) );
		$listReaders[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $linkReader.' '.$name, array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $linkRemove, array( 'class' => '' ) ),
		) );
	}
	$colgroup		= UI_HTML_Elements::ColumnGroup( '', '120px' );
	$tableHeads		= UI_HTML_Elements::TableHeads( array( 'Zugeordnete Leser' ) );
	$thead			= UI_HTML_Tag::create( 'thead', $tableHeads );
	$tbody			= UI_HTML_Tag::create( 'tbody', $listReaders );
	$listReaders	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
		'class'	=> 'table table-condensed table-striped table-fixed'
	) );
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

$optStatus	= UI_HTML_Elements::Options( $words['states'], $group->status );
$optType	= UI_HTML_Elements::Options( $words['types'], $group->type );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.$w->buttonCancel, array(
	'href'		=> './work/newsletter/group',
	'class'		=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.$w->buttonSave, array(
	'type'		=> 'submit',
	'class'		=> 'btn btn-primary',
	'name'		=> 'save',
) );
$buttonExport	= UI_HTML_Tag::create( 'a', $iconExport.$w->buttonExport, array(
	'href'		=> './work/newsletter/group/export/'.$groupId,
	'class'		=> 'btn btn-small not-btn-info',
) );

if( $limiter && $limiter->denies( 'Work.Newsletter.Group:allowExport' ) )
	$buttonExport	= UI_HTML_Tag::create( 'button', $iconExport.$w->buttonExport, array(
		'type'		=> 'button',
		'class'		=> 'btn btn-small not-btn-info disabled',
		'onclick'	=> 'alert("Exportieren von Kategorien ist in dieser Demo-Installation nicht möglich.")',
	) );

$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.$w->buttonRemove, array(
	'href'		=> './work/newsletter/group/remove/'.$groupId,
	'class'		=> 'btn btn-danger btn-small',
	'onclick'	=> "if(!confirm('Wirklich?')) return false;",
) );
if( !$groupReaders )
	$buttonRemove		= UI_HTML_Tag::create( 'a', $iconRemove.$w->buttonRemove, array(
		'href'		=> './work/newsletter/group/remove/'.$groupId,
		'class'		=> 'btn btn-danger btn-small',
		'onclick'	=> "return false;",
		'disabled'	=> 'disabled',
	) );
$buttonReader	= UI_HTML_Tag::create( 'a', $iconAdd.$w->buttonReader, array(
	'href'		=> './work/newsletter/reader/add/?groups[]='.$groupId,
	'class'		=> 'btn btn-success btn-small',
) );

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

extract( $view->populateTexts( array( 'above', 'bottom', 'top' ), 'html/work/newsletter/group/edit/', array( 'words' => $words, 'group' => $group ) ) );

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
