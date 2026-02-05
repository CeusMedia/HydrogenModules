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
/** @var bool $useUserGroupRelations */
/** @var bool $canManageGroupRelations */
/** @var bool $useUserGroupRelations */

const TAB_DETAILS	= 1;
const TAB_HTML		= 2;
const TAB_TEXT		= 3;
const TAB_TEST		= 4;
const TAB_SEND		= 5;
const TAB_QUEUE		= 6;
const TAB_HISTORY	= 7;
const TAB_STATS		= 8;

$currentTab		= (int) $env->getSession()->get( 'work.newsletter.content.tab' );

$disabledTabs	= match( (int) $newsletter->status ){
	Model_Newsletter::STATUS_ABORTED	=> [TAB_HTML, TAB_TEXT, TAB_TEST, TAB_SEND, TAB_QUEUE, TAB_HISTORY, TAB_STATS],
	Model_Newsletter::STATUS_NEW		=> [TAB_SEND, TAB_QUEUE, TAB_HISTORY, TAB_STATS],
	Model_Newsletter::STATUS_READY		=> [/*TAB_HTML, TAB_TEXT, TAB_SEND,*/ TAB_QUEUE, 7, TAB_STATS],
	Model_Newsletter::STATUS_SENT		=> [/*TAB_HTML, TAB_TEXT,*/ TAB_TEST, TAB_SEND],
	default								=> [],
};

if( !$env->getAcl()->has( 'work/newsletter', 'test' ) )
	$disabledTabs[]	= TAB_TEST;
if( !$env->getAcl()->has( 'work/newsletter', 'sendLetter' ) )
	$disabledTabs[]	= TAB_SEND;

$tabsContent	= $view->renderTabs( $words->tabs, 'setContentTab/'.$newsletterId.'/', $currentTab, $disabledTabs );

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

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$tabsMain		= $tabbedLinks ? $view->renderMainTabs( 'work/newsletter' ) : '';

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
	'.$textAbove.'
	'.$tabsContent.'
</div>'.$modalPreview.$textBottom;
