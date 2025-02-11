<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var bool $tabbedLinks */
/** @var object $newsletter */
/** @var string $newsletterId */
/** @var View_Work_Newsletter $view */

$tabsMain		= $tabbedLinks ? $view->renderMainTabs() : '';

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';

$pathDefaults	= 'html/work/newsletter/';

$currentTab		= (int) $this->env->getSession()->get( 'work.newsletter.content.tab' );
$tabs			= $words->tabs;

$disabled		= "";
if( (int) $newsletter->status === Model_Newsletter::STATUS_ABORTED ){
	$disabledTabs	= [2, 3, 4, 5, 6, 7, 8];
	$disabled		= 'disabled="disabled"';
}
else if( (int) $newsletter->status === Model_Newsletter::STATUS_NEW ){
	$disabledTabs	= [5, 6, 7, 8];
//	$disabled		= 'disabled="disabled"';
}
else if( (int) $newsletter->status === Model_Newsletter::STATUS_READY ){
	$disabledTabs	= [/*2, 3, 5,*/ 6, 7, 8];
//	$disabled		= 'disabled="disabled"';
}
else if( (int) $newsletter->status == Model_Newsletter::STATUS_SENT ){
	$disabledTabs	= [/*2, 3,*/ 4];
	$disabled		= 'disabled="disabled"';
}
$tabsContent	= $view->renderTabs( $tabs, 'setContentTab/'.$newsletterId.'/', $currentTab, $disabledTabs );

$listSents	= '<em><small class="muted">Keine.</small></em>';

$tabTemplates	= [
	0	=> 'details',
	1	=> 'html',
	2	=> 'text',
	3	=> 'test',
	4	=> 'sender',
	5	=> 'queue',
	6	=> 'history',
	7	=> 'statistics',
];
$content	= "Invalid tab: ".$currentTab;
if( array_key_exists( $currentTab, $tabTemplates ) )
	$content	= $view->loadTemplate( 'work/newsletter', 'edit.'.$tabTemplates[$currentTab] );

/*
switch( $currentTab ){
	case 0:
		$content	= $view->loadTemplate( 'work/newsletter', 'edit.details' );
		break;
	case 1:
		$content	= $view->loadTemplate( 'work/newsletter', 'edit.html' );
		break;
	case 2:
		$content	= $view->loadTemplate( 'work/newsletter', 'edit.text' );
		break;
	case 3:
		$content	= $view->loadTemplate( 'work/newsletter', 'edit.test' );
		break;
	case 4:
		$content	= $view->loadTemplate( 'work/newsletter', 'edit.sender' );
		break;
	case 5:
		$content	= $view->loadTemplate( 'work/newsletter', 'edit.queue' );
		break;
	case 6:
		$content	= $view->loadTemplate( 'work/newsletter', 'edit.history' );
		break;
	case 7:
		$content	= $view->loadTemplate( 'work/newsletter', 'edit.statistics' );
		break;
	default:
		$content	= "Invalid tab: ".$currentTab;
		break;
}
*/
$tabsContent	.= HtmlTag::create( 'div', $content, ['tab-content'] );

$modalPreview	= '
<div id="modal-preview" class="modal hide -fade preview">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>'.sprintf( $words->preview['heading'], $newsletter->title ).'</h3>
	</div>
	<div class="modal-body">
		<iframe></iframe>
	</div>
	<div class="modal-footer">
<!--		<button type="button" class="btn btn-info" id="preview-refresh"><i class="icon-refresh icon-white"></i> aktualisieren</button>-->
<!--		<a type="button" class="btn btn-small btn-warning" href="./work/newsletter/preview/html/'.$newsletterId.'/1/1" target="_blank"><small>Offline-Modus simulieren</small></a>-->
		<button type="button" class="btn" data-dismiss="modal" aria-hidden="true"><i class="icon-remove"></i> schließen</button>
	</div>
</div>';

$helperNav	= View_Helper_Pagination_PrevNext::create( $env )
	->setModelClass( 'Model_Newsletter' )
	->setCurrentId( $newsletter->newsletterId )
	->setUrlTemplate( './work/newsletter/edit/%d' )
	->useIndex()->setIndexUrl( './work/newsletter' );
$navPrevNext	= HtmlTag::create( 'div', $helperNav->render(), ['class' => 'pull-right'] );

extract( $view->populateTexts(
	array( 'above', 'bottom', 'top' ),
	'html/work/newsletter/edit/',
	array( 'heading' => $words->edit->heading.$navPrevNext, 'title' => $newsletter->title )
) );

return $textTop.'
<script>
$(document).ready(function(){
//	ModuleWorkNewsletter.init("'.$env->url.'", '.$newsletter->newsletterTemplateId.', 0);
});
</script>
<div class="newsletter-content">
	'.$tabsMain.'
<!--	<a href="./work/newsletter" class="btn btn-mini">'.$iconCancel.$words->edit->buttonList.'</a>-->
<!--	<h3><span class="muted">'.$words->edit->heading.':</span> '.$newsletter->title.'</h3>-->
	<hr/>
	'.$textAbove.'
	<hr/>
	'.$tabsContent.'
</div>'.$modalPreview.$textBottom;
