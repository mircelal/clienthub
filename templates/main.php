<?php
script('domaincontrol', 'chart.min');
script('domaincontrol', 'domaincontrol-main');
script('domaincontrol', 'reports');
style('domaincontrol', 'domaincontrol');
?>

<div id="domaincontrol-app" class="domaincontrol-container">
	<div class="domaincontrol-header">
		<h2>ClientHub</h2>
		<p class="domaincontrol-subtitle">Kapsamlı müşteri, proje ve iş yönetim sistemi</p>
	</div>

	<div class="domaincontrol-tabs">
		<button class="tab-button active" data-tab="dashboard">📊 Dashboard</button>
		<button class="tab-button" data-tab="clients">👥 Müşteriler</button>
		<button class="tab-button" data-tab="domains">🌐 Domainler</button>
		<button class="tab-button" data-tab="hostings">🖥️ Hosting</button>
		<button class="tab-button" data-tab="websites">🌍 Websiteler</button>
		<button class="tab-button" data-tab="services">🛠️ Hizmetler</button>
		<button class="tab-button" data-tab="invoices">📄 Faturalar</button>
		<button class="tab-button" data-tab="projects">📁 Projeler</button>
		<button class="tab-button" data-tab="tasks">✅ Görevler</button>
		<button class="tab-button" data-tab="reports">📊 Raporlar</button>
	</div>

	<?php include __DIR__ . '/partials/tabs/dashboard.php'; ?>
	<?php include __DIR__ . '/partials/tabs/clients.php'; ?>
	<?php include __DIR__ . '/partials/tabs/domains.php'; ?>
	<?php include __DIR__ . '/partials/tabs/hostings.php'; ?>
	<?php include __DIR__ . '/partials/tabs/websites.php'; ?>
	<?php include __DIR__ . '/partials/tabs/services.php'; ?>
	<?php include __DIR__ . '/partials/tabs/invoices.php'; ?>
	<?php include __DIR__ . '/partials/tabs/projects.php'; ?>
	<?php include __DIR__ . '/partials/tabs/tasks.php'; ?>
	<?php include __DIR__ . '/partials/tabs/reports.php'; ?>

	<?php include __DIR__ . '/partials/modals/client-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/domain-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/hosting-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/website-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/service-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/invoice-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/project-modal.php'; ?>
	<?php include __DIR__ . '/partials/modals/task-modal.php'; ?>
</div>
