<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var object $person */
/** @var object[] $reserves */

$w		= (object) $env->getLanguage()->getSection( 'work/billing', 'person-reserve' );

//$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list-alt'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setHeading( $w->heading );
$helper->setTransactions( $reserves );
$helper->setFilterUrl( './work/billing/person/reserve/filter/'.$person->personId );
$helper->setFilterPrefix( $filterSessionPrefix );
$panelTransactions	= $helper->render();

$tabs		= View_Work_Billing_Person::renderTabs( $env, $person->personId, 1 );
$heading	= '<h2 class="autocut"><span class="muted">'.$w->headingPrefix.'</span> '.$person->firstname.' '.$person->surname.'</h2>';

return $heading.$tabs.'
<div class="row-fluid">
	<div class="span12">
		'.$panelTransactions.'
	</div>
</div>';
