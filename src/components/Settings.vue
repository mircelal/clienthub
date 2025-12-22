<template>
	<div class="settings-view">
		<div class="settings-container">
			<div class="settings-header">
				<h2 class="settings-title">
					<MaterialIcon name="settings" :size="32" />
					{{ translate('domaincontrol', 'Settings') }}
				</h2>
				<p class="settings-subtitle">
					{{ translate('domaincontrol', 'Manage your application settings and preferences') }}
				</p>
			</div>

			<!-- Loading State -->
			<div v-if="loading" class="loading-content">
				<MaterialIcon name="loading" :size="32" class="loading-icon" />
				<p>{{ translate('domaincontrol', 'Loading settings...') }}</p>
			</div>

			<!-- Settings Content -->
			<div v-else class="settings-content">
				<!-- Active Modules -->
				<div class="settings-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="settings" :size="24" />
							{{ translate('domaincontrol', 'Active Modules') }}
						</h3>
						<p class="section-description">
							{{ translate('domaincontrol', 'Select which modules should be visible in the navigation panel') }}
						</p>
					</div>
					<div class="settings-card">
						<div class="modules-list">
							<div
								v-for="module in availableModules"
								:key="module.id"
								class="module-item"
							>
								<label class="module-checkbox">
									<input
										type="checkbox"
										:value="module.id"
										v-model="activeModules"
										@change="saveSettings"
									/>
									<div class="module-content">
										<div class="module-icon">
											<MaterialIcon :name="module.icon" :size="24" />
										</div>
										<div class="module-info">
											<div class="module-name">{{ translate('domaincontrol', module.label) }}</div>
											<div class="module-description">{{ translate('domaincontrol', module.description) }}</div>
										</div>
									</div>
								</label>
							</div>
						</div>
					</div>
				</div>

				<!-- Currency Management -->
				<div class="settings-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="accounting" :size="24" />
							{{ translate('domaincontrol', 'Currency Management') }}
						</h3>
						<p class="section-description">
							{{ translate('domaincontrol', 'Manage available currencies and set default currency') }}
						</p>
					</div>
					<div class="settings-card">
						<div class="form-group">
							<label class="form-label">{{ translate('domaincontrol', 'Default Currency') }}</label>
							<select
								v-model="defaultCurrency"
								class="form-control"
								@change="saveSettings"
							>
								<option
									v-for="currency in currencies"
									:key="currency.code"
									:value="currency.code"
								>
									{{ currency.symbol }} {{ currency.code }} - {{ currency.name }}
								</option>
							</select>
						</div>

						<div class="currencies-list">
							<div class="list-header">
								<h4>{{ translate('domaincontrol', 'Available Currencies') }}</h4>
								<button
									class="button-vue button-vue--primary"
									@click="showAddCurrencyModal = true"
								>
									<MaterialIcon name="add" :size="18" />
									{{ translate('domaincontrol', 'Add Currency') }}
								</button>
							</div>
							<div class="currencies-items">
								<div
									v-for="(currency, index) in currencies"
									:key="currency.code"
									class="currency-item"
								>
									<div class="currency-content">
										<div class="currency-symbol">{{ currency.symbol }}</div>
										<div class="currency-info">
											<div class="currency-code">{{ currency.code }}</div>
											<div class="currency-name">{{ currency.name }}</div>
										</div>
									</div>
									<div class="currency-actions">
										<button
											v-if="currency.code !== defaultCurrency"
											class="button-vue button-vue--secondary"
											@click="setDefaultCurrency(currency.code)"
										>
											{{ translate('domaincontrol', 'Set as Default') }}
										</button>
										<button
											v-if="!currency.isDefault"
											class="button-vue button-vue--secondary button-vue--danger"
											@click="removeCurrency(index)"
										>
											<MaterialIcon name="delete" :size="16" />
											{{ translate('domaincontrol', 'Remove') }}
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- General Settings -->
				<div class="settings-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="settings" :size="24" />
							{{ translate('domaincontrol', 'General Settings') }}
						</h3>
						<p class="section-description">
							{{ translate('domaincontrol', 'Configure general application preferences') }}
						</p>
					</div>
					<div class="settings-card">
						<div class="form-group">
							<label class="form-label">{{ translate('domaincontrol', 'Date Format') }}</label>
							<select
								v-model="dateFormat"
								class="form-control"
								@change="saveSettings"
							>
								<option value="Y-m-d">YYYY-MM-DD (2024-01-24)</option>
								<option value="d/m/Y">DD/MM/YYYY (24/01/2024)</option>
								<option value="m/d/Y">MM/DD/YYYY (01/24/2024)</option>
								<option value="d.m.Y">DD.MM.YYYY (24.01.2024)</option>
							</select>
						</div>

						<div class="form-group">
							<label class="form-label">{{ translate('domaincontrol', 'Time Format') }}</label>
							<select
								v-model="timeFormat"
								class="form-control"
								@change="saveSettings"
							>
								<option value="24">24-hour (14:30)</option>
								<option value="12">12-hour (2:30 PM)</option>
							</select>
						</div>

						<div class="form-group">
							<label class="form-label">{{ translate('domaincontrol', 'Language') }}</label>
							<select
								v-model="language"
								class="form-control"
								@change="saveSettings"
							>
								<option value="tr">Türkçe</option>
								<option value="en">English</option>
							</select>
						</div>
					</div>
				</div>

				<!-- Invoice Settings -->
				<div class="settings-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="files" :size="24" />
							{{ translate('domaincontrol', 'Invoice Settings') }}
						</h3>
						<p class="section-description">
							{{ translate('domaincontrol', 'Configure invoice numbering and formatting') }}
						</p>
					</div>
					<div class="settings-card">
						<div class="form-group">
							<label class="form-label">{{ translate('domaincontrol', 'Invoice Prefix') }}</label>
							<input
								v-model="invoicePrefix"
								type="text"
								class="form-control"
								placeholder="INV-"
								@change="saveSettings"
							/>
						</div>

						<div class="form-group">
							<label class="form-label">{{ translate('domaincontrol', 'Invoice Number Format') }}</label>
							<select
								v-model="invoiceNumberFormat"
								class="form-control"
								@change="saveSettings"
							>
								<option value="sequential">{{ translate('domaincontrol', 'Sequential (1, 2, 3...)') }}</option>
								<option value="year-sequential">{{ translate('domaincontrol', 'Year-Sequential (2024-001, 2024-002...)') }}</option>
								<option value="month-sequential">{{ translate('domaincontrol', 'Month-Sequential (2024-01-001...)') }}</option>
							</select>
						</div>
					</div>
				</div>

				<!-- Notification Settings -->
				<div class="settings-section">
					<div class="section-header">
						<h3 class="section-title">
							<MaterialIcon name="notifications" :size="24" />
							{{ translate('domaincontrol', 'Notification Settings') }}
						</h3>
						<p class="section-description">
							{{ translate('domaincontrol', 'Manage notification preferences') }}
						</p>
					</div>
					<div class="settings-card">
						<div class="form-group">
							<label class="module-checkbox">
								<input
									type="checkbox"
									v-model="notificationsEnabled"
									@change="saveSettings"
								/>
								<span>{{ translate('domaincontrol', 'Enable Notifications') }}</span>
							</label>
						</div>

						<div class="form-group">
							<label class="module-checkbox">
								<input
									type="checkbox"
									v-model="emailNotifications"
									:disabled="!notificationsEnabled"
									@change="saveSettings"
								/>
								<span>{{ translate('domaincontrol', 'Email Notifications') }}</span>
							</label>
						</div>
					</div>
				</div>

				<!-- Save Button -->
				<div class="settings-actions">
					<button
						class="button-vue button-vue--primary"
						@click="saveAllSettings"
						:disabled="saving"
					>
						<MaterialIcon v-if="saving" name="loading" :size="18" class="loading-icon" />
						{{ saving ? translate('domaincontrol', 'Saving...') : translate('domaincontrol', 'Save All Settings') }}
					</button>
				</div>
			</div>
		</div>

		<!-- Add Currency Modal -->
		<div v-if="showAddCurrencyModal" class="modal-overlay" @click.self="closeCurrencyModal">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title">{{ translate('domaincontrol', 'Add Currency') }}</h3>
					<button class="modal-close" @click="closeCurrencyModal">
						<MaterialIcon name="close" :size="24" />
					</button>
				</div>
				<form @submit.prevent="addCurrency" class="modal-body">
					<div class="form-group">
						<label class="form-label">{{ translate('domaincontrol', 'Currency Code') }} *</label>
						<input
							v-model="newCurrency.code"
							type="text"
							class="form-control"
							placeholder="USD"
							maxlength="3"
							required
							style="text-transform: uppercase;"
						/>
					</div>
					<div class="form-group">
						<label class="form-label">{{ translate('domaincontrol', 'Currency Symbol') }} *</label>
						<input
							v-model="newCurrency.symbol"
							type="text"
							class="form-control"
							placeholder="$"
							maxlength="5"
							required
						/>
					</div>
					<div class="form-group">
						<label class="form-label">{{ translate('domaincontrol', 'Currency Name') }} *</label>
						<input
							v-model="newCurrency.name"
							type="text"
							class="form-control"
							placeholder="US Dollar"
							required
						/>
					</div>
					<div class="form-actions">
						<button type="button" class="button-vue button-vue--secondary" @click="closeCurrencyModal">
							{{ translate('domaincontrol', 'Cancel') }}
						</button>
						<button type="submit" class="button-vue button-vue--primary">
							{{ translate('domaincontrol', 'Add') }}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'

export default {
	name: 'Settings',
	components: {
		MaterialIcon,
	},
	data() {
		return {
			loading: false,
			saving: false,
			showAddCurrencyModal: false,
			activeModules: [],
			currencies: [],
			defaultCurrency: 'USD',
			dateFormat: 'Y-m-d',
			timeFormat: '24',
			language: 'tr',
			invoicePrefix: '',
			invoiceNumberFormat: 'sequential',
			notificationsEnabled: true,
			emailNotifications: false,
			newCurrency: {
				code: '',
				symbol: '',
				name: '',
			},
			availableModules: [
				{ id: 'dashboard', label: 'Dashboard', icon: 'home', description: 'Main dashboard view' },
				{ id: 'clients', label: 'Clients', icon: 'contacts', description: 'Client management' },
				{ id: 'domains', label: 'Domains', icon: 'public', description: 'Domain management' },
				{ id: 'hostings', label: 'Hosting', icon: 'category-office', description: 'Hosting management' },
				{ id: 'websites', label: 'Websites', icon: 'link', description: 'Website management' },
				{ id: 'services', label: 'Services', icon: 'settings', description: 'Service management' },
				{ id: 'invoices', label: 'Invoices', icon: 'files', description: 'Invoice management' },
				{ id: 'projects', label: 'Projects', icon: 'folder', description: 'Project management' },
				{ id: 'tasks', label: 'Tasks', icon: 'checkmark', description: 'Task management' },
				{ id: 'transactions', label: 'Transactions', icon: 'accounting', description: 'Income/Expense tracking' },
				{ id: 'debts', label: 'Debts', icon: 'accounting', description: 'Debt/Credit management' },
				{ id: 'reports', label: 'Reports', icon: 'accounting', description: 'Reports and analytics' },
			],
			defaultCurrencies: [
				{ code: 'USD', symbol: '$', name: 'US Dollar', isDefault: true },
				{ code: 'EUR', symbol: '€', name: 'Euro', isDefault: false },
				{ code: 'TRY', symbol: '₺', name: 'Turkish Lira', isDefault: false },
				{ code: 'AZN', symbol: '₼', name: 'Azerbaijani Manat', isDefault: false },
				{ code: 'GBP', symbol: '£', name: 'British Pound', isDefault: false },
				{ code: 'RUB', symbol: '₽', name: 'Russian Ruble', isDefault: false },
			],
		}
	},
	mounted() {
		this.loadSettings()
	},
	methods: {
		async loadSettings() {
			this.loading = true
			try {
				const response = await api.settings.get()
				const settings = response.data || {}

				// Load active modules
				if (settings.active_modules) {
					try {
						this.activeModules = JSON.parse(settings.active_modules)
					} catch (e) {
						this.activeModules = settings.active_modules.split(',').filter(Boolean)
					}
				} else {
					// Default: all modules active
					this.activeModules = this.availableModules.map(m => m.id)
				}

				// Load currencies
				if (settings.currencies) {
					try {
						this.currencies = JSON.parse(settings.currencies)
					} catch (e) {
						this.currencies = this.defaultCurrencies
					}
				} else {
					this.currencies = [...this.defaultCurrencies]
				}

				// Load other settings
				this.defaultCurrency = settings.default_currency || 'USD'
				this.dateFormat = settings.date_format || 'Y-m-d'
				this.timeFormat = settings.time_format || '24'
				this.language = settings.language || 'tr'
				this.invoicePrefix = settings.invoice_prefix || ''
				this.invoiceNumberFormat = settings.invoice_number_format || 'sequential'
				this.notificationsEnabled = settings.notifications_enabled !== 'false'
				this.emailNotifications = settings.email_notifications === 'true'
			} catch (error) {
				console.error('Error loading settings:', error)
				// Set defaults
				this.activeModules = this.availableModules.map(m => m.id)
				this.currencies = [...this.defaultCurrencies]
			} finally {
				this.loading = false
			}
		},
		async saveSettings() {
			// Debounce: save after 500ms of no changes
			clearTimeout(this.saveTimeout)
			this.saveTimeout = setTimeout(() => {
				this.saveAllSettings()
			}, 500)
		},
		async saveAllSettings() {
			this.saving = true
			try {
				const settings = {
					active_modules: JSON.stringify(this.activeModules),
					currencies: JSON.stringify(this.currencies),
					default_currency: this.defaultCurrency,
					date_format: this.dateFormat,
					time_format: this.timeFormat,
					language: this.language,
					invoice_prefix: this.invoicePrefix,
					invoice_number_format: this.invoiceNumberFormat,
					notifications_enabled: this.notificationsEnabled ? 'true' : 'false',
					email_notifications: this.emailNotifications ? 'true' : 'false',
				}

				await api.settings.update(settings)
				
				// Emit event to update navigation
				if (typeof window !== 'undefined') {
					const event = new CustomEvent('settings-updated', { detail: { activeModules: this.activeModules } })
					window.dispatchEvent(event)
				}
			} catch (error) {
				console.error('Error saving settings:', error)
				alert(this.translate('domaincontrol', 'Error saving settings'))
			} finally {
				this.saving = false
			}
		},
		addCurrency() {
			if (!this.newCurrency.code || !this.newCurrency.symbol || !this.newCurrency.name) {
				return
			}

			// Check if currency already exists
			if (this.currencies.some(c => c.code.toUpperCase() === this.newCurrency.code.toUpperCase())) {
				alert(this.translate('domaincontrol', 'Currency already exists'))
				return
			}

			this.currencies.push({
				code: this.newCurrency.code.toUpperCase(),
				symbol: this.newCurrency.symbol,
				name: this.newCurrency.name,
				isDefault: false,
			})

			this.closeCurrencyModal()
			this.saveAllSettings()
		},
		removeCurrency(index) {
			if (confirm(this.translate('domaincontrol', 'Are you sure you want to remove this currency?'))) {
				const currency = this.currencies[index]
				if (currency.code === this.defaultCurrency) {
					alert(this.translate('domaincontrol', 'Cannot remove default currency'))
					return
				}
				this.currencies.splice(index, 1)
				this.saveAllSettings()
			}
		},
		setDefaultCurrency(code) {
			this.defaultCurrency = code
			this.saveAllSettings()
		},
		closeCurrencyModal() {
			this.showAddCurrencyModal = false
			this.newCurrency = {
				code: '',
				symbol: '',
				name: '',
			}
		},
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
				'Settings': 'Ayarlar',
				'Manage your application settings and preferences': 'Uygulama ayarlarınızı ve tercihlerinizi yönetin',
				'Loading settings...': 'Ayarlar yükleniyor...',
				'Active Modules': 'Aktif Modüller',
				'Select which modules should be visible in the navigation panel': 'Navigasyon panelinde hangi modüllerin görünür olacağını seçin',
				'Currency Management': 'Para Birimi Yönetimi',
				'Manage available currencies and set default currency': 'Mevcut para birimlerini yönetin ve varsayılan para birimini ayarlayın',
				'Default Currency': 'Varsayılan Para Birimi',
				'Available Currencies': 'Mevcut Para Birimleri',
				'Add Currency': 'Para Birimi Ekle',
				'Set as Default': 'Varsayılan Yap',
				'Remove': 'Kaldır',
				'General Settings': 'Genel Ayarlar',
				'Configure general application preferences': 'Genel uygulama tercihlerini yapılandırın',
				'Date Format': 'Tarih Formatı',
				'Time Format': 'Saat Formatı',
				'Language': 'Dil',
				'Invoice Settings': 'Fatura Ayarları',
				'Configure invoice numbering and formatting': 'Fatura numaralandırma ve formatlamayı yapılandırın',
				'Invoice Prefix': 'Fatura Öneki',
				'Invoice Number Format': 'Fatura Numarası Formatı',
				'Sequential (1, 2, 3...)': 'Sıralı (1, 2, 3...)',
				'Year-Sequential (2024-001, 2024-002...)': 'Yıl-Sıralı (2024-001, 2024-002...)',
				'Month-Sequential (2024-01-001...)': 'Ay-Sıralı (2024-01-001...)',
				'Notification Settings': 'Bildirim Ayarları',
				'Manage notification preferences': 'Bildirim tercihlerini yönetin',
				'Enable Notifications': 'Bildirimleri Etkinleştir',
				'Email Notifications': 'E-posta Bildirimleri',
				'Save All Settings': 'Tüm Ayarları Kaydet',
				'Saving...': 'Kaydediliyor...',
				'Currency Code': 'Para Birimi Kodu',
				'Currency Symbol': 'Para Birimi Sembolü',
				'Currency Name': 'Para Birimi Adı',
				'Cancel': 'İptal',
				'Add': 'Ekle',
				'Are you sure you want to remove this currency?': 'Bu para birimini kaldırmak istediğinize emin misiniz?',
				'Cannot remove default currency': 'Varsayılan para birimi kaldırılamaz',
				'Currency already exists': 'Para birimi zaten mevcut',
				'Error saving settings': 'Ayarlar kaydedilirken hata oluştu',
			}

			return translations[text] || text
		},
	},
}
</script>

<style scoped>
.settings-view {
	width: 100%;
	height: 100%;
	padding: 20px;
	padding-bottom: 40px;
}

.settings-container {
	max-width: 1000px;
	margin: 0 auto;
}

.settings-header {
	margin-bottom: 32px;
}

.settings-title {
	display: flex;
	align-items: center;
	gap: 12px;
	margin: 0 0 8px 0;
	font-size: 28px;
	font-weight: 600;
	color: var(--color-main-text);
}

.settings-subtitle {
	margin: 0;
	font-size: 16px;
	color: var(--color-text-maxcontrast);
}

.settings-content {
	display: flex;
	flex-direction: column;
	gap: 32px;
}

.settings-section {
	background-color: var(--color-background-dark);
	border-radius: var(--border-radius-element);
	padding: 24px;
	border: 1px solid var(--color-border);
}

.section-header {
	margin-bottom: 20px;
}

.section-title {
	display: flex;
	align-items: center;
	gap: 8px;
	margin: 0 0 8px 0;
	font-size: 20px;
	font-weight: 600;
	color: var(--color-main-text);
}

.section-description {
	margin: 0;
	font-size: 14px;
	color: var(--color-text-maxcontrast);
}

.settings-card {
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.modules-list {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
	gap: 12px;
}

.module-item {
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	padding: 12px;
	transition: all 0.2s;
}

.module-item:hover {
	background-color: var(--color-background-hover);
	border-color: var(--color-primary-element);
}

.module-checkbox {
	display: flex;
	align-items: center;
	cursor: pointer;
	margin: 0;
}

.module-checkbox input[type="checkbox"] {
	margin-right: 12px;
	width: 18px;
	height: 18px;
	cursor: pointer;
}

.module-content {
	display: flex;
	align-items: center;
	gap: 12px;
	flex: 1;
}

.module-icon {
	width: 40px;
	height: 40px;
	border-radius: 50%;
	background-color: var(--color-primary-element);
	color: var(--color-primary-element-text);
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.module-info {
	flex: 1;
	min-width: 0;
}

.module-name {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
	margin-bottom: 2px;
}

.module-description {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.currencies-list {
	display: flex;
	flex-direction: column;
	gap: 16px;
}

.list-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 12px;
}

.list-header h4 {
	margin: 0;
	font-size: 16px;
	font-weight: 600;
	color: var(--color-main-text);
}

.currencies-items {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.currency-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 12px;
	background-color: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
}

.currency-content {
	display: flex;
	align-items: center;
	gap: 12px;
}

.currency-symbol {
	font-size: 24px;
	font-weight: 600;
	color: var(--color-main-text);
	min-width: 40px;
	text-align: center;
}

.currency-info {
	display: flex;
	flex-direction: column;
	gap: 2px;
}

.currency-code {
	font-size: 16px;
	font-weight: 600;
	color: var(--color-main-text);
}

.currency-name {
	font-size: 13px;
	color: var(--color-text-maxcontrast);
}

.currency-actions {
	display: flex;
	gap: 8px;
}

.form-group {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.form-label {
	font-size: 14px;
	font-weight: 500;
	color: var(--color-main-text);
}

.form-control {
	padding: 8px 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-element);
	background-color: var(--color-main-background);
	color: var(--color-main-text);
	font-size: 14px;
	font-family: inherit;
}

.form-control:focus {
	outline: none;
	border-color: var(--color-primary-element);
}

.settings-actions {
	display: flex;
	justify-content: flex-end;
	padding-top: 24px;
	border-top: 1px solid var(--color-border);
}

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

.modal-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
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
	color: var(--color-text-maxcontrast);
	cursor: pointer;
	padding: 4px;
	border-radius: var(--border-radius-small);
	transition: background-color 0.2s;
	display: flex;
	align-items: center;
	justify-content: center;
}

.modal-close:hover {
	background-color: var(--color-background-hover);
}

.modal-body {
	padding: 20px;
	display: flex;
	flex-direction: column;
	gap: 16px;
}

.form-actions {
	display: flex;
	justify-content: flex-end;
	gap: 12px;
	margin-top: 8px;
	padding-top: 16px;
	border-top: 1px solid var(--color-border);
}

.loading-content {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 60px 20px;
	gap: 16px;
}

.loading-icon {
	animation: spin 1s linear infinite;
}

@keyframes spin {
	from {
		transform: rotate(0deg);
	}
	to {
		transform: rotate(360deg);
	}
}
</style>
