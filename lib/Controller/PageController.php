<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class PageController extends Controller {
	private $userId;

	public function __construct(IRequest $request,
	                            $userId) {
		parent::__construct(Application::APP_ID, $request);
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): TemplateResponse {
		return new TemplateResponse('domaincontrol', 'main');
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function test() {
		return new \OCP\AppFramework\Http\JSONResponse([
			'status' => 'ok',
			'message' => 'DomainControl app is working!',
			'userId' => $this->userId,
			'timestamp' => time()
		]);
	}
}

