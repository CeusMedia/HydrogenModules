<?php /** @noinspection ALL */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Bootstrap\Nav\Tabs;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var View_Manage_Form $view */
/** @var object $form */
/** @var array<object> $transferTargets */
/** @var array<object> $rulesCustomer */
/** @var array<object> $rulesManager */
/** @var array<object> $rulesAttachment */
/** @var int $nrFills */
/** @var bool $hasFills */
/** @var int|string|NULL $activeTab */
/** @var array<string,string|HtmlTag> $navButtons */
/** @var array<string> $references */
/** @var array<string,array<string,string>> $words */

$w			= (object) $words['edit'];

/**
 *	@param		int|string 		$formId
 *	@param		?string			$tabId
 *	@param		?string			$label
 *	@param		string			$dir			One of {prev, next}
 *	@return		string
 */
function renderNavButton( int|string $formId, ?string $tabId, ?string $label, string $dir ): string
{
	$icon	= new Icon( 'arrow-'.( $dir === 'prev' ? 'left' : 'right' ) );
	return HtmlTag::create( 'a', $icon.'&nbsp;'.$label, [
		'href'		=> './manage/form/setTab/'.$formId.'/'.$tabId,
		'class'		=> 'btn',
	] );
}
$iconList	= new Icon( 'list' );

$navButtons	= [
	'list'			=> HtmlTag::create( 'a', $iconList.'&nbsp;'.$w->buttonList, [
		'href'		=> './manage/form',
		'class'		=> 'btn',
	] ),
	'prevFacts'			=> renderNavButton( $form->formId, 'facts', $w->tabFacts, 'prev' ),
	'prevView'			=> renderNavButton( $form->formId, 'view', $w->tabView, 'prev' ),
//	'prevBlocks'		=> renderNavButton( $form->formId, 'blocks', 'Blöcke', 'prev' ),
	'prevContent'		=> renderNavButton( $form->formId, 'content', $w->tabContent, 'prev' ),
	'prevManager'		=> renderNavButton( $form->formId, 'rulesManager', $w->tabRulesManager, 'prev' ),
	'prevCustomer'		=> renderNavButton( $form->formId, 'rulesCustomer', $w->tabRulesCustomer, 'prev' ),
	'prevTransfer'		=> renderNavButton( $form->formId, 'formTransfer', $w->tabRulesTransfer, 'prev' ),
	'prevAttachment'	=> renderNavButton( $form->formId, 'rulesAttachment', $w->tabRulesAttachment, 'prev' ),

	'nextView'			=> renderNavButton( $form->formId, 'view', $w->tabView, 'next' ),
//	'nextBlocks'		=> renderNavButton( $form->formId, 'blocks', 'Blöcke', 'next' ),
	'nextContent'		=> renderNavButton( $form->formId, 'content', $w->tabContent, 'next' ),
	'nextManager'		=> renderNavButton( $form->formId, 'rulesManager', $w->tabRulesManager, 'next' ),
	'nextCustomer'		=> renderNavButton( $form->formId, 'rulesCustomer', $w->tabRulesCustomer, 'next' ),
	'nextTransfer'		=> renderNavButton( $form->formId, 'formTransfer', $w->tabRulesTransfer, 'next' ),
	'nextAttachment'	=> renderNavButton( $form->formId, 'rulesAttachment', $w->tabRulesAttachment, 'next' ),


	'nextFills'			=> renderNavButton( $form->formId, 'fills', $w->tabFills, 'next' ),
];

$panelFacts				= $view->loadTemplateFile( 'manage/form/edit.facts.php', ['navButtons' => $navButtons] );
$panelView				= $view->loadTemplateFile( 'manage/form/edit.view.php', ['navButtons' => $navButtons] );
$panelContent			= $view->loadTemplateFile( 'manage/form/edit.content.php', ['navButtons' => $navButtons] );
//$panelBlocksWithin	= $view->loadTemplateFile( 'manage/form/edit.blocks.within.php', ['navButtons' => $navButtons] );
$panelRulesManager		= $view->loadTemplateFile( 'manage/form/edit.rules.manager.php', ['navButtons' => $navButtons] );
$panelRulesCustomer		= $view->loadTemplateFile( 'manage/form/edit.rules.customer.php', ['navButtons' => $navButtons] );
$panelRulesAttachment	= $view->loadTemplateFile( 'manage/form/edit.rules.attachment.php', ['navButtons' => $navButtons] );

$countRulesManager		= count( $rulesManager ) ? ' <small class="muted">('.count( $rulesManager ).')</small>' : '';
$countRulesCustomer		= count( $rulesCustomer ) ? ' <small class="muted">('.count( $rulesCustomer ).')</small>' : '';
$countRulesAttachment	= count( $rulesAttachment ) ? ' <small class="muted">('.count( $rulesAttachment ).')</small>' : '';

$tabs	= new Tabs( 'tabs-form' );
$tabs->add( 'facts', '#', $w->tabFacts, $panelFacts );
$tabs->add( 'view', '#', $w->tabView, $panelView );
//$tabs->add( 'blocks', '#', 'Blöcke', $panelBlocksWithin );
$tabs->add( 'content', '#', $w->tabContent, $panelContent );
$tabs->add( 'rulesManager', '#', $w->tabRulesManager.$countRulesManager, $panelRulesManager );
$tabs->add( 'rulesCustomer', '#', $w->tabRulesCustomer.$countRulesCustomer, $panelRulesCustomer );
$tabs->add( 'rulesAttachment', '#', $w->tabRulesAttachment.$countRulesAttachment, $panelRulesAttachment );

if( count( $transferTargets ) ){
//	$panelFormTransfer	= '...';
	$panelFormTransfer	= $view->loadTemplateFile( 'manage/form/edit.rules.transfer.php', ['navButtons' => $navButtons] );
	$tabs->add( 'formTransfer', '#', $w->tabTransfer, $panelFormTransfer );
}

$tabs->setActive( $activeTab ?: 'facts' );

return '
<h2><a href="./manage/form" class="muted">'.$w->headingPrefix.'</a> '.$form->title.'</h2>'.$tabs->render().'
<script>
jQuery(document).ready(function(){
	let formId = '.$form->formId.';
	RuleManager.init(formId);
	RuleManager.loadFormView();
	FormEditor.initTabs(formId);
	FormEditor.applyAceEditor("#input_content");
});
</script>';
