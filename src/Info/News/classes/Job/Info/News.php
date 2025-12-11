<?php
class Job_Info_News extends Job_Abstract
{
	protected Model_News $model;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function close(): void
	{
		$id		= (int) $this->parameters->get( 'id', 0 );
		$closedIds	= [];
		if( 0 !== $id ){
			/** @var object $entry */
			$entry	= $this->model->get( $id );
			if( NULL === $entry ){
				$this->out( 'Error: Invalid ID' );
				$this->logError( 'Error: Invalid ID' );
				return;
			}
			$this->out( 'Closing news entry: '.$entry->title );
			if( $this->model->edit( $entry->newsId, [
				'status'		=> Model_News::STATUS_OUTDATED,
				'modifiedAt'	=> time(),
			] ) )
				$closedIds[]	= $entry->newsId;
		}
		else{
			$news	= $this->model->getAllByIndices( [
				'status'	=> Model_News::STATUS_PUBLIC,
				'endsAt'	=> '< '.time(),
			] );
			/** @var object $entry */
			foreach( $news as $entry ){
				$this->out( 'Closing news entry: '.$entry->title );
				if( 0 !== $this->model->edit( $entry->newsId, [
					'status'		=> Model_News::STATUS_OUTDATED,
					'modifiedAt'	=> time(),
				] ) )
					$closedIds[]	= $entry->newsId;
			}
		}
		if( [] === $closedIds ){
			$this->setResult( Entity_Job_Result::STATUS_UNKNOWN, 0 );
			return;
		}
		$this->setResult(
			Entity_Job_Result::STATUS_SUCCESS,
			count( $closedIds ),
			['newsIds' => $closedIds ]
		);
	}

	protected function __onInit(): void
	{
		$this->model	= new Model_News( $this->env );
	}
}
