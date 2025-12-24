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
									:data-default="currency.code === defaultCurrency"
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
							<p class="form-help">{{ translate('domaincontrol', 'Prefix that will be added to invoice numbers (e.g., INV-001)') }}</p>
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
						<div class="form-group checkbox-group">
							<label class="checkbox-label">
								<input
									type="checkbox"
									v-model="notificationsEnabled"
									@change="saveSettings"
									class="checkbox-input"
								/>
								<span class="checkbox-custom"></span>
								<span class="checkbox-text">{{ translate('domaincontrol', 'Enable Notifications') }}</span>
							</label>
							<p class="checkbox-description">{{ translate('domaincontrol', 'Receive notifications for important events and updates') }}</p>
						</div>

						<div class="form-group checkbox-group">
							<label class="checkbox-label" :class="{ 'disabled': !notificationsEnabled }">
								<input
									type="checkbox"
									v-model="emailNotifications"
									:disabled="!notificationsEnabled"
									@change="saveSettings"
									class="checkbox-input"
								/>
								<span class="checkbox-custom"></span>
								<span class="checkbox-text">{{ translate('domaincontrol', 'Email Notifications') }}</span>
							</label>
							<p class="checkbox-description">{{ translate('domaincontrol', 'Receive notifications via email') }}</p>
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
	padding: 24px 32px;
	padding-bottom: 60px;
	background-color: var(--color-main-background);
}

.settings-container {
	max-width: 1200px;
	margin: 0 auto;
}

.settings-header {
	margin-bottom: 40px;
	padding-bottom: 24px;
	border-bottom: 2px solid var(--color-border);
}

.settings-title {
	display: flex;
	align-items: center;
	gap: 16px;
	margin: 0 0 12px 0;
	font-size: 32px;
	font-weight: 700;
	color: var(--color-main-text);
	letter-spacing: -0.5px;
}

.settings-title .material-icon {
	color: var(--color-primary-element-element-element);
	opacity: 1;
}

.settings-subtitle {
	margin: 0;
	font-size: 15px;
	color: var(--color-text-maxcontrast);
	line-height: 1.5;
}

.settings-content {
	display: flex;
	flex-direction: column;
	gap: 28px;
}

.settings-section {
	background-color: var(--color-main-background);
	border-radius: 12px;
	padding: 28px;
	border: 1px solid var(--color-border);
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
	transition: all 0.2s ease;
}

.settings-section:hover {
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
	border-color: var(--color-primary-element-element-element-light);
}

.section-header {
	margin-bottom: 24px;
	padding-bottom: 16px;
	border-bottom: 1px solid var(--color-border);
}

.section-title {
	display: flex;
	align-items: center;
	gap: 10px;
	margin: 0 0 10px 0;
	font-size: 22px;
	font-weight: 600;
	color: var(--color-main-text);
}

.section-title .material-icon {
	color: var(--color-primary-element-element-element);
	opacity: 0.9;
}

.section-description {
	margin: 0;
	font-size: 14px;
	color: var(--color-text-maxcontrast);
	line-height: 1.6;
}

.settings-card {
	display: flex;
	flex-direction: column;
	gap: 24px;
}

.modules-list {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
	gap: 16px;
}

.module-item {
	background-color: var(--color-main-background);
	border: 2px solid var(--color-border);
	border-radius: 10px;
	padding: 16px;
	transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
	cursor: pointer;
	position: relative;
	overflow: hidden;
}

.module-item::before {
	content: '';
	position: absolute;
	top: 0;
	left: 0;
	width: 4px;
	height: 100%;
	background-color: var(--color-primary-element-element-element);
	transform: scaleY(0);
	transition: transform 0.25s ease;
}

.module-item:hover {
	background-color: var(--color-background-hover);
	border-color: var(--color-primary-element-element-element);
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.module-item:hover::before {
	transform: scaleY(1);
}

.module-checkbox {
	display: flex;
	align-items: center;
	cursor: pointer;
	margin: 0;
	width: 100%;
}

.module-checkbox input[type="checkbox"] {
	margin-right: 14px;
	width: 20px;
	height: 20px;
	cursor: pointer;
	accent-color: var(--color-primary-element-element-element);
	flex-shrink: 0;
}

.module-content {
	display: flex;
	align-items: center;
	gap: 14px;
	flex: 1;
}

.module-icon {
	width: 44px;
	height: 44px;
	border-radius: 10px;
	background: linear-gradient(135deg, var(--color-primary-element-element-element) 0%, var(--color-primary-element-element-element-light) 100%);
	color: var(--color-primary-element-element-element-text);
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
	box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
	transition: transform 0.2s ease;
}

.module-item:hover .module-icon {
	transform: scale(1.05);
}

.module-info {
	flex: 1;
	min-width: 0;
}

.module-name {
	font-size: 15px;
	font-weight: 600;
	color: var(--color-main-text);
	margin-bottom: 4px;
	line-height: 1.4;
}

.module-description {
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	line-height: 1.4;
}

.currencies-list {
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.list-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 16px;
	padding-bottom: 12px;
	border-bottom: 1px solid var(--color-border);
}

.list-header h4 {
	margin: 0;
	font-size: 17px;
	font-weight: 600;
	color: var(--color-main-text);
}

.currencies-items {
	display: flex;
	flex-direction: column;
	gap: 14px;
}

.currency-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 16px 18px;
	background-color: var(--color-main-background);
	border: 2px solid var(--color-border);
	border-radius: 10px;
	transition: all 0.2s ease;
}

.currency-item:hover {
	background-color: var(--color-background-hover);
	border-color: var(--color-primary-element-element-element);
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.currency-content {
	display: flex;
	align-items: center;
	gap: 16px;
}

.currency-symbol {
	font-size: 28px;
	font-weight: 600;
	color: var(--color-primary-element-element-element);
	min-width: 48px;
	text-align: center;
	line-height: 1;
}

.currency-info {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.currency-code {
	font-size: 17px;
	font-weight: 600;
	color: var(--color-main-text);
	letter-spacing: 0.5px;
}

.currency-name {
	font-size: 14px;
	color: var(--color-text-maxcontrast);
}

.currency-actions {
	display: flex;
	gap: 10px;
}

.form-group {
	display: flex;
	flex-direction: column;
	gap: 10px;
}

.form-label {
	font-size: 14px;
	font-weight: 600;
	color: var(--color-main-text);
	margin-bottom: 2px;
}

.form-control {
	padding: 11px 14px;
	border: 2px solid var(--color-border);
	border-radius: 8px;
	background-color: var(--color-main-background);
	color: var(--color-main-text);
	font-size: 14px;
	font-family: inherit;
	transition: all 0.2s ease;
}

.form-control:hover {
	border-color: var(--color-primary-element-element-element-light);
}

.form-control:focus {
	outline: none;
	border-color: var(--color-primary-element-element-element);
	box-shadow: 0 0 0 3px rgba(var(--color-primary-element-element-element-rgb), 0.1);
}

.form-control:disabled {
	opacity: 0.5;
	cursor: not-allowed;
	background-color: var(--color-background-dark);
}

.form-help {
	margin: 4px 0 0 0;
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	line-height: 1.4;
}

.settings-actions {
	display: flex;
	justify-content: flex-end;
	padding-top: 28px;
	margin-top: 28px;
	border-top: 2px solid var(--color-border);
}

.modal-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.6);
	backdrop-filter: blur(4px);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 10000;
	padding: 20px;
	animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
	from {
		opacity: 0;
	}
	to {
		opacity: 1;
	}
}

.modal-content {
	background-color: var(--color-main-background);
	border-radius: 16px;
	box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
	max-width: 520px;
	width: 100%;
	max-height: 90vh;
	overflow-y: auto;
	display: flex;
	flex-direction: column;
	animation: slideUp 0.3s ease;
}

@keyframes slideUp {
	from {
		transform: translateY(20px);
		opacity: 0;
	}
	to {
		transform: translateY(0);
		opacity: 1;
	}
}

.modal-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 24px 28px;
	border-bottom: 2px solid var(--color-border);
}

.modal-title {
	margin: 0;
	font-size: 22px;
	font-weight: 600;
	color: var(--color-main-text);
}

.modal-close {
	background: none;
	border: none;
	color: var(--color-text-maxcontrast);
	cursor: pointer;
	padding: 6px;
	border-radius: 8px;
	transition: all 0.2s ease;
	display: flex;
	align-items: center;
	justify-content: center;
}

.modal-close:hover {
	background-color: var(--color-background-hover);
	color: var(--color-main-text);
	transform: rotate(90deg);
}

.modal-body {
	padding: 28px;
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.form-actions {
	display: flex;
	justify-content: flex-end;
	gap: 12px;
	margin-top: 8px;
	padding-top: 20px;
	border-top: 2px solid var(--color-border);
}

.loading-content {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 80px 20px;
	gap: 20px;
}

.loading-content p {
	margin: 0;
	font-size: 15px;
	color: var(--color-text-maxcontrast);
}

.loading-icon {
	animation: spin 1s linear infinite;
	color: var(--color-primary-element-element-element);
}

@keyframes spin {
	from {
		transform: rotate(0deg);
	}
	to {
		transform: rotate(360deg);
	}
}

/* Checkbox improvements */
.module-checkbox input[type="checkbox"]:checked + .module-content .module-icon {
	background: linear-gradient(135deg, var(--color-primary-element-element-element) 0%, var(--color-primary-element-element-element-dark) 100%);
	box-shadow: 0 3px 10px rgba(var(--color-primary-element-element-element-rgb), 0.3);
}

/* Currency item default badge */
.currency-item[data-default="true"] {
	border-color: var(--color-primary-element-element-element);
	background: linear-gradient(135deg, rgba(var(--color-primary-element-element-element-rgb), 0.05) 0%, rgba(var(--color-primary-element-element-element-rgb), 0.02) 100%);
}

.currency-item[data-default="true"]::before {
	content: 'Default';
	position: absolute;
	top: 8px;
	right: 8px;
	font-size: 11px;
	font-weight: 600;
	color: var(--color-primary-element-element-element);
	background-color: rgba(var(--color-primary-element-element-element-rgb), 0.1);
	padding: 2px 8px;
	border-radius: 4px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

/* Custom checkbox styling */
.checkbox-group {
	gap: 8px;
}

.checkbox-label {
	display: flex;
	align-items: center;
	gap: 12px;
	cursor: pointer;
	margin: 0;
	padding: 12px;
	border-radius: 8px;
	transition: background-color 0.2s ease;
}

.checkbox-label:hover {
	background-color: var(--color-background-hover);
}

.checkbox-label.disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

.checkbox-label.disabled:hover {
	background-color: transparent;
}

.checkbox-input {
	position: absolute;
	opacity: 0;
	width: 0;
	height: 0;
}

.checkbox-custom {
	width: 22px;
	height: 22px;
	border: 2px solid var(--color-border);
	border-radius: 6px;
	background-color: var(--color-main-background);
	position: relative;
	flex-shrink: 0;
	transition: all 0.2s ease;
}

.checkbox-input:checked + .checkbox-custom {
	background-color: var(--color-primary-element-element-element);
	border-color: var(--color-primary-element-element-element);
}

.checkbox-input:checked + .checkbox-custom::after {
	content: '';
	position: absolute;
	left: 7px;
	top: 3px;
	width: 5px;
	height: 10px;
	border: solid var(--color-primary-element-element-element-text);
	border-width: 0 2px 2px 0;
	transform: rotate(45deg);
}

.checkbox-input:focus + .checkbox-custom {
	box-shadow: 0 0 0 3px rgba(var(--color-primary-element-element-element-rgb), 0.2);
}

.checkbox-text {
	font-size: 15px;
	font-weight: 500;
	color: var(--color-main-text);
	flex: 1;
}

.checkbox-description {
	margin: 0 0 0 34px;
	font-size: 13px;
	color: var(--color-text-maxcontrast);
	line-height: 1.5;
}

/* Responsive improvements */
@media (max-width: 768px) {
	.settings-view {
		padding: 16px 20px;
	}

	.settings-header {
		margin-bottom: 28px;
		padding-bottom: 20px;
	}

	.settings-title {
		font-size: 26px;
	}

	.settings-section {
		padding: 20px;
	}

	.modules-list {
		grid-template-columns: 1fr;
	}

	.currency-item {
		flex-direction: column;
		align-items: flex-start;
		gap: 12px;
	}

	.currency-actions {
		width: 100%;
		justify-content: flex-end;
	}

	.list-header {
		flex-direction: column;
		align-items: flex-start;
		gap: 12px;
	}
}
</style>
