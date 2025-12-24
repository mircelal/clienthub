<template>
	<div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">
					{{ editingClient ? translate('domaincontrol', 'Edit Client') : translate('domaincontrol', 'Add Client') }}
				</h3>
				<button class="modal-close" @click="closeModal">&times;</button>
			</div>
			<div class="modal-body">
				<div style="margin-bottom: 16px;">
					<button type="button" class="button-vue button-vue--secondary" @click="showContactsModal">
						<MaterialIcon name="contacts" :size="20" />
						{{ translate('domaincontrol', 'Select from Contacts') }}
					</button>
				</div>
				<form @submit.prevent="saveClient">
					<div class="form-group">
						<label for="client-name">{{ translate('domaincontrol', 'Name') }} *</label>
						<input
							type="text"
							id="client-name"
							v-model="formData.name"
							required
							class="form-control"
							:placeholder="translate('domaincontrol', 'Client name')"
						/>
					</div>
					<div class="form-group">
						<label for="client-email">{{ translate('domaincontrol', 'Email') }}</label>
						<input
							type="email"
							id="client-email"
							v-model="formData.email"
							class="form-control"
							:placeholder="translate('domaincontrol', 'Email address')"
						/>
					</div>
					<div class="form-group">
						<label for="client-phone">{{ translate('domaincontrol', 'Phone') }}</label>
						<input
							type="text"
							id="client-phone"
							v-model="formData.phone"
							class="form-control"
							:placeholder="translate('domaincontrol', 'Phone number')"
						/>
					</div>
					<div class="form-group">
						<label for="client-notes">{{ translate('domaincontrol', 'Notes') }}</label>
						<RichTextEditor
							v-model="formData.notes"
							:placeholder="translate('domaincontrol', 'Additional notes')"
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

		<!-- Contacts Selection Modal -->
		<div v-if="contactsModalOpen" class="modal-overlay" @click.self="closeContactsModal">
			<div class="modal-content modal-large">
				<div class="modal-header">
					<h3 class="modal-title">{{ translate('domaincontrol', 'Select from Contacts') }}</h3>
					<button class="modal-close" @click="closeContactsModal">&times;</button>
				</div>
				<div class="modal-body">
					<div class="form-group" style="margin-bottom: 16px;">
						<input
							type="text"
							v-model="contactsSearchQuery"
							class="form-control"
							:placeholder="translate('domaincontrol', 'Search contacts...')"
							@input="filterContacts"
						/>
					</div>
					<div v-if="contactsLoading" class="loading-content">
						<MaterialIcon name="loading" :size="32" class="loading-icon" />
						<p>{{ translate('domaincontrol', 'Loading contacts...') }}</p>
					</div>
					<div v-else-if="filteredContacts.length === 0" class="empty-content">
						<MaterialIcon name="contacts" :size="48" color="var(--color-text-maxcontrast)" class="empty-content__icon" />
						<p class="empty-content__text">
							{{ contactsSearchQuery ? translate('domaincontrol', 'No contacts found') : translate('domaincontrol', 'No contacts available') }}
						</p>
					</div>
					<div v-else class="contacts-list">
						<div
							v-for="contact in filteredContacts"
							:key="contact.id || contact.name"
							class="contact-item"
							@click="selectContact(contact)"
						>
							<div class="contact-avatar">
								<div class="avatar" :style="{ backgroundColor: getAvatarColor(contact.name) }">
									{{ getInitials(contact.name) }}
								</div>
							</div>
							<div class="contact-info">
								<div class="contact-name">{{ contact.name }}</div>
								<div v-if="contact.email" class="contact-email">{{ contact.email }}</div>
								<div v-if="contact.phone" class="contact-phone">{{ contact.phone }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import api from '../services/api'
import MaterialIcon from './MaterialIcon.vue'
import RichTextEditor from './RichTextEditor.vue'

export default {
	name: 'ClientModal',
	components: {
		MaterialIcon,
		RichTextEditor,
	},
	props: {
		open: {
			type: Boolean,
			default: false,
		},
		client: {
			type: Object,
			default: null,
		},
	},
	emits: ['close', 'saved'],
	data() {
		return {
			isOpen: false,
			editingClient: null,
			formData: {
				name: '',
				email: '',
				phone: '',
				notes: '',
			},
			saving: false,
			contactsModalOpen: false,
			contacts: [],
			contactsSearchQuery: '',
			contactsLoading: false,
		}
	},
	computed: {
		filteredContacts() {
			if (!this.contactsSearchQuery) {
				return this.contacts
			}
			const query = this.contactsSearchQuery.toLowerCase()
			return this.contacts.filter(contact => {
				return (
					contact.name?.toLowerCase().includes(query) ||
					contact.email?.toLowerCase().includes(query) ||
					contact.phone?.toLowerCase().includes(query)
				)
			})
		},
	},
	watch: {
		open(newVal) {
			this.isOpen = newVal
			if (newVal) {
				this.resetForm()
				if (this.client) {
					this.editingClient = this.client
					this.formData = {
						name: this.client.name || '',
						email: this.client.email || '',
						phone: this.client.phone || '',
						notes: this.client.notes || '',
					}
				} else {
					this.editingClient = null
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
				'Edit Client': 'Müşteri Düzenle',
				'Add Client': 'Müşteri Ekle',
				'Select from Contacts': 'Kişilerden Seç',
				'Name': 'Ad',
				'Client name': 'Müşteri adı',
				'Email address': 'E-posta adresi',
				'Phone number': 'Telefon numarası',
				'Additional notes': 'Ek notlar',
				'Cancel': 'İptal',
				'Saving...': 'Kaydediliyor...',
				'Save': 'Kaydet',
				'Search contacts...': 'Kişi ara (ad, e-posta, telefon...)',
				'Loading contacts...': 'Kişiler yükleniyor...',
				'No contacts found': 'Arama kriterinize uygun kişi bulunamadı',
				'No contacts available': 'Kişi bulunamadı',
			}

			return translations[text] || text
		},
		resetForm() {
			this.formData = {
				name: '',
				email: '',
				phone: '',
				notes: '',
			}
			this.editingClient = null
		},
		closeModal() {
			this.isOpen = false
			this.resetForm()
			this.$emit('close')
		},
		async saveClient() {
			if (!this.formData.name.trim()) {
				alert(this.translate('domaincontrol', 'Name is required'))
				return
			}

			this.saving = true
			try {
				const data = {
					name: this.formData.name.trim(),
					email: this.formData.email.trim() || '',
					phone: this.formData.phone.trim() || '',
					notes: this.formData.notes.trim() || '',
				}

				if (this.editingClient) {
					await api.clients.update(this.editingClient.id, data)
				} else {
					await api.clients.create(data)
				}

				this.closeModal()
				this.$emit('saved')
			} catch (error) {
				console.error('Error saving client:', error)
				alert(this.translate('domaincontrol', 'Error saving client'))
			} finally {
				this.saving = false
			}
		},
		showContactsModal() {
			this.contactsModalOpen = true
			this.contactsSearchQuery = ''
			this.loadContacts()
		},
		closeContactsModal() {
			this.contactsModalOpen = false
			this.contactsSearchQuery = ''
		},
		async loadContacts() {
			this.contactsLoading = true
			this.contacts = []

			try {
				// Try backend API first
				const response = await fetch(`${OC.generateUrl('/apps/domaincontrol/api')}/contacts`, {
					headers: { 'requesttoken': OC.requestToken },
				})
				const contacts = await response.json()

				if (Array.isArray(contacts) && contacts.length > 0) {
					this.contacts = contacts.sort((a, b) => (a.name || '').localeCompare(b.name || ''))
					this.contactsLoading = false
					return
				}
			} catch (e) {
				console.log('Backend API failed, trying DAV:', e)
			}

			// Try DAV method
			try {
				await this.loadContactsFromDAV()
			} catch (e) {
				console.error('Error loading contacts:', e)
				this.contactsLoading = false
			}
		},
		async loadContactsFromDAV() {
			const userId = OC.currentUser || ''
			if (!userId) {
				this.contactsLoading = false
				return
			}

			const baseUrl = window.location.origin + OC.getRootPath()
			const davUrl = baseUrl + '/remote.php/dav/addressbooks/users/' + encodeURIComponent(userId) + '/contacts/'

			const response = await fetch(davUrl, {
				method: 'PROPFIND',
				headers: {
					'requesttoken': OC.requestToken,
					'Depth': '1',
					'Content-Type': 'application/xml',
				},
				body: '<?xml version="1.0"?><d:propfind xmlns:d="DAV:"><d:prop><d:getcontenttype/><d:getetag/></d:prop></d:propfind>',
			})

			if (!response.ok) {
				throw new Error('DAV API not available')
			}

			const xml = await response.text()
			const parser = new DOMParser()
			const doc = parser.parseFromString(xml, 'text/xml')
			const responses = Array.from(doc.querySelectorAll('response'))

			if (responses.length === 0) {
				this.contactsLoading = false
				return
			}

			const contacts = []
			let loaded = 0
			const total = responses.length

			for (const response of responses) {
				const hrefEl = response.querySelector('href')
				if (!hrefEl) {
					loaded++
					if (loaded === total) {
						this.contacts = contacts.sort((a, b) => (a.name || '').localeCompare(b.name || ''))
						this.contactsLoading = false
					}
					continue
				}

				const href = hrefEl.textContent
				if (href && href.endsWith('.vcf')) {
					try {
						const contact = await this.fetchContactDetails(href)
						if (contact && contact.name) {
							contacts.push(contact)
						}
					} catch (e) {
						console.error('Error fetching contact:', e)
					}
					loaded++

					if (loaded === total) {
						this.contacts = contacts.sort((a, b) => (a.name || '').localeCompare(b.name || ''))
						this.contactsLoading = false
					}
				} else {
					loaded++
					if (loaded === total) {
						this.contacts = contacts.sort((a, b) => (a.name || '').localeCompare(b.name || ''))
						this.contactsLoading = false
					}
				}
			}
		},
		async fetchContactDetails(href) {
			const baseUrl = window.location.origin + OC.getRootPath()
			const contactUrl = baseUrl + '/remote.php/dav' + href

			const response = await fetch(contactUrl, {
				headers: { 'requesttoken': OC.requestToken },
			})

			if (!response.ok) {
				throw new Error('Failed to fetch contact')
			}

			const vcard = await response.text()
			return this.parseVCard(vcard)
		},
		parseVCard(vcard) {
			const contact = {
				id: '',
				name: '',
				email: '',
				phone: '',
				organization: '',
				notes: '',
			}

			const lines = vcard.split('\n')
			lines.forEach(line => {
				line = line.trim()
				if (line.startsWith('FN:')) {
					contact.name = line.substring(3).trim()
				} else if (line.startsWith('EMAIL')) {
					if (!contact.email) {
						const match = line.match(/EMAIL[^:]*:(.+)/)
						if (match) contact.email = match[1].trim()
					}
				} else if (line.startsWith('TEL')) {
					if (!contact.phone) {
						const match = line.match(/TEL[^:]*:(.+)/)
						if (match) contact.phone = match[1].trim()
					}
				} else if (line.startsWith('ORG:')) {
					contact.organization = line.substring(4).trim()
				} else if (line.startsWith('NOTE:')) {
					contact.notes = line.substring(5).trim()
				} else if (line.startsWith('UID:')) {
					contact.id = line.substring(4).trim()
				}
			})

			return contact
		},
		filterContacts() {
			// Filtering is handled by computed property
		},
		selectContact(contact) {
			this.formData.name = contact.name || ''
			this.formData.email = contact.email || ''
			this.formData.phone = contact.phone || ''
			if (contact.notes) {
				this.formData.notes = contact.notes
			}
			this.closeContactsModal()
		},
		getInitials(name) {
			if (!name) return '?'
			const parts = name.trim().split(' ')
			if (parts.length >= 2) {
				return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
			}
			return name.substring(0, 2).toUpperCase()
		},
		getAvatarColor(name) {
			if (!name) return '#999'
			let hash = 0
			for (let i = 0; i < name.length; i++) {
				hash = name.charCodeAt(i) + ((hash << 5) - hash)
			}
			const hue = hash % 360
			return `hsl(${hue}, 70%, 50%)`
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
	border-color: var(--color-primary-element-element);
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
	background-color: var(--color-primary-element-element);
	color: var(--color-primary-element-element-text);
}

.button-vue--primary:hover:not(:disabled) {
	background-color: var(--color-primary-element-element-hover);
}

.button-vue--secondary {
	background-color: transparent;
	color: var(--color-main-text);
	border-color: var(--color-border);
}

.button-vue--secondary:hover:not(:disabled) {
	background-color: var(--color-background-hover);
}

.button-vue .material-icon {
	margin-right: 8px;
}

.contacts-list {
	max-height: 500px;
	overflow-y: auto;
}

.contact-item {
	display: flex;
	align-items: center;
	padding: 12px;
	border-bottom: 1px solid var(--color-border);
	cursor: pointer;
	transition: background-color 0.2s;
}

.contact-item:hover {
	background-color: var(--color-background-hover);
}

.contact-item:last-child {
	border-bottom: none;
}

.contact-avatar {
	margin-right: 12px;
}

.contact-info {
	flex: 1;
	min-width: 0;
}

.contact-name {
	font-weight: 600;
	color: var(--color-main-text);
	margin-bottom: 4px;
}

.contact-email,
.contact-phone {
	font-size: 14px;
	color: var(--color-text-maxcontrast);
	margin-bottom: 2px;
}

.loading-content {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 40px 20px;
	text-align: center;
}

.loading-content .icon-loading {
	font-size: 2em;
	color: var(--color-primary-element-element);
	animation: spin 1s linear infinite;
	margin-bottom: 12px;
}

@keyframes spin {
	from {
		transform: rotate(0deg);
	}
	to {
		transform: rotate(360deg);
	}
}

.empty-content {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 40px 20px;
	text-align: center;
}

.empty-content__icon {
	margin-bottom: 12px;
	opacity: 0.5;
}

.empty-content__text {
	color: var(--color-text-maxcontrast);
	font-size: 14px;
}
</style>

