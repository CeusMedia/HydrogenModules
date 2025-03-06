<?php
declare(strict_types=1);

use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

class Job_Server_IP_Lock extends Job_Abstract
{
	protected Logic_IP_Lock $logic;

	/**
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function unlock(): void
	{
		$nr1	= $this->unlockOverdueLocks();
		$nr2	= $this->unlockDisabledReasonLocks();
		$this->results	= [
			'total'						=> $nr1 + $nr2,
			'unlockOverdueLocks'		=> $nr1,
			'unlockDisabledReasonLocks'	=> $nr2,
		];
	}

	/**
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 *	@throws		DateMalformedIntervalStringException
	 *	@throws		DateInvalidOperationException
	 */
	public function clean(): void
	{
		$age		= $this->parameters->get( '--age', '1M' );
		$threshold	= date_create()->sub( new DateInterval( 'P'.$age ) );

		$locks		= $this->logic->getAll( [
			'status'		=> Model_IP_Lock::STATUS_UNLOCKED,
			'unlockedAt'	=> '< '.$threshold->format( 'U' ),
		] );
		foreach( $locks as $lock )
			$this->logic->remove( $lock );
		$this->results	= [
			'total'	=> count( $locks )
		];
	}
	protected function __onInit(): void
	{
		$this->logic		= new Logic_IP_Lock( $this->env );
	}

	/**
	 *	@return		int
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	protected function unlockOverdueLocks(): int
	{
		$locks	= $this->logic->getAll( [
			'status'		=> Model_IP_Lock::STATUS_LOCKED,
			'unlockedAt'	=> '<= '.time()
		] );
		foreach( $locks as $lock )
			$this->logic->unlock( $lock );
		return count( $locks );
	}

	/**
	 *	@return		int
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	protected function unlockDisabledReasonLocks(): int
	{
		$count		= 0;
		$reasons	= $this->logic->getReasons( ['status' => Model_IP_Lock_Reason::STATUS_DISABLED ] );
		foreach( $reasons as $reason ){
			$locks		= $this->logic->getAll( [
				'status'		=> Model_IP_Lock::STATUS_LOCKED,
				'reasonId'		=> $reason->ipLockReasonId,
			] );
			$count	+= count( $locks );
			foreach( $locks as $lock )
				$this->logic->unlock( $lock );
		}
		return $count;
	}
}