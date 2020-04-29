<?php

function renderNavButton( $formId, $tabId, $label, $dir ){
	$iconClass	= 'fa fa-fw fa-arrow-'.( $dir === 'prev' ? 'left' : 'right' );
	$icon		= UI_HTML_Tag::create( 'i', '', array( 'class' => $iconClass ) );
	return UI_HTML_Tag::create( 'a', $icon.'&nbsp;'.$label, array(
		'href'		=> './manage/form/setTab/'.$formId.'/'.$tabId,
		'class'		=> 'btn',
	) );
}
$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );

$navButtons	= array(
	'list'			=> UI_HTML_Tag::create( 'a', $iconList.'&nbsp;zur Liste', array(
		'href'		=> './manage/form',
		'class'		=> 'btn',
	) ),
	'prevFacts'		=> renderNavButton( $form->formId, 'facts', 'Fakten', 'prev' ),
	'prevView'		=> renderNavButton( $form->formId, 'view', 'Ansicht', 'prev' ),
//	'prevBlocks'	=> renderNavButton( $form->formId, 'blocks', 'Blöcke', 'prev' ),
	'prevContent'	=> renderNavButton( $form->formId, 'content', 'Inhalt', 'prev' ),
	'prevManager'	=> renderNavButton( $form->formId, 'rulesManager', 'Manager-Mail-Regeln', 'prev' ),
	'prevCustomer'	=> renderNavButton( $form->formId, 'rulesCustomer', 'Kunden-Mail-Regeln', 'prev' ),

	'nextView'		=> renderNavButton( $form->formId, 'view', 'Ansicht', 'next' ),
//	'nextBlocks'	=> renderNavButton( $form->formId, 'blocks', 'Blöcke', 'next' ),
	'nextContent'	=> renderNavButton( $form->formId, 'content', 'Inhalt', 'next' ),
	'nextManager'	=> renderNavButton( $form->formId, 'rulesManager', 'Manager-Mail-Regeln', 'next' ),
	'nextCustomer'	=> renderNavButton( $form->formId, 'rulesCustomer', 'Kunden-Mail-Regeln', 'next' ),
	'nextFills'		=> renderNavButton( $form->formId, 'fills', 'Einträge', 'next' ),
);

$panelFacts			= $this->loadTemplateFile( 'manage/form/edit.facts.php', array( 'navButtons' => $navButtons ) );
$panelView			= $this->loadTemplateFile( 'manage/form/edit.view.php', array( 'navButtons' => $navButtons ) );
$panelContent		= $this->loadTemplateFile( 'manage/form/edit.content.php', array( 'navButtons' => $navButtons ) );
//$panelBlocksWithin	= $this->loadTemplateFile( 'manage/form/edit.blocks.within.php', array( 'navButtons' => $navButtons ) );
$panelRulesManager	= $this->loadTemplateFile( 'manage/form/edit.rules.manager.php', array( 'navButtons' => $navButtons ) );
$panelRulesCustomer	= $this->loadTemplateFile( 'manage/form/edit.rules.customer.php', array( 'navButtons' => $navButtons ) );
$panelFills			= '';

$countRulesManager	= count( $rulesManager ) ? ' <small class="muted">('.count( $rulesManager ).')</small>' : '';
$countRulesCustomer	= count( $rulesCustomer ) ? ' <small class="muted">('.count( $rulesCustomer ).')</small>' : '';

$tabs	= new \CeusMedia\Bootstrap\Tabs( 'tabs-form' );
$tabs->add( 'facts', '#', 'Fakten', $panelFacts );
$tabs->add( 'view', '#', 'Ansicht', $panelView );
//$tabs->add( 'blocks', '#', 'Blöcke', $panelBlocksWithin );
$tabs->add( 'content', '#', 'Inhalt', $panelContent );
$tabs->add( 'rulesManager', '#', 'Manager-Mail-Regeln'.$countRulesManager, $panelRulesManager );
$tabs->add( 'rulesCustomer', '#', 'Kunden-Mail-Regeln'.$countRulesCustomer, $panelRulesCustomer );
//$tabs->add( 'fills', '#', 'Einträge', $panelFills );
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
