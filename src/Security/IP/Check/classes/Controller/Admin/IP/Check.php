<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Controller\Web as WebController;

/**
 *	Admin controller for viewing rejected IP check logs.
 *	@category		Library
 *	@package		CeusMedia.HydrogenModules
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2026 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenModules
 */
class Controller_Admin_IP_Check extends WebController
{
	public const PAGE_SIZE	= 50;

	protected string $filterPrefix	= 'filter_admin_ip_check_';

	/**
	 *	List all rejected IP log entries with pagination and filters.
	 *
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function index(): void
	{
		$session	= $this->env->getSession()->getAll( $this->filterPrefix, TRUE );
		$model		= new Model_Log_API_Reject( $this->env );

		// Filters from session
		$filterIp		= $session->get( 'ip' );
		$filterDateFrom	= $session->get( 'dateFrom' );
		$filterDateTo	= $session->get( 'dateTo' );
		$filterScoreMin	= $session->get( 'scoreMin' );

		// Build WHERE clause
		$where	= [];
		$params	= [];
		if( !empty( $filterIp ) ){
			$where[]	= 'ip LIKE ?';
			$params[]	= '%' . $filterIp . '%';
		}
		if( !empty( $filterDateFrom ) ){
			$where[]	= 'createdAt >= ?';
			$params[]	= $filterDateFrom . ' 00:00:00';
		}
		if( !empty( $filterDateTo ) ){
			$where[]	= 'createdAt <= ?';
			$params[]	= $filterDateTo . ' 23:59:59';
		}
		if( NULL !== $filterScoreMin && '' !== $filterScoreMin ){
			$where[]	= 'score <= ?';
			$params[]	= (int) $filterScoreMin;
		}

		$whereSql	= count( $where ) > 0 ? 'WHERE ' . implode( ' AND ', $where ) : '';

		// Total count
		$totalSql	= 'SELECT COUNT(*) FROM ' . $model->getName() . ' ' . $whereSql;
		$total		= $this->env->getDatabase()->query( $totalSql, $params )->fetch( PDO::FETCH_ASSOC );
		$total		= (int) ( $total ?: 0 );

		// Pagination
		$page	= (int) $this->env->getRequest()->get( 'page', 1 );
		if( $page < 1 )
			$page	= 1;
		$offset	= ( $page - 1 ) * self::PAGE_SIZE;

		// Fetch entries
		$selectSql	= 'SELECT * FROM ' . $model->getName() . ' ' . $whereSql . 'ORDER BY createdAt DESC LIMIT ' . self::PAGE_SIZE . ' OFFSET ' . $offset;
		$entries	= $this->env->getDatabase()->query( $selectSql, $params )->fetchAll( PDO::FETCH_ASSOC );

		$this->addData( 'entries', $entries ?: [] );
		$this->addData( 'total', $total );
		$this->addData( 'page', $page );
		$this->addData( 'pageSize', self::PAGE_SIZE );
		$this->addData( 'pages', (int) ceil( $total / self::PAGE_SIZE ) );
		$this->addData( 'filterIp', $filterIp );
		$this->addData( 'filterDateFrom', $filterDateFrom );
		$this->addData( 'filterDateTo', $filterDateTo );
		$this->addData( 'filterScoreMin', $filterScoreMin );
	}

	/**
	 *	Apply or reset filters.
	 *
	 *	@return		void
	 */
	public function filter(): void
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession()->getAll( $this->filterPrefix, TRUE );
		$reset		= $request->get( 'reset', '' );
		if( '' !== $reset ){
			$session->remove( 'ip' );
			$session->remove( 'dateFrom' );
			$session->remove( 'dateTo' );
			$session->remove( 'scoreMin' );
		}
		if( $request->has( 'ip' ) )
			$session->set( 'ip', trim( $request->get( 'ip', '' ) ) );
		if( $request->has( 'dateFrom' ) )
			$session->set( 'dateFrom', trim( $request->get( 'dateFrom', '' ) ) );
		if( $request->has( 'dateTo' ) )
			$session->set( 'dateTo', trim( $request->get( 'dateTo', '' ) ) );
		if( $request->has( 'scoreMin' ) )
			$session->set( 'scoreMin', trim( $request->get( 'scoreMin', '' ) ) );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	View a single rejected IP log entry.
	 *
	 *	@param		int	$id
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function view( int $id ): void
	{
		$model	= new Model_Log_API_Reject( $this->env );
		/** @var ?Entity_Log_API_Reject $entry */
		$entry	= $model->get( $id );
		if( NULL === $entry )
			$this->restart( './admin/ip/check' );

		$this->addData( 'entry', $entry );
	}

	/**
	 *	Delete a single rejected IP log entry.
	 *
	 *	@param		int	$id
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function delete( int $id ): void
	{
		$model	= new Model_Log_API_Reject( $this->env );
		/** @var ?Entity_Log_API_Reject $entry */
		$entry	= $model->get( $id );
		if( NULL !== $entry )
			$model->remove( $entry->id );

		$this->restart( './admin/ip/check' );
	}

	/**
	 *	Delete all rejected IP log entries matching current filters.
	 *
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function deleteAll(): void
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession()->getAll( $this->filterPrefix, TRUE );
		$model		= new Model_Log_API_Reject( $this->env );

		// Build WHERE clause from filters
		$where	= [];
		$params	= [];
		$filterIp		= $session->get( 'ip' );
		$filterDateFrom	= $session->get( 'dateFrom' );
		$filterDateTo	= $session->get( 'dateTo' );
		$filterScoreMin	= $session->get( 'scoreMin' );

		if( !empty( $filterIp ) ){
			$where[]	= 'ip LIKE ?';
			$params[]	= '%' . $filterIp . '%';
		}
		if( !empty( $filterDateFrom ) ){
			$where[]	= 'createdAt >= ?';
			$params[]	= $filterDateFrom . ' 00:00:00';
		}
		if( !empty( $filterDateTo ) ){
			$where[]	= 'createdAt <= ?';
			$params[]	= $filterDateTo . ' 23:59:59';
		}
		if( NULL !== $filterScoreMin && '' !== $filterScoreMin ){
			$where[]	= 'score <= ?';
			$params[]	= (int) $filterScoreMin;
		}

		if( empty( $where ) ){
			// No filters — delete everything
			$sql	= 'DELETE FROM ' . $model->getName();
			$this->env->getDatabase()->exec( $sql );
		}
		else {
			$sql	= 'DELETE FROM ' . $model->getName() . ' WHERE ' . implode( ' AND ', $where );
			$this->env->getDatabase()->exec( $sql, $params );
		}

		// Clear filters
		$session->remove( 'ip' );
		$session->remove( 'dateFrom' );
		$session->remove( 'dateTo' );
		$session->remove( 'scoreMin' );

		$this->restart( './admin/ip/check' );
	}

	/**
	 *	Export rejected IP log entries as CSV or JSON.
	 *
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function export(): void
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession()->getAll( $this->filterPrefix, TRUE );
		$model		= new Model_Log_API_Reject( $this->env );

		// Build WHERE clause from filters
		$where	= [];
		$params	= [];
		$filterIp		= $session->get( 'ip' );
		$filterDateFrom	= $session->get( 'dateFrom' );
		$filterDateTo	= $session->get( 'dateTo' );
		$filterScoreMin	= $session->get( 'scoreMin' );

		if( !empty( $filterIp ) ){
			$where[]	= 'ip LIKE ?';
			$params[]	= '%' . $filterIp . '%';
		}
		if( !empty( $filterDateFrom ) ){
			$where[]	= 'createdAt >= ?';
			$params[]	= $filterDateFrom . ' 00:00:00';
		}
		if( !empty( $filterDateTo ) ){
			$where[]	= 'createdAt <= ?';
			$params[]	= $filterDateTo . ' 23:59:59';
		}
		if( NULL !== $filterScoreMin && '' !== $filterScoreMin ){
			$where[]	= 'score <= ?';
			$params[]	= (int) $filterScoreMin;
		}

		$whereSql	= count( $where ) > 0 ? 'WHERE ' . implode( ' AND ', $where ) : '';
		$selectSql	= 'SELECT * FROM ' . $model->getName() . ' ' . $whereSql . 'ORDER BY createdAt DESC';
		$entries	= $this->env->getDatabase()->query( $selectSql, $params )->fetchAll( PDO::FETCH_ASSOC );

		$format	= $request->get( 'format', 'csv' );

		if( 'json' === $format ){
			header( 'Content-Type: application/json' );
			header( 'Content-Disposition: attachment; filename="ipcheck_rejected.json"' );
			echo json_encode( $entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		}
		else {
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="ipcheck_rejected.csv"' );
			$handle	= fopen( 'php://output', 'w' );
			fputcsv( $handle, [ 'id', 'createdAt', 'ip', 'ipVersion', 'score', 'reasons', 'requestMethod', 'requestUri', 'requestHeaders', 'postData' ] );
			foreach( $entries as $entry ){
				fputcsv( $handle, [
					$entry->id ?? '',
					$entry->createdAt ?? '',
					$entry->ip ?? '',
					$entry->ipVersion ?? '',
					$entry->score ?? '',
					$entry->reasons ?? '',
					$entry->requestMethod ?? '',
					$entry->requestUri ?? '',
					$entry->requestHeaders ?? '',
					$entry->postData ?? '',
				] );
			}
			fclose( $handle );
		}
		exit;
	}

	/**
	 *	Delete old entries based on retention days config.
	 *
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function cleanup(): void
	{
		$model	= new Model_Log_API_Reject( $this->env );

		$retentionDays	= (int) $this->env->getConfig()->get( 'module.Security_IP_Check.log.retention.days', 90 );
		if( $retentionDays < 1 )
			$retentionDays	= 90;

		$cutoff	= date( 'Y-m-d H:i:s', strtotime( '-' . $retentionDays . ' days' ) );
		$sql	= 'DELETE FROM ' . $model->getName() . ' WHERE createdAt < ?';
		$this->env->getDatabase()->exec( $sql, [ $cutoff ] );

		$this->restart( './admin/ip/check' );
	}
}
