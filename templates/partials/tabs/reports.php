<!-- Reports Tab -->
<div id="reports-tab" class="tab-content">
	<!-- Vue.js Reports Component -->
	<div id="vue-reports-container"></div>
		<div class="reports-header">
			<h2>📊 Raporlar ve Analitik</h2>
			<p class="reports-subtitle">İş performansınızı analiz edin ve kararlarınızı veriye dayalı alın</p>
		</div>
		
		<!-- Tarih Filtreleri -->
		<div class="reports-filters">
			<div class="filter-card">
				<div class="form-row">
					<div class="form-group">
						<label for="report-period">Dönem</label>
						<select id="report-period" class="form-control">
							<option value="month">Bu Ay</option>
							<option value="quarter">Bu Çeyrek</option>
							<option value="year">Bu Yıl</option>
							<option value="custom">Özel Tarih</option>
						</select>
					</div>
					<div class="form-group" id="custom-date-group" style="display: none;">
						<label for="report-start-date">Başlangıç</label>
						<input type="date" id="report-start-date" class="form-control">
					</div>
					<div class="form-group" id="custom-date-group-end" style="display: none;">
						<label for="report-end-date">Bitiş</label>
						<input type="date" id="report-end-date" class="form-control">
					</div>
					<div class="form-group">
						<label>&nbsp;</label>
						<button class="btn btn-primary" id="apply-filter-btn">
							<span class="icon-filter"></span> Filtrele
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Özet İstatistikler -->
		<div class="report-section">
			<div class="section-header">
				<h3>📈 Özet İstatistikler</h3>
				<p class="section-description">Genel iş performansı göstergeleri</p>
			</div>
			<div class="report-cards">
				<div class="stat-card stat-card--success">
					<div class="stat-card__icon">💰</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-total-income">0.00 ₼</div>
						<div class="stat-card__label">Toplam Gelir</div>
						<div class="stat-card__subtitle">Tüm zamanlar</div>
					</div>
				</div>
				<div class="stat-card stat-card--info">
					<div class="stat-card__icon">📅</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-monthly-income">0.00 ₼</div>
						<div class="stat-card__label">Aylık Gelir</div>
						<div class="stat-card__subtitle">Bu ay</div>
					</div>
				</div>
				<div class="stat-card stat-card--warning">
					<div class="stat-card__icon">⏳</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-pending-income">0.00 ₼</div>
						<div class="stat-card__label">Bekleyen Ödemeler</div>
						<div class="stat-card__subtitle">Ödenmemiş faturalar</div>
					</div>
				</div>
				<div class="stat-card stat-card--primary">
					<div class="stat-card__icon">👥</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-total-clients">0</div>
						<div class="stat-card__label">Toplam Müşteri</div>
						<div class="stat-card__subtitle" id="report-active-clients-text">0 aktif</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Gelir Trendi -->
		<div class="report-section">
			<div class="section-header">
				<h3>📊 Gelir Trendi</h3>
				<p class="section-description">Aylık gelir trendi ve karşılaştırmalar</p>
			</div>
			<div class="report-chart-container">
				<canvas id="income-trend-chart"></canvas>
			</div>
		</div>

		<!-- Fatura Durumu -->
		<div class="report-section">
			<div class="section-header">
				<h3>📄 Fatura Durumu</h3>
				<p class="section-description">Fatura durumları ve ödeme takibi</p>
			</div>
			<div class="report-cards">
				<div class="stat-card stat-card--primary">
					<div class="stat-card__icon">📋</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-total-invoices">0</div>
						<div class="stat-card__label">Toplam Fatura</div>
					</div>
				</div>
				<div class="stat-card stat-card--success">
					<div class="stat-card__icon">✅</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-paid-invoices">0</div>
						<div class="stat-card__label">Ödenen</div>
					</div>
				</div>
				<div class="stat-card stat-card--danger">
					<div class="stat-card__icon">⚠️</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-overdue-invoices">0</div>
						<div class="stat-card__label">Gecikmiş</div>
					</div>
				</div>
				<div class="stat-card stat-card--warning">
					<div class="stat-card__icon">📤</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-pending-invoices">0</div>
						<div class="stat-card__label">Bekleyen</div>
					</div>
				</div>
			</div>
			<div class="report-chart-container">
				<canvas id="invoice-status-chart"></canvas>
			</div>
		</div>

		<!-- Müşteri Analizi -->
		<div class="report-section">
			<div class="section-header">
				<h3>👥 Müşteri Analizi</h3>
				<p class="section-description">Müşteri istatistikleri ve en çok gelir getirenler</p>
			</div>
			<div class="report-cards">
				<div class="stat-card stat-card--success">
					<div class="stat-card__icon">💼</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-active-clients">0</div>
						<div class="stat-card__label">Aktif Müşteriler</div>
					</div>
				</div>
				<div class="stat-card stat-card--info">
					<div class="stat-card__icon">📊</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-avg-client-income">0.00 ₼</div>
						<div class="stat-card__label">Ortalama Müşteri Geliri</div>
					</div>
				</div>
			</div>
			<div class="report-table-container">
				<div class="table-header">
					<h4>🏆 En Çok Gelir Getiren Müşteriler</h4>
				</div>
				<div id="top-clients-list" class="report-list"></div>
			</div>
			<div class="report-chart-container">
				<canvas id="top-clients-chart"></canvas>
			</div>
		</div>

		<!-- Proje Durumu -->
		<div class="report-section">
			<div class="section-header">
				<h3>📁 Proje Durumu</h3>
				<p class="section-description">Proje durumları ve ilerleme takibi</p>
			</div>
			<div class="report-cards">
				<div class="stat-card stat-card--purple">
					<div class="stat-card__icon">🚀</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-active-projects">0</div>
						<div class="stat-card__label">Aktif Projeler</div>
					</div>
				</div>
				<div class="stat-card stat-card--success">
					<div class="stat-card__icon">✅</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-completed-projects">0</div>
						<div class="stat-card__label">Tamamlanan</div>
					</div>
				</div>
				<div class="stat-card stat-card--warning">
					<div class="stat-card__icon">⏸️</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-onhold-projects">0</div>
						<div class="stat-card__label">Beklemede</div>
					</div>
				</div>
			</div>
			<div class="report-chart-container">
				<canvas id="project-status-chart"></canvas>
			</div>
		</div>

		<!-- Hizmet Analizi -->
		<div class="report-section">
			<div class="section-header">
				<h3>🛠️ Hizmet Analizi</h3>
				<p class="section-description">Hizmet türü bazlı gelir ve yenileme takibi</p>
			</div>
			<div class="report-cards">
				<div class="stat-card stat-card--info">
					<div class="stat-card__icon">⏰</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-expiring-soon">0</div>
						<div class="stat-card__label">Yakında Bitecek</div>
						<div class="stat-card__subtitle">30 gün içinde</div>
					</div>
				</div>
				<div class="stat-card stat-card--danger">
					<div class="stat-card__icon">🔴</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-expired-services">0</div>
						<div class="stat-card__label">Süresi Dolmuş</div>
						<div class="stat-card__subtitle">Acil müdahale gerekli</div>
					</div>
				</div>
				<div class="stat-card stat-card--success">
					<div class="stat-card__icon">🔄</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-active-services">0</div>
						<div class="stat-card__label">Aktif Hizmetler</div>
					</div>
				</div>
			</div>
			<div class="report-table-container">
				<div class="table-header">
					<h4>⏳ Yakında Bitecek Hizmetler</h4>
				</div>
				<div id="expiring-services-list" class="report-list"></div>
			</div>
			<div class="report-chart-container">
				<canvas id="service-type-income-chart"></canvas>
			</div>
		</div>

		<!-- Ödeme Trendi -->
		<div class="report-section">
			<div class="section-header">
				<h3>💳 Ödeme Trendi</h3>
				<p class="section-description">Aylık ödeme akışı ve tahsilat analizi</p>
			</div>
			<div class="report-chart-container">
				<canvas id="payment-trend-chart"></canvas>
			</div>
		</div>

		<!-- Gelir/Gider Analizi -->
		<div class="report-section">
			<div class="section-header">
				<h3>💰 Gelir/Gider Analizi</h3>
				<p class="section-description">Gelir ve gider karşılaştırması, kategori bazlı analiz</p>
			</div>
			<div class="report-cards">
				<div class="stat-card stat-card--success">
					<div class="stat-card__icon">📈</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-total-transaction-income">0.00 ₼</div>
						<div class="stat-card__label">Toplam Gelir</div>
						<div class="stat-card__subtitle">Tüm işlemler</div>
					</div>
				</div>
				<div class="stat-card stat-card--danger">
					<div class="stat-card__icon">📉</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-total-transaction-expense">0.00 ₼</div>
						<div class="stat-card__label">Toplam Gider</div>
						<div class="stat-card__subtitle">Tüm işlemler</div>
					</div>
				</div>
				<div class="stat-card stat-card--primary">
					<div class="stat-card__icon">💵</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-net-transaction">0.00 ₼</div>
						<div class="stat-card__label">Net Kar/Zarar</div>
						<div class="stat-card__subtitle">Gelir - Gider</div>
					</div>
				</div>
			</div>
			<div class="report-chart-container">
				<canvas id="income-expense-chart"></canvas>
			</div>
			<div class="report-chart-container">
				<canvas id="expense-category-chart"></canvas>
			</div>
		</div>

		<!-- Nakit Akışı -->
		<div class="report-section">
			<div class="section-header">
				<h3>💸 Nakit Akışı</h3>
				<p class="section-description">Aylık gelir ve gider akışı</p>
			</div>
			<div class="report-chart-container">
				<canvas id="cash-flow-chart"></canvas>
			</div>
		</div>

		<!-- Borç/Alacak Durumu -->
		<div class="report-section">
			<div class="section-header">
				<h3>💳 Borç/Alacak Durumu</h3>
				<p class="section-description">Toplam borçlar, alacaklar ve ödeme durumu</p>
			</div>
			<div class="report-cards">
				<div class="stat-card stat-card--danger">
					<div class="stat-card__icon">💸</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-total-debts">0.00 ₼</div>
						<div class="stat-card__label">Toplam Borçlar</div>
						<div class="stat-card__subtitle">Ödenmemiş borçlar</div>
					</div>
				</div>
				<div class="stat-card stat-card--success">
					<div class="stat-card__icon">💰</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-total-credits">0.00 ₼</div>
						<div class="stat-card__label">Toplam Alacaklar</div>
						<div class="stat-card__subtitle">Tahsil edilmemiş</div>
					</div>
				</div>
				<div class="stat-card stat-card--warning">
					<div class="stat-card__icon">⏰</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-upcoming-debt-payments">0</div>
						<div class="stat-card__label">Yaklaşan Ödemeler</div>
						<div class="stat-card__subtitle">30 gün içinde</div>
					</div>
				</div>
				<div class="stat-card stat-card--danger">
					<div class="stat-card__icon">🚨</div>
					<div class="stat-card__content">
						<div class="stat-card__value" id="report-overdue-debts">0</div>
						<div class="stat-card__label">Gecikmiş Borçlar</div>
						<div class="stat-card__subtitle">Acil ödeme gerekli</div>
					</div>
				</div>
			</div>
			<div class="report-chart-container">
				<canvas id="debt-status-chart"></canvas>
			</div>
		</div>
	</div>
</div>
