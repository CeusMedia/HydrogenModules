<?php
if( !$useTimer )
	return '';

$helperAdd		= new View_Helper_Work_Time_Modal_Add( $env );
$helperAdd->setModule( 'Work_Missions' );
$helperAdd->setModuleId( $mission->missionId );
$helperAdd->setProjectId( $mission->projectId );

$helperShortList	= new View_Helper_Work_Time_ShortList( $env );
$helperShortList->setStatus( array( 0, 1, 2, 3 ) );
$helperShortList->setButtons( array( 'edit', 'start', 'pause', 'stop' ) );
$helperShortList->setModule( 'Work_Missions' );
$helperShortList->setModuleId( $mission->missionId );
$helperShortList->setProjectId( $mission->projectId );
$list				= $helperShortList->render();
if( !$list )
	$list	= UI_HTML_Tag::create( 'div', 'Noch keine.', array( 'class' => 'alert alert-notice' ) );

$helperTimer	= new View_Helper_Work_Time_Timer( $env );
$helperTimer->setModule( 'Work_Missions' );
$helperTimer->setModuleId( $mission->missionId );

$buttonNew	= UI_HTML_Tag::create( 'button', UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) ).'&nbsp;neue Aktivitität', array(
	'type'		=> 'button',
	'onclick'	=> '$("#myModalWorkTimeAdd").modal("toggle");',
	'class'		=> 'btn btn-small btn-success',
) );

return '
<div class="content-panel content-panel-form">
	<h3>Zeiterfassung</h4>
	<div class="content-panel-inner">
		'.$list.'
<!--		'.$helperTimer->render().'-->
		<div class="buttonbar">
			'.$buttonNew.'
		</div>
	</div>
</div>'.$helperAdd->render();
?>
