<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var bool $tabbedLinks */

$tabsMain	= $tabbedLinks ? $this->renderMainTabs() : '';

$w			= (object) $words['add'];

$optType	= HtmlElements::Options( $words['types'], $group->type );

extract( $view->populateTexts( ['above', 'bottom', 'info', 'top'], 'html/work/newsletter/group/add/', ['words' => $words] ) );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';

$optGroup	= ['' => 'keine'];
foreach( $groups as $item )
	$optGroup[$item->newsletterGroupId]	= $item->title.' ('.$item->count.')';
$optGroup	= HtmlElements::Options( $optGroup );

$panelAdd	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./work/newsletter/group/add" method="post">
			<div class="row-fluid">
				<div class="span12">
					<div class="row-fluid">
						<div class="span8">
							<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $group->title, ENT_QUOTES, 'UTF-8' ).'"  required="required"/>
						</div>
						<div class="span4">
							<label for="input_type">'.$w->labelType.'</label>
							<select name="type" id="input_type" class="span12" required>'.$optType.'</select>
						</div>
					</div>
					<hr/>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_copyUsersOfGroupIds">Leser diese Gruppe(n) übernehmen</label>
							<select name="copyUsersOfGroupIds[]" id="input_copyUsersOfGroupIds" multiple="multiple" size="8" class="span12 multiple">'.$optGroup.'</select>
						</div>
					</div>
					<div class="alert alert-info">
						<strong>Hinweis:</strong> Das Übernehmen von Lesern aus anderen Gruppen muss rechtlich abgesichert sein, also in den Nutzungsbedingungen oder Datenschutzrechtlinien explizit erlaubt worden sein.
					</div>
					<div class="row-fluid">
						<div class="buttonbar">
							<a href="./work/newsletter/group" class="btn btn-small">'.$iconCancel.$w->buttonCancel.'</a>
							<button type="submit" class="btn btn-primary" name="save">'.$iconSave.$w->buttonSave.'</button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>';

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
	<div class="row-fluid">
		<div class="span6">
			'.$panelAdd.'
		</div>
		<div class="span6">
			'.$textInfo.'
		</div>
	</div>
</div>'.$textBottom;
