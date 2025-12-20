<!-- Debts Tab -->
<div id="debts-tab" class="tab-content">
	<div id="debts-list-view">
		<div class="domaincontrol-actions">
			<button class="btn btn-primary" id="add-debt-btn">
				<span class="icon-add"></span> Borç/Alacak Ekle
			</button>
			<div class="filter-buttons">
				<button class="btn btn-filter active" data-filter="all">Tümü</button>
				<button class="btn btn-filter" data-filter="debt">Borçlarım</button>
				<button class="btn btn-filter" data-filter="credit">Alacaklarım</button>
				<button class="btn btn-filter" data-filter="upcoming">Yaklaşan Ödemeler</button>
				<button class="btn btn-filter" data-filter="overdue">Gecikmiş</button>
			</div>
		</div>
		<div id="debts-list" class="domaincontrol-list"></div>
	</div>

	<div id="debt-detail-view" style="display: none;">
		<div class="detail-header">
			<button class="btn btn-back" id="back-to-debts-btn">← Geri</button>
			<h2 id="debt-detail-title"></h2>
			<div class="detail-actions">
				<button class="btn btn-success" id="debt-add-payment-btn">💳 Ödeme Ekle</button>
				<button class="btn btn-secondary" id="debt-detail-edit-btn">Düzenle</button>
				<button class="btn btn-danger" id="debt-detail-delete-btn">Sil</button>
			</div>
		</div>
		<div class="detail-content">
			<div class="detail-stats">
				<div class="stat-card">
					<div class="stat-card__label">Tür</div>
					<div class="stat-card__value" id="debt-detail-type"></div>
				</div>
				<div class="stat-card">
					<div class="stat-card__label">Borç Türü</div>
					<div class="stat-card__value" id="debt-detail-debt-type"></div>
				</div>
				<div class="stat-card">
					<div class="stat-card__label">Toplam Tutar</div>
					<div class="stat-card__value" id="debt-detail-total"></div>
				</div>
				<div class="stat-card">
					<div class="stat-card__label">Ödenen</div>
					<div class="stat-card__value" id="debt-detail-paid"></div>
				</div>
				<div class="stat-card">
					<div class="stat-card__label">Kalan</div>
					<div class="stat-card__value" id="debt-detail-remaining"></div>
				</div>
				<div class="stat-card">
					<div class="stat-card__label">Durum</div>
					<div class="stat-card__value" id="debt-detail-status"></div>
				</div>
			</div>

			<!-- Payment Progress -->
			<div class="detail-info-card" style="margin-bottom: 20px;">
				<div id="debt-payment-progress"></div>
			</div>

			<div class="detail-grid">
				<div class="detail-info-card">
					<h3>Borç/Alacak Bilgileri</h3>
					<p><strong>Alacaklı/Borçlu:</strong> <span id="debt-detail-creditor-debtor"></span></p>
					<p><strong>Müşteri:</strong> <span id="debt-detail-client">-</span></p>
					<p><strong>Başlangıç Tarihi:</strong> <span id="debt-detail-start-date"></span></p>
					<p><strong>Vade Tarihi:</strong> <span id="debt-detail-due-date">-</span></p>
					<p><strong>Sonraki Ödeme:</strong> <span id="debt-detail-next-payment">-</span></p>
					<p><strong>Ödeme Sıklığı:</strong> <span id="debt-detail-frequency">-</span></p>
					<p><strong>Ödeme Tutarı:</strong> <span id="debt-detail-payment-amount">-</span></p>
					<p><strong>Faiz Oranı:</strong> <span id="debt-detail-interest">-</span></p>
				</div>
				<div class="detail-info-card">
					<h3>Açıklama</h3>
					<p id="debt-detail-description">-</p>
				</div>
			</div>

			<div class="detail-info-card">
				<h3>Ödeme Geçmişi</h3>
				<div id="debt-detail-payments"></div>
			</div>

			<div class="detail-info-card">
				<h3>Notlar</h3>
				<p id="debt-detail-notes">-</p>
			</div>
		</div>
	</div>
</div>

