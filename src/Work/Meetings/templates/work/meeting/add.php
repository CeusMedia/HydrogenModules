<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var object $meeting */

$iconCancel	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] ).'&nbsp;';
$iconSave	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-check"] ).'&nbsp;';

$panelAdd	= '
<div class="content-panel">
	<h3>'.$words->add->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				<form action="./work/meeting/add" method="post">
					<div class="row-fluid">
						<div class="span8">
							<div class="row-fluid">
								<div class="span8">
									<label for="input_title" class="mandatory">'.$words->add->labelTitle.'</label>
									<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $meeting->title ?? '', ENT_QUOTES, 'UTF-8' ).'" required/>
								</div>
							</div>
							<div class="row-fluid">
								<div class="span6">
									<label for="input_location" class="mandatory">'.$words->add->labelLocation.'</label>
									<input type="text" name="location" id="input_location" class="span12" value="'.htmlentities( $meeting->location ?? '', ENT_QUOTES, 'UTF-8' ).'" required/>
								</div>
							</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_content" class="mandatory">'.$words->add->labelContent.'</label>
							<textarea name="content" id="input_content" class="span12 TinyMCE" rows="10" required="required">'.htmlentities( $meeting->content ?? '', ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="row-fluid">
						<div class="buttonbar">
							<a href="./work/meeting" class="btn btn-small">'.$iconCancel.$words->add->buttonCancel.'</a>
							<button type="submit" class="btn btn-primary" name="save">'.$iconSave.$words->add->buttonSave.'</button>
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
