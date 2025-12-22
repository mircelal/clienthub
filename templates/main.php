<?php
script('domaincontrol', 'chart.min');
script('domaincontrol', 'domaincontrol-main');
script('domaincontrol', 'reports');
script('domaincontrol', 'vue-test');
style('domaincontrol', 'domaincontrol');
?>

<div id="app">
	<div id="app-navigation">
		<ul class="with-icon">
			<li data-id="dashboard" class="active">
				<a href="#" class="icon-home tab-button" data-tab="dashboard">Dashboard</a>
			</li>
			<li data-id="clients">
				<a href="#" class="icon-contacts tab-button" data-tab="clients">Müşteriler</a>
			</li>
			<li data-id="domains">
				<a href="#" class="icon-public tab-button" data-tab="domains">Domainler</a>
			</li>
			<li data-id="hostings">
				<a href="#" class="icon-category-office tab-button" data-tab="hostings">Hosting</a>
			</li>
			<li data-id="websites">
				<a href="#" class="icon-link tab-button" data-tab="websites">Websiteler</a>
			</li>
			<li data-id="services">
				<a href="#" class="icon-settings tab-button" data-tab="services">Hizmetler</a>
			</li>
			<li data-id="invoices">
				<a href="#" class="icon-files tab-button" data-tab="invoices">Faturalar</a>
			</li>
			<li data-id="projects">
				<a href="#" class="icon-folder tab-button" data-tab="projects">Projeler</a>
			</li>
			<li data-id="tasks">
				<a href="#" class="icon-checkmark tab-button" data-tab="tasks">Görevler</a>
			</li>
			<li data-id="transactions">
				<a href="#" class="icon-category-office tab-button" data-tab="transactions">Gelir/Gider</a>
			</li>
			<li data-id="debts">
				<a href="#" class="icon-files tab-button" data-tab="debts">Borç/Alacak</a>
			</li>
			<li data-id="reports">
				<a href="#" class="icon-category-monitoring tab-button" data-tab="reports">Raporlar</a>
			</li>
		</ul>

		<div id="app-settings" class="app-navigation-entry">
			<div id="app-settings-header">
				<button class="settings-button" data-apps-slide-toggle="#app-settings-content">
					<span class="icon-settings"></span>
					<span>Ayarlar</span>
				</button>
			</div>
			<div id="app-settings-content" class="hidden">
				<ul>
					<li>
						<a href="#" class="icon-settings tab-button" data-tab="settings">Genel Ayarlar</a>
					</li>
				</ul>
			</div>
		</div>
	</div>

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

	<?php include __DIR__ . '/partials/modals/client-modal.php'; ?>
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