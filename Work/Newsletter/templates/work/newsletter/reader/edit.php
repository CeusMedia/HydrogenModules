<?php
$tabsMain		= $tabbedLinks ? $this->renderMainTabs() : '';

$statusIcons	= array(
	-1		=> 'remove',
	0		=> 'star',
	1		=> 'ok',
);

$optStatus	= UI_HTML_Elements::Options( $words->states, $reader->status );
$optGender	= UI_HTML_Elements::Options( $words->gender, $reader->gender );

$optGroup	= array();
foreach( $groups as $group )
	if( !array_key_exists( $group->newsletterGroupId, $readerGroups ) )
		$optGroup[$group->newsletterGroupId]	= $group->title;
$hideGroupAdd	= count( $optGroup ) ? '' : 'style="display: none"';
$optGroup	= UI_HTML_Elements::Options( $optGroup, array_keys( $readerGroups ) );

$listGroups	= UI_HTML_Tag::create( 'div', 'Keine Gruppen zugewiesen.', array( 'class' => 'alert alert-info' ) );
if( $readerGroups ){
	$listGroups	= array();
	foreach( $readerGroups as $readerGroup ){
		$label			= $readerGroup->title;
		$urlRemove		= './work/newsletter/reader/removeGroup/'.$reader->newsletterReaderId.'/'.$readerGroup->newsletterGroupId;
		$iconStatus		= UI_HTML_Tag::create( 'i', "", array( 'class' => 'icon-'.$statusIcons[$readerGroup->status] ) );
		$attributes		= array(
			'href'		=> $urlRemove,
			'class'		=> 'btn btn-mini btn-inverse',
		);
		$linkRemove		= UI_HTML_Tag::create( 'a', '<i class="fa fa-remove"></i>', $attributes );
		$linkRemove		= UI_HTML_Tag::create( 'div', $linkRemove, array( 'class' => 'pull-right' ) );
		$urlGroup		= './work/newsletter/group/edit/'.$readerGroup->newsletterGroupId;
		$linkGroup		= UI_HTML_Tag::create( 'a', /*$iconStatus.' '.*/$label, array( 'href' => $urlGroup ) );

		$listGroups[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $linkGroup, array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $linkRemove, array( 'class' => '' ) ),
		) );
	}
	$colgroup		= UI_HTML_Elements::ColumnGroup( "", "35px" );
	$tableHeads		= UI_HTML_Elements::TableHeads( array( 'Zugewiesene Gruppen', '' ) );
	$thead			= UI_HTML_Tag::create( 'thead', $tableHeads );
	$tbody			= UI_HTML_Tag::create( 'tbody', $listGroups );
	$listGroups		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
		'class'	=> "table table-condensed table-striped table-fixed"
	) );
}

$listLetters	= '<em><small class="muted">Keine.</small></em>';

if( $readerLetters ){
	$stats		= (object) array(
		'sent'		=> 0,
		'opened'	=> 0,
		'ratio'		=> 0,
	);
	$listLetters	= array();
	foreach( $readerLetters as $letter ){
		$attributes		= array(
			'href'	=> './work/newsletter/edit/'.$letter->newsletterId
		);
		$class	= 'label label-error';
		if( $letter->status >= 1 ){
			$stats->sent++;
			$class	= 'label label-warning';
		}
		if( $letter->status >= 2 ){
			$stats->opened++;
			$class	= 'label label-success';
		}
		if( $stats->sent > 0 )
			$stats->ratio	= round( $stats->opened / $stats->sent * 100, 1 );

		$indicator		= UI_HTML_Tag::create( 'span', '&nbsp;&nbsp;&nbsp;', array( 'class' => $class ) );
		$link			= UI_HTML_Tag::create( 'a', $letter->newsletter->title, $attributes );
		$listLetters[]	= UI_HTML_Tag::create( 'li', $indicator.'&nbsp;'.$link, array( 'class' => 'autocut' ) );
	}
	$listLetters	= UI_HTML_Tag::create( 'ul', $listLetters, array( 'class' => 'unstyled' ) );
	if( $stats->sent > 0 ){
		$list	= array();
		$list[]	= UI_HTML_Tag::create( 'dt', 'Zugestellt' );
		$list[]	= UI_HTML_Tag::create( 'dd', $stats->sent );
		$list[]	= UI_HTML_Tag::create( 'dt', 'Geöffnet' );
		$list[]	= UI_HTML_Tag::create( 'dd', $stats->opened );
		$list[]	= UI_HTML_Tag::create( 'dt', 'Rate' );
		$list[]	= UI_HTML_Tag::create( 'dd', $stats->ratio.'%' );
		$listLetters	.= '<hr/>'.UI_HTML_Tag::create( 'dl', $list, array( 'class' => "dl-horizontal" ) );
	}
}

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) ).'&nbsp;';
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) ).'&nbsp;';
$iconConfirm	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-envelope-o' ) ).'&nbsp;';
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ).'&nbsp;';

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


extract( $view->populateTexts( array( 'above', 'bottom', 'top' ), 'html/work/newsletter/reader/edit/', array( 'words' => $words, 'reader' => $reader ) ) );

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
<!--	<a href="./work/newsletter/reader" class="btn btn-mini">'.$iconCancel.$words->edit->buttonList.'</a>-->
	'.$textAbove.'
	<div class="row-fluid">
		<div class="span8">
			<div class="content-panel">
				<h3>Daten</h3>
				<div class="content-panel-inner">
					<form action="./work/newsletter/reader/edit/'.$readerId.'" method="post">
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
			<div class="content-panel">
				<h3>Erhaltende Newsletter</h3>
				<div class="content-panel-inner">
					'.$listLetters.'
				</div>
			</div>
		</div>
		<div class="span4">
			<div class="content-panel">
				<h3>Gruppen</h3>
				<div class="content-panel-inner">
					'.$listGroups.'
					<div class="row-fluid" '.$hideGroupAdd.'>
						<hr style="margin: -3px 0px 6px 0px"/>
						<form action="./work/newsletter/reader/addGroup/'.$reader->newsletterReaderId.'" method="post">
							<div class="span9">
								<select name="groupId" class="span12">'.$optGroup.'</select>
							</div>
							<div class="span3">
								<button type="submit" name="save" class="btn btn-success">'.$iconAdd.'</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
'.$textBottom;
