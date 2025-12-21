<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;

class SettingsController extends Controller
{
	private IConfig $config;
	private $userId;

	public function __construct(IRequest $request,
	                            IConfig $config,
	                            $userId)
	{
		parent::__construct(Application::APP_ID, $request);
		$this->config = $config;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function get(): JSONResponse
	{
		$defaultCurrency = $this->config->getUserValue($this->userId, Application::APP_ID, 'default_currency', 'USD');
		
		return new JSONResponse([
			'defaultCurrency' => $defaultCurrency
		]);
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(): JSONResponse
	{
		try {
			$body = file_get_contents('php://input');
			$data = json_decode($body, true);

			if (isset($data['defaultCurrency'])) {
				$currency = $data['defaultCurrency'];
				// Validate currency code
				$validCurrencies = ['USD', 'EUR', 'GBP', 'TRY', 'AZN', 'RUB'];
				if (in_array($currency, $validCurrencies)) {
					$this->config->setUserValue($this->userId, Application::APP_ID, 'default_currency', $currency);
				} else {
					return new JSONResponse(['error' => 'Invalid currency code'], 400);
				}
			}

			return new JSONResponse(['success' => true]);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}

