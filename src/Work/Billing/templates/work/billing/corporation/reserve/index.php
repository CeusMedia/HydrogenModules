<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var object $corporation */
/** @var object[] $reserves */

$w		= (object) $env->getLanguage()->getSection( 'work/billing', 'corporation-reserve' );

$iconPerson		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-user-o'] );
$iconCompany	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-building-o'] );

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setHeading( $w->heading );
$helper->setTransactions( $reserves );
$helper->setFilterUrl( './work/billing/corporation/reserve/filter/'.$corporation->corporationId );
$helper->setFilterPrefix( $filterSessionPrefix );
$panelTransactions	= $helper->render();

$tabs		= View_Work_Billing_Corporation::renderTabs( $env, $corporationId, 1 );
$heading	= '<h2 class="autocut"><span class="muted">'.$w->headingPrefix.'</span> '.$corporation->title.'</h2>';

return $heading.$tabs.$panelTransactions;
