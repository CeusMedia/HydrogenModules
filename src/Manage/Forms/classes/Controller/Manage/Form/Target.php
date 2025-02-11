<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Form_Target extends Controller
{
	protected Model_Form_Transfer_Target $modelTarget;
	protected Model_Form_Fill_Transfer $modelTransfer;
	protected Model_Form_Transfer_Rule $modelRule;

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		$request	= $this->env->getRequest();
		if( $request->getMethod()->isPost() ){
			$data		= [
				'title'			=> $request->get( 'title' ),
				'className'		=> $request->get( 'className' ),
				'baseUrl'		=> $request->get( 'baseUrl' ),
				'apiKey'		=> $request->get( 'apiKey' ),
				'status'		=> $request->get( 'status' ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			];
			$targetId	= $this->modelTarget->add( $data );
//			$this->restart( 'edit/'.$targetId, TRUE );
			$this->restart( NULL, TRUE );
		}
	}

	/**
	 *	@param		string		$targetId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( string $targetId ): void
	{
		$request	= $this->env->getRequest();
		if( $request->getMethod()->isPost() ){
			$data		= [
				'title'			=> $request->get( 'title' ),
				'className'		=> $request->get( 'className' ),
				'baseUrl'		=> $request->get( 'baseUrl' ),
				'status'		=> $request->get( 'status' ),
				'modifiedAt'	=> time(),
			];
			if( strlen( trim( $request->get( 'apiKey' ) ) ) )
				$data['apiKey']	= $request->get( 'apiKey' );
			$this->modelTarget->edit( $targetId, $data );
//			$this->restart( 'edit/'.$targetId, TRUE );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'target', $this->modelTarget->get( $targetId ) );
		$this->addData( 'fails', $this->getLatestUnhandledFailedTransfers( $targetId ) );
	}

	/**
	 *	@return		void
	 */
	public function index(): void
	{
		/** @var Entity_Form_Transfer_Target[] $targets */
		$targets	= $this->modelTarget->getAll( [], ['title' => 'ASC'] );
		foreach( $targets as $target ){
			$target->rules		= $this->modelRule->countByIndex( 'formTransferTargetId', $target->formTransferTargetId );
			$target->transfers	= $this->modelTransfer->countByIndex( 'formTransferTargetId', $target->formTransferTargetId );
			$target->fails		= count( $this->getLatestUnhandledFailedTransfers( $target->formTransferTargetId ) );
			$target->usedAt		= $this->modelTransfer->getByIndex( 'formTransferTargetId', $target->formTransferTargetId, ['createdAt' => 'DESC'], ['createdAt'] );
		}
		$this->addData( 'targets', $targets );
	}

	/**
	 *	@param		string		$targetId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $targetId ): void
	{
		$this->modelTarget->remove( $targetId );
		$this->restart( NULL, TRUE );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->modelTarget		= new Model_Form_Transfer_Target( $this->env );
		$this->modelTransfer	= new Model_Form_Fill_Transfer( $this->env );
		$this->modelRule		= new Model_Form_Transfer_Rule( $this->env );
	}

	/**
	 *	@param		string		$targetId
	 *	@return		array<object>
	 */
	protected function getLatestUnhandledFailedTransfers( string $targetId ): array
	{
		$query	= <<<EOT
SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
SELECT
-- 	FROM_UNIXTIME(ff.createdAt) AS created,
-- 	FROM_UNIXTIME(fft_latest.timestamp) AS failed,
	ff.fillId AS fillId,
-- 	f.formId,
-- 	ff.fillId AS formFillId,
-- 	fft_latest.transferRuleId,
-- 	ftt.transferTargetId,
-- 	fft_latest.fillTransferId,
-- 	f.formStatus,
-- 	ff.status AS formfillStatus,
-- 	fft_latest.fillTransferStatus,
	f.title AS formTitle,
-- 	ff.data,
	ftr.transferRuleTitle,
	ftt.transferTargetTitle,
	fft_latest.fillTransferMessage,
	ff.createdAt,
	fft_latest.timestamp AS failedAt
FROM
	form_fills AS ff,
	(	SELECT
			fft.fillId,
			fft.formFillTransferId AS fillTransferId,
			fft.formTransferRuleId AS transferRuleId,
			MAX(fft.createdAt) AS timestamp,
			fft.status AS fillTransferStatus,
			fft.message AS fillTransferMessage
		FROM form_fill_transfers AS fft
		WHERE fft.status IN (2,3)
		GROUP BY fillId
	) AS fft_latest,
	(	SELECT
			formId,
			status AS formStatus,
			title
		FROM forms
-- 		WHERE status IN (1)
	) AS f,
	(	SELECT
			formTransferRuleId AS transferRuleId,
			formTransferTargetId AS transferTargetId,
			title as transferRuleTitle
		FROM form_transfer_rules
	) AS ftr,
	(	SELECT
			formTransferTargetId as transferTargetId,
			title AS transferTargetTitle
		FROM form_transfer_targets
	) AS ftt
WHERE
	ff.fillId = fft_latest.fillId AND
	ff.formId = f.formId AND
	fft_latest.transferRuleId = ftr.transferRuleId AND
	ftr.transferTargetId = ftt.transferTargetId AND
	ff.status = 1 AND
	ff.createdAt > UNIX_TIMESTAMP(DATE_SUB(now(), INTERVAL 4 WEEK	)) AND
	ftt.transferTargetId = $targetId
ORDER BY ff.createdAt DESC
;
EOT;
		return $this->env->getDatabase()->query( $query )->fetchAll( PDO::FETCH_OBJ );
	}
}
