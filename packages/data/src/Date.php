<?php
namespace CentralBooking\Data;

use DateTime;

final class Date
{
	private DateTime $date;

	public function __construct(string $Ymd = 'now')
	{
		$this->date = new DateTime($Ymd);
	}

	public function format(string $format = 'Y-m-d')
	{
		return $this->date->format($format);
	}

	public function addDays(int $days)
	{
		$days = absint($days);
		$this->date->modify("+{$days} days");
	}

	public static function today()
	{
		return new Date();
	}
}