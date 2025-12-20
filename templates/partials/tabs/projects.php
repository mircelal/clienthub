<!-- Projects Tab -->
<div id="projects-tab" class="tab-content">
	<div id="projects-list-view">
		<div class="domaincontrol-actions">
			<button class="btn btn-primary" id="add-project-btn">
				<span class="icon-add"></span> Proje Ekle
			</button>
			<div class="filter-buttons">
				<button class="btn btn-filter active" data-filter="all">Tümü</button>
				<button class="btn btn-filter" data-filter="active">Aktif</button>
				<button class="btn btn-filter" data-filter="completed">Tamamlandı</button>
				<button class="btn btn-filter" data-filter="on_hold">Beklemede</button>
			</div>
		</div>
		<div id="projects-list" class="domaincontrol-list"></div>
	</div>
	
	<div id="project-detail-view" style="display: none;">
		<div class="detail-header">
			<button class="btn btn-back" id="back-to-projects-btn">← Geri</button>
			<h2 id="project-detail-name"></h2>
			<div class="detail-actions">
				<button class="btn btn-success" id="project-add-task-btn">✅ Görev Ekle</button>
				<button class="btn btn-info" id="project-add-item-btn">🔗 Öğe Bağla</button>
				<button class="btn btn-secondary" id="project-detail-edit-btn">Düzenle</button>
				<button class="btn btn-danger" id="project-detail-delete-btn">Sil</button>
			</div>
		</div>
		<div class="detail-content">
			<div class="detail-stats">
				<div class="stat-card"><div class="stat-card__label">Müşteri</div><div class="stat-card__value" id="project-detail-client"></div></div>
				<div class="stat-card"><div class="stat-card__label">Proje Türü</div><div class="stat-card__value" id="project-detail-type"></div></div>
				<div class="stat-card"><div class="stat-card__label">Durum</div><div class="stat-card__value" id="project-detail-status"></div></div>
				<div class="stat-card"><div class="stat-card__label">Başlangıç</div><div class="stat-card__value" id="project-detail-start"></div></div>
				<div class="stat-card"><div class="stat-card__label">Deadline</div><div class="stat-card__value" id="project-detail-deadline"></div></div>
				<div class="stat-card"><div class="stat-card__label">Bütçe</div><div class="stat-card__value" id="project-detail-budget"></div></div>
			</div>
			
			<div class="detail-grid">
				<div class="detail-info-card">
					<h3>📝 Proje Açıklaması</h3>
					<p id="project-detail-description"></p>
				</div>
				<div class="detail-info-card">
					<h3>📋 Notlar</h3>
					<p id="project-detail-notes"></p>
				</div>
			</div>
			
			<div class="detail-grid">
				<div class="detail-info-card">
					<h3>🔗 Bağlı Öğeler</h3>
					<p class="text-muted" style="font-size: 12px; margin-bottom: 10px;">Domain, hosting, website ve hizmetleri projeye bağlayın</p>
					<div id="project-linked-items"></div>
				</div>
				<div class="detail-info-card">
					<h3>💰 Finansal Bilgiler</h3>
					<div id="project-financials"></div>
				</div>
			</div>
			
			<div class="detail-info-card">
				<h3>✅ Görevler</h3>
				<div id="project-detail-tasks"></div>
			</div>
		</div>
	</div>
</div>

