<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Work_Issue extends AjaxController
{
	/**
	 *	@param		int		$limit
	 *	@param		int		$offset
	 *	@return		int
	 */
	public function export( int $limit = 10, int $offset = 0 ): int
	{
		$request	= $this->env->getRequest();
		$filters	= $request->get( 'filters', [] );
		if( [] === $filters )
			$filters	= [
				'type'		=> Model_Issue::TYPE_ERROR,
				'status'	=> [
					Model_Issue::STATUS_ASSIGNED,
					Model_Issue::STATUS_ACCEPTED,
					Model_Issue::STATUS_PROGRESSING,
					Model_Issue::STATUS_READY,
					Model_Issue::STATUS_REOPENED,
				],
			];

		$orders	= $request->get( 'orders' );
		if( [] === $orders )
			$orders	= [
				'priority'	=> 'ASC',
				'status'	=> 'ASC',
			];

		$modelIssue		= new Model_Issue( $this->env );
		return $this->respondData( $modelIssue->getAll( $filters, $orders, [$offset, $limit] ) );
	}

	public function renderDashboardPanel( int|string $panelId ): int
	{
		$helper	= new View_Helper_Work_Issue_Dashboard( $this->env );
		$helper->setCurrentUser( Logic_Authentication::getInstance( $this->env )->getCurrentUserId() );
		$helper->setStatuses( [
			Model_Issue::STATUS_NEW,
			Model_Issue::STATUS_ASSIGNED,
			Model_Issue::STATUS_ACCEPTED,
			Model_Issue::STATUS_PROGRESSING
		] );
		$helper->setOrders( ['type' => 'ASC', 'priority' => 'ASC'] );
//		$helper->setLimits( [0, 20] );

		$priorities		= [
			Model_Issue::PRIORITY_NECESSARY,
			Model_Issue::PRIORITY_IMPORTANT,
			Model_Issue::PRIORITY_NORMAL,
			Model_Issue::PRIORITY_DISPENSABLE,
			Model_Issue::PRIORITY_FUTILE,
		];
//		$helper->setPriorities( $priorities );
		return $this->respondData( $helper->render() );
	}

	/**
	 * @return int
	 * @throws JsonException
	 * @todo filter search input
	 */
	public function search(): int
	{
		$request	= $this->env->getRequest();
		$terms		= explode( " ", trim( $request->get( 'term' ) ) );
		$modelIssue	= new Model_Issue( $this->env );
		$issues		= [];
		$ids		= [];

		foreach( $terms as $term ){
			$filters	= ['title' => '%'.$term.'%'];
			foreach( $modelIssue->getAll( $filters ) as $issue ){
				$issues[$issue->issueId]	= $issue;
				if( empty( $ids[$issue->issueId] ) )
					$ids[$issue->issueId]	= 0;
				$ids[$issue->issueId] ++;
			}
		}
		arsort( $ids );
		$list	= [];
		foreach( $ids as $id => $number )
			if( $number == count( $terms ) )
				$list[]	= $issues[$id];
		return $this->respondData( $list );
	}
}