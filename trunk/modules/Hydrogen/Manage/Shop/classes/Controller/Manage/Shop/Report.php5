<?php
class Controller_Manage_Shop_Report extends Controller_Manage_Shop{

	public function index(){
		
		$dbc	= $this->env->getDatabase();
		$prefix	= $dbc->getPrefix();
		
		$queryOrdersPerYear	= "
SELECT
	FROM_UNIXTIME(o.created, '%Y') as year,
	COUNT(order_id) AS per_year
FROM ".$prefix."orders AS o
WHERE FROM_UNIXTIME(o.created, '%Y')>=2000
GROUP BY FROM_UNIXTIME(o.created, '%Y')
ORDER BY year ASC";


		$years	= array();
		foreach( $dbc->query( $queryOrdersPerYear )->fetchAll( PDO::FETCH_OBJ ) as $row ){
			$queryYearTurnover	= "
SELECT SUM(op.quantity * a.price) AS turnover
FROM
	".$prefix."articles AS a,
	".$prefix."orders AS o,
	".$prefix."orderpositions AS op
WHERE
	a.article_id=op.article_id AND
	op.order_id=o.order_id AND
	FROM_UNIXTIME(o.created, '%Y')=".(int)$row->year." AND
	o.customer_id > 0";
			$turnover	= $dbc->query( $queryYearTurnover )->fetch( PDO::FETCH_OBJ )->turnover;
			$years[]	= (object) array(
				'year'		=> $row->year,
				'orders'	=> $row->per_year,
				'turnover'	=> $turnover,
			);
		}
		$this->addData( 'ordersPerYear', $years );
	}
}
?>
