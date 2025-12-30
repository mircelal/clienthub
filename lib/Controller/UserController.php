<?php
declare(strict_types=1);

namespace OCA\DomainControl\Controller;

use OCA\DomainControl\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUserManager;

class UserController extends Controller
{
	private $userId;
	private IUserManager $userManager;

	public function __construct(
		IRequest $request,
		IUserManager $userManager,
		$userId
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->userManager = $userManager;
		$this->userId = $userId;
	}

	/**
	 * Get list of all Nextcloud users
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): JSONResponse
	{
		try {
			$users = [];
			$this->userManager->callForAllUsers(function ($user) use (&$users) {
				$users[] = [
					'userId' => $user->getUID(),
					'displayName' => $user->getDisplayName() ?: $user->getUID(),
					'email' => $user->getEMailAddress() ?: null,
				];
			});

			// Sort by display name
			usort($users, function ($a, $b) {
				return strcmp($a['displayName'], $b['displayName']);
			});

			return new JSONResponse($users);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}

