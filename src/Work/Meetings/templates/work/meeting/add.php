<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var array $words */
/** @var object $meeting */
/** @var Dictionary $moduleConfig */

$w			= (object) $words['add'];
$iconCancel	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] ).'&nbsp;';
$iconSave	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-check"] ).'&nbsp;';

$optRoles	= HtmlElements::Options( $words['types'] );

$useRoles	= $moduleConfig->get( 'useRoles' );

$fieldParticipation	= '';
if( $useRoles ){
	$fieldParticipation	= '
		<div class="span3">
			<label for="input_role">'.$w->labelParticipation.'</label>
			<select name="role" id="input_role" class="span12">'.$optRoles.'</select>
		</div>';
}

$panelAdd	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				<form action="./work/meeting/add" method="post">
					<div class="row-fluid">
						<div class="span8">
							<div class="row-fluid">
								<div class="span8">
									<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
									<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $meeting->title ?? '', ENT_QUOTES, 'UTF-8' ).'" required/>
								</div>
								<div class="span4">
									<label for="input_location" class="mandatory">'.$w->labelLocation.'</label>
									<input type="text" name="location" id="input_location" class="span12" value="'.htmlentities( $meeting->location ?? '', ENT_QUOTES, 'UTF-8' ).'" required/>
								</div>
							</div>
							<div class="row-fluid">
								'.$fieldParticipation.'
								<div class="'.( $useRoles ? 'span9' : 'span12' ).'">
									<label for="input_link">'.$w->labelLink.'</label>
									<input type="text" name="link" id="input_link" class="span12" value="'.htmlentities( $meeting->link ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
								</div>
							</div>
						</div>
						<div class="span4">
							<div class="row-fluid">
								<div class="span6">
									<label for="input_dateStart_date" class="mandatory">'.$w->labelDateStart_date.'</label>
									<input type="date" name="dateStart_date" id="input_dateStart_date" class="span12"/>
								</div>
								<div class="span6">
									<label for="input_dateEnd_date" class="mandatory">'.$w->labelDateEnd_date.'</label>
									<input type="date" name="dateEnd_date" id="input_dateEnd_date" class="span12"/>
								</div>
							</div>
							<div class="row-fluid">
								<div class="span6">
									<label for="input_dateStart_time" class="mandatory">'.$w->labelDateStart_time.'</label>
									<input type="time" name="dateStart_time" id="input_dateStart_time" class="span12"/>
								</div>
								<div class="span6">
									<label for="input_dateEnd_time" class="mandatory">'.$w->labelDateEnd_time.'</label>
									<input type="time" name="dateEnd_time" id="input_dateEnd_time" class="span12"/>
								</div>
							</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_content" class="mandatory">'.$w->labelContent.'</label>
							<textarea name="content" id="input_content" class="span12 TinyMCE" data-tinymce-mode="minimal" data-tinymce-height="250" rows="10">'.htmlentities( $meeting->content ?? '', ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="row-fluid">
						<div class="buttonbar">
							<a href="./work/meeting" class="btn btn-small">'.$iconCancel.$w->buttonCancel.'</a>
							<button type="submit" class="btn btn-primary" name="save">'.$iconSave.$w->buttonSave.'</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';

extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/meeting/add/', ['words' => $words] ) );

return $textTop.'
<script>$(document).ready(function(){});</script>
<div class="newsletter-content">
	'.$textAbove.'
	'.$panelAdd.'
</div>
'.$textBottom;
