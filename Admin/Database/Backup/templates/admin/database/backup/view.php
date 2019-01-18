<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconRestore	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cog' ) );
$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

if( is_string( $dump->comment ) ){
	$dump->comment	= array(
		'comment'		=> $dump->comment,
		'copyPrefix'	=> NULL,
		'copyTimestamp'	=> NULL,
	);
}
//print_m( $dump->comment );die;

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;zurück zur Liste', array(
	'href'	=> './admin/database/backup/',
	'class'	=> 'btn'
) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
	'href'	=> './admin/database/backup/remove/'.$dump->id,
	'class'	=> 'btn btn-danger'
) );


/*  --  PANEL: COPY  --  */
$buttonCreateCopy	= UI_HTML_Tag::create( 'a', $iconRestore.' Kopie installieren', array(
	'href'	=> './admin/database/backup/createCopy/'.$dump->id,
	'class'	=> 'btn btn-primary'
) );
$buttonRemoveCopy	= UI_HTML_Tag::create( 'a', $iconRemove.' Kopie entfernen', array(
	'href'	=> './admin/database/backup/removeCopy/'.$dump->id,
	'class'	=> 'btn btn-danger'
) );
$facts					= 'Eine Sicherung kann als Kopie in der Datenbank installiert werden.<br/>Diese Kopie kann zur temporären Ansicht für den aktuellen Benutzer aktiviert werden.<br/>Der Kopiervorgang kann, abhängig von der Datenbankgröße, einige Zeit beanspruchen.';
$buttonActivateCopy		= '';
$buttonDeactivateCopy	= '';
if( !empty( $dump->comment['copyPrefix'] ) ){
	$buttonCreateCopy		= '';
	$buttonDeactivateCopy	= '';
	$buttonActivateCopy		= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp; Kopie aktivieren', array(
		'href'	=> './admin/database/backup/activateCopy/'.$dump->id,
		'class'	=> 'btn btn-success'
	) );
	$facts	= '
		<dl class="dl-horizontal">
			<dt>Präfix</dt>
			<dd>'.$dump->comment['copyPrefix'].'</dd>
			<dt>Erstellungsdatum</dt>
			<dd>'.date( 'Y-m-d H:i:s', (float) $dump->comment['copyTimestamp'] ).'</dd>
		</dl>';
	if( $dump->comment['copyPrefix'] === $currentCopyPrefix ){
		$buttonActivateCopy		= '';
		$buttonRemoveCopy		= '';
		$buttonDeactivateCopy	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp; Kopie deaktivieren', array(
			'href'	=> './admin/database/backup/deactivateCopy/'.$dump->id,
			'class'	=> 'btn btn-inverse'
		) );
	}
}

$panelCopy	= '
<div class="content-panel">
	<h3>Kopie erstellen und aktivieren</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.$facts.'
			</div>
		</div>
		<div class="buttonbar">
			'.$buttonCreateCopy.'
			'.$buttonActivateCopy.'
			'.$buttonDeactivateCopy.'
		</div>
	</div>
</div>';

$w	= (object) array(
	'labelPasswordCurrent_title'	=> 'Passwort',
	'labelPasswordCurrent'			=> 'Passwort',
	'buttonRecover'					=> 'wiederherstellen',
);

$panelRecover	= '
<div class="content-panel">
	<h3>Wiederherstellen</h3>
	<div class="content-panel-inner">
		<form action="./admin/database/backup/restore/'.$dump->id.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<div class="alert alert-danger">
						Das Wiederherstellen einer Sicherung löscht den aktuellen Datenbestand vollständig.<br/>
						<strong>Bitte nur mit Bedacht ausführen!</strong>
					</div>
				</div>
			</div>
			'.HTML::Buttons( array(
				UI_HTML_Tag::create( 'small', $w->labelPasswordCurrent_title, array( 'class' => 'not-muted' ) ),
				HTML::DivClass( 'row-fluid',
					HTML::DivClass( 'span6', array(
						HTML::DivClass( 'input-prepend input-append',
							HTML::SpanClass( 'add-on', '<i class="fa fa-fw fa-lock"></i>' ).
							UI_HTML_Tag::create( 'input', '', array(
								'type'			=> 'password',
								'name'			=> 'password',
								'id'			=> 'input_password',
								'class'			=> 'span7',
								'required'		=> 'required',
								'autocomplete'	=> 'current-password',
								'placeholder'	=> $w->labelPasswordCurrent,
							) ).
							UI_HTML_Elements::Button( 'save', '<i class="fa fa-fw fa-check"></i> '.$w->buttonRecover, 'btn btn-primary' )
						)
					) )
				)
			) ).'
		</form>
	</div>
</div>';

$w	= (object) array(
	'labelPasswordCurrent_title'	=> 'Passwort',
	'labelPasswordCurrent'			=> 'Passwort',
	'buttonDownload'				=> 'herunterladen',
);

$panelDownload	= '
<div class="content-panel">
	<h3>Download</h3>
	<div class="content-panel-inner">
		<form action="./admin/database/backup/download/'.$dump->id.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<p>
						Die Datei kann als SQL-Script herunter geladen werden.<br/>
					</p>
					<div class="alert alert-notice">
						Diese Datei dient ausschließlich der manuellen Datensicherung und ist nicht für den Import von Daten vorgesehen.
					</div>
				</div>
			</div>
			'.HTML::Buttons( array(
				UI_HTML_Tag::create( 'small', $w->labelPasswordCurrent_title, array( 'class' => 'not-muted' ) ),
				HTML::DivClass( 'row-fluid',
					HTML::DivClass( 'span6', array(
						HTML::DivClass( 'input-prepend input-append',
							HTML::SpanClass( 'add-on', '<i class="fa fa-fw fa-lock"></i>' ).
							UI_HTML_Tag::create( 'input', '', array(
								'type'			=> 'password',
								'name'			=> 'password',
								'id'			=> 'input_password',
								'class'			=> 'span7',
								'required'		=> 'required',
								'autocomplete'	=> 'current-password',
								'placeholder'	=> $w->labelPasswordCurrent,
							) ).
							UI_HTML_Elements::Button( 'save', '<i class="fa fa-fw fa-download"></i> '.$w->buttonDownload, 'btn btn-primary' )
						)
					) )
				)
			) ).'
		</form>
	</div>
</div>';


$comment	= $dump->comment['comment'] ? $dump->comment['comment'] : '<em class="muted">Kein Kommentar</em>';

return '
<div class="row-fluid">
	<div class="span8">
		<div class="content-panel">
			<h3>Sicherung</h3>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span12">
						<dl class="dl-horizontal">
							<dt>Dateiname</dt>
							<dd>'.$dump->filename.'</dd>
							<dt>Kommentar</dt>
							<dd>'.$comment.'</dd>
							<dt>Speicherort</dt>
							<dd>'.substr( $dump->pathname, 0, -1 * strlen( $dump->filename ) ).'</dd>
							<dt>Dateigröße</dt>
							<dd>'.Alg_UnitFormater::formatBytes( $dump->filesize ).'</dd>
							<dt>Erstellungsdatum</dt>
							<dd>'.date( 'Y-m-d', $dump->timestamp ).' <small class="muted">'.date( 'H:i:s', $dump->timestamp ).'</small></dd>
						</dl>
					</div>
				</div>
				<div class="buttonbar">
					'.$buttonCancel.'
					'.$buttonRemove.'
				</div>
			</div>
		</div>
		'.$panelCopy.'
	</div>
	<div class="span4">
		'.$panelDownload.'
		'.$panelRecover.'
	</div>
</div>';
