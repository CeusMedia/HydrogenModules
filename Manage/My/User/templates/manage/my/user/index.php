<?php
$w			= (object) $words['index'];

/* TO BE USED LATER FOR STATUS INFO
$indicator	= new UI_HTML_Indicator();
$indicator->setIndicatorClass( 'indicator-small' );
$ind1		= $indicator->build( 75, 100 );
*/

$panelInfo		= $view->loadTemplateFile( 'manage/my/user/index.info.php' );
$panelPassword	= $view->loadTemplateFile( 'manage/my/user/index.password.php' );
$panelEmail		= $view->loadTemplateFile( 'manage/my/user/index.email.php' );
$panelUsername	= $view->loadTemplateFile( 'manage/my/user/index.username.php' );
$panelEdit		= $view->loadTemplateFile( 'manage/my/user/index.edit.php' );
$panelAccount	= $view->loadTemplateFile( 'manage/my/user/index.account.php' );

$rows	= array();
foreach( $passwords as $password ){
	$rowClass	= 'info';
	if( $password->status == 0 )
		$rowClass	= 'warning';
	if( $password->status == 1 )
		$rowClass	= 'success';
	$dateCreated	= date( 'd.m.Y', $password->createdAt ).'&nbsp;<span class="muted">'.date( 'H:i', $password->createdAt ).'</small>';
	$dateUsed		= $password->usedAt ? date( 'd.m.Y', $password->usedAt ).'&nbsp;<span class="muted">'.date( 'H:i', $password->usedAt ).'</small>' : '-';
	$labelStatus	= $words['password-statuses'][$password->status];
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $labelStatus ),
		UI_HTML_Tag::create( 'td', '<small class="not-muted">'.$dateCreated.'</small>' ),
		UI_HTML_Tag::create( 'td', '<small class="not-muted">'.$dateUsed.'</small>' ),
	), array( 'class' => $rowClass ) );
}

$panelPasswords	= HTML::DivClass( 'content-panel content-panel-form', array(
	UI_HTML_Tag::create( 'h4', 'Passwörter' ),
	HTML::DivClass( 'content-panel-inner', array(
		UI_HTML_Tag::create( 'table', array(
			UI_HTML_Elements::ColumnGroup( '', '120px', '120px' ),
			UI_HTML_Tag::create( 'thead', UI_HTML_Elements::tableHeads( array(
				'Zustand',
				'erstellt',
				'zuletzt genutzt',
			) ) ),
			UI_HTML_Tag::create( 'tbody', $rows )
		), array( 'class' => 'table table-condensed table-fixed' ) )
	) ),
) );

//print_m( $passwords );die;


$tabs	= View_Manage_My_User::renderTabs( $env );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/manage/my/user/' ) );

return $textTop.$tabs.
HTML::DivClass( 'row-fluid', array(
	HTML::DivClass( 'span8', $panelAccount ),
	HTML::DivClass( 'span4', $panelInfo ),
) ).
HTML::DivClass( 'row-fluid', array(
	HTML::DivClass( 'span8', $panelEdit ),
) ).
HTML::DivClass( 'row-fluid', array(
	HTML::DivClass( 'span3', $panelPassword ),
	HTML::DivClass( 'span5', $panelPasswords ),
) ).
HTML::DivClass( 'row-fluid', array(
	HTML::DivClass( 'span8', $panelEmail ),
	HTML::DivClass( 'span4', $panelUsername ),
) ).$textBottom;
?>
