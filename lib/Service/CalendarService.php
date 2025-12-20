<?php
declare(strict_types=1);

namespace OCA\DomainControl\Service;

use OCP\Calendar\ICalendar;
use OCP\Calendar\ICalendarEventBuilder;
use OCP\Calendar\ICreateFromString;
use OCP\Calendar\IManager;
use Psr\Log\LoggerInterface;

class CalendarService
{
	private ?IManager $calendarManager;
	private LoggerInterface $logger;

	public function __construct(
		?IManager $calendarManager,
		LoggerInterface $logger
	) {
		$this->calendarManager = $calendarManager;
		$this->logger = $logger;
	}

	/**
	 * Add a debt payment reminder to calendar
	 * @param string $userId
	 * @param string $title
	 * @param string $date Date in Y-m-d format
	 * @param string $description
	 * @return string|null Event UID on success, null on failure
	 */
	public function addDebtEvent(string $userId, string $title, string $date, string $description = ''): ?string
	{
		if (!$this->calendarManager) {
			$this->logger->info('Calendar manager not available, skipping calendar event creation');
			return null;
		}

		try {
			$principal = 'principals/users/' . $userId;
			$calendars = $this->calendarManager->getCalendarsForPrincipal($principal);
			
			if (empty($calendars)) {
				$this->logger->warning('No calendars found for user: ' . $userId);
				return null;
			}

			// Find a writable calendar
			$writableCalendar = null;
			foreach ($calendars as $calendar) {
				if ($calendar instanceof ICreateFromString) {
					$writableCalendar = $calendar;
					break;
				}
			}

			if ($writableCalendar === null) {
				$this->logger->warning('No writable calendar found for user: ' . $userId);
				return null;
			}

			// Build event using ICalendarEventBuilder
			$startDate = (new \DateTimeImmutable($date . ' 09:00:00'))
				->setTimezone(new \DateTimeZone(date_default_timezone_get()));
			$endDate = $startDate->add(new \DateInterval('PT1H'));
			
			$eventUid = uniqid('debt-payment-') . '@domaincontrol';
			
			$builder = $this->calendarManager->createEventBuilder()
				->setStartDate($startDate)
				->setEndDate($endDate)
				->setSummary($title)
				->setDescription($description);

			// Create event in calendar
			$builder->createInCalendar($writableCalendar);

			// Also create using ICreateFromString for compatibility
			$ics = $builder->toIcs();
			$filename = $eventUid . '.ics';
			$writableCalendar->createFromString($filename, $ics);

			$this->logger->info('Created calendar event for debt payment: ' . $title);
			return $eventUid;
		} catch (\Throwable $e) {
			$this->logger->error('Failed to create calendar event: ' . $e->getMessage(), [
				'exception' => $e
			]);
			return null;
		}
	}

	/**
	 * Remove a debt payment reminder from calendar
	 * @param string $userId
	 * @param string $uid Event UID
	 * @return bool Success status
	 */
	public function removeDebtEvent(string $userId, string $uid): bool
	{
		if (!$this->calendarManager) {
			return false;
		}

		try {
			$principal = 'principals/users/' . $userId;
			$calendars = $this->calendarManager->getCalendarsForPrincipal($principal);
			
			foreach ($calendars as $calendar) {
				if (!($calendar instanceof ICreateFromString)) {
					continue;
				}
				
				$objects = $calendar->getChildren();
				foreach ($objects as $object) {
					$data = $object->getCalendarData();
					if (strpos($data, 'UID:' . $uid) !== false || strpos($data, 'UID:' . $uid . '@') !== false) {
						$object->delete();
						$this->logger->info('Deleted calendar event with UID: ' . $uid);
						return true;
					}
				}
			}
			return false;
		} catch (\Throwable $e) {
			$this->logger->error('Failed to delete calendar event: ' . $e->getMessage(), [
				'exception' => $e
			]);
			return false;
		}
	}
}

