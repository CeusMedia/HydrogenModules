<?php
class Controller_Ajax_Work_Mission_Archive extends Controller_Ajax_Work_Mission
{
	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		JsonException
	 */
	public function renderIndex(): void
	{
		//  get list limit and page filters and sanitize them
		$limitMin	= 20;
		$limitMax	= 100;
		$limit		= (int) $this->session->get( $this->filterKeyPrefix.'limit' );
		$limit		= max( $limitMin, min( $limitMax, abs( $limit ) ) );
		$page		= (int) $this->session->get( $this->filterKeyPrefix.'page' );
		$page		= abs( $page );

		//  get all filtered user missions and count them
		$missions	= $this->logic->getFilteredUserMissions( $this->userId, $this->filterKeyPrefix );
		$total		= count( $missions );

		//  correct page if invalid and cut missions to limit and offset
		if( ( $page * $limit ) >= $total )
			$this->session->set( $this->filterKeyPrefix.'page', $page = 0 );
		$offset		= $page * $limit;
		$missions	= array_slice( $missions, $offset, $limit );

		$words		= $this->env->getLanguage()->getWords( 'work/mission' );

		if( $missions ){
			$helperButtons	= new View_Helper_Work_Mission_List_Pagination( $this->env );
			$helperList		= new View_Helper_Work_Mission_List( $this->env );

			$helperList->setMissions( $missions );
			$helperList->setWords( $words );
//			$helperList->setOrder( 0, 'dayStart', 'ASC' );
			$helperList->setBadges( TRUE, TRUE, FALSE );

			$listLarge		= $helperList->renderDayList( 2, 0, TRUE, TRUE, TRUE, FALSE );
			$buttonsLarge	= $helperButtons->render( $total, $limit, $page );
		}
		else{
			$buttonsLarge	= "";
			$listLarge		= '<div class="alert alert-warning"><em>'.$words['index']['messageNoEntries'].'</em></div>';
		}

		$data		= [
			'buttons'	=> [
				'large'	=> $buttonsLarge,
			],
			'lists'		=> [
				'large'	=> $listLarge,
			]
		];
		$this->respondData( $data );
	}
}