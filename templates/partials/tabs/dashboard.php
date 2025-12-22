<div id="dashboard-tab" class="tab-content active">
	<!-- Vue.js Test Container (Nextcloud Native) -->
	<div id="vue-test-container"></div>
	
	<!-- Welcome Header -->
	<div class="dashboard-welcome">
		<div class="welcome-text">
			<h1>Hoş Geldiniz, <span id="current-user-name">Calal</span> 👋</h1>
			<p>Bugün işletmenizde neler olup bitiyor? İşte güncel özetiniz.</p>
		</div>
		<div class="welcome-date" id="dashboard-current-date">
			<!-- Date will be populated via JS -->
		</div>
	</div>

	<!-- Main Stats Overview -->
	<div class="section">
		<div class="dashboard-grid">
			<div class="stat-card stat-card--gradient-primary">
				<div class="stat-card__icon">👥</div>
				<div class="stat-card__info">
					<div class="stat-card__label">Toplam Müşteri</div>
					<div class="stat-card__value" id="stat-clients">0</div>
				</div>
			</div>
			<div class="stat-card stat-card--gradient-success">
				<div class="stat-card__icon">🚀</div>
				<div class="stat-card__info">
					<div class="stat-card__label">Aktif Projeler</div>
					<div class="stat-card__value" id="stat-projects">0</div>
				</div>
			</div>
			<div class="stat-card stat-card--gradient-warning">
				<div class="stat-card__icon">📝</div>
				<div class="stat-card__info">
					<div class="stat-card__label">Bekleyen Görevler</div>
					<div class="stat-card__value" id="stat-tasks">0</div>
				</div>
			</div>
			<div class="stat-card stat-card--gradient-danger">
				<div class="stat-card__icon">💰</div>
				<div class="stat-card__info">
					<div class="stat-card__label">Ödenmemiş Fatura</div>
					<div class="stat-card__value" id="stat-unpaid-invoices">0</div>
				</div>
			</div>
		</div>
	</div>

	<div class="dashboard-main-row">
		<div class="dashboard-column dashboard-column--left">
			<!-- Detailed Stats Group -->
			<div class="section mini-stats-section">
				<div class="mini-stats-grid">
					<div class="mini-stat">
						<span class="mini-stat__label">Domainler</span>
						<span class="mini-stat__value" id="stat-domains">0</span>
					</div>
					<div class="mini-stat">
						<span class="mini-stat__label">Hosting</span>
						<span class="mini-stat__value" id="stat-hostings">0</span>
					</div>
					<div class="mini-stat">
						<span class="mini-stat__label">Websiteler</span>
						<span class="mini-stat__value" id="stat-websites">0</span>
					</div>
					<div class="mini-stat">
						<span class="mini-stat__label">Gelir (Bu Ay)</span>
						<span class="mini-stat__value"><span id="stat-monthly-income">0</span> ₼</span>
					</div>
					<div class="mini-stat">
						<span class="mini-stat__label">Gider (Bu Ay)</span>
						<span class="mini-stat__value"><span id="stat-monthly-expense">0</span> ₼</span>
					</div>
					<div class="mini-stat">
						<span class="mini-stat__label">Net Kar/Zarar</span>
						<span class="mini-stat__value"><span id="stat-net-profit">0</span> ₼</span>
					</div>
				</div>
			</div>

			<!-- Quick Actions Card -->
			<div class="section actions-card">
				<h3>⚡ Hızlı İşlemler</h3>
				<div class="action-buttons">
					<button class="btn btn-action" id="quick-add-client">
						<span class="action-icon">👤</span> Yeni Müşteri
					</button>
					<button class="btn btn-action" id="quick-add-project">
						<span class="action-icon">📂</span> Yeni Proje
					</button>
					<button class="btn btn-action" id="quick-add-task">
						<span class="action-icon">✅</span> Yeni Görev
					</button>
					<button class="btn btn-action" id="quick-add-invoice">
						<span class="action-icon">📄</span> Fatura Oluştur
					</button>
				</div>
			</div>

			<!-- Recent Items -->
			<div class="section">
				<div class="section-header">
					<h3>🆕 Son Eklenen Müşteriler</h3>
					<button class="btn btn-link" onclick="DomainControl.switchTab('clients')">Tümünü Gör</button>
				</div>
				<div id="recent-clients" class="domaincontrol-list dashboard-list"></div>
			</div>
		</div>

		<div class="dashboard-column dashboard-column--right">
			<!-- Alert Panels -->
			<div class="alert-section">
				<div class="alert-panel alert-panel--danger">
					<header>
						<h3>🚨 Geciken Ödemeler</h3>
						<span id="overdue-count" class="badge badge--danger">0</span>
					</header>
					<div id="overdue-invoices-list" class="alert-panel__body">
						<p class="empty-message">Geciken ödeme yok</p>
					</div>
				</div>

				<div class="alert-panel alert-panel--warning">
					<header>
						<h3>⏰ Yaklaşan Ödemeler</h3>
						<span id="upcoming-count" class="badge badge--warning">0</span>
					</header>
					<div id="upcoming-payments-list" class="alert-panel__body">
						<p class="empty-message">Yaklaşan ödeme yok</p>
					</div>
				</div>

				<div class="alert-panel alert-panel--info">
					<header>
						<h3>📋 Yaklaşan Görevler</h3>
						<span id="upcoming-tasks-count" class="badge badge--info">0</span>
					</header>
					<div id="upcoming-tasks-list" class="alert-panel__body">
						<p class="empty-message">Yaklaşan görev yok</p>
					</div>
				</div>

				<div class="alert-panel alert-panel--warning">
					<header>
						<h3>💳 Yaklaşan Borç Ödemeleri</h3>
						<span id="upcoming-debts-count" class="badge badge--warning">0</span>
					</header>
					<div id="upcoming-debts-list" class="alert-panel__body">
						<p class="empty-message">Yaklaşan borç ödemesi yok</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>