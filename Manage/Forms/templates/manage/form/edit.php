<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

function renderNavButton( $formId, $tabId, $label, $dir ){
	$iconClass	= 'fa fa-fw fa-arrow-'.( $dir === 'prev' ? 'left' : 'right' );
	$icon		= HtmlTag::create( 'i', '', ['class' => $iconClass] );
	return HtmlTag::create( 'a', $icon.'&nbsp;'.$label, array(
		'href'		=> './manage/form/setTab/'.$formId.'/'.$tabId,
		'class'		=> 'btn',
	) );
}
$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );

$navButtons	= array(
	'list'			=> HtmlTag::create( 'a', $iconList.'&nbsp;zur Liste', array(
		'href'		=> './manage/form',
		'class'		=> 'btn',
	) ),
	'prevFacts'			=> renderNavButton( $form->formId, 'facts', 'Fakten', 'prev' ),
	'prevView'			=> renderNavButton( $form->formId, 'view', 'Ansicht', 'prev' ),
//	'prevBlocks'		=> renderNavButton( $form->formId, 'blocks', 'Blöcke', 'prev' ),
	'prevContent'		=> renderNavButton( $form->formId, 'content', 'Inhalt', 'prev' ),
	'prevManager'		=> renderNavButton( $form->formId, 'rulesManager', 'Manager-Mail-Regeln', 'prev' ),
	'prevCustomer'		=> renderNavButton( $form->formId, 'rulesCustomer', 'Kunden-Mail-Regeln', 'prev' ),
	'prevTransfer'		=> renderNavButton( $form->formId, 'formTransfer', 'Transfer-Regeln', 'prev' ),
	'prevAttachment'	=> renderNavButton( $form->formId, 'rulesAttachment', 'Anhänge', 'prev' ),

	'nextView'			=> renderNavButton( $form->formId, 'view', 'Ansicht', 'next' ),
//	'nextBlocks'		=> renderNavButton( $form->formId, 'blocks', 'Blöcke', 'next' ),
	'nextContent'		=> renderNavButton( $form->formId, 'content', 'Inhalt', 'next' ),
	'nextManager'		=> renderNavButton( $form->formId, 'rulesManager', 'Manager-Mail-Regeln', 'next' ),
	'nextCustomer'		=> renderNavButton( $form->formId, 'rulesCustomer', 'Kunden-Mail-Regeln', 'next' ),
	'nextTransfer'		=> renderNavButton( $form->formId, 'formTransfer', 'Transfer-Regeln', 'next' ),
	'nextAttachment'	=> renderNavButton( $form->formId, 'rulesAttachment', 'Anhänge', 'next' ),


	'nextFills'			=> renderNavButton( $form->formId, 'fills', 'Einträge', 'next' ),
);

$panelFacts				= $this->loadTemplateFile( 'manage/form/edit.facts.php', ['navButtons' => $navButtons] );
$panelView				= $this->loadTemplateFile( 'manage/form/edit.view.php', ['navButtons' => $navButtons] );
$panelContent			= $this->loadTemplateFile( 'manage/form/edit.content.php', ['navButtons' => $navButtons] );
//$panelBlocksWithin	= $this->loadTemplateFile( 'manage/form/edit.blocks.within.php', ['navButtons' => $navButtons] );
$panelRulesManager		= $this->loadTemplateFile( 'manage/form/edit.rules.manager.php', ['navButtons' => $navButtons] );
$panelRulesCustomer		= $this->loadTemplateFile( 'manage/form/edit.rules.customer.php', ['navButtons' => $navButtons] );
$panelRulesAttachment	= $this->loadTemplateFile( 'manage/form/edit.rules.attachment.php', ['navButtons' => $navButtons] );

$countRulesManager		= count( $rulesManager ) ? ' <small class="muted">('.count( $rulesManager ).')</small>' : '';
$countRulesCustomer		= count( $rulesCustomer ) ? ' <small class="muted">('.count( $rulesCustomer ).')</small>' : '';
$countRulesAttachment	= count( $rulesAttachment ) ? ' <small class="muted">('.count( $rulesAttachment ).')</small>' : '';

$tabs	= new \CeusMedia\Bootstrap\Nav\Tabs( 'tabs-form' );
$tabs->add( 'facts', '#', 'Fakten', $panelFacts );
$tabs->add( 'view', '#', 'Ansicht', $panelView );
//$tabs->add( 'blocks', '#', 'Blöcke', $panelBlocksWithin );
$tabs->add( 'content', '#', 'Inhalt', $panelContent );
$tabs->add( 'rulesManager', '#', 'Manager-Mail-Regeln'.$countRulesManager, $panelRulesManager );
$tabs->add( 'rulesCustomer', '#', 'Kunden-Mail-Regeln'.$countRulesCustomer, $panelRulesCustomer );
$tabs->add( 'rulesAttachment', '#', 'Anhänge'.$countRulesAttachment, $panelRulesAttachment );

if( count( $transferTargets ) ){
//	$panelFormTransfer	= '...';
	$panelFormTransfer	= $this->loadTemplateFile( 'manage/form/edit.rules.transfer.php', ['navButtons' => $navButtons] );
	$tabs->add( 'formTransfer', '#', 'Datenweitergabe', $panelFormTransfer );
}

$tabs->setActive( $activeTab ? $activeTab : 'facts' );

return '
<h2><a href="./manage/form" class="muted">Formular:</a> '.$form->title.'</h2>'.$tabs->render().'
<script>
jQuery(document).ready(function(){
	RuleManager.init('.$form->formId.');
	RuleManager.loadFormView();
	FormEditor.initTabs();
	FormEditor.applyAceEditor("#input_content");
});
</script>';
