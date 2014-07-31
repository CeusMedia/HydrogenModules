<?php
$iconEnable		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok-circle icon-white' ) );
$iconDisable	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ban-circle icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$list	= '<div><em class="muted">Keine Anhänge registriert.</em></div>';
if( $attachments ){
	$list	= array();
	foreach( $attachments as $attachment ){
		$buttonStatus	= UI_HTML_Tag::create( 'a', $iconEnable, array( 'href' => './mail/attachment/setStatus/'.$attachment->mailAttachmentId.'/1', 'class' => 'btn btn-success btn-small', 'title' => 'aktivieren' ) );
		if( $attachment->status )
			$buttonStatus	= UI_HTML_Tag::create( 'a', $iconDisable, array( 'href' => './mail/attachment/setStatus/'.$attachment->mailAttachmentId.'/0', 'class' => 'btn btn-warning btn-small', 'title' => 'deaktivieren' ) );
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array( 'href' => '', 'class' => 'btn btn-danger btn-small', 'title' => 'entfernen (Dabei bleibt erhalten)' ) );

		$label		= UI_HTML_Tag::create( 'big', $attachment->filename ).'<br/>'.UI_HTML_Tag::create( 'small', $attachment->mimeType, array( 'class' => 'muted' ) );
		$status		= $attachment->status ? '<span class="label label-success">'.$iconEnable.'</i> aktiv</span>' : '<span class="label label-warning">'.$iconDisable.' inaktiv</span>';
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $label ),
//			UI_HTML_Tag::create( 'td', $attachment->mimeType ),
			UI_HTML_Tag::create( 'td', $attachment->className.'<br/>'.$status ),
//			UI_HTML_Tag::create( 'td', $attachment->countAttached ),
			UI_HTML_Tag::create( 'td', date( "d.m.Y H:i", $attachment->createdAt ) ),
			UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'div', array( $buttonStatus, $buttonRemove ), array( 'class' => 'btn-group' )) ),
			), array() );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "30%", /*"18%",*/ "27%", "10%", /*"15%",*/ "25%" );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Datei',
//		'MIME-Typ',
		'Klasse',
//		'verwendet',
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

return '
<small><em class="muted">Mail::Attachment::Index</em></small>
<h2>Anhänge</h2>
'.$list.'
<div class="row-fluid">
	<div class="span6">
		<h3>Neuer Anhang</h3>
		<form action="./mail/attachment/add" metod="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_file">Datei</label>
					<select id="input_file" name="file" class="span12">'.$optFile.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_class">E-Mail-Klasse</label>
					<select id="input_class" name="class" class="span12">'.$optClass.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="add" class="btn btn-success"><i class="icon-ok icon-white"></i>&nbsp;registrieren</button>
			</div>
		</form>
	</div>
	<div class="span6">
		<h3>Neuer Datei</h3>
		<form action="./mail/attachment/upload" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_file">neue Datei</label>
					<input type="file" name="file"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="add" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;hochladen</button>
			</div>
		</form>
	</div>
</div>
';
