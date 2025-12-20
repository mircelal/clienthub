<!-- Tasks Tab -->
<div id="tasks-tab" class="tab-content">
	<div id="tasks-list-view">
		<div class="domaincontrol-actions">
			<button class="btn btn-primary" id="add-task-btn">
				<span class="icon-add"></span> Görev Ekle
			</button>
			<div class="filter-buttons">
				<button class="btn btn-filter active" data-filter="all">Tümü</button>
				<button class="btn btn-filter" data-filter="todo">Yapılacak</button>
				<button class="btn btn-filter" data-filter="in_progress">Devam Ediyor</button>
				<button class="btn btn-filter" data-filter="done">Tamamlandı</button>
			</div>
		</div>
		<div id="tasks-list" class="domaincontrol-list"></div>
	</div>
	
	<div id="task-detail-view" style="display: none;">
		<div class="detail-header">
			<button class="btn btn-back" id="back-to-tasks-btn">← Geri</button>
			<h2 id="task-detail-title"></h2>
			<div class="detail-actions">
				<button class="btn btn-success" id="task-toggle-btn">✅ Durumu Değiştir</button>
				<button class="btn btn-secondary" id="task-detail-edit-btn">Düzenle</button>
				<button class="btn btn-danger" id="task-detail-delete-btn">Sil</button>
			</div>
		</div>
		<div class="detail-content">
			<div class="detail-stats">
				<div class="stat-card"><div class="stat-card__label">Proje</div><div class="stat-card__value" id="task-detail-project"></div></div>
				<div class="stat-card"><div class="stat-card__label">Durum</div><div class="stat-card__value" id="task-detail-status"></div></div>
				<div class="stat-card"><div class="stat-card__label">Öncelik</div><div class="stat-card__value" id="task-detail-priority"></div></div>
				<div class="stat-card"><div class="stat-card__label">Bitiş Tarihi</div><div class="stat-card__value" id="task-detail-due-date"></div></div>
			</div>
			<div class="detail-info-card">
				<h3>Açıklama</h3>
				<p id="task-detail-description"></p>
			</div>
		</div>
	</div>
</div>

