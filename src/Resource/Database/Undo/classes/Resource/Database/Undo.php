<?php

use CeusMedia\Database\PDO\Connection as DatabasePdoConnection;
use CeusMedia\Database\PDO\Table\Reader as TableReader;
use CeusMedia\Database\PDO\Table\Writer as TableWriter;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Model;

class Resource_Database_Undo
{
	protected Environment $env;
	protected Model_Undo_Log $storage;
	protected ?string $userId	= NULL;

	/**
	 *	@param		Environment		$env
	 *	@param		string|NULL		$userId
	 *	@throws		ReflectionException
	 */
	public function __construct( Environment $env, ?string $userId = NULL )
	{
		$this->env		= $env;
		$this->storage	= new Model_Undo_Log( $env );
		if( $userId )
			$this->setUserId( $userId );
	}

	public function getAllChanges( ?string $tableName = NULL, $maxAge = 0 ): array
	{
		$conditions	= ['userId'	=> $this->userId];
		if( $tableName )
			$conditions['tableName']	= $tableName;
		if( $maxAge > 0 )
			$conditions['timestamp']	= '<= '.( time() - $maxAge );
		return $this->storage->getAll( $conditions );
	}

	public function getLatestChangeOfModel( Model $model, $maxAge = 0 )
	{
		return $this->getLatestChangeOfTable( $model->getName(), $maxAge );
	}

	public function getLatestChangeOfTable( $tableName, $maxAge = 0 )
	{
		$conditions	= ['userId' => $this->userId, 'tableName' => $tableName];
		if( $maxAge > 0 )
			$conditions['timestamp']	= '<= '.( time() - $maxAge );
		$orders		= ['timestamp' => 'DESC'];
		$limits		= [0, 1];
		$actions	= $this->storage->getAll( $conditions, $orders, $limits );
		if( $actions )
			return $actions[0];
		return NULL;
	}

	protected function noteByIds( Model $model, $ids, $mode )
	{

	}

	protected function note( Model $model, $conditions, $mode ): bool
	{
		$tableName			= $model->getName();
		$tableColumns		= $model->getColumns();
		$tablePrimaryKey	= $model->getPrimaryKey();

		$data	= [
			'userId'		=> $this->userId,
			'mode'			=> $mode,
			'tableName'		=> $tableName,
			'primaryKey'	=> $tablePrimaryKey,
			'values'		=> [],
			'timestamp'		=> time(),
		];

		//  old solution
/*		$cond	= [];
		foreach( $conditions as $key => $value )
			$cond[]	= $key."='".$value."'";
		$conditions	= join( " AND ", $cond );
		$query	= 'SELECT * FROM '.$tableName.' WHERE '.$conditions;
		$dbc	= $this->env->getDatabase();
		$rows	= $dbc->query( $query )->fetchAll( PDO::FETCH_ASSOC );*/

		//  new solution @ 2026-06 (untested, use module Test_Undo from protected Ceus Media library)
/*		$modelClone	= clone( $model );
		$rows		= $modelClone->setFetchMode( PDO::FETCH_ASSOC )->getAll( $conditions );*/

		//  even newer solution @ 2026-06 (untested, use module Test_Undo from protected Ceus Media library)
		/** @var DatabasePdoConnection $dbc */;
		$dbc	= $this->env->getDatabase();
		$reader	= new TableReader( $dbc, $tableName, $tableColumns, $tablePrimaryKey );
		$rows	= $reader->setFetchMode( PDO::FETCH_ASSOC )->find( $conditions );
		if( !$rows )
			throw new RuntimeException( 'No data found by given conditions' );

		$data['values']		= json_encode( $rows );
		$this->storage->add( $data );
		return TRUE;
	}

	public function noteDelete( Model $model, $conditions ): bool
	{
		return $this->note( $model, $conditions, Model_Undo_Log::MODE_DELETE );
	}

	public function noteInsert( Model $model, $id ): bool
	{
		$primaryKey	= $model->getPrimaryKey();
		$conditions	= [$primaryKey => $id];
		return $this->note( $model, $conditions, Model_Undo_Log::MODE_INSERT );
	}

	public function noteUpdate( Model $model, $conditions ): bool
	{
		return $this->note( $model, $conditions, Model_Undo_Log::MODE_UPDATE );
	}

	public function isLatestChangeOnTable( $changeId, $tableName ): bool
	{
		$change	= $this->getLatestChangeOfTable( $tableName );
		return $change->changeId == $changeId;
	}

	/**
	 *	@param		string		$changeId
	 *	@return		void
	 */
	public function revert( string $changeId ): void
	{
		$indices	= ['changeId' => $changeId, 'userId' => $this->userId];
		$action	= $this->storage->getByIndices( $indices );
		if( !$action )
			throw new InvalidArgumentException( 'Invalid change ID' );
		if( !$this->isLatestChangeOnTable( $changeId, $action->tableName ) )
			throw new RuntimeException( 'Given change ID is not the lastest change' );

		/** @var DatabasePdoConnection $dbc */;
		$dbc		= $this->env->getDatabase();
		try{
			$values		= json_decode( $action->values, TRUE );
			if( !$values )
				throw new Exception( 'No values stored for change' );

			$tableName	= $action->tableName;
			$primaryKey	= $action->primaryKey;
			$columns	= array_keys( $values[0] );
			$dbc->beginTransaction();
			$table		= new TableWriter( $dbc, $tableName, $columns, $primaryKey );
			switch( $action->mode ){
				case Model_Undo_Log::MODE_DELETE:
					foreach( $values as $data )
						$table->insert( $data );
					$this->storage->remove( $changeId );
					break;
				case Model_Undo_Log::MODE_INSERT:
					$id		= $values[0][$primaryKey];
					$table->deleteByConditions( [$primaryKey => $id] );
					$this->storage->remove( $changeId );
					break;
				case Model_Undo_Log::MODE_UPDATE:
					foreach( $values as $data ){
						$id	= $data[$primaryKey];
						unset( $data[$primaryKey] );
						$table->updateByConditions( $data, [$primaryKey => $id] );
					}
					$this->storage->remove( $changeId );
					break;
			}
			$dbc->commit();
		}
		catch( Exception $e ){
			$dbc->rollBack();
			throw new RuntimeException( 'Revert failed ('.$e->getMessage().')', 0, $e );
		}
	}

	public function setUserId( ?string $userId ): self
	{
		$this->userId	= $userId;
		return $this;
	}
}
