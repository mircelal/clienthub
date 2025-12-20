<!-- Dashboard Tab -->
<div id="dashboard-tab" class="tab-content active">
	<!-- Ana İstatistikler -->
	<div class="dashboard-grid">
		<div class="stat-card stat-card--primary">
			<div class="stat-card__icon">👥</div>
			<div class="stat-card__content">
				<div class="stat-card__value" id="stat-clients">0</div>
				<div class="stat-card__label">Müşteriler</div>
			</div>
		</div>
		<div class="stat-card stat-card--success">
			<div class="stat-card__icon">🌐</div>
			<div class="stat-card__content">
				<div class="stat-card__value" id="stat-domains">0</div>
				<div class="stat-card__label">Domainler</div>
			</div>
		</div>
		<div class="stat-card stat-card--info">
			<div class="stat-card__icon">🖥️</div>
			<div class="stat-card__content">
				<div class="stat-card__value" id="stat-hostings">0</div>
				<div class="stat-card__label">Hosting</div>
			</div>
		</div>
		<div class="stat-card stat-card--warning">
			<div class="stat-card__icon">🌍</div>
			<div class="stat-card__content">
				<div class="stat-card__value" id="stat-websites">0</div>
				<div class="stat-card__label">Websiteler</div>
			</div>
		</div>
		<div class="stat-card stat-card--purple">
			<div class="stat-card__icon">📁</div>
			<div class="stat-card__content">
				<div class="stat-card__value" id="stat-projects">0</div>
				<div class="stat-card__label">Aktif Projeler</div>
			</div>
		</div>
		<div class="stat-card stat-card--teal">
			<div class="stat-card__icon">✅</div>
			<div class="stat-card__content">
				<div class="stat-card__value" id="stat-tasks">0</div>
				<div class="stat-card__label">Bekleyen Görevler</div>
			</div>
		</div>
		<div class="stat-card stat-card--danger">
			<div class="stat-card__icon">📄</div>
			<div class="stat-card__content">
				<div class="stat-card__value" id="stat-unpaid-invoices">0</div>
				<div class="stat-card__label">Ödenmemiş Fatura</div>
			</div>
		</div>
		<div class="stat-card stat-card--gold">
			<div class="stat-card__icon">💰</div>
			<div class="stat-card__content">
				<div class="stat-card__value" id="stat-monthly-income">0</div>
				<div class="stat-card__label">Bu Ay Gelir</div>
			</div>
		</div>
	</div>

	<!-- Uyarı Panelleri -->
	<div class="dashboard-alerts">
		<!-- Geciken Ödemeler -->
		<div class="alert-panel alert-panel--danger">
			<div class="alert-panel__header">
				<h4>🚨 Geciken Ödemeler</h4>
				<span class="alert-panel__count" id="overdue-count">0</span>
			</div>
			<div class="alert-panel__body" id="overdue-invoices-list">
				<p class="empty-message">Geciken ödeme yok</p>
			</div>
		</div>

		<!-- Yaklaşan Ödemeler -->
		<div class="alert-panel alert-panel--warning">
			<div class="alert-panel__header">
				<h4>⏰ Yaklaşan Ödemeler (30 gün)</h4>
				<span class="alert-panel__count" id="upcoming-count">0</span>
			</div>
			<div class="alert-panel__body" id="upcoming-payments-list">
				<p class="empty-message">Yaklaşan ödeme yok</p>
			</div>
		</div>

		<!-- Yaklaşan Görevler -->
		<div class="alert-panel alert-panel--info">
			<div class="alert-panel__header">
				<h4>📋 Yaklaşan Görevler (7 gün)</h4>
				<span class="alert-panel__count" id="upcoming-tasks-count">0</span>
			</div>
			<div class="alert-panel__body" id="upcoming-tasks-list">
				<p class="empty-message">Yaklaşan görev yok</p>
			</div>
		</div>
	</div>

	<!-- Hızlı İşlemler -->
	<div class="dashboard-actions">
		<h3>Hızlı İşlemler</h3>
		<div class="button-group">
			<button class="btn btn-primary" id="quick-add-client">
				<span class="icon-add"></span> Müşteri Ekle
			</button>
			<button class="btn btn-secondary" id="quick-add-domain">
				<span class="icon-add"></span> Domain Ekle
			</button>
			<button class="btn btn-secondary" id="quick-add-hosting">
				<span class="icon-add"></span> Hosting Ekle
			</button>
			<button class="btn btn-secondary" id="quick-add-website">
				<span class="icon-add"></span> Website Ekle
			</button>
			<button class="btn btn-success" id="quick-add-invoice">
				<span class="icon-add"></span> Fatura Oluştur
			</button>
			<button class="btn btn-info" id="quick-add-payment">
				<span class="icon-add"></span> Ödeme Ekle
			</button>
			<button class="btn btn-purple" id="quick-add-project">
				<span class="icon-add"></span> Proje Ekle
			</button>
			<button class="btn btn-teal" id="quick-add-task">
				<span class="icon-add"></span> Görev Ekle
			</button>
		</div>
	</div>

	<div class="dashboard-recent">
		<h3>Son Eklenenler</h3>
		<div id="recent-clients" class="domaincontrol-list"></div>
	</div>
</div>

