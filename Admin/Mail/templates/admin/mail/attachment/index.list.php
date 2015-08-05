<?php
$iconEnable		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok-circle icon-white' ) );
$iconDisable	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ban-circle icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$w		= (object) $words['index'];
$list	= '<div><em class="muted">'.$w->noEntries.'</em></div><br/>';
if( count( $attachments ) ){
	$list	= array();
	foreach( $attachments as $attachment ){
		$buttonStatus	= UI_HTML_Tag::create( 'a', $iconEnable, array(
			'href'	=> './admin/mail/attachment/setStatus/'.$attachment->mailAttachmentId.'/1',
			'class'	=> 'btn btn-success not-btn-small',
			'title'	=> $w->buttonActivate
		) );
		if( $attachment->status )
			$buttonStatus	= UI_HTML_Tag::create( 'a', $iconDisable, array(
				'href'	=> './admin/mail/attachment/setStatus/'.$attachment->mailAttachmentId.'/0',
				'class'	=> 'btn btn-warning not-btn-small',
				'title'	=> $w->buttonDeactivate
			) );
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> './admin/mail/attachment/unregister/'.$attachment->mailAttachmentId,
			'class'	=> 'btn btn-danger not-btn-small',
			'title'	=> $w->buttonUnregister
		) );

		$language	= UI_HTML_Tag::create( 'span', $attachment->language, array( 'class' => 'label' ) );
		$mimeType	= UI_HTML_Tag::create( 'small', $w->labelMimeType.' '.$attachment->mimeType, array( 'class' => 'muted' ) );
		$label		= $language.' '.UI_HTML_Tag::create( 'big', $attachment->filename ).'<br/>'.$mimeType;
		$status		= (object) array (
			'label'		=> $words['states'][(int) $attachment->status],
			'icon'		=> $attachment->status > 0 ? $iconEnable : $iconDisable,
			'class'		=> $attachment->status > 0 ? 'label label-success' : 'label label-warning',
		);
		$status		= UI_HTML_Tag::create( 'span', $status->icon.' '.$status->label,  array( 'class' => $status->class ) );
		$date		= date( "d.m.Y", $attachment->createdAt );
		$time		= date( "H:i", $attachment->createdAt );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $label ),
			UI_HTML_Tag::create( 'td', $attachment->className.'<br/>'.$status ),
			UI_HTML_Tag::create( 'td', $date.' <small class="muted">'.$time.'</small>' ),
			UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'div', array( $buttonStatus, $buttonRemove ), array( 'class' => 'btn-group' )) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "", "", "140px", "80px" );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		$w->headFile,
		$w->headClass,
		$w->headCreatedAt,
		$w->headActions
	) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

return '
<!-- templates/admin/mail/attachment/index.list.php -->
<div class="content-panel content-panel-list">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
?>
