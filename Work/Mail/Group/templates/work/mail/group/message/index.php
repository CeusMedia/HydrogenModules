<?php


$optGroup	= array( '' => '- alle -');
foreach( $groups as $group )
	$optGroup[$group->mailGroupId]	= $group->title;
$optGroup	= UI_HTML_Elements::Options( $optGroup, $filterGroupId );

$panelFilter	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Filter' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'label', 'Mail-Gruppe' ),
			UI_HTML_Tag::create( 'select', $optGroup, array(
				'name'		=> 'groupId',
				'id'		=> 'input_groupId',
				'class'		=> 'span12',
				'onchange'	=> 'this.form.submit()',
			) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'button', 'filtern', array(
					'type'		=> 'submit',
					'name'		=> 'save',
					'class'		=> 'btn btn-primary',
				) )
			), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './work/mail/group/message/filter', 'method' => 'POST' ) )
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

$list	= UI_HTML_Tag::create( 'div', 'Keine gefunden.', array( 'class' => 'alert alert-info' ) );
if( count( $messages ) ){
	$list	= array();
	foreach( $messages as $message ){
		$sender	=  $message->object->getSender();
		if( $sender->getName() ){
//			$address	= UI_HTML_Tag::create( 'small', '&lt;'.htmlentities( $sender->getAddress(), ENT_QUOTES, 'UTF-8' ).'&gt;', array( 'class' => 'not-muted' ) );
			$sender	= $sender->getName();
		}
		else{
			$sender	= htmlentities( $sender->getAddress(), ENT_QUOTES, 'UTF-8' );
		}

		$subject	= htmlentities( $message->object->getSubject(), ENT_QUOTES, 'UTF-8' );
		$link		= UI_HTML_Tag::create( 'a', $subject, array(
			'href'	=> './work/mail/group/message/view/'.$message->mailGroupMessageId,
		) );

//		print_m( $message->object );die;
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $message->mailGroupMessageId ),
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $sender ),
			UI_HTML_Tag::create( 'td', $words['message-statuses'][$message->status] ),
			UI_HTML_Tag::create( 'td', date( 'd.m.y ', $message->createdAt ).' <small>'.date( 'H:i:s', $message->createdAt ).'</small>' ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		'20px',
		'',
		'30%',
		'120px',
		'140px',
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'#',
		'Betreff',
		'Absender',
		'Zustand',
		'Datum',
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-condensed' ) );
}

$pagination	= new \CeusMedia\Bootstrap\PageControl( './work/mail/group/message', $page, $pages );

$panelList	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Mails' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
 		UI_HTML_Tag::create( 'div', $pagination, array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

$tabs	= $view->renderTabs( $env, 'message' );

return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', $panelFilter, array( 'class' => 'span3' ) ),
	UI_HTML_Tag::create( 'div', $panelList, array( 'class' => 'span9' ) )
), array( 'class' => 'row-fluid' ) );
