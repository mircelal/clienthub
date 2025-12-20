<!-- Transactions Tab -->
<div id="transactions-tab" class="tab-content">
	<div id="transactions-list-view">
		<div class="domaincontrol-actions">
			<button class="btn btn-primary" id="add-transaction-btn">
				<span class="icon-add"></span> İşlem Ekle
			</button>
			<div class="filter-buttons">
				<button class="btn btn-filter active" data-filter="all">Tümü</button>
				<button class="btn btn-filter" data-filter="income">Gelirler</button>
				<button class="btn btn-filter" data-filter="expense">Giderler</button>
			</div>
		</div>
		<div id="transactions-list" class="domaincontrol-list"></div>
	</div>

	<div id="transaction-detail-view" style="display: none;">
		<div class="detail-header">
			<button class="btn btn-back" id="back-to-transactions-btn">← Geri</button>
			<h2 id="transaction-detail-title"></h2>
			<div class="detail-actions">
				<button class="btn btn-secondary" id="transaction-detail-edit-btn">Düzenle</button>
				<button class="btn btn-danger" id="transaction-detail-delete-btn">Sil</button>
			</div>
		</div>
		<div class="detail-content">
			<div class="detail-stats">
				<div class="stat-card">
					<div class="stat-card__label">Tür</div>
					<div class="stat-card__value" id="transaction-detail-type"></div>
				</div>
				<div class="stat-card">
					<div class="stat-card__label">Kategori</div>
					<div class="stat-card__value" id="transaction-detail-category"></div>
				</div>
				<div class="stat-card">
					<div class="stat-card__label">Tutar</div>
					<div class="stat-card__value" id="transaction-detail-amount"></div>
				</div>
				<div class="stat-card">
					<div class="stat-card__label">Tarih</div>
					<div class="stat-card__value" id="transaction-detail-date"></div>
				</div>
				<div class="stat-card">
					<div class="stat-card__label">Ödeme Yöntemi</div>
					<div class="stat-card__value" id="transaction-detail-payment-method"></div>
				</div>
				<div class="stat-card">
					<div class="stat-card__label">Müşteri</div>
					<div class="stat-card__value" id="transaction-detail-client"></div>
				</div>
			</div>

			<div class="detail-grid">
				<div class="detail-info-card">
					<h3>Açıklama</h3>
					<p id="transaction-detail-description">-</p>
				</div>
				<div class="detail-info-card">
					<h3>Proje</h3>
					<p id="transaction-detail-project">-</p>
				</div>
			</div>

			<div class="detail-info-card">
				<h3>Notlar</h3>
				<p id="transaction-detail-notes">-</p>
			</div>
		</div>
	</div>
</div>

