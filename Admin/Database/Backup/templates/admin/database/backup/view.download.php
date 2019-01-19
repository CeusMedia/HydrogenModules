<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconRestore	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cog' ) );
$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$w	= (object) array(
	'labelPasswordCurrent_title'	=> 'Passwort',
	'labelPasswordCurrent'			=> 'Passwort',
	'buttonDownload'				=> 'herunterladen',
);

return '
<div class="content-panel">
	<h3>Download</h3>
	<div class="content-panel-inner">
		<form action="./admin/database/backup/download/'.$backup->id.'" method="post">
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
