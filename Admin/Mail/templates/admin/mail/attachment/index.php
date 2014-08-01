<?php
$iconEnable		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok-circle icon-white' ) );
$iconDisable	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ban-circle icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$list	= '<div><em class="muted">Keine Anhänge registriert.</em></div>';
if( $attachments ){
	$list	= array();
	foreach( $attachments as $attachment ){
		$buttonStatus	= UI_HTML_Tag::create( 'a', $iconEnable, array( 'href' => './admin/mail/attachment/setStatus/'.$attachment->mailAttachmentId.'/1', 'class' => 'btn btn-success btn-small', 'title' => 'aktivieren' ) );
		if( $attachment->status )
			$buttonStatus	= UI_HTML_Tag::create( 'a', $iconDisable, array( 'href' => './admin/mail/attachment/setStatus/'.$attachment->mailAttachmentId.'/0', 'class' => 'btn btn-warning btn-small', 'title' => 'deaktivieren' ) );
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array( 'href' => '', 'class' => 'btn btn-danger btn-small', 'title' => 'entfernen (Dabei bleibt erhalten)' ) );

		$label		= UI_HTML_Tag::create( 'big', $attachment->filename ).'<br/>'.UI_HTML_Tag::create( 'small', $attachment->mimeType, array( 'class' => 'muted' ) );
		$status		= (object) array (
			'label'		=> $words['states'][(int) $attachment->status],
			'icon'		=> $attachment->status > 0 ? $iconEnable : $iconDisable,
			'class'		=> $attachment->status > 0 ? 'label label-success' : 'label label-warning',
		);
		$status		= UI_HTML_Tag::create( 'span', $status->icon.' '.$status->label,  array( 'class' => $status->class ) );
		$date		= date( "d.m.Y", $attachment->createdAt );
		$time		= date( "H:i", $attachment->createdAt );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $label ),
			UI_HTML_Tag::create( 'td', $attachment->className.'<br/>'.$status ),
//			UI_HTML_Tag::create( 'td', $attachment->countAttached ),
			UI_HTML_Tag::create( 'td', $date.' <small class="muted">'.$time.'</small>' ),
			UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'div', array( $buttonStatus, $buttonRemove ), array( 'class' => 'btn-group' )) ),
			), array() );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "", /*"18%",*/ "", "140px", /*"15%",*/ "80px" );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Datei',
		'Klasse',
		'angehangen',
		''
	) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

$optFile	= array();
foreach( $files as $file )
	$optFile[$file->fileName]	= $file->fileName;
$optFile	= UI_HTML_Elements::Options( $optFile );
$optClass	= UI_HTML_Elements::Options( array_combine( $classes, $classes ) );

$w			= (object) $words['add'];
$optStatus	= UI_HTML_Elements::Options( $words['states'], 1 );
$panelAdd	= '
		<h3>'.$w->heading.'</h3>
		<form action="./admin/mail/attachment/add" metod="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_file">'.$w->labelFile.'</label>
					<select id="input_file" name="file" class="span12">'.$optFile.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_class">'.$w->labelClass.'</label>
					<select id="input_class" name="class" class="span12">'.$optClass.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select id="input_status" name="status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="add" class="btn btn-success"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
';

$w			= (object) $words['upload'];
$panelUpload	= '
		<h3>'.$w->heading.'</h3>
		<form action="./admin/mail/attachment/upload" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_file">'.$w->labelFile.'</label>
					<input type="file" name="file"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="hint">
						<small><em class="muted">'.$w->hintMimeType.'</em></small>
					</div>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="add" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
';

$w			= (object) $words['index'];
return '
<h2>'.$w->heading.'</h2>
'.$list.'
<div class="row-fluid">
	<div class="span6">
		'.$panelAdd.'
	</div>
	<div class="span5 offset1">
		'.$panelUpload.'
	</div>
</div>
';
