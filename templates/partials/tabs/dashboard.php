<div id="dashboard-tab" class="tab-content active">
	<div class="section">
		<h2>Özet Bilgiler</h2>
		<div class="dashboard-grid">
			<div class="stat-card">
				<div class="stat-card__label">Müşteriler</div>
				<div class="stat-card__value" id="stat-clients">0</div>
			</div>
			<div class="stat-card">
				<div class="stat-card__label">Domainler</div>
				<div class="stat-card__value" id="stat-domains">0</div>
			</div>
			<div class="stat-card">
				<div class="stat-card__label">Hosting</div>
				<div class="stat-card__value" id="stat-hostings">0</div>
			</div>
			<div class="stat-card">
				<div class="stat-card__label">Websiteler</div>
				<div class="stat-card__value" id="stat-websites">0</div>
			</div>
			<div class="stat-card">
				<div class="stat-card__label">Aktif Projeler</div>
				<div class="stat-card__value" id="stat-projects">0</div>
			</div>
			<div class="stat-card">
				<div class="stat-card__label">Bekleyen Görevler</div>
				<div class="stat-card__value" id="stat-tasks">0</div>
			</div>
			<div class="stat-card">
				<div class="stat-card__label">Ödenmemiş Fatura</div>
				<div class="stat-card__value" id="stat-unpaid-invoices">0</div>
			</div>
			<div class="stat-card">
				<div class="stat-card__label">Bu Ay Gelir</div>
				<div class="stat-card__value" id="stat-monthly-income">0</div>
			</div>
		</div>
	</div>

	<div class="section">
		<h2>Hızlı İşlemler</h2>
		<div class="button-group">
			<button class="btn btn-primary" id="quick-add-client">+ Müşteri Ekle</button>
			<button class="btn btn-primary" id="quick-add-domain">+ Domain Ekle</button>
			<button class="btn btn-primary" id="quick-add-hosting">+ Hosting Ekle</button>
			<button class="btn btn-primary" id="quick-add-invoice">+ Fatura Oluştur</button>
		</div>
	</div>

	<div class="section">
		<div class="dashboard-alerts">
			<div class="alert-panel">
				<header>
					<h3>🚨 Geciken Ödemeler</h3>
					<span id="overdue-count" class="badge">0</span>
				</header>
				<div id="overdue-invoices-list" class="alert-panel__body">
					<p class="empty-message">Geciken ödeme yok</p>
				</div>
			</div>

			<div class="alert-panel">
				<header>
					<h3>⏰ Yaklaşan Ödemeler</h3>
					<span id="upcoming-count" class="badge">0</span>
				</header>
				<div id="upcoming-payments-list" class="alert-panel__body">
					<p class="empty-message">Yaklaşan ödeme yok</p>
				</div>
			</div>
		</div>
	</div>

	<div class="section">
		<h2>Son Eklenen Müşteriler</h2>
		<div id="recent-clients" class="domaincontrol-list"></div>
	</div>
</div>