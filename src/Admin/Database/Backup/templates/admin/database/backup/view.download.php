<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $backup */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconRestore	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-cog'] );
$iconDownload	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$w	= (object) [
	'labelPasswordCurrent_title'	=> 'Passwort',
	'labelPasswordCurrent'			=> 'Passwort',
	'buttonDownload'				=> 'herunterladen',
];

return '
<div class="content-panel">
	<h3>Download</h3>
	<div class="content-panel-inner">
		<form action="./admin/database/backup/download/'.$backup->id.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<p>
						Die Datei kann als SQL-Script heruntergeladen werden.<br/>
					</p>
					<div class="alert alert-notice">
						Diese Datei dient ausschließlich der manuellen Datensicherung und ist nicht für den Import von Daten vorgesehen.
					</div>
				</div>
			</div>
			'.HTML::Buttons( [
				HtmlTag::create( 'small', $w->labelPasswordCurrent_title, ['class' => 'not-muted'] ),
				HTML::DivClass( 'row-fluid',
					HTML::DivClass( 'span6', [
						HTML::DivClass( 'input-prepend input-append',
							HTML::SpanClass( 'add-on', '<i class="fa fa-fw fa-lock"></i>' ).
							HtmlTag::create( 'input', '', [
								'type'			=> 'password',
								'name'			=> 'password',
								'id'			=> 'input_password',
								'class'			=> 'span7',
								'required'		=> 'required',
								'autocomplete'	=> 'current-password',
								'placeholder'	=> $w->labelPasswordCurrent,
							] ).
							HtmlElements::Button( 'save', '<i class="fa fa-fw fa-download"></i> '.$w->buttonDownload, 'btn btn-primary' )
						)
					] )
				)
			] ).'
		</form>
	</div>
</div>';
