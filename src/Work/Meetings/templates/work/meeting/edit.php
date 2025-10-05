<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var View_Work_Meeting $view */
/** @var array $words */
/** @var Entity_Work_Meeting $meeting */
/** @var Entity_Role[] $roles */
/** @var Entity_Group[] $groups */
/** @var Entity_User[] $users */
/** @var bool $editMode */

$panelEdit			= $editMode ? $view->renderEditPanel( $words, $meeting ) : $view->renderEditViewPanel( $words, $meeting );
$panelParticipants	= $view->renderEditParticipantsPanel( $words, $meeting, $roles, $groups, $users );
$panelStatus		= $view->renderEditStatusPanel( $words, $meeting );

extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/meeting/edit/', ['words' => $words] ) );

return $textTop.HtmlTag::create( 'div', [
	$textAbove,
	HtmlTag::create( 'div', [
		HtmlTag::create( 'div', $panelEdit, ['class' => 'span8' ] ),
		HtmlTag::create( 'div', $panelParticipants, ['class' => 'span4' ] ),
	], ['class' => 'row-fluid' ] ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'div', $panelStatus, ['class' => 'span12' ] ),
	], ['class' => 'row-fluid' ] ),
], ['class' => 'meeting-editor' ] ).$textBottom;
