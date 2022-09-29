<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $backup */

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconRestore	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-cog' ) );
$iconDownload	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$w	= (object) array(
	'labelPasswordCurrent_title'	=> 'Passwort',
	'labelPasswordCurrent'			=> 'Passwort',
	'buttonRecover'					=> 'wiederherstellen',
);

return '
<div class="content-panel">
	<h3>Wiederherstellen</h3>
	<div class="content-panel-inner">
		<form action="./admin/database/backup/restore/'.$backup->id.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<div class="alert alert-danger">
						Das Wiederherstellen einer Sicherung löscht den aktuellen Datenbestand vollständig.<br/>
						<strong>Bitte nur mit Bedacht ausführen!</strong>
					</div>
				</div>
			</div>
			'.HTML::Buttons( array(
				HtmlTag::create( 'small', $w->labelPasswordCurrent_title, array( 'class' => 'not-muted' ) ),
				HTML::DivClass( 'row-fluid',
					HTML::DivClass( 'span6', array(
						HTML::DivClass( 'input-prepend input-append',
							HTML::SpanClass( 'add-on', '<i class="fa fa-fw fa-lock"></i>' ).
							HtmlTag::create( 'input', '', array(
								'type'			=> 'password',
								'name'			=> 'password',
								'id'			=> 'input_password',
								'class'			=> 'span7',
								'required'		=> 'required',
								'autocomplete'	=> 'current-password',
								'placeholder'	=> $w->labelPasswordCurrent,
							) ).
							HtmlElements::Button( 'save', '<i class="fa fa-fw fa-check"></i> '.$w->buttonRecover, 'btn btn-primary' )
						)
					) )
				)
			) ).'
		</form>
	</div>
</div>';
