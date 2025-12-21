<!-- Hosting Package Modal -->
<div id="hosting-package-modal" class="modal">
	<div class="modal-content modal-large">
		<div class="modal-header">
			<h3 id="hosting-package-modal-title">Hosting Paketi Ekle</h3>
			<span class="modal-close" data-modal="hosting-package-modal">&times;</span>
		</div>
		<div class="modal-body">
			<form id="hosting-package-form">
				<input type="hidden" id="hosting-package-id" name="id">
				
				<div class="form-row">
					<div class="form-group">
						<label for="hpkg-name">Paket Adı *</label>
						<input type="text" id="hpkg-name" name="name" required class="form-control" placeholder="Eko, Premium, Pro...">
					</div>
					<div class="form-group">
						<label for="hpkg-provider">Sağlayıcı *</label>
						<input type="text" id="hpkg-provider" name="provider" required class="form-control" placeholder="Vultr, Hetzner, DigitalOcean...">
					</div>
				</div>
				
				<div class="form-row">
					<div class="form-group">
						<label for="hpkg-price-monthly">Aylık Fiyat</label>
						<input type="number" id="hpkg-price-monthly" name="priceMonthly" step="0.01" class="form-control" placeholder="9.99">
					</div>
					<div class="form-group">
						<label for="hpkg-price-yearly">Yıllık Fiyat</label>
						<input type="number" id="hpkg-price-yearly" name="priceYearly" step="0.01" class="form-control" placeholder="99.99">
					</div>
					<div class="form-group">
						<label for="hpkg-currency">Para Birimi</label>
						<select id="hpkg-currency" name="currency" class="form-control">
							<option value="USD">$ USD</option>
							<option value="EUR">€ EUR</option>
							<option value="TRY">₺ TRY</option>
							<option value="AZN">₼ AZN</option>
							<option value="GBP">£ GBP</option>
							<option value="RUB">₽ RUB</option>
						</select>
					</div>
				</div>
				
				<h4 style="margin-top: 20px; margin-bottom: 10px;">Kaynaklar</h4>
				
				<div class="form-row">
					<div class="form-group">
						<label for="hpkg-disk-space">Disk Alanı (GB)</label>
						<input type="number" id="hpkg-disk-space" name="diskSpaceGb" class="form-control" placeholder="10">
					</div>
					<div class="form-group">
						<label for="hpkg-traffic">Trafik (GB)</label>
						<input type="number" id="hpkg-traffic" name="trafficGb" class="form-control" placeholder="100">
					</div>
					<div class="form-group">
						<label for="hpkg-bandwidth-unlimited">Sınırsız Bant Genişliği</label>
						<select id="hpkg-bandwidth-unlimited" name="bandwidthUnlimited" class="form-control">
							<option value="0">Hayır</option>
							<option value="1">Evet</option>
						</select>
					</div>
				</div>
				
				<div class="form-row">
					<div class="form-group">
						<label for="hpkg-domains-allowed">Domain Sayısı</label>
						<input type="number" id="hpkg-domains-allowed" name="domainsAllowed" class="form-control" value="1" min="1">
					</div>
					<div class="form-group">
						<label for="hpkg-databases-allowed">Veritabanı Sayısı</label>
						<input type="number" id="hpkg-databases-allowed" name="databasesAllowed" class="form-control" value="0" min="0">
					</div>
					<div class="form-group">
						<label for="hpkg-email-accounts">E-posta Hesabı</label>
						<input type="number" id="hpkg-email-accounts" name="emailAccounts" class="form-control" value="0" min="0">
					</div>
				</div>
				
				<div class="form-row">
					<div class="form-group">
						<label for="hpkg-ftp-accounts">FTP Hesabı</label>
						<input type="number" id="hpkg-ftp-accounts" name="ftpAccounts" class="form-control" value="0" min="0">
					</div>
					<div class="form-group">
						<label for="hpkg-ssl-included">SSL Dahil</label>
						<select id="hpkg-ssl-included" name="sslIncluded" class="form-control">
							<option value="0">Hayır</option>
							<option value="1">Evet</option>
						</select>
					</div>
					<div class="form-group">
						<label for="hpkg-backup-included">Yedekleme Dahil</label>
						<select id="hpkg-backup-included" name="backupIncluded" class="form-control">
							<option value="0">Hayır</option>
							<option value="1">Evet</option>
						</select>
					</div>
				</div>
				
				<div class="form-group">
					<label for="hpkg-description">Açıklama</label>
					<textarea id="hpkg-description" name="description" class="form-control" rows="3" placeholder="Paket açıklaması..."></textarea>
				</div>
				
				<div class="form-group">
					<label for="hpkg-is-active">Durum</label>
					<select id="hpkg-is-active" name="isActive" class="form-control">
						<option value="1">Aktif</option>
						<option value="0">Pasif</option>
					</select>
				</div>
				
				<div class="form-actions">
					<button type="button" class="btn btn-secondary modal-cancel" data-modal="hosting-package-modal">İptal</button>
					<button type="submit" class="btn btn-primary">Kaydet</button>
				</div>
			</form>
		</div>
	</div>
</div>

