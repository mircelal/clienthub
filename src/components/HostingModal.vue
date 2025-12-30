<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content modal-large">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ editingHosting ? translate('domaincontrol', 'Edit Hosting') : translate('domaincontrol', 'Add Hosting') }}
				</h3>
				<button class="modal-close" @click="closeModal">&times;</button>
			</div>
			<div class="modal-body">
				<form @submit.prevent="saveHosting">
					<div class="form-row">
						<div class="form-group">
							<label for="hosting-client-id">{{ translate('domaincontrol', 'Client') }} *</label>
							<select id="hosting-client-id" v-model="formData.clientId" required class="form-control">
								<option value="">{{ translate('domaincontrol', 'Select Client') }}</option>
								<option v-for="client in clients" :key="client.id" :value="client.id">
									{{ client.name }}
								</option>
							</select>
						</div>
						<div class="form-group">
							<label for="hosting-package-id">{{ translate('domaincontrol', 'Hosting Package') }}</label>
							<select id="hosting-package-id" v-model="formData.packageId" class="form-control" @change="onPackageSelected">
								<option value="">{{ translate('domaincontrol', 'Select Package (Optional)') }}</option>
								<option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
									{{ pkg.name }} - {{ pkg.provider }}
								</option>
							</select>
							<small class="form-text">{{ translate('domaincontrol', 'Selecting a package will auto-fill information') }}</small>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="hosting-provider">{{ translate('domaincontrol', 'Provider') }} *</label>
							<input
								type="text"
								id="hosting-provider"
								v-model="formData.provider"
								required
								class="form-control"
								:placeholder="translate('domaincontrol', 'Vultr, Hetzner, DigitalOcean...')"
							/>
						</div>
						<div class="form-group">
							<label for="hosting-plan">{{ translate('domaincontrol', 'Plan') }}</label>
							<input
								type="text"
								id="hosting-plan"
								v-model="formData.plan"
								class="form-control"
								:placeholder="translate('domaincontrol', 'VPS 4GB, Shared Pro...')"
							/>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="hosting-server-type">{{ translate('domaincontrol', 'Server Type') }}</label>
							<select id="hosting-server-type" v-model="formData.serverType" class="form-control">
								<option value="own">{{ translate('domaincontrol', 'Own Server') }}</option>
								<option value="external">{{ translate('domaincontrol', 'External Server') }}</option>
							</select>
						</div>
						<div class="form-group">
							<label for="hosting-server-ip">{{ translate('domaincontrol', 'Server IP') }}</label>
							<input
								type="text"
								id="hosting-server-ip"
								v-model="formData.serverIp"
								class="form-control"
								placeholder="192.168.1.1"
							/>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="hosting-renewal-interval">{{ translate('domaincontrol', 'Renewal Interval') }}</label>
							<select id="hosting-renewal-interval" v-model="formData.renewalInterval" class="form-control">
								<option value="monthly">{{ translate('domaincontrol', 'Monthly') }}</option>
								<option value="quarterly">{{ translate('domaincontrol', 'Quarterly') }}</option>
								<option value="yearly">{{ translate('domaincontrol', 'Yearly') }}</option>
								<option value="biennial">{{ translate('domaincontrol', 'Biennial') }}</option>
							</select>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="hosting-start-date">{{ translate('domaincontrol', 'Start Date') }}</label>
							<input
								type="date"
								id="hosting-start-date"
								v-model="formData.startDate"
								class="form-control"
							/>
						</div>
						<div class="form-group">
							<label for="hosting-expiration-date">{{ translate('domaincontrol', 'Next Payment Date') }}</label>
							<input
								type="date"
								id="hosting-expiration-date"
								v-model="formData.expirationDate"
								class="form-control"
							/>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="hosting-price">{{ translate('domaincontrol', 'Price') }}</label>
							<input
								type="number"
								id="hosting-price"
								v-model="formData.price"
								step="0.01"
								class="form-control"
								placeholder="9.99"
							/>
						</div>
						<div class="form-group">
							<label for="hosting-currency">{{ translate('domaincontrol', 'Currency') }}</label>
							<select id="hosting-currency" v-model="formData.currency" class="form-control">
								<option value="USD">$ USD</option>
								<option value="EUR">€ EUR</option>
								<option value="TRY">₺ TRY</option>
								<option value="AZN">₼ AZN</option>
								<option value="GBP">£ GBP</option>
								<option value="RUB">₽ RUB</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="hosting-panel-url">{{ translate('domaincontrol', 'Panel URL') }}</label>
						<input
							type="text"
							id="hosting-panel-url"
							v-model="formData.panelUrl"
							class="form-control"
							placeholder="https://panel.provider.com"
						/>
					</div>
					<div class="form-group">
						<label for="hosting-panel-notes">{{ translate('domaincontrol', 'Panel Login Info') }}</label>
						<textarea
							id="hosting-panel-notes"
							v-model="formData.panelNotes"
							class="form-control"
							rows="2"
							:placeholder="translate('domaincontrol', 'Username: admin\\nPassword: ****')"
						></textarea>
					</div>
					<div class="form-group">
						<label for="hosting-notes">{{ translate('domaincontrol', 'General Notes') }}</label>
						<RichTextEditor
							v-model="formData.notes"
							:placeholder="translate('domaincontrol', 'Other notes...')"
						/>
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
import RichTextEditor from './RichTextEditor.vue'

export default {
	name: 'HostingModal',
	components: {
		RichTextEditor,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		hosting: {
			type: Object,
			default: null,
		},
		clients: {
			type: Array,
			default: () => [],
		},
		packages: {
			type: Array,
			default: () => [],
		},
		presetClientId: {
			type: [Number, String],
			default: null,
		},
	},
	emits: ['close', 'saved'],
	data() {
		return {
			isOpen: false,
			editingHosting: null,
			formData: {
				id: null,
				clientId: '',
				packageId: '',
				provider: '',
				plan: '',
				serverType: 'external',
				serverIp: '',
				renewalInterval: 'yearly',
				startDate: '',
				expirationDate: '',
				price: null,
				currency: 'USD',
				panelUrl: '',
				panelNotes: '',
				notes: '',
			},
			saving: false,
		}
	},
	watch: {
		open(newVal) {
			this.isOpen = newVal
			if (newVal) {
				if (this.hosting) {
					this.loadHostingData()
				} else {
					this.resetForm()
					if (this.presetClientId) {
						this.formData.clientId = this.presetClientId
					}
				}
			}
		},
		presetClientId(newVal) {
			if (newVal && !this.hosting) {
				this.formData.clientId = newVal
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
				'Add Hosting': 'Hosting Ekle',
				'Edit Hosting': 'Hosting Düzenle',
				'Client': 'Müşteri',
				'Select Client': 'Müşteri Seçin',
				'Hosting Package': 'Hosting Paketi',
				'Select Package (Optional)': 'Paket Seçin (Opsiyonel)',
				'Selecting a package will auto-fill information': 'Paket seçerseniz bilgiler otomatik doldurulur',
				'Provider': 'Sağlayıcı',
				'Vultr, Hetzner, DigitalOcean...': 'Vultr, Hetzner, DigitalOcean...',
				'Plan': 'Paket',
				'VPS 4GB, Shared Pro...': 'VPS 4GB, Shared Pro...',
				'Server Type': 'Sunucu Tipi',
				'Own Server': 'Kendi Sunucum',
				'External Server': 'Harici Sunucu',
				'Server IP': 'Sunucu IP',
				'Renewal Interval': 'Ödeme Periyodu',
				'Monthly': 'Aylık',
				'Quarterly': '3 Aylık',
				'Yearly': 'Yıllık',
				'Biennial': '2 Yıllık',
				'Start Date': 'Başlangıç Tarihi',
				'Next Payment Date': 'Sonraki Ödeme Tarihi',
				'Price': 'Fiyat',
				'Currency': 'Para Birimi',
				'Panel URL': 'Panel URL',
				'Panel Login Info': 'Panel Giriş Bilgileri',
				'Username: admin&#10;Password: ****': 'Kullanıcı: admin\nŞifre: ****',
				'General Notes': 'Genel Notlar',
				'Other notes...': 'Diğer notlar...',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Provider and client are required': 'Sağlayıcı ve müşteri zorunludur',
				'Error saving hosting': 'Hosting kaydedilirken hata oluştu',
			}

			return translations[text] || text
		},
		resetForm() {
			this.formData = {
				id: null,
				clientId: this.presetClientId || '',
				packageId: '',
				provider: '',
				plan: '',
				serverType: 'external',
				serverIp: '',
				renewalInterval: 'yearly',
				startDate: '',
				expirationDate: '',
				price: null,
				currency: 'USD',
				panelUrl: '',
				panelNotes: '',
				notes: '',
			}
			this.editingHosting = null
		},
		loadHostingData() {
			if (!this.hosting) return
			const h = this.hosting
			this.editingHosting = h
			this.formData = {
				id: h.id,
				clientId: h.clientId || h.client_id || this.presetClientId || '',
				packageId: h.packageId || h.package_id || '',
				provider: h.provider || '',
				plan: h.plan || '',
				serverType: h.serverType || h.server_type || 'external',
				serverIp: h.serverIp || h.server_ip || '',
				renewalInterval: h.renewalInterval || h.renewal_interval || 'yearly',
				startDate: (h.startDate || h.start_date) ? (h.startDate || h.start_date).split(' ')[0] : '',
				expirationDate: (h.expirationDate || h.expiration_date) ? (h.expirationDate || h.expiration_date).split(' ')[0] : '',
				price: h.price || null,
				currency: h.currency || 'USD',
				panelUrl: h.panelUrl || h.panel_url || '',
				panelNotes: h.panelNotes || h.panel_notes || '',
				notes: h.notes || '',
			}
		},
		closeModal() {
			this.$emit('close')
			this.resetForm()
		},
		onPackageSelected() {
			if (this.formData.packageId) {
				const selectedPackage = this.packages.find(p => p.id == this.formData.packageId)
				if (selectedPackage) {
					// Auto-fill provider and plan from package
					this.formData.provider = selectedPackage.provider || this.formData.provider
					this.formData.plan = selectedPackage.name || this.formData.plan
					
					// Auto-fill price based on renewal interval
					if (this.formData.renewalInterval === 'monthly' && selectedPackage.priceMonthly) {
						this.formData.price = selectedPackage.priceMonthly
					} else if (this.formData.renewalInterval === 'yearly' && selectedPackage.priceYearly) {
						this.formData.price = selectedPackage.priceYearly
					}
					
					if (selectedPackage.currency) {
						this.formData.currency = selectedPackage.currency
					}
				}
			}
		},
		async saveHosting() {
			if (!this.formData.provider || !this.formData.clientId) {
				alert(this.translate('domaincontrol', 'Provider and client are required'))
				return
			}

			this.saving = true
			try {
				const data = {
					clientId: this.formData.clientId || this.presetClientId,
					packageId: this.formData.packageId || '',
					provider: (this.formData.provider || '').trim(),
					plan: (this.formData.plan || '').trim(),
					serverType: this.formData.serverType || 'external',
					serverIp: (this.formData.serverIp || '').trim(),
					renewalInterval: this.formData.renewalInterval || 'yearly',
					startDate: this.formData.startDate || '',
					expirationDate: this.formData.expirationDate || '',
					price: this.formData.price ? parseFloat(this.formData.price) : null,
					currency: this.formData.currency || 'USD',
					panelUrl: (this.formData.panelUrl || '').trim(),
					panelNotes: (this.formData.panelNotes || '').trim(),
					notes: (this.formData.notes || '').trim(),
				}
				
				// Remove empty strings and null values
				Object.keys(data).forEach(key => {
					if (data[key] === '' || data[key] === null) {
						delete data[key]
					}
				})

				if (this.editingHosting) {
					await api.hostings.update(this.editingHosting.id, data)
				} else {
					await api.hostings.create(data)
				}

				this.closeModal()
				this.$emit('saved')
				this.closeModal()
			} catch (error) {
				console.error('Error saving hosting:', error)
				const errorMessage = error.response?.data?.error || error.message || this.translate('domaincontrol', 'Error saving hosting')
				alert(errorMessage)
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
	max-height: 85vh;
	display: flex;
	flex-direction: column;
	overflow: hidden;
}

.modal-content.modal-large {
	max-width: 700px;
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
	overflow-y: auto;
	flex: 1;
	min-height: 0;
}

.form-row {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
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
	border-color: var(--color-primary-element);
}

.form-control::placeholder {
	color: var(--color-text-maxcontrast);
}

.form-text {
	display: block;
	margin-top: 4px;
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.form-actions {
	display: flex;
	justify-content: flex-end;
	gap: 12px;
	margin-top: 24px;
	padding-top: 20px;
	border-top: 1px solid var(--color-border);
	position: sticky;
	bottom: 0;
	background-color: var(--color-main-background);
	z-index: 10;
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
	background-color: var(--color-primary-element);
	color: var(--color-primary-element-text);
}

.button-vue--primary:hover:not(:disabled) {
	background-color: var(--color-primary-element-hover);
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

