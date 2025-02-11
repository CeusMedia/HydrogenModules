<?php
class Job_Work_Mail_Sync extends Job_Abstract
{
	protected $logic;
	protected $statistics	= [];
	protected $hostMap;

	public function sync()
	{
		$indices	= ['status' => Model_Mail_Sync::STATUS_SYNCHING];
		$running	= $this->logic->getSyncs( $indices );
		if( $running )
			return;

		$active		= $this->logic->getSyncs(
			array( 'status' => Model_Mail_Sync::STATUS_ACTIVE ),
			array( 'modifiedAt' => 'ASC' ),
			array( 0, 1 )
		);
		$resyncs		= $this->logic->getSyncs(
			array( 'status' => Model_Mail_Sync::STATUS_SYNCHED, 'resync' => 1, 'modifiedAt' => '< '.( time() - 900 ) ),
			array( 'modifiedAt' => 'ASC' ),
			array( 0, 10 )
		);
		if( $active ){
//			$this->out( 'Running: '.count( $running ) );
//			$this->out( 'Active: '.count( $active ) );
			$active	= array_pop( $active );
			$this->out( 'Sync: '.$active->sourceUsername.' -> '.$active->targetUsername );
			$this->executeMailboxSync( $active );
		}
		else if( $resyncs ){
			foreach( $resyncs as $resync ){
				$this->out( 'Resync: '.$resync->sourceUsername.' -> '.$resync->targetUsername );
				$this->executeMailboxSync( $resync );
			}
		}
	}

	protected function __onInit(): void
	{
		$this->logic	= new Logic_Mail_Sync( $this->env );
		$this->hostMap	= [];
		foreach( $this->logic->getSyncHosts() as $host )
			$this->hostMap[$host->mailSyncHostId]	= $host;
	}

	protected function executeMailboxSync( $sync )
	{
		$parameters	= [
			'--host1 '.$this->hostMap[$sync->sourceMailHostId]->ip,
			'--host2 '.$this->hostMap[$sync->targetMailHostId]->ip,
			'--user1 '.$sync->sourceUsername,
			'--user2 '.$sync->targetUsername,
			'--password1 "'.$sync->sourcePassword.'"',
			'--password2 "'.$sync->targetPassword.'"',
		];
		if( $this->hostMap[$sync->sourceMailHostId]->ssl )
			$parameters[]	= '--ssl1';
		if( $this->hostMap[$sync->targetMailHostId]->ssl )
			$parameters[]	= '--ssl2';

//		$parameters[]	= '--authmech1 CRAM-MD5';
//		$parameters[]	= '--authmech2 CRAM-MD5';
//		$parameters[]	= '--dry';

		$command	= "imapsync ".join( ' ', $parameters );
//		$this->out( $command ); return;

		$syncRunId	= $this->logic->addSyncRun( $sync->mailSyncId );


		$this->logic->editSync( $sync->mailSyncId, [
			'status'		=> Model_Mail_Sync::STATUS_SYNCHING,
			'modifiedAt'	=> time(),
		] );
		$lastline	= exec( $command, $results, $code );
$this->out( 'Code: '.$code );

		$lines		= [];
		$status		= 0;
		foreach( $results as $line ){
			if( $status == 1 )
				$lines[]	= $line;
			else if( trim( $line ) === '++++ Statistics' )
				$status	= 1;
		}

		if( $status > 0 ){
			$statistics	= $this->readStatistics( $lines );
			$this->logic->editSyncRun( $syncRunId, [
				'status'		=> Model_Mail_Sync_Run::STATUS_SUCCESS,
				'output'		=> json_encode( $results ),
				'statistics'	=> json_encode( $statistics ),
			] );
			$this->logic->editSync( $sync->mailSyncId, [
				'status'		=> Model_Mail_Sync::STATUS_SYNCHED,
			] );
			$this->out( "DONE!" );
			foreach( $statistics as $key => $value )
				$this->out( $key.':'.$value );
		}
		else{
			$this->logic->editSyncRun( $syncRunId, [
				'status'		=> Model_Mail_Sync_Run::STATUS_FAIL,
				'message'		=> $lastline,
				'output'		=> json_encode( $results ),
				'statistics'	=> json_encode( [] ),
			] );
			$this->logic->editSync( $sync->mailSyncId, [
				'status'		=> Model_Mail_Sync::STATUS_ERROR,
			] );
			$this->out( "ERROR!" );
			$this->out( $lastline );
		}
	}

	protected function readStatistics( array $lines ): array
	{
		$list	= [];
		foreach( $lines as $line ){
			$parts	= explode( ":", $line, 2 );
			if( count( $parts ) > 1 )
				$list[trim( $parts[0] )]	= $parts[1];
		}
		return $list;
	}
}
