/**
 * Domain Control - Main JavaScript
 * Version: 2.1.1 - Build: 20241218-FIX
 */
(function() {
	'use strict';

	const DomainControl = {
		apiBase: OC.generateUrl('/apps/domaincontrol/api'),
		currentTab: 'dashboard',
		clients: [],
		domains: [],
		hostings: [],
		websites: [],

	init: function() {
		console.log('DomainControl: v2.2.0 Starting...');
		console.log('DomainControl: API Base:', this.apiBase);
		try {
			this.setupTabs();
			this.setupForms();
			this.setupButtons();
			this.loadData();
			this.updateDashboard();
			console.log('DomainControl: v2.2.0 Ready!');
		} catch (e) {
			console.error('DomainControl: Init error:', e);
		}
	},

	setupTabs: function() {
		const tabs = document.querySelectorAll('.tab-button');
		tabs.forEach(tab => {
			tab.addEventListener('click', (e) => {
				const tabName = e.target.getAttribute('data-tab');
				this.switchTab(tabName);
			});
		});
	},

	setupButtons: function() {
		// Add buttons
		document.getElementById('add-client-btn')?.addEventListener('click', () => this.showClientModal());
		document.getElementById('add-domain-btn')?.addEventListener('click', () => this.showDomainModal());
		document.getElementById('add-hosting-btn')?.addEventListener('click', () => this.showHostingModal());
		document.getElementById('add-website-btn')?.addEventListener('click', () => this.showWebsiteModal());

		// Quick add buttons
		document.getElementById('quick-add-client')?.addEventListener('click', () => this.showClientModal());
		document.getElementById('quick-add-domain')?.addEventListener('click', () => this.showDomainModal());
		document.getElementById('quick-add-hosting')?.addEventListener('click', () => this.showHostingModal());
		document.getElementById('quick-add-website')?.addEventListener('click', () => this.showWebsiteModal());

		// Modal close buttons
		document.querySelectorAll('.modal-close, .modal-cancel').forEach(btn => {
			btn.addEventListener('click', (e) => {
				const modalId = e.target.getAttribute('data-modal');
				if (modalId) {
					this.closeModal(modalId);
				}
			});
		});

		// Click outside modal to close
		window.addEventListener('click', (e) => {
			if (e.target.classList.contains('modal')) {
				e.target.style.display = 'none';
			}
		});

		// Domain detail buttons
		document.getElementById('back-to-domains-btn')?.addEventListener('click', () => this.hideDomainDetail());
		document.getElementById('domain-detail-extend-btn')?.addEventListener('click', () => {
			if (this.currentDomainId) this.showExtendDomainModal(this.currentDomainId);
		});
		document.getElementById('domain-detail-edit-btn')?.addEventListener('click', () => {
			if (this.currentDomainId) {
				// Don't hide detail, just show modal
				this.showDomainModal(this.currentDomainId);
			}
		});
		document.getElementById('domain-detail-delete-btn')?.addEventListener('click', () => {
			if (this.currentDomainId && confirm('Bu domaini silmek istediğinize emin misiniz?')) {
				this.deleteDomain(this.currentDomainId);
				this.hideDomainDetail();
			}
		});

		// Client detail buttons
		document.getElementById('back-to-clients-btn')?.addEventListener('click', () => this.hideClientDetail());
		document.getElementById('client-detail-edit-btn')?.addEventListener('click', () => {
			if (this.currentClientId) {
				this.showClientModal(this.currentClientId);
			}
		});
		document.getElementById('client-detail-delete-btn')?.addEventListener('click', () => {
			if (this.currentClientId && confirm('Bu müşteriyi silmek istediğinize emin misiniz?')) {
				this.deleteClient(this.currentClientId);
				this.hideClientDetail();
			}
		});

		// Hosting detail buttons
		document.getElementById('back-to-hostings-btn')?.addEventListener('click', () => this.hideHostingDetail());
		document.getElementById('hosting-detail-pay-btn')?.addEventListener('click', () => {
			if (this.currentHostingId) this.showHostingPaymentModal(this.currentHostingId);
		});
		document.getElementById('hosting-detail-edit-btn')?.addEventListener('click', () => {
			if (this.currentHostingId) {
				this.showHostingModal(this.currentHostingId);
			}
		});
		document.getElementById('hosting-detail-delete-btn')?.addEventListener('click', () => {
			if (this.currentHostingId && confirm('Bu hostingi silmek istediğinize emin misiniz?')) {
				this.deleteHosting(this.currentHostingId);
				this.hideHostingDetail();
			}
		});
	},

		switchTab: function(tabName) {
			this.currentTab = tabName;
			
			// Update tab buttons
			document.querySelectorAll('.tab-button').forEach(btn => {
				btn.classList.remove('active');
			});
			document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
			
			// Update tab content
			document.querySelectorAll('.tab-content').forEach(content => {
				content.classList.remove('active');
			});
			document.getElementById(`${tabName}-tab`).classList.add('active');
			
			// Load data for the tab
			this.loadTabData(tabName);
		},

		loadData: function() {
			this.loadClients();
			this.loadDomains();
			this.loadHostings();
			this.loadWebsites();
		},

	loadTabData: function(tabName) {
		switch(tabName) {
			case 'dashboard':
				this.updateDashboard();
				break;
			case 'clients':
				this.loadClients();
				break;
			case 'domains':
				this.loadDomains();
				break;
			case 'hostings':
				this.loadHostings();
				break;
			case 'websites':
				this.loadWebsites();
				break;
		}
	},

	updateDashboard: function() {
		// Update stats (null-safe)
		const statClients = document.getElementById('stat-clients');
		const statDomains = document.getElementById('stat-domains');
		const statHostings = document.getElementById('stat-hostings');
		const statWebsites = document.getElementById('stat-websites');
		
		if (statClients) statClients.textContent = this.clients.length;
		if (statDomains) statDomains.textContent = this.domains.length;
		if (statHostings) statHostings.textContent = this.hostings.length;
		if (statWebsites) statWebsites.textContent = this.websites.length;

		// Show recent clients
		const recentContainer = document.getElementById('recent-clients');
		if (!recentContainer) return;

		const recentClients = this.clients.slice(0, 5);
		if (recentClients.length === 0) {
			recentContainer.innerHTML = '<p class="empty-state">Henüz müşteri eklenmemiş</p>';
			return;
		}

		let html = '';
		recentClients.forEach(client => {
			const initials = client.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
			html += `
				<div class="list-item">
					<div class="list-item__avatar">${initials}</div>
					<div class="list-item__content">
						<div class="list-item__title">${this.escapeHtml(client.name)}</div>
						<div class="list-item__meta">
							${client.email ? `<span>📧 ${this.escapeHtml(client.email)}</span>` : ''}
						</div>
					</div>
				</div>
			`;
		});
		recentContainer.innerHTML = html;
	},

	loadClients: function() {
		console.log('DomainControl: Loading clients...');
		fetch(this.apiBase + '/clients', {
			headers: {
				'requesttoken': OC.requestToken
			}
		})
			.then(response => {
				console.log('Response status:', response.status);
				if (!response.ok) {
					throw new Error('HTTP ' + response.status);
				}
				return response.json();
			})
			.then(data => {
				console.log('Clients loaded:', data);
				if (data.error) {
					throw new Error(data.error);
				}
				this.clients = Array.isArray(data) ? data : [];
				this.renderClients();
				this.updateClientSelects();
				this.updateDashboard();
			})
			.catch(error => {
				console.error('Error loading clients:', error);
				this.showError('Müşteriler yüklenemedi: ' + error.message);
				this.clients = [];
				this.renderClients();
			});
	},

	loadDomains: function() {
		console.log('DomainControl: Loading domains...');
		fetch(this.apiBase + '/domains', {
			headers: {
				'requesttoken': OC.requestToken
			}
		})
		.then(response => {
			console.log('Domains response status:', response.status);
			return response.json();
		})
		.then(data => {
			console.log('Domains loaded:', data);
			if (data.error) {
				throw new Error(data.error);
			}
			this.domains = Array.isArray(data) ? data : [];
			this.renderDomains();
			this.updateDashboard();
		})
		.catch(error => {
			console.error('Error loading domains:', error);
			this.showError('Domainler yüklenemedi: ' + error.message);
			this.domains = [];
			this.renderDomains();
		});
	},

	loadHostings: function() {
		console.log('DomainControl: Loading hostings...');
		fetch(this.apiBase + '/hostings', {
			headers: {
				'requesttoken': OC.requestToken
			}
		})
		.then(response => {
			console.log('Hostings response status:', response.status);
			return response.json();
		})
		.then(data => {
			console.log('Hostings loaded:', data);
			if (data.error) {
				throw new Error(data.error);
			}
			this.hostings = Array.isArray(data) ? data : [];
			this.renderHostings();
			this.updateDashboard();
		})
		.catch(error => {
			console.error('Error loading hostings:', error);
			this.showError('Hostingler yüklenemedi: ' + error.message);
			this.hostings = [];
			this.renderHostings();
		});
	},

	loadWebsites: function() {
		fetch(this.apiBase + '/websites', {
			headers: {
				'requesttoken': OC.requestToken
			}
		})
			.then(response => response.json())
			.then(data => {
				this.websites = data;
				this.renderWebsites();
			})
			.catch(error => {
				console.error('Error loading websites:', error);
				this.showError('Failed to load websites');
			});
	},

		updateClientSelects: function() {
			const selects = document.querySelectorAll('[id$="-client-id"]');
			selects.forEach(select => {
				select.innerHTML = '<option value="">Select Client</option>';
				this.clients.forEach(client => {
					const option = document.createElement('option');
					option.value = client.id;
					option.textContent = client.name;
					select.appendChild(option);
				});
			});
		},

	renderClients: function() {
		const container = document.getElementById('clients-list');
		
		if (!this.clients || this.clients.length === 0) {
			container.innerHTML = '<p class="empty-state">No clients found. Add your first client to get started.</p>';
			return;
		}

		let html = '';
		this.clients.forEach(client => {
			// Get client initials for avatar
			const initials = client.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
			
			// Count related items (will be 0 for now)
			const domainCount = (this.domains || []).filter(d => d.clientId == client.id).length;
			const hostingCount = (this.hostings || []).filter(h => h.clientId == client.id).length;
			const websiteCount = (this.websites || []).filter(w => w.clientId == client.id).length;
			
			html += `
				<div class="list-item" data-client-id="${client.id}" style="cursor: pointer;">
					<div class="list-item__avatar">${initials}</div>
					<div class="list-item__content">
						<div class="list-item__title">${this.escapeHtml(client.name)}</div>
						<div class="list-item__meta">
							${client.email ? `<span>📧 ${this.escapeHtml(client.email)}</span>` : ''}
							${client.phone ? `<span>📱 ${this.escapeHtml(client.phone)}</span>` : ''}
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">Domainler</div>
							<div class="list-item__stat-value">${domainCount}</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">Hosting</div>
							<div class="list-item__stat-value">${hostingCount}</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">Websiteler</div>
							<div class="list-item__stat-value">${websiteCount}</div>
						</div>
					</div>
					<div class="list-item__actions">
						<button class="icon-edit edit-client-btn" data-id="${client.id}" title="Düzenle"></button>
						<button class="icon-delete delete-client-btn" data-id="${client.id}" title="Sil"></button>
					</div>
				</div>
			`;
		});
	container.innerHTML = html;
		
		// Attach event listeners
		container.querySelectorAll('.list-item').forEach(item => {
			item.addEventListener('click', (e) => {
				// Don't open detail if clicking on buttons
				if (e.target.closest('.list-item__actions')) return;
				const clientItem = e.target.closest('.list-item');
				if (clientItem) {
					const id = clientItem.getAttribute('data-client-id');
					if (id) this.showClientDetail(parseInt(id));
				}
			});
		});
		container.querySelectorAll('.edit-client-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.stopPropagation();
				const id = parseInt(e.target.getAttribute('data-id'));
				this.showClientModal(id);
			});
		});
		container.querySelectorAll('.delete-client-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.stopPropagation();
				const id = parseInt(e.target.getAttribute('data-id'));
				this.deleteClient(id);
			});
		});
	},

	renderDomains: function() {
		const container = document.getElementById('domains-list');
		
		if (!this.domains || this.domains.length === 0) {
			container.innerHTML = '<p class="empty-state">Henüz domain eklenmemiş. İlk domaininizi ekleyin.</p>';
			return;
		}

		let html = '';
		this.domains.forEach(domain => {
			const client = (this.clients || []).find(c => c.id == domain.clientId);
			const daysLeft = this.getDaysUntilExpiry(domain.expirationDate);
			const statusClass = daysLeft <= 7 ? 'status-critical' : (daysLeft <= 30 ? 'status-warning' : 'status-ok');
			const statusText = daysLeft <= 0 ? 'SÜRESİ DOLDU' : (daysLeft <= 7 ? 'KRİTİK' : (daysLeft <= 30 ? 'YAKLAŞAN' : 'AKTİF'));
			
			html += `
				<div class="list-item ${statusClass}" data-domain-id="${domain.id}" style="cursor: pointer;">
					<div class="list-item__avatar">🌐</div>
					<div class="list-item__content">
						<div class="list-item__title">${this.escapeHtml(domain.domainName)}</div>
						<div class="list-item__meta">
							<span>👤 ${client ? this.escapeHtml(client.name) : 'Atanmamış'}</span>
							${domain.registrar ? `<span>🏢 ${this.escapeHtml(domain.registrar)}</span>` : ''}
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">Bitiş</div>
							<div class="list-item__stat-value">${domain.expirationDate || '-'}</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">Kalan</div>
							<div class="list-item__stat-value">${daysLeft > 0 ? daysLeft + ' gün' : 'Doldu'}</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">Durum</div>
							<div class="list-item__stat-value status-badge ${statusClass}">${statusText}</div>
						</div>
					</div>
					<div class="list-item__actions">
						<button class="btn-extend extend-domain-btn" data-id="${domain.id}" title="Süreyi Uzat">+${domain.renewalInterval || 1}Y</button>
						<button class="icon-edit edit-domain-btn" data-id="${domain.id}" title="Düzenle"></button>
						<button class="icon-delete delete-domain-btn" data-id="${domain.id}" title="Sil"></button>
					</div>
				</div>
			`;
		});
		container.innerHTML = html;
		
		// Attach event listeners
		container.querySelectorAll('.list-item').forEach(item => {
			item.addEventListener('click', (e) => {
				// Don't open detail if clicking on buttons
				if (e.target.closest('.list-item__actions')) return;
				const domainItem = e.target.closest('.list-item');
				if (domainItem) {
					const id = domainItem.getAttribute('data-domain-id');
					if (id) this.showDomainDetail(parseInt(id));
				}
			});
		});
		container.querySelectorAll('.extend-domain-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.stopPropagation();
				const id = parseInt(e.target.getAttribute('data-id'));
				this.showExtendDomainModal(id);
			});
		});
		container.querySelectorAll('.edit-domain-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.stopPropagation();
				const id = parseInt(e.target.getAttribute('data-id'));
				this.showDomainModal(id);
			});
		});
		container.querySelectorAll('.delete-domain-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.stopPropagation();
				const id = parseInt(e.target.getAttribute('data-id'));
				this.deleteDomain(id);
			});
		});
	},

	showExtendDomainModal: function(id) {
		const domain = this.domains.find(d => d.id == id);
		if (!domain) return;

		const modal = document.getElementById('domain-extend-modal');
		document.getElementById('extend-domain-id').value = domain.id;
		document.getElementById('extend-domain-name').textContent = domain.domainName;
		document.getElementById('extend-current-expiry').textContent = domain.expirationDate || 'Belirtilmemiş';
		document.getElementById('extend-years').value = domain.renewalInterval || '1';
		document.getElementById('extend-price').value = domain.price || '';
		document.getElementById('extend-currency').value = domain.currency || 'USD';
		document.getElementById('extend-note').value = '';
		
		// Calculate new expiry
		this.updateNewExpiryDate();
		
		modal.style.display = 'block';
	},

	updateNewExpiryDate: function() {
		const currentExpiry = document.getElementById('extend-current-expiry').textContent;
		const years = parseInt(document.getElementById('extend-years').value) || 1;
		
		let baseDate;
		if (currentExpiry && currentExpiry !== 'Belirtilmemiş') {
			baseDate = new Date(currentExpiry);
		} else {
			baseDate = new Date();
		}
		
		// If expired, start from today
		if (baseDate < new Date()) {
			baseDate = new Date();
		}
		
		const newDate = new Date(baseDate);
		newDate.setFullYear(newDate.getFullYear() + years);
		
		const formatted = newDate.toISOString().split('T')[0];
		document.getElementById('extend-new-expiry').textContent = formatted;
	},

	extendDomain: function() {
		const id = document.getElementById('extend-domain-id').value;
		const years = parseInt(document.getElementById('extend-years').value);
		const price = document.getElementById('extend-price').value;
		const currency = document.getElementById('extend-currency').value;
		const note = document.getElementById('extend-note').value;
		const newExpiry = document.getElementById('extend-new-expiry').textContent;
		
		const domain = this.domains.find(d => d.id == id);
		if (!domain) return;

		// Parse existing history or create new array
		let history = [];
		if (domain.renewalHistory) {
			try {
				history = JSON.parse(domain.renewalHistory);
			} catch(e) {
				history = [];
			}
		}
		
		// Add new entry
		const today = new Date().toISOString().split('T')[0];
		const currencySymbol = this.getCurrencySymbol(currency);
		history.push({
			date: today,
			years: years,
			newExpiry: newExpiry,
			price: price ? currencySymbol + price + ' ' + currency : '',
			note: note
		});

		console.log('Extending domain:', { id, years, newExpiry, price, currency, note });

		const params = new URLSearchParams();
		params.append('expirationDate', newExpiry);
		params.append('renewalHistory', JSON.stringify(history));
		if (price) {
			params.append('price', price);
			params.append('currency', currency);
		}

		fetch(`${this.apiBase}/domains/${id}`, {
			method: 'PUT',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'requesttoken': OC.requestToken
			},
			body: params.toString()
		})
		.then(response => response.json())
		.then(result => {
			console.log('Extend result:', result);
			if (result.error) {
				this.showError('Uzatma başarısız: ' + result.error);
			} else {
				this.closeModal('domain-extend-modal');
				const detailOpen = this.currentDomainId;
				this.loadDomains();
				// Refresh detail view if open
				if (detailOpen) {
					setTimeout(() => this.showDomainDetail(detailOpen), 500);
				}
				this.showSuccess(`Domain ${years} yıl uzatıldı! Yeni bitiş: ${newExpiry}`);
			}
		})
		.catch(error => {
			console.error('Error extending domain:', error);
			this.showError('Uzatma başarısız: ' + error.message);
		});
	},

	getCurrencySymbol: function(currency) {
		const symbols = { USD: '$', EUR: '€', TRY: '₺', AZN: '₼', GBP: '£', RUB: '₽' };
		return symbols[currency] || '$';
	},

	getDaysUntilExpiry: function(expirationDate) {
		if (!expirationDate) return 999;
		const exp = new Date(expirationDate);
		const now = new Date();
		const diff = exp - now;
		return Math.ceil(diff / (1000 * 60 * 60 * 24));
	},

	showDomainDetail: function(id) {
		const domain = this.domains.find(d => d.id == id);
		if (!domain) return;

		const client = (this.clients || []).find(c => c.id == domain.clientId);
		const daysLeft = this.getDaysUntilExpiry(domain.expirationDate);
		const statusClass = daysLeft <= 7 ? 'status-critical' : (daysLeft <= 30 ? 'status-warning' : 'status-ok');
		const statusText = daysLeft <= 0 ? 'SÜRESİ DOLDU' : (daysLeft <= 7 ? 'KRİTİK' : (daysLeft <= 30 ? 'YAKLAŞAN' : 'AKTİF'));
		const currencySymbol = this.getCurrencySymbol(domain.currency);

		// Hide list, show detail
		document.getElementById('domains-list-view').style.display = 'none';
		document.getElementById('domain-detail-view').style.display = 'block';

		// Fill detail info
		document.getElementById('domain-detail-name').textContent = domain.domainName;
		document.getElementById('domain-detail-expiry').textContent = domain.expirationDate || '-';
		document.getElementById('domain-detail-days-left').textContent = daysLeft > 0 ? daysLeft + ' gün' : 'Doldu';
		document.getElementById('domain-detail-days-left').className = 'stat-card__value ' + statusClass;
		document.getElementById('domain-detail-status').innerHTML = `<span class="status-badge ${statusClass}">${statusText}</span>`;
		document.getElementById('domain-detail-price').textContent = domain.price ? currencySymbol + parseFloat(domain.price).toFixed(2) + ' ' + (domain.currency || 'USD') : '-';
		
		document.getElementById('domain-detail-client').textContent = client ? client.name : 'Atanmamış';
		document.getElementById('domain-detail-registrar').textContent = domain.registrar || '-';
		document.getElementById('domain-detail-registration').textContent = domain.registrationDate || '-';
		document.getElementById('domain-detail-interval').textContent = (domain.renewalInterval || '1') + ' Yıl';

		// Display panel notes and renewal history separately
		this.renderDomainNotesAndHistory(domain);

		// Store current domain id for actions
		this.currentDomainId = domain.id;
	},

	renderDomainNotesAndHistory: function(domain) {
		const notesEl = document.getElementById('domain-detail-notes');
		const historyEl = document.getElementById('domain-detail-history');

		// Display panel notes
		notesEl.textContent = domain.panelNotes || 'Panel giriş bilgisi eklenmemiş';

		// Display renewal history from renewalHistory field (JSON)
		let historyEntries = [];
		if (domain.renewalHistory) {
			try {
				historyEntries = JSON.parse(domain.renewalHistory);
			} catch(e) {
				historyEntries = [];
			}
		}

		if (historyEntries.length === 0) {
			historyEl.innerHTML = '<p class="empty-state">Henüz uzatma yapılmamış</p>';
		} else {
			let html = '';
			// Show most recent first
			historyEntries.slice().reverse().forEach(entry => {
				html += `
					<div class="history-item">
						<div class="history-date">📅 ${entry.date}</div>
						<div class="history-content">
							<strong>${entry.years} yıl uzatıldı</strong>
							<span class="history-detail">Yeni bitiş: ${entry.newExpiry}</span>
							${entry.price ? `<span class="history-detail">Ücret: ${entry.price}</span>` : ''}
							${entry.note ? `<span class="history-note">${this.escapeHtml(entry.note)}</span>` : ''}
						</div>
					</div>
				`;
			});
			historyEl.innerHTML = html;
		}
	},

	hideDomainDetail: function() {
		document.getElementById('domains-list-view').style.display = 'block';
		document.getElementById('domain-detail-view').style.display = 'none';
		this.currentDomainId = null;
	},

	showClientDetail: function(id) {
		const client = this.clients.find(c => c.id == id);
		if (!client) return;

		// Count related items
		const clientDomains = (this.domains || []).filter(d => d.clientId == id);
		const clientHostings = (this.hostings || []).filter(h => h.clientId == id);
		const clientWebsites = (this.websites || []).filter(w => w.clientId == id);

		// Hide list, show detail
		document.getElementById('clients-list-view').style.display = 'none';
		document.getElementById('client-detail-view').style.display = 'block';

		// Fill detail info
		document.getElementById('client-detail-name').textContent = client.name;
		document.getElementById('client-detail-email').textContent = client.email || '-';
		document.getElementById('client-detail-phone').textContent = client.phone || '-';
		document.getElementById('client-detail-created').textContent = client.createdAt ? client.createdAt.split(' ')[0] : '-';
		document.getElementById('client-detail-notes').textContent = client.notes || 'Not bulunmuyor';

		// Update counts
		document.getElementById('client-detail-domains-count').textContent = clientDomains.length;
		document.getElementById('client-detail-hostings-count').textContent = clientHostings.length;
		document.getElementById('client-detail-websites-count').textContent = clientWebsites.length;

		// Render client's domains
		const domainsListEl = document.getElementById('client-domains-list');
		if (clientDomains.length === 0) {
			domainsListEl.innerHTML = '<p class="empty-mini">Domain yok</p>';
		} else {
			let domainsHtml = '';
			clientDomains.forEach(d => {
				const daysLeft = this.getDaysUntilExpiry(d.expirationDate);
				const statusClass = daysLeft <= 7 ? 'status-critical' : (daysLeft <= 30 ? 'status-warning' : 'status-ok');
				domainsHtml += `<div class="mini-item ${statusClass}"><span>🌐 ${this.escapeHtml(d.domainName)}</span><span>${d.expirationDate || '-'}</span></div>`;
			});
			domainsListEl.innerHTML = domainsHtml;
		}

		// Render client's hostings
		const hostingsListEl = document.getElementById('client-hostings-list');
		if (clientHostings.length === 0) {
			hostingsListEl.innerHTML = '<p class="empty-mini">Hosting yok</p>';
		} else {
			let hostingsHtml = '';
			clientHostings.forEach(h => {
				const daysLeft = this.getDaysUntilExpiry(h.expirationDate);
				const statusClass = daysLeft <= 7 ? 'status-critical' : (daysLeft <= 30 ? 'status-warning' : 'status-ok');
				const serverIcon = h.serverType === 'own' ? '🏠' : '🖥️';
				hostingsHtml += `<div class="mini-item ${statusClass}" data-hosting-id="${h.id}" style="cursor:pointer;"><span>${serverIcon} ${this.escapeHtml(h.provider || 'N/A')}</span><span>${h.expirationDate || '-'}</span></div>`;
			});
			hostingsListEl.innerHTML = hostingsHtml;

			// Add click listeners for hostings
			hostingsListEl.querySelectorAll('.mini-item').forEach(item => {
				item.addEventListener('click', () => {
					const hId = item.getAttribute('data-hosting-id');
					if (hId) {
						this.hideClientDetail();
						this.switchTab('hostings');
						setTimeout(() => this.showHostingDetail(parseInt(hId)), 100);
					}
				});
			});
		}

		// Render client's websites
		const websitesListEl = document.getElementById('client-websites-list');
		if (clientWebsites.length === 0) {
			websitesListEl.innerHTML = '<p class="empty-mini">Website yok</p>';
		} else {
			let websitesHtml = '';
			clientWebsites.forEach(w => {
				websitesHtml += `<div class="mini-item"><span>🌍 ${this.escapeHtml(w.name || 'N/A')}</span><span>${w.url || '-'}</span></div>`;
			});
			websitesListEl.innerHTML = websitesHtml;
		}

		// Store current client id
		this.currentClientId = client.id;
	},

	hideClientDetail: function() {
		document.getElementById('clients-list-view').style.display = 'block';
		document.getElementById('client-detail-view').style.display = 'none';
		this.currentClientId = null;
	},

	showHostingDetail: function(id) {
		const hosting = this.hostings.find(h => h.id == id);
		if (!hosting) return;

		const client = (this.clients || []).find(c => c.id == hosting.clientId);
		const daysLeft = this.getDaysUntilExpiry(hosting.expirationDate);
		const statusClass = daysLeft <= 7 ? 'status-critical' : (daysLeft <= 30 ? 'status-warning' : 'status-ok');
		const currencySymbol = this.getCurrencySymbol(hosting.currency);
		const intervalText = this.getIntervalText(hosting.renewalInterval);
		const serverTypeText = hosting.serverType === 'own' ? '🏠 Kendi Sunucum' : '🌐 Harici';

		// Hide list, show detail
		document.getElementById('hostings-list-view').style.display = 'none';
		document.getElementById('hosting-detail-view').style.display = 'block';

		// Fill detail info
		document.getElementById('hosting-detail-name').textContent = hosting.provider + (hosting.plan ? ' - ' + hosting.plan : '');
		document.getElementById('hosting-detail-expiry').textContent = hosting.expirationDate || '-';
		document.getElementById('hosting-detail-days-left').textContent = daysLeft > 0 ? daysLeft + ' gün' : 'Geçti';
		document.getElementById('hosting-detail-days-left').className = 'stat-card__value ' + statusClass;
		document.getElementById('hosting-detail-price').textContent = hosting.price ? currencySymbol + parseFloat(hosting.price).toFixed(2) + '/' + intervalText : '-';
		document.getElementById('hosting-detail-server-type').textContent = serverTypeText;
		
		document.getElementById('hosting-detail-client').textContent = client ? client.name : 'Atanmamış';
		document.getElementById('hosting-detail-plan').textContent = hosting.plan || '-';
		document.getElementById('hosting-detail-ip').textContent = hosting.serverIp || '-';
		document.getElementById('hosting-detail-start').textContent = hosting.startDate || '-';
		document.getElementById('hosting-detail-last-payment').textContent = hosting.lastPaymentDate || '-';

		// Panel info
		const panelUrlEl = document.getElementById('hosting-detail-panel-url');
		if (hosting.panelUrl) {
			panelUrlEl.innerHTML = `<a href="${this.escapeHtml(hosting.panelUrl)}" target="_blank">${this.escapeHtml(hosting.panelUrl)}</a>`;
		} else {
			panelUrlEl.textContent = '';
		}
		document.getElementById('hosting-detail-panel-notes').textContent = hosting.panelNotes || 'Panel giriş bilgisi eklenmemiş';

		// Bağlı domainler (website'ların hostingId'si bu hosting'e eşit olanlar)
		const hostingWebsites = (this.websites || []).filter(w => w.hostingId == id);
		const hostingDomainIds = hostingWebsites.map(w => w.domainId).filter(d => d);
		const hostingDomains = (this.domains || []).filter(d => hostingDomainIds.includes(d.id));

		const domainsListEl = document.getElementById('hosting-domains-list');
		if (hostingDomains.length === 0) {
			domainsListEl.innerHTML = '<p class="empty-mini">Bağlı domain yok</p>';
		} else {
			let domainsHtml = '';
			hostingDomains.forEach(d => {
				domainsHtml += `<div class="mini-item"><span>🌐 ${this.escapeHtml(d.domainName)}</span></div>`;
			});
			domainsListEl.innerHTML = domainsHtml;
		}

		// Bağlı websiteler
		const websitesListEl = document.getElementById('hosting-websites-list');
		if (hostingWebsites.length === 0) {
			websitesListEl.innerHTML = '<p class="empty-mini">Bağlı website yok</p>';
		} else {
			let websitesHtml = '';
			hostingWebsites.forEach(w => {
				websitesHtml += `<div class="mini-item"><span>🌍 ${this.escapeHtml(w.name || w.software || 'N/A')}</span></div>`;
			});
			websitesListEl.innerHTML = websitesHtml;
		}

		// Payment history
		this.renderHostingPaymentHistory(hosting);

		this.currentHostingId = hosting.id;
	},

	hideHostingDetail: function() {
		document.getElementById('hostings-list-view').style.display = 'block';
		document.getElementById('hosting-detail-view').style.display = 'none';
		this.currentHostingId = null;
	},

	showHostingPaymentModal: function(id) {
		const hosting = this.hostings.find(h => h.id == id);
		if (!hosting) return;

		const modal = document.getElementById('hosting-payment-modal');
		document.getElementById('payment-hosting-id').value = hosting.id;
		document.getElementById('payment-hosting-name').textContent = hosting.provider + (hosting.plan ? ' - ' + hosting.plan : '');
		document.getElementById('payment-current-expiry').textContent = hosting.expirationDate || 'Belirtilmemiş';
		document.getElementById('payment-amount').value = hosting.price || '';
		document.getElementById('payment-currency').value = hosting.currency || 'USD';
		document.getElementById('payment-period').value = hosting.renewalInterval === 'yearly' ? '12' : (hosting.renewalInterval === 'monthly' ? '1' : '12');
		document.getElementById('payment-note').value = '';
		
		this.updatePaymentNewExpiry();
		
		modal.style.display = 'block';
	},

	updatePaymentNewExpiry: function() {
		const currentExpiry = document.getElementById('payment-current-expiry').textContent;
		const months = parseInt(document.getElementById('payment-period').value) || 1;
		
		let baseDate;
		if (currentExpiry && currentExpiry !== 'Belirtilmemiş') {
			baseDate = new Date(currentExpiry);
		} else {
			baseDate = new Date();
		}
		
		if (baseDate < new Date()) {
			baseDate = new Date();
		}
		
		const newDate = new Date(baseDate);
		newDate.setMonth(newDate.getMonth() + months);
		
		const formatted = newDate.toISOString().split('T')[0];
		document.getElementById('payment-new-expiry').textContent = formatted;
	},

	addHostingPayment: function() {
		const id = document.getElementById('payment-hosting-id').value;
		const amount = document.getElementById('payment-amount').value;
		const currency = document.getElementById('payment-currency').value;
		const months = parseInt(document.getElementById('payment-period').value);
		const note = document.getElementById('payment-note').value;
		const newExpiry = document.getElementById('payment-new-expiry').textContent;
		
		const hosting = this.hostings.find(h => h.id == id);
		if (!hosting) return;

		// Parse existing history
		let history = [];
		if (hosting.paymentHistory) {
			try {
				history = JSON.parse(hosting.paymentHistory);
			} catch(e) {
				history = [];
			}
		}
		
		// Add new entry
		const today = new Date().toISOString().split('T')[0];
		const currencySymbol = this.getCurrencySymbol(currency);
		history.push({
			date: today,
			amount: amount ? currencySymbol + amount + ' ' + currency : '',
			months: months,
			newExpiry: newExpiry,
			note: note
		});

		console.log('Adding payment:', { id, amount, currency, months, newExpiry, note });

		const params = new URLSearchParams();
		params.append('expirationDate', newExpiry);
		params.append('lastPaymentDate', today);
		params.append('paymentHistory', JSON.stringify(history));
		if (amount) {
			params.append('price', amount);
			params.append('currency', currency);
		}

		fetch(`${this.apiBase}/hostings/${id}`, {
			method: 'PUT',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'requesttoken': OC.requestToken
			},
			body: params.toString()
		})
		.then(response => response.json())
		.then(result => {
			console.log('Payment result:', result);
			if (result.error) {
				this.showError('Ödeme kaydedilemedi: ' + result.error);
			} else {
				this.closeModal('hosting-payment-modal');
				const detailOpen = this.currentHostingId;
				this.loadHostings();
				if (detailOpen) {
					setTimeout(() => this.showHostingDetail(detailOpen), 500);
				}
				this.showSuccess('Ödeme başarıyla kaydedildi!');
			}
		})
		.catch(error => {
			console.error('Error adding payment:', error);
			this.showError('Ödeme kaydedilemedi: ' + error.message);
		});
	},

	renderHostingPaymentHistory: function(hosting) {
		const historyEl = document.getElementById('hosting-detail-payments');
		
		let historyEntries = [];
		if (hosting.paymentHistory) {
			try {
				historyEntries = JSON.parse(hosting.paymentHistory);
			} catch(e) {
				historyEntries = [];
			}
		}

		if (historyEntries.length === 0) {
			historyEl.innerHTML = '<p class="empty-state">Henüz ödeme kaydı yok</p>';
		} else {
			let html = '';
			historyEntries.slice().reverse().forEach(entry => {
				html += `
					<div class="history-item">
						<div class="history-date">📅 ${entry.date}</div>
						<div class="history-content">
							<strong>${entry.months} ay uzatıldı</strong>
							<span class="history-detail">Yeni bitiş: ${entry.newExpiry}</span>
							${entry.amount ? `<span class="history-detail">Tutar: ${entry.amount}</span>` : ''}
							${entry.note ? `<span class="history-note">${this.escapeHtml(entry.note)}</span>` : ''}
						</div>
					</div>
				`;
			});
			historyEl.innerHTML = html;
		}
	},

	renderHostings: function() {
		const container = document.getElementById('hostings-list');
		if (!container) return;
		
		if (!this.hostings || this.hostings.length === 0) {
			container.innerHTML = '<p class="empty-state">Henüz hosting eklenmemiş. İlk hostinginizi ekleyin.</p>';
			return;
		}

		let html = '';
		this.hostings.forEach(hosting => {
			const client = (this.clients || []).find(c => c.id == hosting.clientId);
			const daysLeft = this.getDaysUntilExpiry(hosting.expirationDate);
			const statusClass = daysLeft <= 7 ? 'status-critical' : (daysLeft <= 30 ? 'status-warning' : 'status-ok');
			const statusText = daysLeft <= 0 ? 'ÖDENMEDİ' : (daysLeft <= 7 ? 'ACİL' : (daysLeft <= 30 ? 'YAKLAŞAN' : 'AKTİF'));
			const currencySymbol = this.getCurrencySymbol(hosting.currency);
			const serverIcon = hosting.serverType === 'own' ? '🏠' : '🌐';
			const intervalText = this.getIntervalText(hosting.renewalInterval);
			
			html += `
				<div class="list-item ${statusClass}" data-hosting-id="${hosting.id}" style="cursor: pointer;">
					<div class="list-item__avatar">${serverIcon}</div>
					<div class="list-item__content">
						<div class="list-item__title">${this.escapeHtml(hosting.provider)} ${hosting.plan ? '- ' + this.escapeHtml(hosting.plan) : ''}</div>
						<div class="list-item__meta">
							<span>👤 ${client ? this.escapeHtml(client.name) : 'Atanmamış'}</span>
							${hosting.serverIp ? `<span>🖥️ ${this.escapeHtml(hosting.serverIp)}</span>` : ''}
							<span>📅 Ödeme: ${hosting.expirationDate || 'Belirtilmemiş'}</span>
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">Kalan</div>
							<div class="list-item__stat-value">${daysLeft > 0 ? daysLeft + ' gün' : 'Geçti'}</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">Fiyat</div>
							<div class="list-item__stat-value">${hosting.price ? currencySymbol + parseFloat(hosting.price).toFixed(2) : '-'}/${intervalText}</div>
						</div>
					</div>
					<div class="list-item__actions">
						<button class="btn-extend pay-hosting-btn" data-id="${hosting.id}" title="Ödeme Ekle">💳</button>
						<button class="icon-edit edit-hosting-btn" data-id="${hosting.id}" title="Düzenle"></button>
						<button class="icon-delete delete-hosting-btn" data-id="${hosting.id}" title="Sil"></button>
					</div>
				</div>
			`;
		});
		container.innerHTML = html;
		
		// Attach event listeners
		container.querySelectorAll('.list-item').forEach(item => {
			item.addEventListener('click', (e) => {
				if (e.target.closest('.list-item__actions')) return;
				const hostingItem = e.target.closest('.list-item');
				if (hostingItem) {
					const id = hostingItem.getAttribute('data-hosting-id');
					if (id) this.showHostingDetail(parseInt(id));
				}
			});
		});
		container.querySelectorAll('.pay-hosting-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.stopPropagation();
				const id = parseInt(e.target.getAttribute('data-id'));
				this.showHostingPaymentModal(id);
			});
		});
		container.querySelectorAll('.edit-hosting-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.stopPropagation();
				const id = parseInt(e.target.getAttribute('data-id'));
				this.showHostingModal(id);
			});
		});
		container.querySelectorAll('.delete-hosting-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.stopPropagation();
				const id = parseInt(e.target.getAttribute('data-id'));
				this.deleteHosting(id);
			});
		});
	},

	getIntervalText: function(interval) {
		const texts = { monthly: 'ay', quarterly: '3ay', yearly: 'yıl', biennial: '2yıl' };
		return texts[interval] || 'ay';
	},

	renderWebsites: function() {
			const container = document.getElementById('websites-list');
			if (this.websites.length === 0) {
				container.innerHTML = '<p class="empty-state">No websites found. Add your first website to get started.</p>';
				return;
			}

			let html = '<div class="domaincontrol-grid">';
			this.websites.forEach(website => {
				const client = this.clients.find(c => c.id === website.clientId);
				const domain = this.domains.find(d => d.id === website.domainId);
				const hosting = this.hostings.find(h => h.id === website.hostingId);
				html += `
					<div class="domaincontrol-card">
						<div class="card-header">
						<h4>${this.escapeHtml(website.software || 'Website')}</h4>
						<div class="card-actions">
							<button class="icon-edit edit-website-btn" data-id="${website.id}" title="Edit"></button>
							<button class="icon-delete delete-website-btn" data-id="${website.id}" title="Delete"></button>
						</div>
						</div>
						<div class="card-body">
							<p><strong>Client:</strong> ${client ? this.escapeHtml(client.name) : 'N/A'}</p>
							${domain ? `<p><strong>Domain:</strong> ${this.escapeHtml(domain.domainName)}</p>` : ''}
							${hosting ? `<p><strong>Hosting:</strong> ${this.escapeHtml(hosting.provider)}</p>` : ''}
							${website.installationDate ? `<p><strong>Installed:</strong> ${website.installationDate}</p>` : ''}
							${website.notes ? `<p class="notes">${this.escapeHtml(website.notes)}</p>` : ''}
						</div>
					</div>
				`;
		});
		html += '</div>';
		container.innerHTML = html;
		
		// Attach event listeners
		container.querySelectorAll('.edit-website-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				const id = parseInt(e.target.getAttribute('data-id'));
				this.showWebsiteModal(id);
			});
		});
		container.querySelectorAll('.delete-website-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				const id = parseInt(e.target.getAttribute('data-id'));
				this.deleteWebsite(id);
			});
		});
	},

	setupForms: function() {
			// Client form
			document.getElementById('client-form').addEventListener('submit', (e) => {
				e.preventDefault();
				this.saveClient();
			});

			// Domain form
			document.getElementById('domain-form').addEventListener('submit', (e) => {
				e.preventDefault();
				this.saveDomain();
			});

			// Hosting form
			document.getElementById('hosting-form').addEventListener('submit', (e) => {
				e.preventDefault();
				this.saveHosting();
			});

			// Website form
			document.getElementById('website-form').addEventListener('submit', (e) => {
				e.preventDefault();
				this.saveWebsite();
			});

			// Domain extend form
			document.getElementById('domain-extend-form').addEventListener('submit', (e) => {
				e.preventDefault();
				this.extendDomain();
			});

			// Update new expiry when years change
			document.getElementById('extend-years').addEventListener('change', () => {
				this.updateNewExpiryDate();
			});

			// Hosting payment form
			document.getElementById('hosting-payment-form')?.addEventListener('submit', (e) => {
				e.preventDefault();
				this.addHostingPayment();
			});

			// Update payment expiry when period changes
			document.getElementById('payment-period')?.addEventListener('change', () => {
				this.updatePaymentNewExpiry();
			});
		},

		showClientModal: function(id = null) {
			const modal = document.getElementById('client-modal');
			const form = document.getElementById('client-form');
			const title = document.getElementById('client-modal-title');
			
			form.reset();
			document.getElementById('client-id').value = '';
			
			if (id) {
				title.textContent = 'Edit Client';
				const client = this.clients.find(c => c.id === id);
				if (client) {
					document.getElementById('client-id').value = client.id;
					document.getElementById('client-name').value = client.name || '';
					document.getElementById('client-email').value = client.email || '';
					document.getElementById('client-phone').value = client.phone || '';
					document.getElementById('client-notes').value = client.notes || '';
				}
			} else {
				title.textContent = 'Add Client';
			}
			
			modal.style.display = 'block';
		},

		showDomainModal: function(id = null) {
		const modal = document.getElementById('domain-modal');
		const form = document.getElementById('domain-form');
		const title = document.getElementById('domain-modal-title');
		
		form.reset();
		document.getElementById('domain-id').value = '';
		this.updateClientSelects();
		
		if (id) {
			title.textContent = 'Domain Düzenle';
			const domain = this.domains.find(d => d.id == id);
			if (domain) {
				document.getElementById('domain-id').value = domain.id;
				document.getElementById('domain-client-id').value = domain.clientId || '';
				document.getElementById('domain-name').value = domain.domainName || '';
				document.getElementById('domain-registrar').value = domain.registrar || '';
				document.getElementById('domain-registration-date').value = domain.registrationDate || '';
				document.getElementById('domain-expiration-date').value = domain.expirationDate || '';
				document.getElementById('domain-price').value = domain.price || '';
				document.getElementById('domain-currency').value = domain.currency || 'USD';
				document.getElementById('domain-renewal-interval').value = domain.renewalInterval || '1';
				document.getElementById('domain-panel-notes').value = domain.panelNotes || '';
				document.getElementById('domain-notes').value = domain.notes || '';
			}
		} else {
			title.textContent = 'Domain Ekle';
		}
		
		modal.style.display = 'block';
	},

		showHostingModal: function(id = null) {
		const modal = document.getElementById('hosting-modal');
		const form = document.getElementById('hosting-form');
		const title = document.getElementById('hosting-modal-title');
		
		form.reset();
		document.getElementById('hosting-id').value = '';
		this.updateClientSelects();
		
		if (id) {
			title.textContent = 'Hosting Düzenle';
			const hosting = this.hostings.find(h => h.id == id);
			if (hosting) {
				document.getElementById('hosting-id').value = hosting.id;
				document.getElementById('hosting-client-id').value = hosting.clientId || '';
				document.getElementById('hosting-provider').value = hosting.provider || '';
				document.getElementById('hosting-plan').value = hosting.plan || '';
				document.getElementById('hosting-server-type').value = hosting.serverType || 'external';
				document.getElementById('hosting-server-ip').value = hosting.serverIp || '';
				document.getElementById('hosting-start-date').value = hosting.startDate || '';
				document.getElementById('hosting-expiration-date').value = hosting.expirationDate || '';
				document.getElementById('hosting-price').value = hosting.price || '';
				document.getElementById('hosting-currency').value = hosting.currency || 'USD';
				document.getElementById('hosting-renewal-interval').value = hosting.renewalInterval || 'yearly';
				document.getElementById('hosting-panel-url').value = hosting.panelUrl || '';
				document.getElementById('hosting-panel-notes').value = hosting.panelNotes || '';
				document.getElementById('hosting-notes').value = hosting.notes || '';
			}
		} else {
			title.textContent = 'Hosting Ekle';
		}
		
		modal.style.display = 'block';
	},

		showWebsiteModal: function(id = null) {
			const modal = document.getElementById('website-modal');
			const form = document.getElementById('website-form');
			const title = document.getElementById('website-modal-title');
			
			form.reset();
			document.getElementById('website-id').value = '';
			this.updateClientSelects();
			this.updateDomainSelect();
			this.updateHostingSelect();
			
			if (id) {
				title.textContent = 'Edit Website';
				const website = this.websites.find(w => w.id === id);
				if (website) {
					document.getElementById('website-id').value = website.id;
					document.getElementById('website-client-id').value = website.clientId || '';
					document.getElementById('website-domain-id').value = website.domainId || '';
					document.getElementById('website-hosting-id').value = website.hostingId || '';
					document.getElementById('website-software').value = website.software || '';
					document.getElementById('website-installation-date').value = website.installationDate || '';
					document.getElementById('website-notes').value = website.notes || '';
				}
			} else {
				title.textContent = 'Add Website';
			}
			
			modal.style.display = 'block';
		},

		updateDomainSelect: function() {
			const select = document.getElementById('website-domain-id');
			select.innerHTML = '<option value="">None</option>';
			this.domains.forEach(domain => {
				const option = document.createElement('option');
				option.value = domain.id;
				option.textContent = domain.domainName;
				select.appendChild(option);
			});
		},

		updateHostingSelect: function() {
			const select = document.getElementById('website-hosting-id');
			select.innerHTML = '<option value="">None</option>';
			this.hostings.forEach(hosting => {
				const option = document.createElement('option');
				option.value = hosting.id;
				option.textContent = hosting.provider + (hosting.plan ? ' - ' + hosting.plan : '');
				select.appendChild(option);
			});
		},

		closeModal: function(modalId) {
			document.getElementById(modalId).style.display = 'none';
		},

	saveClient: function() {
		const id = document.getElementById('client-id').value;
		const name = document.getElementById('client-name').value;
		const email = document.getElementById('client-email').value;
		const phone = document.getElementById('client-phone').value;
		const notes = document.getElementById('client-notes').value;

		console.log('saveClient:', { id, name, email, phone, notes });

		const url = id ? `${this.apiBase}/clients/${id}` : `${this.apiBase}/clients`;
		const method = id ? 'PUT' : 'POST';

		// URLSearchParams for form-urlencoded (Nextcloud standard)
		const params = new URLSearchParams();
		params.append('name', name);
		params.append('email', email);
		params.append('phone', phone);
		params.append('notes', notes);

		fetch(url, {
			method: method,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'requesttoken': OC.requestToken
			},
			body: params.toString()
		})
		.then(response => {
			console.log('Response status:', response.status);
			return response.json();
		})
		.then(data => {
			console.log('Response data:', data);
			if (data.error) {
				this.showError('Müşteri kaydedilemedi: ' + data.error);
			} else {
				this.closeModal('client-modal');
				const detailOpen = this.currentClientId;
				this.loadClients();
				// Refresh detail view if open
				if (detailOpen) {
					setTimeout(() => this.showClientDetail(detailOpen), 500);
				}
				this.showSuccess('Müşteri başarıyla kaydedildi');
			}
		})
		.catch(error => {
			console.error('Error saving client:', error);
			this.showError('Müşteri kaydedilemedi: ' + error.message);
		});
	},

		saveDomain: function() {
		const id = document.getElementById('domain-id').value;
		const clientId = document.getElementById('domain-client-id').value;
		const domainName = document.getElementById('domain-name').value;
		const registrar = document.getElementById('domain-registrar').value;
		const registrationDate = document.getElementById('domain-registration-date').value;
		const expirationDate = document.getElementById('domain-expiration-date').value;
		const price = document.getElementById('domain-price').value;
		const currency = document.getElementById('domain-currency').value;
		const renewalInterval = document.getElementById('domain-renewal-interval').value;
		const panelNotes = document.getElementById('domain-panel-notes').value;
		const notes = document.getElementById('domain-notes').value;

		console.log('saveDomain:', { id, clientId, domainName, currency });

		const url = id ? `${this.apiBase}/domains/${id}` : `${this.apiBase}/domains`;
		const method = id ? 'PUT' : 'POST';

		const params = new URLSearchParams();
		params.append('clientId', clientId);
		params.append('domainName', domainName);
		params.append('registrar', registrar);
		params.append('registrationDate', registrationDate);
		params.append('expirationDate', expirationDate);
		params.append('price', price);
		params.append('currency', currency);
		params.append('renewalInterval', renewalInterval);
		params.append('panelNotes', panelNotes);
		params.append('notes', notes);

		fetch(url, {
			method: method,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'requesttoken': OC.requestToken
			},
			body: params.toString()
		})
		.then(response => response.json())
		.then(result => {
			console.log('Domain save result:', result);
			if (result.error) {
				this.showError('Domain kaydedilemedi: ' + result.error);
			} else {
				this.closeModal('domain-modal');
				const detailOpen = this.currentDomainId;
				this.loadDomains();
				// Refresh detail view if open
				if (detailOpen) {
					setTimeout(() => this.showDomainDetail(detailOpen), 500);
				}
				this.showSuccess('Domain başarıyla kaydedildi');
			}
		})
		.catch(error => {
			console.error('Error saving domain:', error);
			this.showError('Domain kaydedilemedi: ' + error.message);
		});
	},

		saveHosting: function() {
		const id = document.getElementById('hosting-id').value;
		const clientId = document.getElementById('hosting-client-id').value;
		const provider = document.getElementById('hosting-provider').value;
		const plan = document.getElementById('hosting-plan').value;
		const serverType = document.getElementById('hosting-server-type').value;
		const serverIp = document.getElementById('hosting-server-ip').value;
		const startDate = document.getElementById('hosting-start-date').value;
		const expirationDate = document.getElementById('hosting-expiration-date').value;
		const price = document.getElementById('hosting-price').value;
		const currency = document.getElementById('hosting-currency').value;
		const renewalInterval = document.getElementById('hosting-renewal-interval').value;
		const panelUrl = document.getElementById('hosting-panel-url').value;
		const panelNotes = document.getElementById('hosting-panel-notes').value;
		const notes = document.getElementById('hosting-notes').value;

		console.log('saveHosting:', { id, clientId, provider, serverType });

		const url = id ? `${this.apiBase}/hostings/${id}` : `${this.apiBase}/hostings`;
		const method = id ? 'PUT' : 'POST';

		const params = new URLSearchParams();
		params.append('clientId', clientId);
		params.append('provider', provider);
		params.append('plan', plan);
		params.append('serverType', serverType);
		params.append('serverIp', serverIp);
		params.append('startDate', startDate);
		params.append('expirationDate', expirationDate);
		params.append('price', price);
		params.append('currency', currency);
		params.append('renewalInterval', renewalInterval);
		params.append('panelUrl', panelUrl);
		params.append('panelNotes', panelNotes);
		params.append('notes', notes);

		fetch(url, {
			method: method,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'requesttoken': OC.requestToken
			},
			body: params.toString()
		})
		.then(response => response.json())
		.then(result => {
			console.log('Hosting save result:', result);
			if (result.error) {
				this.showError('Hosting kaydedilemedi: ' + result.error);
			} else {
				this.closeModal('hosting-modal');
				const detailOpen = this.currentHostingId;
				this.loadHostings();
				if (detailOpen) {
					setTimeout(() => this.showHostingDetail(detailOpen), 500);
				}
				this.showSuccess('Hosting başarıyla kaydedildi');
			}
		})
		.catch(error => {
			console.error('Error saving hosting:', error);
			this.showError('Hosting kaydedilemedi: ' + error.message);
		});
	},

		saveWebsite: function() {
			const form = document.getElementById('website-form');
			const formData = new FormData(form);
			const data = Object.fromEntries(formData);
			const id = document.getElementById('website-id').value;

			data.clientId = parseInt(data.clientId);
			data.domainId = data.domainId ? parseInt(data.domainId) : 0;
			data.hostingId = data.hostingId ? parseInt(data.hostingId) : 0;

			const url = id ? `${this.apiBase}/websites/${id}` : `${this.apiBase}/websites`;
			const method = id ? 'PUT' : 'POST';

		fetch(url, {
			method: method,
			headers: {
				'Content-Type': 'application/json',
				'requesttoken': OC.requestToken
			},
			body: JSON.stringify(data)
		})
			.then(response => response.json())
			.then(result => {
				if (result.error) {
					this.showError(result.error);
				} else {
					this.closeModal('website-modal');
					this.loadWebsites();
					this.showSuccess('Website saved successfully');
				}
			})
			.catch(error => {
				console.error('Error saving website:', error);
				this.showError('Failed to save website');
			});
		},

		editClient: function(id) {
			this.showClientModal(id);
		},

		editDomain: function(id) {
			this.showDomainModal(id);
		},

		editHosting: function(id) {
			this.showHostingModal(id);
		},

		editWebsite: function(id) {
			this.showWebsiteModal(id);
		},

	deleteClient: function(id) {
		if (confirm('Are you sure you want to delete this client?')) {
			fetch(`${this.apiBase}/clients/${id}`, {
				method: 'DELETE',
				headers: {
					'requesttoken': OC.requestToken
				}
			})
				.then(response => response.json())
				.then(result => {
					if (result.error) {
						this.showError(result.error);
					} else {
						this.loadClients();
						this.showSuccess('Client deleted successfully');
					}
				})
				.catch(error => {
					console.error('Error deleting client:', error);
					this.showError('Failed to delete client');
				});
			}
		},

	deleteDomain: function(id) {
		if (confirm('Are you sure you want to delete this domain?')) {
			fetch(`${this.apiBase}/domains/${id}`, {
				method: 'DELETE',
				headers: {
					'requesttoken': OC.requestToken
				}
			})
				.then(response => response.json())
				.then(result => {
					if (result.error) {
						this.showError(result.error);
					} else {
						this.loadDomains();
						this.showSuccess('Domain deleted successfully');
					}
				})
				.catch(error => {
					console.error('Error deleting domain:', error);
					this.showError('Failed to delete domain');
				});
			}
		},

	deleteHosting: function(id) {
		if (confirm('Are you sure you want to delete this hosting?')) {
			fetch(`${this.apiBase}/hostings/${id}`, {
				method: 'DELETE',
				headers: {
					'requesttoken': OC.requestToken
				}
			})
				.then(response => response.json())
				.then(result => {
					if (result.error) {
						this.showError(result.error);
					} else {
						this.loadHostings();
						this.showSuccess('Hosting deleted successfully');
					}
				})
				.catch(error => {
					console.error('Error deleting hosting:', error);
					this.showError('Failed to delete hosting');
				});
			}
		},

	deleteWebsite: function(id) {
		if (confirm('Are you sure you want to delete this website?')) {
			fetch(`${this.apiBase}/websites/${id}`, {
				method: 'DELETE',
				headers: {
					'requesttoken': OC.requestToken
				}
			})
				.then(response => response.json())
				.then(result => {
					if (result.error) {
						this.showError(result.error);
					} else {
						this.loadWebsites();
						this.showSuccess('Website deleted successfully');
					}
				})
				.catch(error => {
					console.error('Error deleting website:', error);
					this.showError('Failed to delete website');
				});
			}
		},

		isExpiringSoon: function(expirationDate) {
			if (!expirationDate) return false;
			const exp = new Date(expirationDate);
			const now = new Date();
			const daysUntilExpiration = Math.ceil((exp - now) / (1000 * 60 * 60 * 24));
			return daysUntilExpiration <= 30 && daysUntilExpiration >= 0;
		},

		escapeHtml: function(text) {
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		},

		showError: function(message) {
			OC.Notification.showTemporary(message, { type: 'error' });
		},

		showSuccess: function(message) {
			OC.Notification.showTemporary(message, { type: 'success' });
		}
	};

	// Close modal when clicking outside
	window.addEventListener('click', function(event) {
		const modals = document.querySelectorAll('.modal');
		modals.forEach(modal => {
			if (event.target === modal) {
				modal.style.display = 'none';
			}
		});
	});

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => DomainControl.init());
	} else {
		DomainControl.init();
	}

	// Make DomainControl available globally
	window.DomainControl = DomainControl;
})();

