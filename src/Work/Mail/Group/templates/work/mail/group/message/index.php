<?php

use CeusMedia\Bootstrap\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var object[] $groups */
/** @var array $words */

$optGroup	= ['' => '- alle -'];
foreach( $groups as $group )
	$optGroup[$group->mailGroupId]	= $group->title;
$optGroup	= HtmlElements::Options( $optGroup, $filterGroupId );

$panelFilter	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Filter' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'label', 'Mail-Gruppe' ),
			HtmlTag::create( 'select', $optGroup, [
				'name'		=> 'groupId',
				'id'		=> 'input_groupId',
				'class'		=> 'span12',
				'onchange'	=> 'this.form.submit()',
			] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'button', 'filtern', [
					'type'		=> 'submit',
					'name'		=> 'save',
					'class'		=> 'btn btn-primary',
				] )
			], ['class' => 'buttonbar'] ),
		], ['action' => './work/mail/group/message/filter', 'method' => 'POST'] )
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

$list	= HtmlTag::create( 'div', 'Keine gefunden.', ['class' => 'alert alert-info'] );
if( count( $messages ) ){
	$list	= [];
	foreach( $messages as $message ){
		$sender	=  $message->object->getSender();
		if( $sender->getName() ){
//			$address	= HtmlTag::create( 'small', '&lt;'.htmlentities( $sender->getAddress(), ENT_QUOTES, 'UTF-8' ).'&gt;', ['class' => 'not-muted'] );
			$sender	= $sender->getName();
		}
		else{
			$sender	= htmlentities( $sender->getAddress(), ENT_QUOTES, 'UTF-8' );
		}

		$subject	= htmlentities( $message->object->getSubject(), ENT_QUOTES, 'UTF-8' );
		$link		= HtmlTag::create( 'a', $subject, [
			'href'	=> './work/mail/group/message/view/'.$message->mailGroupMessageId,
		] );

//		print_m( $message->object );die;
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $message->mailGroupMessageId ),
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $sender ),
			HtmlTag::create( 'td', $words['message-statuses'][$message->status] ),
			HtmlTag::create( 'td', date( 'd.m.y ', $message->createdAt ).' <small>'.date( 'H:i:s', $message->createdAt ).'</small>' ),
		] );
	}
	$colgroup	= HtmlElements::ColumnGroup( [
		'20px',
		'',
		'30%',
		'120px',
		'140px',
	] );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( [
		'#',
		'Betreff',
		'Absender',
		'Zustand',
		'Datum',
	] ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-condensed'] );
}

$pagination	= new PageControl( './work/mail/group/message', $page, $pages );

$panelList	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Mails' ),
	HtmlTag::create( 'div', [
		$list,
		HtmlTag::create( 'div', $pagination, ['class' => 'buttonbar'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

$tabs	= $view->renderTabs( $env, 'message' );

return $tabs.HtmlTag::create( 'div', [
	HtmlTag::create( 'div', $panelFilter, ['class' => 'span3'] ),
	HtmlTag::create( 'div', $panelList, ['class' => 'span9'] )
], ['class' => 'row-fluid'] );
