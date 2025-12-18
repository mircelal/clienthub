<?php
declare(strict_types=1);

return [
	'routes' => [
		// Main page
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#test', 'url' => '/test', 'verb' => 'GET'],
		
		// Client routes
		['name' => 'client#index', 'url' => '/api/clients', 'verb' => 'GET'],
		['name' => 'client#show', 'url' => '/api/clients/{id}', 'verb' => 'GET'],
		['name' => 'client#create', 'url' => '/api/clients', 'verb' => 'POST'],
		['name' => 'client#update', 'url' => '/api/clients/{id}', 'verb' => 'PUT'],
		['name' => 'client#delete', 'url' => '/api/clients/{id}', 'verb' => 'DELETE'],
		
		// Domain routes
		['name' => 'domain#index', 'url' => '/api/domains', 'verb' => 'GET'],
		['name' => 'domain#show', 'url' => '/api/domains/{id}', 'verb' => 'GET'],
		['name' => 'domain#create', 'url' => '/api/domains', 'verb' => 'POST'],
		['name' => 'domain#update', 'url' => '/api/domains/{id}', 'verb' => 'PUT'],
		['name' => 'domain#delete', 'url' => '/api/domains/{id}', 'verb' => 'DELETE'],
		['name' => 'domain#byClient', 'url' => '/api/clients/{clientId}/domains', 'verb' => 'GET'],
		
		// Hosting routes
		['name' => 'hosting#index', 'url' => '/api/hostings', 'verb' => 'GET'],
		['name' => 'hosting#show', 'url' => '/api/hostings/{id}', 'verb' => 'GET'],
		['name' => 'hosting#create', 'url' => '/api/hostings', 'verb' => 'POST'],
		['name' => 'hosting#update', 'url' => '/api/hostings/{id}', 'verb' => 'PUT'],
		['name' => 'hosting#delete', 'url' => '/api/hostings/{id}', 'verb' => 'DELETE'],
		['name' => 'hosting#byClient', 'url' => '/api/clients/{clientId}/hostings', 'verb' => 'GET'],
		
		// Website routes
		['name' => 'website#index', 'url' => '/api/websites', 'verb' => 'GET'],
		['name' => 'website#show', 'url' => '/api/websites/{id}', 'verb' => 'GET'],
		['name' => 'website#create', 'url' => '/api/websites', 'verb' => 'POST'],
		['name' => 'website#update', 'url' => '/api/websites/{id}', 'verb' => 'PUT'],
		['name' => 'website#delete', 'url' => '/api/websites/{id}', 'verb' => 'DELETE'],
		['name' => 'website#byClient', 'url' => '/api/clients/{clientId}/websites', 'verb' => 'GET'],
	],
];

