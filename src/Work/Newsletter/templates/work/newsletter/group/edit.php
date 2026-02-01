<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var View_Work_Newsletter_Group $view */
/** @var View_Work_Newsletter_Group $this */
/** @var object $words */
/** @var bool $tabbedLinks */
/** @var array<object> $groupReaders */
/** @var object $group */
/** @var int|string $groupId */
/** @var ?Logic_Limiter $limiter */
/** @var bool $useUserGroupRelations */
/** @var bool $canManageGroupRelations */
/** @var bool $canExport */
/** @var bool $canImport */

$tabsMain		= $tabbedLinks ? $view->renderMainTabs( 'work/newsletter/group' ) : '';

$iconExport		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] ).'&nbsp;';
$iconImport		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-upload'] ).'&nbsp;';

//  --  PANEL: FORM  --  //
$w			= (object) $words['edit'];


$panelForm	= $view->loadTemplateFile( 'work/newsletter/group/edit.details.php' );


//  --  PANEL: EXPORT  --  //

$panelImport	= '';
if( $canExport ){
	$buttonImport	= HtmlTag::create( 'a', $iconImport.$w->buttonImport, [
		'href'			=> '#modalImportCsv',
		'class'			=> 'btn btn-small not-btn-info',
		'role'			=> 'button',
		'data-toggle'	=> 'modal',
	] );

	$modalImportCsv		= $view->loadTemplateFile( 'work/newsletter/group/modal.import.csv.php' );
	$panelImport	= '
	<div class="content-panel">
		<h3>Import</h3>
		<div class="content-panel-inner">
			<p>
				CSV-Listen lassen sich in die Liste importieren.<br/>
			</p>
			<div class="buttonbar">
				'.$buttonImport.'
			</div>
		</div>
	</div>'.$modalImportCsv;
}


//  --  PANEL: EXPORT  --  //
$panelExport	= '';
if( $canExport ){
	$buttonExport	= HtmlTag::create( 'a', $iconExport.$w->buttonExport, [
		'href'		=> './work/newsletter/group/export/'.$groupId,
		'class'		=> 'btn btn-small not-btn-info',
	] );

	if( $limiter && $limiter->denies( 'Work.Newsletter.Group:allowExport' ) )
		$buttonExport	= HtmlTag::create( 'button', $iconExport.$w->buttonExport, [
			'type'		=> 'button',
			'class'		=> 'btn btn-small not-btn-info disabled',
			'onclick'	=> 'alert("Exportieren von Kategorien ist in dieser Demo-Installation nicht möglich.")',
		] );

	$panelExport	= '
<div class="content-panel">
	<h3>Export</h3>
	<div class="content-panel-inner">
		<p>
			Die aktuelle Liste kann im CSV-Format exportiert werden.<br/>
		</p>
		<div class="buttonbar">
			'.$buttonExport.'
		</div>
	</div>
</div>';

}


//  --  PANEL: READERS  --  //

$helperReaders	= new View_Helper_Work_Newsletter_GroupReaders( $env );
$helperReaders->setGroup( $group );
$helperReaders->setReaders( $groupReaders );
$helperReaders->setWords( $words );
$panelReaders	= $helperReaders->render();

//  --  PANEL: USER GROUPS  --  //
$helperPanelGroups = new View_Helper_Manage_Group_EntityRelationEditor( $this->env );
$panelGroups	= $helperPanelGroups
	->setModule( 'Resource_Newsletter.Group' )
	->visible( $useUserGroupRelations )
	->enable( $useUserGroupRelations && $canManageGroupRelations )
	->setFrom( 'work/newsletter/group/edit/'.$groupId )
	->setEntityId( $groupId )
	->render();


extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/newsletter/group/edit/', ['words' => $words, 'group' => $group] ) );

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	<!--<a href="./work/newsletter/group" class="btn btn-mini">'.$iconCancel.$w->buttonList.'</a>-->
	'.$textAbove.'
	<div class="row-fluid">
		<div class="span6">
			'.$panelForm.'
			'.$panelGroups.'
			'.$panelImport.'
			'.$panelExport.'
		</div>
		<div class="span6">
			'.$panelReaders.'
		</div>
	</div>
</div>
'.$textBottom;
