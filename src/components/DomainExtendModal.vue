<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ translate('domaincontrol', 'Extend Domain') }}
				</h3>
				<button class="modal-close" @click="closeModal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="domain-extend-info">
					<p>
						<strong>{{ translate('domaincontrol', 'Domain') }}:</strong>
						<span>{{ extendingDomain?.domainName || '-' }}</span>
					</p>
					<p>
						<strong>{{ translate('domaincontrol', 'Current Expiry') }}:</strong>
						<span>{{ formatDate(extendingDomain?.expirationDate) || translate('domaincontrol', 'Not specified') }}</span>
					</p>
					<p>
						<strong>{{ translate('domaincontrol', 'New Expiry') }}:</strong>
						<span class="text-success">{{ newExpiryDate || '-' }}</span>
					</p>
				</div>

				<form @submit.prevent="extendDomain">
					<div class="form-group">
						<label for="extend-years">{{ translate('domaincontrol', 'Extension Period') }} ({{ translate('domaincontrol', 'Year') }})</label>
						<select
							id="extend-years"
							v-model="formData.years"
							required
							class="form-control"
							@change="updateNewExpiry"
						>
							<option value="1">1 {{ translate('domaincontrol', 'Year') }}</option>
							<option value="2">2 {{ translate('domaincontrol', 'Year') }}</option>
							<option value="3">3 {{ translate('domaincontrol', 'Year') }}</option>
							<option value="5">5 {{ translate('domaincontrol', 'Year') }}</option>
							<option value="10">10 {{ translate('domaincontrol', 'Year') }}</option>
						</select>
					</div>

					<div class="form-row">
						<div class="form-group">
							<label for="extend-price">{{ translate('domaincontrol', 'Extension Price') }}</label>
							<input
								type="number"
								id="extend-price"
								v-model="formData.price"
								step="0.01"
								class="form-control"
								placeholder="12.99"
							/>
						</div>
						<div class="form-group">
							<label for="extend-currency">{{ translate('domaincontrol', 'Currency') }}</label>
							<select
								id="extend-currency"
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
						<label for="extend-note">{{ translate('domaincontrol', 'Extension Note') }}</label>
						<textarea
							id="extend-note"
							v-model="formData.note"
							class="form-control"
							rows="2"
							:placeholder="translate('domaincontrol', 'Note about extension (optional)...')"
						></textarea>
					</div>

					<div class="form-actions">
						<button type="button" class="button-vue button-vue--secondary" @click="closeModal">
							{{ translate('domaincontrol', 'Cancel') }}
						</button>
						<button type="submit" class="button-vue button-vue--success" :disabled="saving">
							{{ saving ? translate('domaincontrol', 'Extending...') : translate('domaincontrol', 'Extend') }}
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
	name: 'DomainExtendModal',
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		domain: {
			type: Object,
			default: null,
		},
	},
	emits: ['close', 'extended'],
	data() {
		return {
			formData: {
				years: '1',
				price: '',
				currency: 'USD',
				note: '',
			},
			saving: false,
			newExpiryDate: '',
		}
	},
	computed: {
		isOpen() {
			return this.open
		},
		extendingDomain() {
			return this.domain
		},
	},
	watch: {
		domain: {
			handler(newDomain) {
				if (newDomain) {
					this.formData = {
						years: newDomain.renewalInterval || '1',
						price: newDomain.price || '',
						currency: newDomain.currency || 'USD',
						note: '',
					}
					this.updateNewExpiry()
				} else {
					this.resetForm()
				}
			},
			immediate: true,
		},
	},
	mounted() {
		if (this.domain) {
			this.updateNewExpiry()
		}
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
				'Extend Domain': 'Domain Süresini Uzat',
				'Domain': 'Domain',
				'Current Expiry': 'Mevcut Bitiş',
				'New Expiry': 'Yeni Bitiş',
				'Not specified': 'Belirtilmemiş',
				'Extension Period': 'Uzatma Süresi',
				'Year': 'Yıl',
				'Extension Price': 'Uzatma Ücreti',
				'Currency': 'Para Birimi',
				'Extension Note': 'Uzatma Notu',
				'Note about extension (optional)...': 'Uzatma hakkında not (isteğe bağlı)...',
				'Cancel': 'İptal',
				'Extending...': 'Uzatılıyor...',
				'Extend': 'Süreyi Uzat',
			}

			return translations[text] || text
		},
		resetForm() {
			this.formData = {
				years: '1',
				price: '',
				currency: 'USD',
				note: '',
			}
			this.newExpiryDate = ''
		},
		updateNewExpiry() {
			if (!this.extendingDomain) {
				this.newExpiryDate = ''
				return
			}

			const years = parseInt(this.formData.years) || 1
			let baseDate

			if (this.extendingDomain.expirationDate) {
				baseDate = new Date(this.extendingDomain.expirationDate)
			} else {
				baseDate = new Date()
			}

			// If expired, start from today
			if (baseDate < new Date()) {
				baseDate = new Date()
			}

			const newDate = new Date(baseDate)
			newDate.setFullYear(newDate.getFullYear() + years)

			this.newExpiryDate = newDate.toISOString().split('T')[0]
		},
		closeModal() {
			this.$emit('close')
			this.resetForm()
		},
		async extendDomain() {
			if (!this.extendingDomain) return

			this.saving = true
			try {
				// Parse existing history or create new array
				let history = []
				if (this.extendingDomain.renewalHistory) {
					try {
						history = JSON.parse(this.extendingDomain.renewalHistory)
					} catch (e) {
						history = []
					}
				}

				// Add new entry
				const today = new Date().toISOString().split('T')[0]
				const currencySymbol = this.getCurrencySymbol(this.formData.currency)
				const priceText = this.formData.price
					? `${currencySymbol}${this.formData.price} ${this.formData.currency}`
					: ''

				history.push({
					date: today,
					years: parseInt(this.formData.years),
					newExpiry: this.newExpiryDate,
					price: priceText,
					note: this.formData.note || '',
				})

				const data = {
					expirationDate: this.newExpiryDate,
					renewalHistory: JSON.stringify(history),
				}

				if (this.formData.price) {
					data.price = this.formData.price
					data.currency = this.formData.currency
				}

				await api.domains.extend(this.extendingDomain.id, data)

				this.$emit('extended')
				this.closeModal()
			} catch (error) {
				console.error('Error extending domain:', error)
				alert(this.translate('domaincontrol', 'Error extending domain'))
			} finally {
				this.saving = false
			}
		},
		getCurrencySymbol(currency) {
			const symbols = { USD: '$', EUR: '€', TRY: '₺', AZN: '₼', GBP: '£', RUB: '₽' }
			return symbols[currency] || currency
		},
		formatDate(date) {
			if (!date) return '-'
			try {
				const d = new Date(date)
				return d.toLocaleDateString('tr-TR', { year: 'numeric', month: 'long', day: 'numeric' })
			} catch (e) {
				return date
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
	max-width: 500px;
	max-height: 90vh;
	overflow-y: auto;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
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

.domain-extend-info {
	background: var(--color-background-hover);
	padding: 16px;
	border-radius: var(--border-radius);
	margin-bottom: 20px;
}

.domain-extend-info p {
	margin: 8px 0;
	color: var(--color-main-text);
}

.domain-extend-info strong {
	color: var(--color-text-maxcontrast);
	margin-right: 8px;
}

.text-success {
	color: var(--color-success);
	font-weight: 500;
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

.button-vue--success {
	background-color: var(--color-success);
	color: var(--color-success-text);
}

.button-vue--success:hover:not(:disabled) {
	background-color: var(--color-success-hover);
}
</style>

