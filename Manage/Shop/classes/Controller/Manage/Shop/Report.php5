<?php
class Controller_Manage_Shop_Report extends Controller_Manage_Shop{

	public function index(){

		$dbc	= $this->env->getDatabase();
		$prefix	= $dbc->getPrefix();

		$queryOrdersPerYear	= "
SELECT
	FROM_UNIXTIME(o.createdAt, '%Y') as year,
	COUNT(orderId) AS per_year
FROM ".$prefix."shop_orders AS o
WHERE FROM_UNIXTIME(o.createdAt, '%Y')>=2000
GROUP BY FROM_UNIXTIME(o.createdAt, '%Y')
ORDER BY year ASC";

		$years	= array();
		foreach( $dbc->query( $queryOrdersPerYear )->fetchAll( PDO::FETCH_OBJ ) as $row ){
			$queryYearTurnover	= "
SELECT SUM(op.quantity * a.price) AS turnover
FROM
	".$prefix."catalog_articles AS a,
	".$prefix."shop_orders AS o,
	".$prefix."shop_order_positions AS op
WHERE
	a.articleId=op.articleId AND
	op.orderId=o.orderId AND
	FROM_UNIXTIME(o.createdAt, '%Y')=".(int)$row->year." AND
	o.customerId > 0";
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
