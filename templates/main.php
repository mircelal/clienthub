<?php
script('domaincontrol', 'chart.min');
script('domaincontrol', 'domaincontrol-main');
script('domaincontrol', 'reports');
script('domaincontrol', 'vue-components'); // Vue.js components
style('domaincontrol', 'domaincontrol');
?>

<div id="app">
	<!-- Vue.js Navigation Component -->
	<div id="vue-navigation-container"></div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<?php include __DIR__ . '/partials/tabs/dashboard.php'; ?>
			<?php include __DIR__ . '/partials/tabs/clients.php'; ?>
			<?php include __DIR__ . '/partials/tabs/domains.php'; ?>
			<?php include __DIR__ . '/partials/tabs/hostings.php'; ?>
			<?php include __DIR__ . '/partials/tabs/websites.php'; ?>
			<?php include __DIR__ . '/partials/tabs/services.php'; ?>
			<?php include __DIR__ . '/partials/tabs/invoices.php'; ?>
			<?php include __DIR__ . '/partials/tabs/projects.php'; ?>
			<?php include __DIR__ . '/partials/tabs/tasks.php'; ?>
			<?php include __DIR__ . '/partials/tabs/transactions.php'; ?>
			<?php include __DIR__ . '/partials/tabs/debts.php'; ?>
			<?php include __DIR__ . '/partials/tabs/reports.php'; ?>
			<?php include __DIR__ . '/partials/tabs/settings.php'; ?>
		</div>
	</div>

	<!-- Client Modal is now handled by Vue.js ClientModal component -->
	<?php include __DIR__ . '/partials/modals/domain-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/hosting-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/hosting-package-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/hosting-domain-link-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/hosting-website-link-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/website-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/service-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/invoice-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/project-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/task-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/project-share-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/transaction-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/transaction-category-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/debt-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/debt-payment-modal.php'; ?>
</div>