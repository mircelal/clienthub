<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\DomainControl\Db\SettingMapper;

class SettingsController extends Controller
{
	private $userId;
	private SettingMapper $mapper;

	public function __construct(IRequest $request,
	                            SettingMapper $mapper,
	                            $userId)
	{
		parent::__construct(Application::APP_ID, $request);
		$this->mapper = $mapper;
		$this->userId = $userId;
	}

	private function getRequestData(): array
	{
		$body = file_get_contents('php://input');
		parse_str($body, $data);
		return $data;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function get(): JSONResponse
	{
		try {
			$settings = $this->mapper->findAll($this->userId);
			$result = [];
			foreach ($settings as $setting) {
				$result[$setting->getSettingKey()] = $setting->getSettingValue();
			}
			return new JSONResponse($result);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(): JSONResponse
	{
		try {
			$data = $this->getRequestData();

			foreach ($data as $key => $value) {
				// Only allow specific setting keys for security
				$allowedKeys = [
					'active_modules',
					'currencies',
					'default_currency',
					'date_format',
					'time_format',
					'language',
					'notifications_enabled',
					'email_notifications',
					'auto_backup',
					'invoice_prefix',
					'invoice_number_format',
				];

				if (in_array($key, $allowedKeys)) {
					// Handle JSON values
					if (is_array($value) || is_object($value)) {
						$value = json_encode($value);
					}
					$this->mapper->setValue($this->userId, $key, $value);
				}
			}

			$settings = $this->mapper->findAll($this->userId);
			$result = [];
			foreach ($settings as $setting) {
				$result[$setting->getSettingKey()] = $setting->getSettingValue();
			}

			return new JSONResponse($result);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}
