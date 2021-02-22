<?php
class Model_Event extends CMF_Hydrogen_Model
{
	const STATUS_INACTIVE	= -2;
	const STATUS_REJECTED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_CHANGED	= 1;
	const STATUS_ACTIVE		= 2;

	protected $radiusEarth  = 6371;

	protected $name			= 'events';

	protected $columns		= array(
		'eventId',
		'addressId',
		'status',
		'dateStart',
		'dateEnd',
		'timeStart',
		'timeEnd',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'eventId';

	protected $indices		= array(
		'addressId',
		'status',
		'dateStart',
		'dateEnd',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;

	public function getAllWithinTimeAndSpaceRanges( SpaceRange $spaceRange, TimeRange $timeRange ): array
	{
		$coords	= $spaceRange->getCoords();
		$query	= "
SELECT e.*, a.country, a.postcode, a.city, a.street, a.number, a.latitude, a.longitude, a.x, a.y, a.z
FROM
	events as e,
	addresses as a
WHERE e.addressId=a.addressId
AND dateStart >= '".$timeRange->getDateStart()."'
AND dateEnd <= '".$timeRange->getDateEnd()."'
AND POWER(".$coords->x." - a.x, 2) + POWER(".$coords->y." - a.y, 2) + POWER(".$coords->z." - a.z, 2) <= ".pow( $spaceRange->getDistance(), 2 )."
ORDER BY dateStart ASC, timeStart ASC
";
//xmp( $query );die;
		$list	= array();
		foreach( $this->env->dbc->query( $query )->fetchAll( PDO::FETCH_OBJ ) as $event ){
			$powX	= pow( $coords->x - $event->x, 2);
			$powY	= pow( $coords->y - $event->y, 2);
			$powZ	= pow( $coords->z - $event->z, 2);
			$event->distance	= sqrt( $powX + $powY + $powZ );
			$list[]	= $event;
		}
		return $list;
	}
}


class TimeRange
{
	protected $dateStart;
	protected $dateEnd;
	protected $timeStart;
	protected $timeEnd;

	public function __construct( $dateStart, $dateEnd, $timeStart = "00:00:00", $timeEnd = "23:59:59" )
	{
		$this->setStart( $dateStart, $timeStart );
		$this->setEnd( $dateEnd, $timeEnd );
	}

	public function getDateStart()
	{
		return $this->dateStart;
	}

	public function getDateEnd()
	{
		return $this->dateEnd;
	}

	public function getEnd()
	{
		return (object) array(
			'date'	=> $this->dateEnd,
			'time'	=> $this->timeEnd,
		);
	}

	public function getStart()
	{
		return (object) array(
			'date'	=> $this->dateStart,
			'time'	=> $this->timeStart,
		);
	}

	public function getTimeStart()
	{
		return $this->timeStart;
	}

	public function getTimeEnd()
	{
		return $this->timeEnd;
	}

	public function setDateEnd( $date ): self
	{
		$this->dateEnd	= $date;
		return $this;
	}

	public function setDateStart( $date ): self
	{
		$this->dateStart	= $date;
		return $this;
	}

	public function setEnd( $date, $time ): self
	{
		$this->setDateEnd( $date );
		$this->setTimeEnd( $time );
		return $this;
	}

	public function setStart( $date, $time ): self
	{
		$this->setDateStart( $date );
		$this->setTimeStart( $time );
		return $this;
	}

	public function setTimeStart( $time ): self
	{
		$this->timeStart	= $time;
		return $this;
	}

	public function setTimeEnd( $time ): self
	{
		$this->timeEnd	= $time;
		return $this;
	}
}
class SpaceRange
{
	protected $x;
	protected $y;
	protected $z;
	protected $distance;

	public function __construct( $x, $y, $z, $distance )
	{
		$this->setCoords( $x, $y, $z );
		$this->setDistance( $distance );
	}

	public function getCoords()
	{
		return (object) array(
			'x' => $this->x,
			'y'	=> $this->y,
			'z'	=> $this->z,
		);
	}

	public function getDistance()
	{
		return $this->distance;
	}

	public function setCoords( $x, $y, $z ): self
	{
		$this->x	= $x;
		$this->y	= $y;
		$this->z	= $z;
		return $this;
	}

	public function setDistance( $distance ): self
	{
		$this->distance	= $distance;
		return $this;
	}
}
