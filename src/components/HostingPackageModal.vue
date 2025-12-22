<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content modal-large">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ editingPackage ? translate('domaincontrol', 'Edit Package') : translate('domaincontrol', 'Add Package') }}
				</h3>
				<button class="modal-close" @click="closeModal">&times;</button>
			</div>
			<div class="modal-body">
				<form @submit.prevent="savePackage">
					<div class="form-row">
						<div class="form-group">
							<label for="hpkg-name">{{ translate('domaincontrol', 'Package Name') }} *</label>
							<input
								type="text"
								id="hpkg-name"
								v-model="formData.name"
								required
								class="form-control"
								:placeholder="translate('domaincontrol', 'Eko, Premium, Pro...')"
							/>
						</div>
						<div class="form-group">
							<label for="hpkg-provider">{{ translate('domaincontrol', 'Provider') }} *</label>
							<input
								type="text"
								id="hpkg-provider"
								v-model="formData.provider"
								required
								class="form-control"
								:placeholder="translate('domaincontrol', 'Vultr, Hetzner, DigitalOcean...')"
							/>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="hpkg-price-monthly">{{ translate('domaincontrol', 'Monthly Price') }}</label>
							<input
								type="number"
								id="hpkg-price-monthly"
								v-model="formData.priceMonthly"
								step="0.01"
								class="form-control"
								placeholder="9.99"
							/>
						</div>
						<div class="form-group">
							<label for="hpkg-price-yearly">{{ translate('domaincontrol', 'Yearly Price') }}</label>
							<input
								type="number"
								id="hpkg-price-yearly"
								v-model="formData.priceYearly"
								step="0.01"
								class="form-control"
								placeholder="99.99"
							/>
						</div>
						<div class="form-group">
							<label for="hpkg-currency">{{ translate('domaincontrol', 'Currency') }}</label>
							<select id="hpkg-currency" v-model="formData.currency" class="form-control">
								<option value="USD">$ USD</option>
								<option value="EUR">€ EUR</option>
								<option value="TRY">₺ TRY</option>
								<option value="AZN">₼ AZN</option>
								<option value="GBP">£ GBP</option>
								<option value="RUB">₽ RUB</option>
							</select>
						</div>
					</div>
					<h4 style="margin-top: 20px; margin-bottom: 10px; color: var(--color-main-text);">{{ translate('domaincontrol', 'Resources') }}</h4>
					<div class="form-row">
						<div class="form-group">
							<label for="hpkg-disk-space">{{ translate('domaincontrol', 'Disk Space') }} (GB)</label>
							<input
								type="number"
								id="hpkg-disk-space"
								v-model="formData.diskSpaceGb"
								class="form-control"
								placeholder="10"
							/>
						</div>
						<div class="form-group">
							<label for="hpkg-traffic">{{ translate('domaincontrol', 'Traffic') }} (GB)</label>
							<input
								type="number"
								id="hpkg-traffic"
								v-model="formData.trafficGb"
								class="form-control"
								placeholder="100"
							/>
						</div>
						<div class="form-group">
							<label for="hpkg-bandwidth-unlimited">{{ translate('domaincontrol', 'Unlimited Bandwidth') }}</label>
							<select id="hpkg-bandwidth-unlimited" v-model="formData.bandwidthUnlimited" class="form-control">
								<option :value="0">{{ translate('domaincontrol', 'No') }}</option>
								<option :value="1">{{ translate('domaincontrol', 'Yes') }}</option>
							</select>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="hpkg-domains-allowed">{{ translate('domaincontrol', 'Domains Allowed') }}</label>
							<input
								type="number"
								id="hpkg-domains-allowed"
								v-model="formData.domainsAllowed"
								class="form-control"
								:value="formData.domainsAllowed || 1"
								min="1"
							/>
						</div>
						<div class="form-group">
							<label for="hpkg-databases-allowed">{{ translate('domaincontrol', 'Databases Allowed') }}</label>
							<input
								type="number"
								id="hpkg-databases-allowed"
								v-model="formData.databasesAllowed"
								class="form-control"
								:value="formData.databasesAllowed || 0"
								min="0"
							/>
						</div>
						<div class="form-group">
							<label for="hpkg-email-accounts">{{ translate('domaincontrol', 'Email Accounts') }}</label>
							<input
								type="number"
								id="hpkg-email-accounts"
								v-model="formData.emailAccounts"
								class="form-control"
								:value="formData.emailAccounts || 0"
								min="0"
							/>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="hpkg-ftp-accounts">{{ translate('domaincontrol', 'FTP Accounts') }}</label>
							<input
								type="number"
								id="hpkg-ftp-accounts"
								v-model="formData.ftpAccounts"
								class="form-control"
								:value="formData.ftpAccounts || 0"
								min="0"
							/>
						</div>
						<div class="form-group">
							<label for="hpkg-ssl-included">{{ translate('domaincontrol', 'SSL Included') }}</label>
							<select id="hpkg-ssl-included" v-model="formData.sslIncluded" class="form-control">
								<option :value="0">{{ translate('domaincontrol', 'No') }}</option>
								<option :value="1">{{ translate('domaincontrol', 'Yes') }}</option>
							</select>
						</div>
						<div class="form-group">
							<label for="hpkg-backup-included">{{ translate('domaincontrol', 'Backup Included') }}</label>
							<select id="hpkg-backup-included" v-model="formData.backupIncluded" class="form-control">
								<option :value="0">{{ translate('domaincontrol', 'No') }}</option>
								<option :value="1">{{ translate('domaincontrol', 'Yes') }}</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="hpkg-description">{{ translate('domaincontrol', 'Description') }}</label>
						<textarea
							id="hpkg-description"
							v-model="formData.description"
							class="form-control"
							rows="3"
							:placeholder="translate('domaincontrol', 'Package description...')"
						></textarea>
					</div>
					<div class="form-group">
						<label for="hpkg-is-active">{{ translate('domaincontrol', 'Status') }}</label>
						<select id="hpkg-is-active" v-model="formData.isActive" class="form-control">
							<option :value="1">{{ translate('domaincontrol', 'Active') }}</option>
							<option :value="0">{{ translate('domaincontrol', 'Inactive') }}</option>
						</select>
					</div>
					<div class="form-actions">
						<button type="button" class="button-vue button-vue--secondary" @click="closeModal">
							{{ translate('domaincontrol', 'Cancel') }}
						</button>
						<button type="submit" class="button-vue button-vue--primary" :disabled="saving">
							{{ saving ? translate('domaincontrol', 'Saving...') : translate('domaincontrol', 'Save') }}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'

export default {
	name: 'HostingPackageModal',
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		package: {
			type: Object,
			default: null,
		},
	},
	emits: ['close', 'saved'],
	data() {
		return {
			isOpen: false,
			editingPackage: null,
			formData: {
				id: null,
				name: '',
				provider: '',
				priceMonthly: null,
				priceYearly: null,
				currency: 'USD',
				diskSpaceGb: null,
				trafficGb: null,
				bandwidthUnlimited: 0,
				domainsAllowed: 1,
				databasesAllowed: 0,
				emailAccounts: 0,
				ftpAccounts: 0,
				sslIncluded: 0,
				backupIncluded: 0,
				description: '',
				isActive: 1,
			},
			saving: false,
		}
	},
	watch: {
		open(newVal) {
			this.isOpen = newVal
			if (newVal) {
				this.resetForm()
				if (this.package) {
					this.editingPackage = this.package
					this.formData = {
						id: this.package.id,
						name: this.package.name || '',
						provider: this.package.provider || '',
						priceMonthly: this.package.priceMonthly || null,
						priceYearly: this.package.priceYearly || null,
						currency: this.package.currency || 'USD',
						diskSpaceGb: this.package.diskSpaceGb || null,
						trafficGb: this.package.trafficGb || null,
						bandwidthUnlimited: this.package.bandwidthUnlimited || 0,
						domainsAllowed: this.package.domainsAllowed || 1,
						databasesAllowed: this.package.databasesAllowed || 0,
						emailAccounts: this.package.emailAccounts || 0,
						ftpAccounts: this.package.ftpAccounts || 0,
						sslIncluded: this.package.sslIncluded || 0,
						backupIncluded: this.package.backupIncluded || 0,
						description: this.package.description || '',
						isActive: this.package.isActive !== undefined ? this.package.isActive : 1,
					}
				} else {
					this.editingPackage = null
				}
			}
		},
	},
	methods: {
		translate(appId, text, vars) {
			try {
				if (typeof window !== 'undefined') {
					if (typeof OC !== 'undefined' && OC.L10n && typeof OC.L10n.translate === 'function') {
						const translated = OC.L10n.translate(appId, text, vars || {})
						if (translated && translated !== text) {
							return translated
						}
					}
					if (typeof window.t === 'function') {
						const translated = window.t(appId, text, vars || {})
						if (translated && translated !== text) {
							return translated
						}
					}
				}
			} catch (e) {
				console.warn('Translation error:', e)
			}

			const translations = {
				'Add Package': 'Paket Ekle',
				'Edit Package': 'Paket Düzenle',
				'Package Name': 'Paket Adı',
				'Eko, Premium, Pro...': 'Eko, Premium, Pro...',
				'Provider': 'Sağlayıcı',
				'Vultr, Hetzner, DigitalOcean...': 'Vultr, Hetzner, DigitalOcean...',
				'Monthly Price': 'Aylık Fiyat',
				'Yearly Price': 'Yıllık Fiyat',
				'Currency': 'Para Birimi',
				'Resources': 'Kaynaklar',
				'Disk Space': 'Disk Alanı',
				'Traffic': 'Trafik',
				'Unlimited Bandwidth': 'Sınırsız Bant Genişliği',
				'No': 'Hayır',
				'Yes': 'Evet',
				'Domains Allowed': 'Domain Sayısı',
				'Databases Allowed': 'Veritabanı Sayısı',
				'Email Accounts': 'E-posta Hesabı',
				'FTP Accounts': 'FTP Hesabı',
				'SSL Included': 'SSL Dahil',
				'Backup Included': 'Yedekleme Dahil',
				'Description': 'Açıklama',
				'Package description...': 'Paket açıklaması...',
				'Status': 'Durum',
				'Active': 'Aktif',
				'Inactive': 'Pasif',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Package name and provider are required': 'Paket adı ve sağlayıcı zorunludur',
				'Error saving package': 'Paket kaydedilirken hata oluştu',
			}

			return translations[text] || text
		},
		resetForm() {
			this.formData = {
				id: null,
				name: '',
				provider: '',
				priceMonthly: null,
				priceYearly: null,
				currency: 'USD',
				diskSpaceGb: null,
				trafficGb: null,
				bandwidthUnlimited: 0,
				domainsAllowed: 1,
				databasesAllowed: 0,
				emailAccounts: 0,
				ftpAccounts: 0,
				sslIncluded: 0,
				backupIncluded: 0,
				description: '',
				isActive: 1,
			}
			this.editingPackage = null
		},
		closeModal() {
			this.$emit('close')
			this.resetForm()
		},
		async savePackage() {
			if (!this.formData.name || !this.formData.provider) {
				alert(this.translate('domaincontrol', 'Package name and provider are required'))
				return
			}

			this.saving = true
			try {
				const data = {
					name: this.formData.name,
					provider: this.formData.provider,
					priceMonthly: this.formData.priceMonthly || null,
					priceYearly: this.formData.priceYearly || null,
					currency: this.formData.currency,
					diskSpaceGb: this.formData.diskSpaceGb || null,
					trafficGb: this.formData.trafficGb || null,
					bandwidthUnlimited: this.formData.bandwidthUnlimited || 0,
					domainsAllowed: this.formData.domainsAllowed || 1,
					databasesAllowed: this.formData.databasesAllowed || 0,
					emailAccounts: this.formData.emailAccounts || 0,
					ftpAccounts: this.formData.ftpAccounts || 0,
					sslIncluded: this.formData.sslIncluded || 0,
					backupIncluded: this.formData.backupIncluded || 0,
					description: this.formData.description || '',
					isActive: this.formData.isActive || 1,
				}

				if (this.editingPackage) {
					await api.hostingPackages.update(this.editingPackage.id, data)
				} else {
					await api.hostingPackages.create(data)
				}

				this.closeModal()
				this.$emit('saved')
			} catch (error) {
				console.error('Error saving package:', error)
				alert(this.translate('domaincontrol', 'Error saving package'))
			} finally {
				this.saving = false
			}
		},
	},
}
</script>

<style scoped>
.modal-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.5);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 10000;
	padding: 20px;
}

.modal-content {
	background-color: var(--color-main-background);
	border-radius: var(--border-radius-container);
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
	max-width: 500px;
	width: 100%;
	max-height: 90vh;
	overflow-y: auto;
	display: flex;
	flex-direction: column;
}

.modal-content.modal-large {
	max-width: 800px;
}

.modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20px;
	border-bottom: 1px solid var(--color-border);
}

.modal-title {
	margin: 0;
	font-size: 20px;
	font-weight: 600;
	color: var(--color-main-text);
}

.modal-close {
	background: none;
	border: none;
	font-size: 28px;
	color: var(--color-text-maxcontrast);
	cursor: pointer;
	padding: 0;
	width: 32px;
	height: 32px;
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: var(--border-radius-small);
	transition: background-color 0.2s;
}

.modal-close:hover {
	background-color: var(--color-background-hover);
}

.modal-body {
	padding: 20px;
}

.form-row {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 16px;
	margin-bottom: 16px;
}

.form-group {
	margin-bottom: 16px;
}

.form-group label {
	display: block;
	margin-bottom: 6px;
	font-weight: 500;
	color: var(--color-main-text);
}

.form-control {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-size: 14px;
	box-sizing: border-box;
}

.form-control:focus {
	outline: none;
	border-color: var(--color-primary);
}

.form-control::placeholder {
	color: var(--color-text-maxcontrast);
}

.form-actions {
	display: flex;
	justify-content: flex-end;
	gap: 12px;
	margin-top: 24px;
	padding-top: 20px;
	border-top: 1px solid var(--color-border);
}

.button-vue {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 8px 16px;
	border-radius: var(--border-radius-small);
	font-size: 14px;
	cursor: pointer;
	transition: all 0.2s ease;
	text-decoration: none;
	white-space: nowrap;
	box-sizing: border-box;
	border: 1px solid transparent;
}

.button-vue:disabled {
	opacity: 0.6;
	cursor: not-allowed;
}

.button-vue--primary {
	background-color: var(--color-primary);
	color: var(--color-primary-text);
}

.button-vue--primary:hover:not(:disabled) {
	background-color: var(--color-primary-hover);
}

.button-vue--secondary {
	background-color: transparent;
	color: var(--color-main-text);
	border-color: var(--color-border);
}

.button-vue--secondary:hover:not(:disabled) {
	background-color: var(--color-background-hover);
}
</style>

