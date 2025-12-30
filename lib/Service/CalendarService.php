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

	/**
	 * Add a task to calendar
	 * @param string $userId
	 * @param string $title
	 * @param string $date Date in Y-m-d format
	 * @param string $description
	 * @return string|null Event UID on success, null on failure
	 */
	public function addTaskEvent(string $userId, string $title, string $date, string $description = ''): ?string
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
			
			$eventUid = uniqid('task-') . '@domaincontrol';
			
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

			$this->logger->info('Created calendar event for task: ' . $title);
			return $eventUid;
		} catch (\Throwable $e) {
			$this->logger->error('Failed to create calendar event: ' . $e->getMessage(), [
				'exception' => $e
			]);
			return null;
		}
	}

	/**
	 * Remove a task from calendar
	 * @param string $userId
	 * @param string $uid Event UID
	 * @return bool Success status
	 */
	public function removeTaskEvent(string $userId, string $uid): bool
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

	/**
	 * Add a domain expiration reminder to calendar
	 * @param string $userId
	 * @param string $domainName
	 * @param string $expirationDate Date in Y-m-d format
	 * @return string|null Event UID on success, null on failure
	 */
	public function addDomainExpirationEvent(string $userId, string $domainName, string $expirationDate): ?string
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
			$startDate = (new \DateTimeImmutable($expirationDate . ' 09:00:00'))
				->setTimezone(new \DateTimeZone(date_default_timezone_get()));
			$endDate = $startDate->add(new \DateInterval('PT1H'));
			
			$eventUid = uniqid('domain-exp-') . '@domaincontrol';
			
			$title = 'Domain Bitiş: ' . $domainName;
			$description = 'Domain ' . $domainName . ' bu tarihte sona erecek.';
			
			$builder = $this->calendarManager->createEventBuilder()
				->setStartDate($startDate)
				->setEndDate($endDate)
				->setSummary($title)
				->setDescription($description);

			// Create event in calendar
			$builder->createInCalendar($writableCalendar);

			// Also create using ICreateFromString for compatibility with reminder
			$ics = $builder->toIcs();
			
			// Add reminder (5 days before) to ICS
			$ics = $this->addReminderToIcs($ics, 5, 'Domain Bitiş Hatırlatması');
			
			$filename = $eventUid . '.ics';
			$writableCalendar->createFromString($filename, $ics);

			$this->logger->info('Created calendar event for domain expiration: ' . $domainName);
			return $eventUid;
		} catch (\Throwable $e) {
			$this->logger->error('Failed to create calendar event: ' . $e->getMessage(), [
				'exception' => $e
			]);
			return null;
		}
	}

	/**
	 * Add a hosting expiration reminder to calendar
	 * @param string $userId
	 * @param string $hostingName
	 * @param string $expirationDate Date in Y-m-d format
	 * @return string|null Event UID on success, null on failure
	 */
	public function addHostingExpirationEvent(string $userId, string $hostingName, string $expirationDate): ?string
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
			$startDate = (new \DateTimeImmutable($expirationDate . ' 09:00:00'))
				->setTimezone(new \DateTimeZone(date_default_timezone_get()));
			$endDate = $startDate->add(new \DateInterval('PT1H'));
			
			$eventUid = uniqid('hosting-exp-') . '@domaincontrol';
			
			$title = 'Hosting Bitiş: ' . $hostingName;
			$description = 'Hosting ' . $hostingName . ' bu tarihte sona erecek.';
			
			$builder = $this->calendarManager->createEventBuilder()
				->setStartDate($startDate)
				->setEndDate($endDate)
				->setSummary($title)
				->setDescription($description);

			// Create event in calendar
			$builder->createInCalendar($writableCalendar);

			// Also create using ICreateFromString for compatibility with reminder
			$ics = $builder->toIcs();
			
			// Add reminder (5 days before) to ICS
			$ics = $this->addReminderToIcs($ics, 5, 'Hosting Bitiş Hatırlatması');
			
			$filename = $eventUid . '.ics';
			$writableCalendar->createFromString($filename, $ics);

			$this->logger->info('Created calendar event for hosting expiration: ' . $hostingName);
			return $eventUid;
		} catch (\Throwable $e) {
			$this->logger->error('Failed to create calendar event: ' . $e->getMessage(), [
				'exception' => $e
			]);
			return null;
		}
	}

	/**
	 * Remove a calendar event by UID (generic method)
	 * @param string $userId
	 * @param string $uid Event UID
	 * @return bool Success status
	 */
	public function removeEvent(string $userId, string $uid): bool
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

	/**
	 * Add reminder (VALARM) to ICS string
	 * @param string $ics Original ICS string
	 * @param int $daysBefore Number of days before event to trigger reminder
	 * @param string $description Reminder description
	 * @return string ICS string with reminder added
	 */
	private function addReminderToIcs(string $ics, int $daysBefore, string $description): string
	{
		// Find the end of the event (before END:VEVENT)
		$endEventPos = strrpos($ics, 'END:VEVENT');
		
		if ($endEventPos === false) {
			// If END:VEVENT not found, try to find END:VTODO or just append before last END:
			$endEventPos = strrpos($ics, 'END:');
			if ($endEventPos === false) {
				return $ics;
			}
		}
		
		// Build VALARM component
		$alarm = "\r\nBEGIN:VALARM\r\n";
		$alarm .= "TRIGGER:-P{$daysBefore}D\r\n"; // P5D = 5 days, - means before
		$alarm .= "ACTION:DISPLAY\r\n";
		$alarm .= "DESCRIPTION:" . $description . "\r\n";
		$alarm .= "END:VALARM\r\n";
		
		// Insert alarm before END:VEVENT
		$ics = substr_replace($ics, $alarm, $endEventPos, 0);
		
		return $ics;
	}
}

