<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Controller_Ajax_Work_Mission_Kanban extends Controller_Ajax_Work_Mission
{
	protected ?DateTime $today	= NULL;
	public function renderIndex(): void
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->today	= new DateTime( date( 'Y-m-d', time() - $this->logic->timeOffset ) );

		$script	= '<script>
$(document).ready(function(){
	WorkMissionsKanban.userId = '.(int) $this->userId.';
/*	if(typeof cmContextMenu !== "undefined"){
		WorkMissionsKanban.initContextMenu();
	};*/
});
	WorkMissionsKanban.initBlockMovability();
</script>';
//		$this->env->getPage()->addHead( $script );

		$data			= [
			'total'		=> 1,
			/*			'buttons'	=> array(
							'large'	=> $this->renderControls( $year, $month ),
							'small'	=> $this->renderControls( $year, $month ),
						),*/
			'lists' => [
				'large'	=> $this->renderLarge( $this->userId ).$script,
				'small'	=> $this->renderSmall( $this->userId ),
			],
			'filters'	=> $this->env->getSession()->getAll( 'filter.work.mission.kanban.' ),
		];
		$this->respondData( $data );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setMissionStatus(): void
	{
		$missionId	= $this->request->get( 'missionId' );
		$status		= (int) $this->request->get( 'status' );

		try{
			if( !$missionId )
				throw new InvalidArgumentException( 'Mission ID is missing' );
			if( !in_array( $status, [0, 1, 2, 3] ) )
				throw new InvalidArgumentException( 'Invalid status given' );
			$mission	= $this->model->get( $missionId );
			if( !$mission )
				throw new InvalidArgumentException( 'Invalid mission ID given' );
			$responseStatus	= FALSE;
			if( $mission->status != $status ){
				$data	= [
					'status'		=> $status,
					'modifiedAt'	=> time(),
				];
//				if( $status === 1 )
//					$data['workerId']	= $this->userId;
				$this->model->edit( $missionId, $data );
				$this->logic->noteChange( 'update', $missionId, $mission, $this->userId );
				$responseStatus		= TRUE;
				$mission	= $this->model->get( $missionId );
			}
			$this->respondData( [
				'status'	=> $responseStatus,
				'item'		=> $mission,
			] );
		}
		catch( Exception $e ){
			header( "HTTP/1.1 400 OK" );
			print( json_encode( $e->getMessage() ) );
		}
		exit;
	}

	//  --  PROTECTED  --  //

	protected function renderControls( $year, $month ): string
	{
		$btnExport		= HtmlTag::create( 'a', '<i class="icon-calendar icon-white"></i> iCal-Export', [
			'href'		=> './work/mission/export/ical',
			'target'	=> '_blank',
			'class'		=> 'btn not-btn-small btn-warning',
			'style'		=> 'font-weight: normal',
		] );
		return '
	<div id="mission-calendar-control" class="row-fluid">
		<div class="span4" style="text-align: right">
			'.$btnExport.'
<!--			<a href="./work/mission/export/ical" target="_blank" class="btn not-btn-small" style="font-weight: normal"><i class="icon-calendar"></i> iCal-Export</a>-->
		</div>
	</div>';
	}

	public function renderLarge( $userId ): string
	{
		$statuses	= [
			'0'		=> 'Neu',
			'1'		=> 'Angenommen',
			'2'		=> 'In Arbeit',
			'3'		=> 'Abnahmebereit',
		];
		$this->projects	= $this->logic->getUserProjects( $userId );
		$filters		= $this->env->getSession()->getAll( 'filter.work.mission.kanban.', TRUE );
		if( $filters->has( 'projects' ) )
			foreach( $this->projects as $projectId => $project )
				if( !in_array( $projectId, $filters->get( 'projects' ) ) )
					unset( $this->projects[$projectId] );

		$lanes		= [];
		foreach( $this->projects as $projectId => $project ){
			$missionCount	= 0;
			$columns		= [];
			foreach( $statuses as $status => $statusLabel ){
				$conditions	= $this->logic->getFilterConditions( 'filter.work.mission.kanban.' );
				$conditions['status']		= $status;
				$conditions['projectId']	= $projectId;

				$missions	= $this->logic->getUserMissions( $userId, $conditions, ['priority' => 'ASC'] );
				$rows	= [];
				foreach( $missions as $mission ){
					$missionCount++;
					$buttonView	= HtmlTag::create( 'a', '<i class="fa fa-eye"></i>&nbsp;<span class="hidden-tablet">anzeigen</span>', [
						'href'	=> './work/mission/view/'.$mission->missionId,
						'class'	=> 'btn btn-small',
						'alt'	=> 'anzeigen',
						'title'	=> 'anzeigen',
					] );
					$buttonEdit	= HtmlTag::create( 'a', '<i class="fa fa-pencil"></i>&nbsp;<span class="hidden-tablet">bearbeiten</span>', [
						'href'	=> './work/mission/edit/'.$mission->missionId,
						'class'	=> 'btn btn-small',
						'alt'	=> 'bearbeiten',
						'title'	=> 'bearbeiten',
					] );
					$cells	= [];
					$cells[]	= HtmlTag::create( 'div', $mission->title, ['class' => 'mission-title'] );
					if( $mission->workerId ){
						$worker	= '<del class="muted">entfernt</del>';
						if( isset( $this->userMap[$mission->workerId] ) )
							$worker	= $this->userMap[$mission->workerId]->username;
						$label		= HtmlTag::create( 'small', 'Bearbeiter: '.$worker );
						$cells[]	= HtmlTag::create( 'div', $label );
					}
					if( $mission->projectId ){
						$projectLabel	= '<del class="muted">entfernt</del>';
						if( isset( $this->projects[$mission->projectId] ) )
							$projectLabel	= $this->projects[$mission->projectId]->title;
						$label		= HtmlTag::create( 'small', 'Project: '.$projectLabel );
						$cells[]	= HtmlTag::create( 'div', $label );
					}
					$cells[]	= HtmlTag::create( 'div', $buttonView.$buttonEdit, ['class' => 'btn-group'] );
					$rows[]	= HtmlTag::create( 'li', $cells, ['class' => 'mission-block priority-'.$mission->priority, 'data-id' => $mission->missionId] );
				}
				$columns[]	= HtmlTag::create( 'div', [
					HtmlTag::create( 'h4', $statusLabel, ['class' => ''] ),
					HtmlTag::create( 'ul', $rows, ['class' => 'sortable unstyled equalize-auto', 'id' => 'sortable-status-'.$status] ),
				], ['class' => 'span3'] );
			}
			if( !$missionCount )
				continue;

			$laneLabel	= HtmlTag::create( 'h3', '<span class="muted">Projekt:</span>&nbsp;'.$project->title );
			$columns	= HtmlTag::create( 'div', $columns, ['class' => 'row-fluid'] );
			$lanes[]	= HtmlTag::create( 'div', [$laneLabel, $columns], [
				'class'		=> 'row-fluid work-mission-kanban-lane-item',
				'id'		=> 'kanban-lane-'.$projectId.'-'.$status,
			] );
		}

		return HtmlTag::create( 'div', $lanes, ['class' => 'work-mission-kanban-lane-list'] );
	}

	protected function renderSmall( int|string $userId ): string
	{
		return 'kanban';
	}
}
