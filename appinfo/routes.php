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
		['name' => 'client#getContacts', 'url' => '/api/contacts', 'verb' => 'GET'],

		// Domain routes
		['name' => 'domain#index', 'url' => '/api/domains', 'verb' => 'GET'],
		['name' => 'domain#sendExpirationReminders', 'url' => '/api/domains/send-reminders', 'verb' => 'POST'],
		['name' => 'domain#show', 'url' => '/api/domains/{id}', 'verb' => 'GET'],
		['name' => 'domain#create', 'url' => '/api/domains', 'verb' => 'POST'],
		['name' => 'domain#update', 'url' => '/api/domains/{id}', 'verb' => 'PUT'],
		['name' => 'domain#delete', 'url' => '/api/domains/{id}', 'verb' => 'DELETE'],
		['name' => 'domain#byClient', 'url' => '/api/clients/{clientId}/domains', 'verb' => 'GET'],

		// Hosting Package routes (Templates)
		['name' => 'hosting_package#index', 'url' => '/api/hosting-packages', 'verb' => 'GET'],
		['name' => 'hosting_package#active', 'url' => '/api/hosting-packages/active', 'verb' => 'GET'],
		['name' => 'hosting_package#show', 'url' => '/api/hosting-packages/{id}', 'verb' => 'GET'],
		['name' => 'hosting_package#create', 'url' => '/api/hosting-packages', 'verb' => 'POST'],
		['name' => 'hosting_package#update', 'url' => '/api/hosting-packages/{id}', 'verb' => 'PUT'],
		['name' => 'hosting_package#delete', 'url' => '/api/hosting-packages/{id}', 'verb' => 'DELETE'],

		// Hosting routes (Client Instances)
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
		['name' => 'website#byHosting', 'url' => '/api/hostings/{hostingId}/websites', 'verb' => 'GET'],

		// File routes
		['name' => 'file#uploadWebsiteFile', 'url' => '/api/websites/{websiteId}/files', 'verb' => 'POST'],
		['name' => 'file#listWebsiteFiles', 'url' => '/api/websites/{websiteId}/files', 'verb' => 'GET'],
		['name' => 'file#deleteWebsiteFile', 'url' => '/api/websites/{websiteId}/files/{fileName}', 'verb' => 'DELETE'],

		// Invoice File routes
		['name' => 'file#uploadInvoiceFile', 'url' => '/api/invoices/{invoiceId}/files', 'verb' => 'POST'],
		['name' => 'file#listInvoiceFiles', 'url' => '/api/invoices/{invoiceId}/files', 'verb' => 'GET'],
		['name' => 'file#deleteInvoiceFile', 'url' => '/api/invoices/{invoiceId}/files/{fileName}', 'verb' => 'DELETE'],

		// Transaction File routes
		['name' => 'file#uploadTransactionFile', 'url' => '/api/transactions/{transactionId}/files', 'verb' => 'POST'],
		['name' => 'file#listTransactionFiles', 'url' => '/api/transactions/{transactionId}/files', 'verb' => 'GET'],
		['name' => 'file#deleteTransactionFile', 'url' => '/api/transactions/{transactionId}/files/{fileName}', 'verb' => 'DELETE'],

		// Debt File routes
		['name' => 'file#uploadDebtFile', 'url' => '/api/debts/{debtId}/files', 'verb' => 'POST'],
		['name' => 'file#listDebtFiles', 'url' => '/api/debts/{debtId}/files', 'verb' => 'GET'],
		['name' => 'file#deleteDebtFile', 'url' => '/api/debts/{debtId}/files/{fileName}', 'verb' => 'DELETE'],

		// Service Type routes
		['name' => 'service_type#index', 'url' => '/api/service-types', 'verb' => 'GET'],
		['name' => 'service_type#show', 'url' => '/api/service-types/{id}', 'verb' => 'GET'],
		['name' => 'service_type#create', 'url' => '/api/service-types', 'verb' => 'POST'],
		['name' => 'service_type#update', 'url' => '/api/service-types/{id}', 'verb' => 'PUT'],
		['name' => 'service_type#delete', 'url' => '/api/service-types/{id}', 'verb' => 'DELETE'],
		['name' => 'service_type#initPredefined', 'url' => '/api/service-types/init', 'verb' => 'POST'],

		// Service routes
		['name' => 'service#index', 'url' => '/api/services', 'verb' => 'GET'],
		['name' => 'service#show', 'url' => '/api/services/{id}', 'verb' => 'GET'],
		['name' => 'service#create', 'url' => '/api/services', 'verb' => 'POST'],
		['name' => 'service#update', 'url' => '/api/services/{id}', 'verb' => 'PUT'],
		['name' => 'service#delete', 'url' => '/api/services/{id}', 'verb' => 'DELETE'],
		['name' => 'service#byClient', 'url' => '/api/clients/{clientId}/services', 'verb' => 'GET'],
		['name' => 'service#expiringSoon', 'url' => '/api/services/expiring-soon', 'verb' => 'GET'],
		['name' => 'service#extend', 'url' => '/api/services/{id}/extend', 'verb' => 'POST'],

		// Invoice routes - specific routes BEFORE {id} routes
		['name' => 'invoice#index', 'url' => '/api/invoices', 'verb' => 'GET'],
		['name' => 'invoice#overdue', 'url' => '/api/invoices/overdue', 'verb' => 'GET'],
		['name' => 'invoice#upcoming', 'url' => '/api/invoices/upcoming', 'verb' => 'GET'],
		['name' => 'invoice#unpaid', 'url' => '/api/invoices/unpaid', 'verb' => 'GET'],
		// Invoice Item routes - MUST be before {id} routes
		['name' => 'invoice_item#byInvoice', 'url' => '/api/invoices/{invoiceId}/items', 'verb' => 'GET'],
		['name' => 'invoice#addItem', 'url' => '/api/invoices/{id}/items', 'verb' => 'POST'],
		['name' => 'invoice#removeItem', 'url' => '/api/invoices/{id}/items/{itemId}', 'verb' => 'DELETE'],
		// Invoice routes with {id} - must be after /items routes
		['name' => 'invoice#show', 'url' => '/api/invoices/{id}', 'verb' => 'GET'],
		['name' => 'invoice#create', 'url' => '/api/invoices', 'verb' => 'POST'],
		['name' => 'invoice#update', 'url' => '/api/invoices/{id}', 'verb' => 'PUT'],
		['name' => 'invoice#delete', 'url' => '/api/invoices/{id}', 'verb' => 'DELETE'],
		['name' => 'invoice#duplicate', 'url' => '/api/invoices/{id}/duplicate', 'verb' => 'POST'],
		['name' => 'invoice#downloadPdf', 'url' => '/api/invoices/{id}/download-pdf', 'verb' => 'GET'],
		['name' => 'invoice#sendEmail', 'url' => '/api/invoices/{id}/send-email', 'verb' => 'POST'],
		['name' => 'invoice#exportReport', 'url' => '/api/invoices/export', 'verb' => 'GET'],
		['name' => 'invoice#byClient', 'url' => '/api/clients/{clientId}/invoices', 'verb' => 'GET'],
		['name' => 'invoice_item#index', 'url' => '/api/invoice-items', 'verb' => 'GET'],
		['name' => 'invoice_item#show', 'url' => '/api/invoice-items/{id}', 'verb' => 'GET'],
		['name' => 'invoice_item#create', 'url' => '/api/invoice-items', 'verb' => 'POST'],
		['name' => 'invoice_item#update', 'url' => '/api/invoice-items/{id}', 'verb' => 'PUT'],
		['name' => 'invoice_item#delete', 'url' => '/api/invoice-items/{id}', 'verb' => 'DELETE'],

		// Payment routes - specific routes BEFORE {id} routes
		['name' => 'payment#index', 'url' => '/api/payments', 'verb' => 'GET'],
		['name' => 'payment#monthlyTotal', 'url' => '/api/payments/monthly-total', 'verb' => 'GET'],
		['name' => 'payment#show', 'url' => '/api/payments/{id}', 'verb' => 'GET'],
		['name' => 'payment#create', 'url' => '/api/payments', 'verb' => 'POST'],
		['name' => 'payment#update', 'url' => '/api/payments/{id}', 'verb' => 'PUT'],
		['name' => 'payment#delete', 'url' => '/api/payments/{id}', 'verb' => 'DELETE'],
		['name' => 'payment#byClient', 'url' => '/api/clients/{clientId}/payments', 'verb' => 'GET'],
		['name' => 'payment#byInvoice', 'url' => '/api/invoices/{invoiceId}/payments', 'verb' => 'GET'],

		// Project routes - specific routes BEFORE {id} routes
		['name' => 'project#index', 'url' => '/api/projects', 'verb' => 'GET'],
		['name' => 'project#active', 'url' => '/api/projects/active', 'verb' => 'GET'],
		['name' => 'project#approachingDeadline', 'url' => '/api/projects/approaching-deadline', 'verb' => 'GET'],
		['name' => 'project#show', 'url' => '/api/projects/{id}', 'verb' => 'GET'],
		['name' => 'project#create', 'url' => '/api/projects', 'verb' => 'POST'],
		['name' => 'project#update', 'url' => '/api/projects/{id}', 'verb' => 'PUT'],
		['name' => 'project#delete', 'url' => '/api/projects/{id}', 'verb' => 'DELETE'],
		['name' => 'project#items', 'url' => '/api/projects/{id}/items', 'verb' => 'GET'],
		['name' => 'project#addItem', 'url' => '/api/projects/{id}/items', 'verb' => 'POST'],
		['name' => 'project#removeItem', 'url' => '/api/projects/{id}/items/{itemId}', 'verb' => 'DELETE'],
		['name' => 'project#byClient', 'url' => '/api/clients/{clientId}/projects', 'verb' => 'GET'],

		// Project Share routes
		['name' => 'project_share#index', 'url' => '/api/projects/{projectId}/shares', 'verb' => 'GET'],
		['name' => 'project_share#share', 'url' => '/api/projects/{projectId}/shares', 'verb' => 'POST'],
		['name' => 'project_share#unshare', 'url' => '/api/projects/{projectId}/shares/{sharedWithUserId}', 'verb' => 'DELETE'],

		// User routes
		['name' => 'user#index', 'url' => '/api/users', 'verb' => 'GET'],

		// Task routes - specific routes BEFORE {id} routes
		['name' => 'task#index', 'url' => '/api/tasks', 'verb' => 'GET'],
		['name' => 'task#pending', 'url' => '/api/tasks/pending', 'verb' => 'GET'],
		['name' => 'task#approachingDeadline', 'url' => '/api/tasks/approaching-deadline', 'verb' => 'GET'],
		['name' => 'task#overdue', 'url' => '/api/tasks/overdue', 'verb' => 'GET'],
		['name' => 'task#show', 'url' => '/api/tasks/{id}', 'verb' => 'GET'],
		['name' => 'task#create', 'url' => '/api/tasks', 'verb' => 'POST'],
		['name' => 'task#update', 'url' => '/api/tasks/{id}', 'verb' => 'PUT'],
		['name' => 'task#delete', 'url' => '/api/tasks/{id}', 'verb' => 'DELETE'],
		['name' => 'task#toggleStatus', 'url' => '/api/tasks/{id}/toggle', 'verb' => 'POST'],
		['name' => 'task#byProject', 'url' => '/api/projects/{projectId}/tasks', 'verb' => 'GET'],
		['name' => 'task#byClient', 'url' => '/api/clients/{clientId}/tasks', 'verb' => 'GET'],
		['name' => 'task#subtasks', 'url' => '/api/tasks/{id}/subtasks', 'verb' => 'GET'],

		// Time Entry routes
		['name' => 'time_entry#byProject', 'url' => '/api/projects/{projectId}/time-entries', 'verb' => 'GET'],
		['name' => 'time_entry#getRunning', 'url' => '/api/projects/{projectId}/time-entries/running', 'verb' => 'GET'],
		['name' => 'time_entry#start', 'url' => '/api/projects/{projectId}/time-entries/start', 'verb' => 'POST'],
		['name' => 'time_entry#stop', 'url' => '/api/projects/{projectId}/time-entries/stop', 'verb' => 'POST'],
		['name' => 'time_entry#update', 'url' => '/api/time-entries/{id}', 'verb' => 'PUT'],
		['name' => 'time_entry#delete', 'url' => '/api/time-entries/{id}', 'verb' => 'DELETE'],

		// Project Note routes
		['name' => 'project_note#index', 'url' => '/api/projects/{projectId}/notes', 'verb' => 'GET'],
		['name' => 'project_note#show', 'url' => '/api/projects/{projectId}/notes/{id}', 'verb' => 'GET'],
		['name' => 'project_note#create', 'url' => '/api/projects/{projectId}/notes', 'verb' => 'POST'],
		['name' => 'project_note#update', 'url' => '/api/projects/{projectId}/notes/{id}', 'verb' => 'PUT'],
		['name' => 'project_note#delete', 'url' => '/api/projects/{projectId}/notes/{id}', 'verb' => 'DELETE'],

		// Client Note routes
		['name' => 'client_note#index', 'url' => '/api/clients/{clientId}/notes', 'verb' => 'GET'],
		['name' => 'client_note#show', 'url' => '/api/clients/{clientId}/notes/{id}', 'verb' => 'GET'],
		['name' => 'client_note#create', 'url' => '/api/clients/{clientId}/notes', 'verb' => 'POST'],
		['name' => 'client_note#update', 'url' => '/api/clients/{clientId}/notes/{id}', 'verb' => 'PUT'],
		['name' => 'client_note#delete', 'url' => '/api/clients/{clientId}/notes/{id}', 'verb' => 'DELETE'],

		// Project File routes
		['name' => 'project_file#index', 'url' => '/api/projects/{projectId}/files', 'verb' => 'GET'],
		['name' => 'project_file#show', 'url' => '/api/projects/{projectId}/files/{id}', 'verb' => 'GET'],
		['name' => 'project_file#create', 'url' => '/api/projects/{projectId}/files', 'verb' => 'POST'],
		['name' => 'project_file#delete', 'url' => '/api/projects/{projectId}/files/{id}', 'verb' => 'DELETE'],

		// Project Activity routes
		['name' => 'project_activity#index', 'url' => '/api/projects/{projectId}/activities', 'verb' => 'GET'],

		// Transaction Category routes
		['name' => 'transaction_category#index', 'url' => '/api/transaction-categories', 'verb' => 'GET'],
		['name' => 'transaction_category#byType', 'url' => '/api/transaction-categories/type/{type}', 'verb' => 'GET'],
		['name' => 'transaction_category#create', 'url' => '/api/transaction-categories', 'verb' => 'POST'],
		['name' => 'transaction_category#update', 'url' => '/api/transaction-categories/{id}', 'verb' => 'PUT'],
		['name' => 'transaction_category#delete', 'url' => '/api/transaction-categories/{id}', 'verb' => 'DELETE'],

		// Transaction routes - specific routes BEFORE {id} routes
		['name' => 'transaction#index', 'url' => '/api/transactions', 'verb' => 'GET'],
		['name' => 'transaction#byType', 'url' => '/api/transactions/type/{type}', 'verb' => 'GET'],
		['name' => 'transaction#byCategory', 'url' => '/api/transactions/category/{categoryId}', 'verb' => 'GET'],
		['name' => 'transaction#byProject', 'url' => '/api/projects/{projectId}/transactions', 'verb' => 'GET'],
		['name' => 'transaction#byClient', 'url' => '/api/clients/{clientId}/transactions', 'verb' => 'GET'],
		['name' => 'transaction#monthlySummary', 'url' => '/api/transactions/monthly-summary', 'verb' => 'GET'],
		['name' => 'transaction#summaryByCategory', 'url' => '/api/transactions/summary-by-category', 'verb' => 'GET'],
		['name' => 'transaction#show', 'url' => '/api/transactions/{id}', 'verb' => 'GET'],
		['name' => 'transaction#create', 'url' => '/api/transactions', 'verb' => 'POST'],
		['name' => 'transaction#update', 'url' => '/api/transactions/{id}', 'verb' => 'PUT'],
		['name' => 'transaction#delete', 'url' => '/api/transactions/{id}', 'verb' => 'DELETE'],

		// Debt routes - specific routes BEFORE {id} routes
		['name' => 'debt#index', 'url' => '/api/debts', 'verb' => 'GET'],
		['name' => 'debt#byType', 'url' => '/api/debts/type/{type}', 'verb' => 'GET'],
		['name' => 'debt#upcomingPayments', 'url' => '/api/debts/upcoming-payments', 'verb' => 'GET'],
		['name' => 'debt#overdue', 'url' => '/api/debts/overdue', 'verb' => 'GET'],
		['name' => 'debt#show', 'url' => '/api/debts/{id}', 'verb' => 'GET'],
		['name' => 'debt#create', 'url' => '/api/debts', 'verb' => 'POST'],
		['name' => 'debt#update', 'url' => '/api/debts/{id}', 'verb' => 'PUT'],
		['name' => 'debt#delete', 'url' => '/api/debts/{id}', 'verb' => 'DELETE'],
		['name' => 'debt#addPayment', 'url' => '/api/debts/{id}/payments', 'verb' => 'POST'],

		// Debt Payment routes
		['name' => 'debt_payment#delete', 'url' => '/api/debt-payments/{id}', 'verb' => 'DELETE'],

		// Settings routes
		['name' => 'settings#get', 'url' => '/api/settings', 'verb' => 'GET'],
		['name' => 'settings#update', 'url' => '/api/settings', 'verb' => 'PUT'],
	],
];
