<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content modal-large">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ editingDomain ? translate('domaincontrol', 'Edit Domain') : translate('domaincontrol', 'Add Domain') }}
				</h3>
				<button class="modal-close" @click="closeModal">&times;</button>
			</div>
			<div class="modal-body">
				<form @submit.prevent="saveDomain">
					<input type="hidden" v-model="formData.id" />
					<div class="form-row">
						<div class="form-group">
							<label for="domain-client-id">{{ translate('domaincontrol', 'Client') }} *</label>
							<select
								id="domain-client-id"
								v-model="formData.clientId"
								required
								class="form-control"
							>
								<option value="">{{ translate('domaincontrol', 'Select Client') }}</option>
								<option
									v-for="client in clients"
									:key="client.id"
									:value="client.id"
								>
									{{ client.name }}
								</option>
							</select>
						</div>
						<div class="form-group">
							<label for="domain-name">{{ translate('domaincontrol', 'Domain Name') }} *</label>
							<input
								type="text"
								id="domain-name"
								v-model="formData.domainName"
								required
								class="form-control"
								placeholder="ornek.com"
							/>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="domain-registrar">{{ translate('domaincontrol', 'Registrar') }}</label>
							<input
								type="text"
								id="domain-registrar"
								v-model="formData.registrar"
								class="form-control"
								placeholder="GoDaddy, Namecheap..."
							/>
						</div>
						<div class="form-group">
							<label for="domain-renewal-interval">{{ translate('domaincontrol', 'Interval') }} ({{ translate('domaincontrol', 'Year') }})</label>
							<select
								id="domain-renewal-interval"
								v-model="formData.renewalInterval"
								class="form-control"
							>
								<option value="1">1 {{ translate('domaincontrol', 'Year') }}</option>
								<option value="2">2 {{ translate('domaincontrol', 'Year') }}</option>
								<option value="3">3 {{ translate('domaincontrol', 'Year') }}</option>
								<option value="5">5 {{ translate('domaincontrol', 'Year') }}</option>
								<option value="10">10 {{ translate('domaincontrol', 'Year') }}</option>
							</select>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="domain-registration-date">{{ translate('domaincontrol', 'Registration Date') }}</label>
							<input
								type="date"
								id="domain-registration-date"
								v-model="formData.registrationDate"
								class="form-control"
							/>
						</div>
						<div class="form-group">
							<label for="domain-expiration-date">{{ translate('domaincontrol', 'Expiration Date') }}</label>
							<input
								type="date"
								id="domain-expiration-date"
								v-model="formData.expirationDate"
								class="form-control"
							/>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="domain-price">{{ translate('domaincontrol', 'Price') }}</label>
							<input
								type="number"
								id="domain-price"
								v-model="formData.price"
								step="0.01"
								class="form-control"
								placeholder="12.99"
							/>
						</div>
						<div class="form-group">
							<label for="domain-currency">{{ translate('domaincontrol', 'Currency') }}</label>
							<select
								id="domain-currency"
								v-model="formData.currency"
								class="form-control"
							>
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
						<label for="domain-panel-notes">{{ translate('domaincontrol', 'Panel Access Information') }}</label>
						<textarea
							id="domain-panel-notes"
							v-model="formData.panelNotes"
							class="form-control"
							rows="3"
							:placeholder="translate('domaincontrol', 'Domain panel URL, username, password notes...')"
						></textarea>
					</div>
					<div class="form-group">
						<label for="domain-notes">{{ translate('domaincontrol', 'General Notes') }}</label>
						<textarea
							id="domain-notes"
							v-model="formData.notes"
							class="form-control"
							rows="2"
							:placeholder="translate('domaincontrol', 'Other notes...')"
						></textarea>
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
	name: 'DomainModal',
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		domain: {
			type: Object,
			default: null,
		},
		clients: {
			type: Array,
			default: () => [],
		},
	},
	data() {
		return {
			formData: {
				id: null,
				clientId: '',
				domainName: '',
				registrar: '',
				registrationDate: '',
				expirationDate: '',
				price: '',
				currency: 'USD',
				renewalInterval: '1',
				panelNotes: '',
				notes: '',
			},
			saving: false,
		}
	},
	computed: {
		isOpen() {
			return this.open
		},
		editingDomain() {
			return this.domain
		},
	},
	watch: {
		domain: {
			handler(newDomain) {
				if (newDomain) {
					this.formData = {
						id: newDomain.id || null,
						clientId: newDomain.clientId || '',
						domainName: newDomain.domainName || '',
						registrar: newDomain.registrar || '',
						registrationDate: newDomain.registrationDate ? newDomain.registrationDate.split(' ')[0] : '',
						expirationDate: newDomain.expirationDate ? newDomain.expirationDate.split(' ')[0] : '',
						price: newDomain.price || '',
						currency: newDomain.currency || 'USD',
						renewalInterval: newDomain.renewalInterval || '1',
						panelNotes: newDomain.panelNotes || '',
						notes: newDomain.notes || '',
					}
				} else {
					this.resetForm()
				}
			},
			immediate: true,
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
				'Edit Domain': 'Domain Düzenle',
				'Add Domain': 'Domain Ekle',
				'Client': 'Müşteri',
				'Select Client': 'Müşteri Seçin',
				'Domain Name': 'Domain Adı',
				'Registrar': 'Kayıtçı (Registrar)',
				'Interval': 'Süre',
				'Year': 'Yıl',
				'Registration Date': 'Kayıt Tarihi',
				'Expiration Date': 'Bitiş Tarihi',
				'Price': 'Fiyat',
				'Currency': 'Para Birimi',
				'Panel Access Information': 'Panel Giriş Bilgileri',
				'Domain panel URL, username, password notes...': 'Domain paneli URL, kullanıcı adı, şifre notları...',
				'General Notes': 'Genel Notlar',
				'Other notes...': 'Diğer notlar...',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
			}

			return translations[text] || text
		},
		resetForm() {
			this.formData = {
				id: null,
				clientId: '',
				domainName: '',
				registrar: '',
				registrationDate: '',
				expirationDate: '',
				price: '',
				currency: 'USD',
				renewalInterval: '1',
				panelNotes: '',
				notes: '',
			}
		},
		closeModal() {
			this.$emit('close')
			this.resetForm()
		},
		async saveDomain() {
			if (!this.formData.domainName || !this.formData.clientId) {
				alert(this.translate('domaincontrol', 'Domain name and client are required'))
				return
			}

			this.saving = true
			try {
				const data = {
					clientId: this.formData.clientId,
					domainName: this.formData.domainName,
					registrar: this.formData.registrar,
					registrationDate: this.formData.registrationDate || null,
					expirationDate: this.formData.expirationDate || null,
					price: this.formData.price || null,
					currency: this.formData.currency,
					renewalInterval: this.formData.renewalInterval,
					panelNotes: this.formData.panelNotes,
					notes: this.formData.notes,
				}

				if (this.formData.id) {
					await api.domains.update(this.formData.id, data)
				} else {
					await api.domains.create(data)
				}

				this.$emit('saved')
			} catch (error) {
				console.error('Error saving domain:', error)
				alert(this.translate('domaincontrol', 'Error saving domain'))
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
	background: rgba(0, 0, 0, 0.5);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 1000;
}

.modal-content {
	background: var(--color-main-background);
	border-radius: var(--border-radius-large);
	width: 90%;
	max-width: 600px;
	max-height: 90vh;
	overflow-y: auto;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.modal-large {
	max-width: 800px;
}

.modal-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 20px;
	border-bottom: 1px solid var(--color-border);
}

.modal-title {
	margin: 0;
	font-size: 18px;
	font-weight: 500;
}

.modal-close {
	background: none;
	border: none;
	font-size: 24px;
	cursor: pointer;
	color: var(--color-main-text);
	padding: 0;
	width: 32px;
	height: 32px;
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: var(--border-radius);
}

.modal-close:hover {
	background: var(--color-background-hover);
}

.modal-body {
	padding: 20px;
}

.form-row {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 16px;
	margin-bottom: 16px;
}

.form-group {
	margin-bottom: 16px;
}

.form-group label {
	display: block;
	margin-bottom: 8px;
	font-weight: 500;
	color: var(--color-main-text);
}

.form-control {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius);
	background: var(--color-main-background);
	color: var(--color-main-text);
	font-size: var(--default-font-size);
	box-sizing: border-box;
}

.form-control:focus {
	outline: none;
	border-color: var(--color-primary-element);
}

.form-actions {
	display: flex;
	justify-content: flex-end;
	gap: 12px;
	margin-top: 24px;
	padding-top: 20px;
	border-top: 1px solid var(--color-border);
}
</style>

