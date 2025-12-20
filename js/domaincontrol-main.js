// @ts-nocheck
/**
 * Domain Control - Main JavaScript
 * Version: 3.0.0
 * Build: 2024-12-19-INVOICES-PROJECTS
 * 
 * IMPORTANT: If you see "Initializing modern dashboard" in console,
 * this file is NOT loaded! Upload this file to server.
 * @fileoverview Main JavaScript file for ClientHub application
 */
(function () {
	'use strict';

	const DomainControl = {
		apiBase: OC.generateUrl('/apps/domaincontrol/api'),
		currentTab: 'dashboard',
		clients: [],
		domains: [],
		hostings: [],
		websites: [],
		serviceTypes: [],
		services: [],
		invoices: [],
		payments: [],
		projects: [],
		tasks: [],
		currentServiceId: null,
		currentInvoiceId: null,
		currentProjectId: null,
		currentTaskId: null,
		timerInterval: null,
		currentRunningEntry: null,
		timerButtonsSetup: false,

		init: function () {
			console.log('ClientHub: v3.3.0 Starting...');
			console.log('DomainControl: API Base:', this.apiBase);
			try {
				this.setupTabs();
				this.setupForms();
				this.setupButtons();
				this.loadData();
				this.switchTab(this.currentTab);
				this.updateDashboard();
				this.updateDashboardDate();

				// Set user name if available
				if (typeof OC !== 'undefined' && OC.getCurrentUser()) {
					const userNameEl = document.getElementById('current-user-name');
					if (userNameEl) userNameEl.textContent = OC.getCurrentUser().uid;
				}

				console.log('ClientHub: v3.3.0 Ready!');
			} catch (e) {
				console.error('DomainControl: Init error:', e);
			}
		},

		setupTabs: function () {
			const tabs = document.querySelectorAll('.tab-button');
			tabs.forEach(tab => {
				tab.addEventListener('click', (e) => {
					e.preventDefault();
					const target = e.currentTarget;
					const tabName = target.getAttribute('data-tab');
					this.switchTab(tabName);
				});
			});
		},

		setupButtons: function () {
			// Add buttons
			document.getElementById('add-client-btn')?.addEventListener('click', () => this.showClientModal());
			document.getElementById('select-from-contacts-btn')?.addEventListener('click', () => this.showContactsModal());
			document.getElementById('add-domain-btn')?.addEventListener('click', () => this.showDomainModal());
			document.getElementById('test-email-btn')?.addEventListener('click', () => this.testEmailReminders());
			document.getElementById('add-hosting-btn')?.addEventListener('click', () => this.showHostingModal());
			document.getElementById('add-website-btn')?.addEventListener('click', () => this.showWebsiteModal());
			document.getElementById('add-service-btn')?.addEventListener('click', () => this.showServiceModal());
			document.getElementById('add-invoice-btn')?.addEventListener('click', () => this.showInvoiceModal());
			document.getElementById('add-project-btn')?.addEventListener('click', () => this.showProjectModal());
			document.getElementById('add-task-btn')?.addEventListener('click', () => this.showTaskModal());

			// Quick add buttons with tab switching
			document.getElementById('quick-add-client')?.addEventListener('click', () => {
				this.switchTab('clients');
				this.showClientModal();
			});
			document.getElementById('quick-add-domain')?.addEventListener('click', () => {
				this.switchTab('domains');
				this.showDomainModal();
			});
			document.getElementById('quick-add-hosting')?.addEventListener('click', () => {
				this.switchTab('hostings');
				this.showHostingModal();
			});
			document.getElementById('quick-add-website')?.addEventListener('click', () => {
				this.switchTab('websites');
				this.showWebsiteModal();
			});
			document.getElementById('quick-add-invoice')?.addEventListener('click', () => {
				this.switchTab('invoices');
				this.showInvoiceModal();
			});
			document.getElementById('quick-add-payment')?.addEventListener('click', () => {
				this.switchTab('payments');
				this.showPaymentModal();
			});
			document.getElementById('quick-add-project')?.addEventListener('click', () => {
				this.switchTab('projects');
				this.showProjectModal();
			});
			document.getElementById('quick-add-task')?.addEventListener('click', () => {
				this.switchTab('tasks');
				this.showTaskModal();
			});

			// Service type management
			document.getElementById('manage-service-types-btn')?.addEventListener('click', () => this.showServiceTypesModal());
			document.getElementById('add-service-type-btn')?.addEventListener('click', () => this.showServiceTypeModal());
			document.getElementById('init-predefined-btn')?.addEventListener('click', () => this.initPredefinedServiceTypes());

			// Service detail buttons
			document.getElementById('back-to-services-btn')?.addEventListener('click', () => this.hideServiceDetail());
			document.getElementById('service-detail-edit-btn')?.addEventListener('click', () => {
				if (this.currentServiceId) this.showServiceModal(this.currentServiceId);
			});
			document.getElementById('service-detail-delete-btn')?.addEventListener('click', () => {
				if (this.currentServiceId && confirm('Bu hizmeti silmek istediğinize emin misiniz?')) {
					this.deleteService(this.currentServiceId);
					this.hideServiceDetail();
				}
			});
			document.getElementById('service-create-invoice-btn')?.addEventListener('click', () => {
				if (this.currentServiceId) this.createInvoiceFromService(this.currentServiceId);
			});
			document.getElementById('service-extend-btn')?.addEventListener('click', () => {
				if (this.currentServiceId) this.showServiceExtendModal(this.currentServiceId);
			});

			// Invoice detail buttons
			document.getElementById('back-to-invoices-btn')?.addEventListener('click', () => this.hideInvoiceDetail());
			document.getElementById('invoice-add-payment-btn')?.addEventListener('click', () => {
				if (this.currentInvoiceId) this.showPaymentModal(null, this.currentInvoiceId);
			});
			document.getElementById('invoice-detail-edit-btn')?.addEventListener('click', () => {
				if (this.currentInvoiceId) this.showInvoiceModal(this.currentInvoiceId);
			});
			document.getElementById('invoice-detail-delete-btn')?.addEventListener('click', () => {
				if (this.currentInvoiceId && confirm('Bu faturayı silmek istediğinize emin misiniz?')) {
					this.deleteInvoice(this.currentInvoiceId);
					this.hideInvoiceDetail();
				}
			});
			document.getElementById('invoice-add-item-btn')?.addEventListener('click', () => {
				if (this.currentInvoiceId) this.showInvoiceItemModal();
			});

			// Invoice status change buttons
			document.querySelectorAll('.change-invoice-status-btn').forEach(btn => {
				btn.addEventListener('click', () => {
					const status = btn.dataset.status;
					if (status) this.changeInvoiceStatus(status);
				});
			});

			// Project detail buttons
			document.getElementById('back-to-projects-btn')?.addEventListener('click', () => this.hideProjectDetail());
			document.getElementById('project-add-task-btn')?.addEventListener('click', () => {
				if (this.currentProjectId) this.showTaskModal(null, this.currentProjectId);
			});
			document.getElementById('project-add-item-btn')?.addEventListener('click', () => {
				if (this.currentProjectId) this.showProjectItemModal(this.currentProjectId);
			});
			document.getElementById('project-detail-edit-btn')?.addEventListener('click', () => {
				if (this.currentProjectId) this.showProjectModal(this.currentProjectId);
			});
			document.getElementById('project-detail-delete-btn')?.addEventListener('click', () => {
				if (this.currentProjectId && confirm('Bu projeyi silmek istediğinize emin misiniz?')) {
					this.deleteProject(this.currentProjectId);
					this.hideProjectDetail();
				}
			});

			// Task detail buttons
			document.getElementById('back-to-tasks-btn')?.addEventListener('click', () => this.hideTaskDetail());
			document.getElementById('task-toggle-btn')?.addEventListener('click', () => {
				if (this.currentTaskId) this.toggleTaskStatus(this.currentTaskId);
			});
			document.getElementById('task-postpone-btn')?.addEventListener('click', () => {
				if (this.currentTaskId) this.postponeTask(this.currentTaskId);
			});
			document.getElementById('task-cancel-btn')?.addEventListener('click', () => {
				if (this.currentTaskId) this.cancelTask(this.currentTaskId);
			});
			document.getElementById('task-add-note-btn')?.addEventListener('click', () => {
				if (this.currentTaskId) this.showTaskModal(this.currentTaskId); // Re-use modal for notes
			});
			document.getElementById('add-subtask-btn')?.addEventListener('click', () => {
				if (this.currentTaskId) {
					const task = this.tasks.find(t => t.id == this.currentTaskId);
					this.showTaskModal(null, task.projectId, task.id);
				}
			});
			document.getElementById('task-detail-edit-btn')?.addEventListener('click', () => {
				if (this.currentTaskId) this.showTaskModal(this.currentTaskId);
			});
			document.getElementById('task-detail-delete-btn')?.addEventListener('click', () => {
				if (this.currentTaskId && confirm('Bu görevi silmek istediğinize emin misiniz?')) {
					this.deleteTask(this.currentTaskId);
					this.hideTaskDetail();
				}
			});

			// Filter buttons
			document.querySelectorAll('.filter-buttons .btn-filter').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const filter = e.target.getAttribute('data-filter');
					const parent = e.target.closest('.filter-buttons');
					parent.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
					e.target.classList.add('active');
					this.applyFilter(filter);
				});
			});

			// Modal close buttons
			document.querySelectorAll('.modal-close, .modal-cancel').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const modalId = e.target.getAttribute('data-modal');
					if (modalId) {
						this.closeModal(modalId);
					}
				});
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

			// Website detail buttons
			document.getElementById('back-to-websites-btn')?.addEventListener('click', () => this.hideWebsiteDetail());
			document.getElementById('website-detail-edit-btn')?.addEventListener('click', () => {
				if (this.currentWebsiteId) {
					this.showWebsiteModal(this.currentWebsiteId);
				}
			});
			document.getElementById('website-detail-delete-btn')?.addEventListener('click', () => {
				if (this.currentWebsiteId && confirm('Bu websiteyi silmek istediğinize emin misiniz?')) {
					this.deleteWebsite(this.currentWebsiteId);
					this.hideWebsiteDetail();
				}
			});

			// Website file upload
			document.getElementById('website-upload-file-btn')?.addEventListener('click', () => {
				document.getElementById('website-file-input')?.click();
			});
			document.getElementById('website-file-input')?.addEventListener('change', (e) => {
				if (this.currentWebsiteId && e.target.files.length > 0) {
					this.uploadWebsiteFiles(this.currentWebsiteId, Array.from(e.target.files));
				}
			});

			// Invoice file upload
			document.getElementById('invoice-upload-files-btn')?.addEventListener('click', () => {
				if (this.currentInvoiceId) {
					const fileInput = document.getElementById('invoice-file-input');
					if (fileInput && fileInput.files.length > 0) {
						this.uploadInvoiceFiles(this.currentInvoiceId, Array.from(fileInput.files));
					} else {
						fileInput?.click();
					}
				}
			});
			// Remove change event - only upload when button is clicked
		},

		switchTab: function (tabName) {
			if (!tabName) {
				console.warn('DomainControl: Tab name is required');
				return;
			}

			this.currentTab = tabName;

			document.querySelectorAll('#app-navigation li').forEach(li => {
				li.classList.remove('active');
			});
			const activeButton = document.querySelector(`.tab-button[data-tab="${tabName}"]`);
			if (activeButton) {
				const li = activeButton.closest('li');
				if (li) li.classList.add('active');
			}

			// Update tab content
			document.querySelectorAll('.tab-content').forEach(content => {
				content.classList.remove('active');
			});
			const activeTab = document.getElementById(`${tabName}-tab`);
			if (activeTab) {
				activeTab.classList.add('active');
			} else {
				console.error('DomainControl: Tab content not found for:', tabName);
				return;
			}

			// Load data for the tab
			this.loadTabData(tabName);
		},

		loadData: function () {
			this.loadClients();
			this.loadDomains();
			this.loadHostings();
			this.loadWebsites();
			this.loadServiceTypes();
			this.loadServices();
			this.loadInvoices();
			this.loadPayments();
			this.loadProjects();
			this.loadTasks();
		},

		loadTabData: function (tabName) {
			switch (tabName) {
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
				case 'services':
					this.loadServices();
					break;
				case 'invoices':
					this.loadInvoices();
					break;
				case 'projects':
					this.loadProjects();
					break;
				case 'tasks':
					this.loadTasks();
					break;
				case 'reports':
					if (window.Reports && window.Reports.loadReports) {
						window.Reports.loadReports();
					}
					break;
			}
		},

		updateDashboard: function () {
			// Update stats (null-safe)
			const statClients = document.getElementById('stat-clients');
			const statDomains = document.getElementById('stat-domains');
			const statHostings = document.getElementById('stat-hostings');
			const statWebsites = document.getElementById('stat-websites');
			const statProjects = document.getElementById('stat-projects');
			const statTasks = document.getElementById('stat-tasks');
			const statUnpaidInvoices = document.getElementById('stat-unpaid-invoices');
			const statMonthlyIncome = document.getElementById('stat-monthly-income');

			if (statClients) statClients.textContent = this.clients.length;
			if (statDomains) statDomains.textContent = this.domains.length;
			if (statHostings) statHostings.textContent = this.hostings.length;
			if (statWebsites) statWebsites.textContent = this.websites.length;

			// New stats
			const activeProjects = (this.projects || []).filter(p => p.status === 'active');
			const pendingTasks = (this.tasks || []).filter(t => t.status !== 'done');
			const unpaidInvoices = (this.invoices || []).filter(i => ['draft', 'sent', 'overdue'].includes(i.status));

			if (statProjects) statProjects.textContent = activeProjects.length;
			if (statTasks) statTasks.textContent = pendingTasks.length;
			if (statUnpaidInvoices) statUnpaidInvoices.textContent = unpaidInvoices.length;

			// Calculate monthly income
			this.loadMonthlyIncome();

			// Update alert panels
			this.updateAlertPanels();

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

		updateDashboardDate: function () {
			const dateEl = document.getElementById('dashboard-current-date');
			if (!dateEl) return;
			const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
			dateEl.textContent = new Date().toLocaleDateString('tr-TR', options);
		},

		loadClients: function () {
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

		loadDomains: function () {
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

		loadHostings: function () {
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

		loadWebsites: function () {
			console.log('DomainControl: Loading websites...');
			fetch(this.apiBase + '/websites', {
				headers: {
					'requesttoken': OC.requestToken
				}
			})
				.then(response => {
					console.log('Websites response status:', response.status);
					return response.json();
				})
				.then(data => {
					console.log('Websites loaded:', data);
					if (data.error) {
						throw new Error(data.error);
					}
					this.websites = Array.isArray(data) ? data : [];
					this.renderWebsites();
					this.updateDashboard();
				})
				.catch(error => {
					console.error('Error loading websites:', error);
					this.showError('Websiteler yüklenemedi: ' + error.message);
					this.websites = [];
					this.renderWebsites();
				});
		},

		updateClientSelects: function () {
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

		renderClients: function () {
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

		renderDomains: function () {
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

		showExtendDomainModal: function (id) {
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

		updateNewExpiryDate: function () {
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

		extendDomain: function () {
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
				} catch (e) {
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

		getCurrencySymbol: function (currency) {
			const symbols = { USD: '$', EUR: '€', TRY: '₺', AZN: '₼', GBP: '£', RUB: '₽' };
			return symbols[currency] || '$';
		},

		getDaysUntilExpiry: function (expirationDate) {
			if (!expirationDate) return 999;
			const exp = new Date(expirationDate);
			const now = new Date();
			const diff = exp - now;
			return Math.ceil(diff / (1000 * 60 * 60 * 24));
		},

		showDomainDetail: function (id) {
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

		renderDomainNotesAndHistory: function (domain) {
			const notesEl = document.getElementById('domain-detail-notes');
			const historyEl = document.getElementById('domain-detail-history');

			// Display panel notes
			notesEl.textContent = domain.panelNotes || 'Panel giriş bilgisi eklenmemiş';

			// Display renewal history from renewalHistory field (JSON)
			let historyEntries = [];
			if (domain.renewalHistory) {
				try {
					historyEntries = JSON.parse(domain.renewalHistory);
				} catch (e) {
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

		hideDomainDetail: function () {
			document.getElementById('domains-list-view').style.display = 'block';
			document.getElementById('domain-detail-view').style.display = 'none';
			this.currentDomainId = null;
		},

		showClientDetail: function (id) {
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

			// Render client's services
			const clientServices = (this.services || []).filter(s => s.clientId == id);
			const servicesListEl = document.getElementById('client-services-list');
			if (servicesListEl) {
				if (clientServices.length === 0) {
					servicesListEl.innerHTML = '<p class="empty-mini">Hizmet yok</p>';
				} else {
					let servicesHtml = '';
					clientServices.forEach(s => {
						const statusClass = s.status === 'active' ? 'status-ok' : 'status-warning';
						servicesHtml += `<div class="mini-item ${statusClass}" data-service-id="${s.id}" style="cursor:pointer;"><span>🛠️ ${this.escapeHtml(s.name)}</span><span>${s.price || 0} ${s.currency}</span></div>`;
					});
					servicesListEl.innerHTML = servicesHtml;

					// Add click listeners
					servicesListEl.querySelectorAll('.mini-item').forEach(item => {
						item.addEventListener('click', () => {
							const sId = item.getAttribute('data-service-id');
							if (sId) {
								this.hideClientDetail();
								this.switchTab('services');
								setTimeout(() => this.showServiceDetail(parseInt(sId)), 100);
							}
						});
					});
				}
			}

			// Render client's invoices
			const clientInvoices = (this.invoices || []).filter(i => i.clientId == id);
			const invoicesListEl = document.getElementById('client-invoices-list');
			if (invoicesListEl) {
				if (clientInvoices.length === 0) {
					invoicesListEl.innerHTML = '<p class="empty-mini">Fatura yok</p>';
				} else {
					let invoicesHtml = '';
					clientInvoices.forEach(inv => {
						const statusClass = inv.status === 'paid' ? 'status-ok' : (inv.status === 'overdue' ? 'status-critical' : 'status-warning');
						invoicesHtml += `<div class="mini-item ${statusClass}" data-invoice-id="${inv.id}" style="cursor:pointer;"><span>📄 ${inv.invoiceNumber}</span><span>${inv.totalAmount || 0} ${inv.currency}</span></div>`;
					});
					invoicesListEl.innerHTML = invoicesHtml;

					invoicesListEl.querySelectorAll('.mini-item').forEach(item => {
						item.addEventListener('click', () => {
							const invId = item.getAttribute('data-invoice-id');
							if (invId) {
								this.hideClientDetail();
								this.switchTab('invoices');
								setTimeout(() => this.showInvoiceDetail(parseInt(invId)), 100);
							}
						});
					});
				}
			}

			// Render client's payments
			const clientPayments = (this.payments || []).filter(p => p.clientId == id);
			const paymentsListEl = document.getElementById('client-payments-list');
			if (paymentsListEl) {
				if (clientPayments.length === 0) {
					paymentsListEl.innerHTML = '<p class="empty-mini">Ödeme yok</p>';
				} else {
					let paymentsHtml = '';
					clientPayments.slice(0, 5).forEach(p => {
						paymentsHtml += `<div class="mini-item status-ok"><span>💳 ${p.paymentDate || '-'}</span><span>${p.amount || 0} ${p.currency}</span></div>`;
					});
					paymentsListEl.innerHTML = paymentsHtml;
				}
			}

			// Store current client id
			this.currentClientId = client.id;
		},

		hideClientDetail: function () {
			document.getElementById('clients-list-view').style.display = 'block';
			document.getElementById('client-detail-view').style.display = 'none';
			this.currentClientId = null;
		},

		showHostingDetail: function (id) {
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

		hideHostingDetail: function () {
			document.getElementById('hostings-list-view').style.display = 'block';
			document.getElementById('hosting-detail-view').style.display = 'none';
			this.currentHostingId = null;
		},

		showHostingPaymentModal: function (id) {
			const hosting = this.hostings.find(h => h.id == id);
			if (!hosting) return;

			const modal = document.getElementById('hosting-payment-modal');
			document.getElementById('hp-hosting-id').value = hosting.id;
			document.getElementById('hp-hosting-name').textContent = hosting.provider + (hosting.plan ? ' - ' + hosting.plan : '');
			document.getElementById('hp-current-expiry').textContent = hosting.expirationDate || 'Belirtilmemiş';
			document.getElementById('hp-amount').value = hosting.price || '';
			document.getElementById('hp-currency').value = hosting.currency || 'USD';
			document.getElementById('hp-period').value = hosting.renewalInterval === 'yearly' ? '12' : (hosting.renewalInterval === 'monthly' ? '1' : '12');
			document.getElementById('hp-note').value = '';

			this.updateHostingPaymentNewExpiry();

			modal.style.display = 'block';
		},

		updateHostingPaymentNewExpiry: function () {
			const currentExpiry = document.getElementById('hp-current-expiry').textContent;
			const months = parseInt(document.getElementById('hp-period').value) || 1;

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
			document.getElementById('hp-new-expiry').textContent = formatted;
		},

		addHostingPayment: function () {
			const id = document.getElementById('hp-hosting-id').value;
			const amount = document.getElementById('hp-amount').value;
			const currency = document.getElementById('hp-currency').value;
			const months = parseInt(document.getElementById('hp-period').value);
			const note = document.getElementById('hp-note').value;
			const newExpiry = document.getElementById('hp-new-expiry').textContent;

			const hosting = this.hostings.find(h => h.id == id);
			if (!hosting) return;

			// Parse existing history
			let history = [];
			if (hosting.paymentHistory) {
				try {
					history = JSON.parse(hosting.paymentHistory);
				} catch (e) {
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

		renderHostingPaymentHistory: function (hosting) {
			const historyEl = document.getElementById('hosting-detail-payments');

			let historyEntries = [];
			if (hosting.paymentHistory) {
				try {
					historyEntries = JSON.parse(hosting.paymentHistory);
				} catch (e) {
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

		renderHostings: function () {
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

		getIntervalText: function (interval) {
			const texts = { monthly: 'ay', quarterly: '3ay', yearly: 'yıl', biennial: '2yıl' };
			return texts[interval] || 'ay';
		},

		renderWebsites: function () {
			const container = document.getElementById('websites-list');
			if (!container) return;

			if (!this.websites || this.websites.length === 0) {
				container.innerHTML = '<p class="empty-state">Henüz website eklenmemiş. İlk websitenizi ekleyin.</p>';
				return;
			}

			let html = '';
			this.websites.forEach(website => {
				const client = (this.clients || []).find(c => c.id == website.clientId);
				const domain = (this.domains || []).find(d => d.id == website.domainId);
				const hosting = (this.hostings || []).find(h => h.id == website.hostingId);
				const statusIcon = this.getWebsiteStatusIcon(website.status);
				const statusClass = website.status === 'active' ? 'status-ok' : (website.status === 'inactive' ? 'status-critical' : 'status-warning');

				html += `
				<div class="list-item ${statusClass}" data-website-id="${website.id}" style="cursor: pointer;">
					<div class="list-item__avatar">🌍</div>
					<div class="list-item__content">
						<div class="list-item__title">${this.escapeHtml(website.name || website.software || 'Website')}</div>
						<div class="list-item__meta">
							<span>👤 ${client ? this.escapeHtml(client.name) : 'Atanmamış'}</span>
							${domain ? `<span>🌐 ${this.escapeHtml(domain.domainName)}</span>` : ''}
							${website.software ? `<span>📦 ${this.escapeHtml(website.software)}</span>` : ''}
						</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">Durum</div>
							<div class="list-item__stat-value">${statusIcon}</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">Hosting</div>
							<div class="list-item__stat-value">${hosting ? this.escapeHtml(hosting.provider) : '-'}</div>
						</div>
					</div>
					<div class="list-item__actions">
						<button class="icon-edit edit-website-btn" data-id="${website.id}" title="Düzenle"></button>
						<button class="icon-delete delete-website-btn" data-id="${website.id}" title="Sil"></button>
					</div>
				</div>
			`;
			});
			container.innerHTML = html;

			// Attach event listeners
			container.querySelectorAll('.list-item').forEach(item => {
				item.addEventListener('click', (e) => {
					if (e.target.closest('.list-item__actions')) return;
					const websiteItem = e.target.closest('.list-item');
					if (websiteItem) {
						const id = websiteItem.getAttribute('data-website-id');
						if (id) this.showWebsiteDetail(parseInt(id));
					}
				});
			});
			container.querySelectorAll('.edit-website-btn').forEach(btn => {
				btn.addEventListener('click', (e) => {
					e.stopPropagation();
					const id = parseInt(e.target.getAttribute('data-id'));
					this.showWebsiteModal(id);
				});
			});
			container.querySelectorAll('.delete-website-btn').forEach(btn => {
				btn.addEventListener('click', (e) => {
					e.stopPropagation();
					const id = parseInt(e.target.getAttribute('data-id'));
					this.deleteWebsite(id);
				});
			});
		},

		getWebsiteStatusIcon: function (status) {
			const icons = {
				'active': '🟢 Aktif',
				'maintenance': '🟡 Bakımda',
				'development': '🔵 Geliştirme',
				'inactive': '🔴 Pasif'
			};
			return icons[status] || '⚪ Bilinmiyor';
		},

		showWebsiteDetail: function (id) {
			const website = this.websites.find(w => w.id == id);
			if (!website) return;

			const client = (this.clients || []).find(c => c.id == website.clientId);
			const domain = (this.domains || []).find(d => d.id == website.domainId);
			const hosting = (this.hostings || []).find(h => h.id == website.hostingId);

			// Hide list, show detail
			document.getElementById('websites-list-view').style.display = 'none';
			document.getElementById('website-detail-view').style.display = 'block';

			// Fill detail info
			document.getElementById('website-detail-name').textContent = website.name || website.software || 'Website';
			document.getElementById('website-detail-software').textContent = website.software || '-';
			document.getElementById('website-detail-version').textContent = website.version || '-';
			document.getElementById('website-detail-status').textContent = this.getWebsiteStatusIcon(website.status);
			document.getElementById('website-detail-install-date').textContent = website.installationDate || '-';

			document.getElementById('website-detail-client').textContent = client ? client.name : 'Atanmamış';
			document.getElementById('website-detail-domain').textContent = domain ? domain.domainName : '-';
			document.getElementById('website-detail-hosting').textContent = hosting ? hosting.provider : '-';

			const urlEl = document.getElementById('website-detail-url');
			if (website.url) {
				urlEl.innerHTML = `<a href="${this.escapeHtml(website.url)}" target="_blank">${this.escapeHtml(website.url)}</a>`;
			} else {
				urlEl.textContent = '-';
			}

			// Admin info
			const adminUrlEl = document.getElementById('website-detail-admin-url');
			if (website.adminUrl) {
				adminUrlEl.innerHTML = `<a href="${this.escapeHtml(website.adminUrl)}" target="_blank">${this.escapeHtml(website.adminUrl)}</a>`;
			} else {
				adminUrlEl.textContent = '';
			}
			document.getElementById('website-detail-admin-notes').textContent = website.adminNotes || 'Admin giriş bilgisi eklenmemiş';
			// Display notes as HTML (from rich text editor)
			const notesEl = document.getElementById('website-detail-notes');
			if (notesEl) {
				if (website.notes && website.notes.trim()) {
					notesEl.innerHTML = website.notes;
				} else {
					notesEl.textContent = 'Not bulunmuyor';
				}
			}

			this.currentWebsiteId = website.id;

			// Load website files
			this.loadWebsiteFiles(website.id);
		},

		loadWebsiteFiles: function (websiteId) {
			fetch(`${this.apiBase}/websites/${websiteId}/files`, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(files => {
					const container = document.getElementById('website-files-list');
					if (!container) return;

					if (!files || files.length === 0) {
						container.innerHTML = '<p class="empty-message">Henüz dosya yüklenmemiş</p>';
						return;
					}

					let html = '<div class="files-grid">';
					files.forEach(file => {
						const fileSize = this.formatFileSize(file.size);
						const fileIcon = this.getFileIcon(file.mimeType);
						const fileDate = new Date(file.mtime * 1000).toLocaleDateString('tr-TR');

						// Generate file URL
						const fileUrl = this.getWebsiteFileUrl(file.path, file.name);

						html += `
					<div class="file-item">
						<div class="file-icon">${fileIcon}</div>
						<div class="file-info">
							<div class="file-name">${this.escapeHtml(file.name)}</div>
							<div class="file-meta">${fileSize} • ${fileDate}</div>
						</div>
						<div class="file-actions">
							<a href="${fileUrl}" target="_blank" class="btn btn-sm btn-secondary" title="Aç">👁️</a>
							<button class="btn btn-sm btn-danger delete-file-btn" data-file="${this.escapeHtml(file.name)}" title="Sil">🗑️</button>
						</div>
					</div>
				`;
					});
					html += '</div>';
					container.innerHTML = html;

					// Event listeners for delete buttons
					container.querySelectorAll('.delete-file-btn').forEach(btn => {
						btn.addEventListener('click', () => {
							const fileName = btn.dataset.file;
							if (confirm(`"${fileName}" dosyasını silmek istediğinize emin misiniz?`)) {
								this.deleteWebsiteFile(websiteId, fileName);
							}
						});
					});
				})
				.catch(e => {
					console.error('Error loading website files:', e);
					const container = document.getElementById('website-files-list');
					if (container) {
						container.innerHTML = '<p class="empty-message">Dosyalar yüklenirken hata oluştu</p>';
					}
				});
		},

		uploadWebsiteFiles: function (websiteId, files) {
			if (!files || files.length === 0) {
				this.showError('Lütfen yüklenecek dosya seçin');
				return;
			}

			const uploadBtn = document.getElementById('website-upload-file-btn');
			const fileInput = document.getElementById('website-file-input');

			// Disable button during upload
			if (uploadBtn) {
				uploadBtn.disabled = true;
				uploadBtn.textContent = '⏳ Yükleniyor...';
			}

			const formData = new FormData();
			// Append files - PHP will handle as array if multiple
			files.forEach(file => {
				formData.append('file[]', file);
			});

			fetch(`${this.apiBase}/websites/${websiteId}/files`, {
				method: 'POST',
				headers: { 'requesttoken': OC.requestToken },
				body: formData
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					const fileCount = result.files ? result.files.length : 1;
					this.showSuccess(`${fileCount} dosya başarıyla yüklendi`);
					this.loadWebsiteFiles(websiteId);
					// Clear file input to allow re-uploading
					if (fileInput) {
						fileInput.value = '';
					}
				})
				.catch(e => {
					this.showError('Dosya yükleme hatası: ' + e.message);
				})
				.finally(() => {
					// Re-enable button
					if (uploadBtn) {
						uploadBtn.disabled = false;
						uploadBtn.textContent = '📤 Dosya Yükle';
					}
				});
		},

		deleteWebsiteFile: function (websiteId, fileName) {
			fetch(`${this.apiBase}/websites/${websiteId}/files/${encodeURIComponent(fileName)}`, {
				method: 'DELETE',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.showSuccess('Dosya silindi');
					this.loadWebsiteFiles(websiteId);
				})
				.catch(e => {
					this.showError('Dosya silme hatası: ' + e.message);
				});
		},

		formatFileSize: function (bytes) {
			if (bytes === 0) return '0 Bytes';
			const k = 1024;
			const sizes = ['Bytes', 'KB', 'MB', 'GB'];
			const i = Math.floor(Math.log(bytes) / Math.log(k));
			return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
		},

		getFileIcon: function (mimeType) {
			if (!mimeType) return '📄';
			if (mimeType.startsWith('image/')) return '🖼️';
			if (mimeType.startsWith('video/')) return '🎥';
			if (mimeType.startsWith('audio/')) return '🎵';
			if (mimeType.includes('pdf')) return '📕';
			if (mimeType.includes('zip') || mimeType.includes('rar')) return '📦';
			if (mimeType.includes('word')) return '📝';
			if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return '📊';
			if (mimeType.includes('database') || mimeType.includes('sql')) return '🗄️';
			if (mimeType.includes('code') || mimeType.includes('text')) return '💻';
			return '📄';
		},

		getWebsiteFileUrl: function (filePath, fileName) {
			// Generate URL to Nextcloud Files app
			// Format: /apps/files/?dir=/path/to/dir&scrollto=filename
			const dirPath = filePath.substring(0, filePath.lastIndexOf('/'));
			const encodedDir = encodeURIComponent(dirPath);
			const encodedFile = encodeURIComponent(fileName);
			return OC.generateUrl('/apps/files/') + '?dir=' + encodedDir + '&scrollto=' + encodedFile;
		},

		// Invoice Files Functions
		loadInvoiceFiles: function (invoiceId) {
			fetch(`${this.apiBase}/invoices/${invoiceId}/files`, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(files => {
					this.renderInvoiceFiles(invoiceId, files);
				})
				.catch(e => {
					console.error('Error loading invoice files:', e);
					const container = document.getElementById('invoice-files-list');
					if (container) {
						container.innerHTML = '<p class="empty-message">Dosyalar yüklenirken hata oluştu</p>';
					}
				});
		},

		renderInvoiceFiles: function (invoiceId, files) {
			const container = document.getElementById('invoice-files-list');
			if (!container) return;

			if (!files || files.length === 0) {
				container.innerHTML = '<p class="empty-message">Henüz dosya eklenmemiş</p>';
				return;
			}

			let html = '<div class="file-list">';
			files.forEach(file => {
				const fileSize = this.formatFileSize(file.size);
				const fileIcon = this.getFileIcon(file.mimeType);
				const fileDate = new Date(file.mtime * 1000).toLocaleDateString('tr-TR');

				// Generate file URL
				const fileUrl = this.getInvoiceFileUrl(file.path, file.name);

				html += `
				<div class="file-item">
					<div class="file-icon">${fileIcon}</div>
					<div class="file-info">
						<div class="file-name">${this.escapeHtml(file.name)}</div>
						<div class="file-meta">${fileSize} • ${fileDate}</div>
					</div>
					<div class="file-actions">
						<a href="${fileUrl}" target="_blank" class="btn btn-sm btn-secondary" title="Aç">👁️</a>
						<button class="btn btn-sm btn-danger delete-invoice-file-btn" data-file="${this.escapeHtml(file.name)}" title="Sil">🗑️</button>
					</div>
				</div>
			`;
			});
			html += '</div>';
			container.innerHTML = html;

			// Event listeners for delete buttons
			container.querySelectorAll('.delete-invoice-file-btn').forEach(btn => {
				btn.addEventListener('click', () => {
					const fileName = btn.dataset.file;
					if (confirm(`"${fileName}" dosyasını silmek istediğinize emin misiniz?`)) {
						this.deleteInvoiceFile(invoiceId, fileName);
					}
				});
			});
		},

		uploadInvoiceFiles: function (invoiceId, files) {
			if (!files || files.length === 0) {
				this.showError('Lütfen yüklenecek dosya seçin');
				return;
			}

			const uploadBtn = document.getElementById('invoice-upload-files-btn');
			const fileInput = document.getElementById('invoice-file-input');

			// Disable button during upload
			if (uploadBtn) {
				uploadBtn.disabled = true;
				uploadBtn.textContent = '⏳ Yükleniyor...';
			}

			const formData = new FormData();
			// Append files - PHP will handle as array if multiple
			files.forEach(file => {
				formData.append('file[]', file);
			});

			fetch(`${this.apiBase}/invoices/${invoiceId}/files`, {
				method: 'POST',
				headers: { 'requesttoken': OC.requestToken },
				body: formData
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					const fileCount = result.files ? result.files.length : 1;
					this.showSuccess(`${fileCount} dosya başarıyla yüklendi`);
					this.loadInvoiceFiles(invoiceId);
					// Clear file input to allow re-uploading
					if (fileInput) {
						fileInput.value = '';
					}
				})
				.catch(e => {
					this.showError('Dosya yükleme hatası: ' + e.message);
				})
				.finally(() => {
					// Re-enable button
					if (uploadBtn) {
						uploadBtn.disabled = false;
						uploadBtn.textContent = '📤 Dosyaları Yükle';
					}
				});
		},

		deleteInvoiceFile: function (invoiceId, fileName) {
			fetch(`${this.apiBase}/invoices/${invoiceId}/files/${encodeURIComponent(fileName)}`, {
				method: 'DELETE',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.showSuccess('Dosya silindi');
					this.loadInvoiceFiles(invoiceId);
				})
				.catch(e => {
					this.showError('Dosya silme hatası: ' + e.message);
				});
		},

		getInvoiceFileUrl: function (filePath, fileName) {
			// Generate URL to Nextcloud Files app
			// Format: /apps/files/?dir=/path/to/dir&scrollto=filename
			const dirPath = filePath.substring(0, filePath.lastIndexOf('/'));
			const encodedDir = encodeURIComponent(dirPath);
			const encodedFile = encodeURIComponent(fileName);
			return OC.generateUrl('/apps/files/') + '?dir=' + encodedDir + '&scrollto=' + encodedFile;
		},

		hideWebsiteDetail: function () {
			document.getElementById('websites-list-view').style.display = 'block';
			document.getElementById('website-detail-view').style.display = 'none';
			this.currentWebsiteId = null;
		},

		setupForms: function () {
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

			// Service Type form
			document.getElementById('service-type-form')?.addEventListener('submit', (e) => {
				e.preventDefault();
				this.saveServiceType();
			});

			// Service form
			document.getElementById('service-form')?.addEventListener('submit', (e) => {
				e.preventDefault();
				this.saveService();
			});

			// Invoice form
			document.getElementById('invoice-form')?.addEventListener('submit', (e) => {
				e.preventDefault();
				this.saveInvoice();
			});

			// Payment form
			document.getElementById('payment-form')?.addEventListener('submit', (e) => {
				e.preventDefault();
				this.savePayment();
			});

			// Project form
			document.getElementById('project-form')?.addEventListener('submit', (e) => {
				e.preventDefault();
				this.saveProject();
			});

			// Task form
			document.getElementById('task-form')?.addEventListener('submit', (e) => {
				e.preventDefault();
				this.saveTask();
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

			// Update hosting payment expiry when period changes
			document.getElementById('hp-period')?.addEventListener('change', () => {
				this.updateHostingPaymentNewExpiry();
			});

			// Invoice item form
			document.getElementById('invoice-item-form')?.addEventListener('submit', (e) => {
				e.preventDefault();
				this.saveInvoiceItem();
			});

			// Project item form
			document.getElementById('project-item-form')?.addEventListener('submit', (e) => {
				e.preventDefault();
				this.saveProjectItem();
			});
		},

		showClientModal: function (id = null) {
			const modal = document.getElementById('client-modal');
			const form = document.getElementById('client-form');
			const title = document.getElementById('client-modal-title');

			form.reset();
			document.getElementById('client-id').value = '';

			if (id) {
				title.textContent = 'Müşteri Düzenle';
				const client = this.clients.find(c => c.id == id);
				if (client) {
					document.getElementById('client-id').value = client.id;
					document.getElementById('client-name').value = client.name || '';
					document.getElementById('client-email').value = client.email || '';
					document.getElementById('client-phone').value = client.phone || '';
					document.getElementById('client-notes').value = client.notes || '';
				}
			} else {
				title.textContent = 'Müşteri Ekle';
			}

			modal.style.display = 'block';
		},

		showContactsModal: function () {
			const modal = document.getElementById('contacts-modal');
			const loadingEl = document.getElementById('contacts-loading');
			const listEl = document.getElementById('contacts-list');
			const emptyEl = document.getElementById('contacts-empty');
			const searchInput = document.getElementById('contacts-search');

			modal.style.display = 'block';
			loadingEl.style.display = 'block';
			listEl.innerHTML = '';
			emptyEl.style.display = 'none';
			if (searchInput) searchInput.value = '';

			// Load contacts from Nextcloud Contacts API
			this.loadContacts();
		},

		loadContacts: function () {
			const loadingEl = document.getElementById('contacts-loading');
			const listEl = document.getElementById('contacts-list');

			// Try multiple methods to fetch contacts
			// Method 1: Try our backend API
			fetch(`${this.apiBase}/contacts`, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(contacts => {
					if (Array.isArray(contacts) && contacts.length > 0) {
						loadingEl.style.display = 'none';
						this.renderContactsList(contacts);
						return;
					}
					// If empty, try DAV method
					throw new Error('No contacts from backend');
				})
				.catch(e => {
					console.log('Backend API failed, trying DAV:', e);
					// Method 2: Try DAV method
					this.loadContactsFromDAV();
				});
		},

		loadContactsFromDAV: function () {
			const loadingEl = document.getElementById('contacts-loading');
			const listEl = document.getElementById('contacts-list');

			const userId = OC.currentUser || '';
			if (!userId) {
				loadingEl.style.display = 'none';
				listEl.innerHTML = '<p class="empty-message">Kullanıcı bilgisi alınamadı</p>';
				return;
			}

			// Build DAV URL manually
			const baseUrl = window.location.origin + OC.getRootPath();
			const davUrl = baseUrl + '/remote.php/dav/addressbooks/users/' + encodeURIComponent(userId) + '/contacts/';

			fetch(davUrl, {
				method: 'PROPFIND',
				headers: {
					'requesttoken': OC.requestToken,
					'Depth': '1',
					'Content-Type': 'application/xml'
				},
				body: '<?xml version="1.0"?><d:propfind xmlns:d="DAV:"><d:prop><d:getcontenttype/><d:getetag/></d:prop></d:propfind>'
			})
				.then(r => {
					if (!r.ok) throw new Error('DAV API not available');
					return r.text();
				})
				.then(xml => {
					// Parse XML response
					const parser = new DOMParser();
					const doc = parser.parseFromString(xml, 'text/xml');
					const responses = Array.from(doc.querySelectorAll('response'));

					if (responses.length === 0) {
						listEl.innerHTML = '<p class="empty-message">Kişi bulunamadı</p>';
						loadingEl.style.display = 'none';
						return;
					}

					// Fetch each contact's details
					const contacts = [];
					let loaded = 0;
					const total = responses.length;

					responses.forEach(response => {
						const hrefEl = response.querySelector('href');
						if (!hrefEl) {
							loaded++;
							if (loaded === total) {
								loadingEl.style.display = 'none';
								this.renderContactsList(contacts);
							}
							return;
						}

						const href = hrefEl.textContent;
						if (href && href.endsWith('.vcf')) {
							this.fetchContactDetails(href, (contact) => {
								if (contact && contact.name) {
									contacts.push(contact);
								}
								loaded++;

								if (loaded === total) {
									loadingEl.style.display = 'none';
									this.renderContactsList(contacts);
								}
							});
						} else {
							loaded++;
							if (loaded === total) {
								loadingEl.style.display = 'none';
								this.renderContactsList(contacts);
							}
						}
					});
				})
				.catch(e => {
					console.error('Error loading contacts:', e);
					loadingEl.style.display = 'none';
					listEl.innerHTML = '<p class="empty-message">Kişiler yüklenemedi. Nextcloud Kişiler uygulamasının kurulu olduğundan emin olun.</p>';
				});
		},

		fetchContactDetails: function (href, callback) {
			// href is relative path from DAV, build full URL
			const baseUrl = window.location.origin + OC.getRootPath();
			const contactUrl = baseUrl + '/remote.php/dav' + href;

			fetch(contactUrl, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => {
					if (!r.ok) throw new Error('Failed to fetch contact');
					return r.text();
				})
				.then(vcard => {
					const contact = this.parseVCard(vcard);
					callback(contact);
				})
				.catch(e => {
					console.error('Error fetching contact:', e);
					callback(null);
				});
		},

		parseVCard: function (vcard) {
			const contact = {
				id: '',
				name: '',
				email: '',
				phone: '',
				organization: '',
				notes: ''
			};

			const lines = vcard.split('\n');
			lines.forEach(line => {
				line = line.trim();
				if (line.startsWith('FN:')) {
					contact.name = line.substring(3).trim();
				} else if (line.startsWith('EMAIL')) {
					if (!contact.email) {
						const match = line.match(/EMAIL[^:]*:(.+)/);
						if (match) contact.email = match[1].trim();
					}
				} else if (line.startsWith('TEL')) {
					if (!contact.phone) {
						const match = line.match(/TEL[^:]*:(.+)/);
						if (match) contact.phone = match[1].trim();
					}
				} else if (line.startsWith('ORG:')) {
					contact.organization = line.substring(4).trim();
				} else if (line.startsWith('NOTE:')) {
					contact.notes = line.substring(5).trim();
				} else if (line.startsWith('UID:')) {
					contact.id = line.substring(4).trim();
				}
			});

			return contact;
		},

		renderContactsList: function (contacts) {
			const loadingEl = document.getElementById('contacts-loading');
			const listEl = document.getElementById('contacts-list');
			const emptyEl = document.getElementById('contacts-empty');

			loadingEl.style.display = 'none';

			// Store all contacts for filtering
			this.allContacts = contacts;

			if (contacts.length === 0) {
				listEl.innerHTML = '';
				emptyEl.style.display = 'block';
				return;
			}

			// Sort by name
			contacts.sort((a, b) => (a.name || '').localeCompare(b.name || ''));

			// Render contacts
			this.renderFilteredContacts(contacts);

			// Setup search
			const searchInput = document.getElementById('contacts-search');
			if (searchInput) {
				// Remove existing listeners
				const newSearchInput = searchInput.cloneNode(true);
				searchInput.parentNode.replaceChild(newSearchInput, searchInput);

				// Add search listener
				newSearchInput.addEventListener('input', (e) => {
					this.filterContacts(e.target.value);
				});

				// Focus on search input
				setTimeout(() => newSearchInput.focus(), 100);
			}
		},

		filterContacts: function (searchTerm) {
			const listEl = document.getElementById('contacts-list');
			const emptyEl = document.getElementById('contacts-empty');

			if (!this.allContacts || this.allContacts.length === 0) {
				return;
			}

			const term = searchTerm.toLowerCase().trim();

			if (!term) {
				// Show all contacts
				this.renderFilteredContacts(this.allContacts);
				emptyEl.style.display = 'none';
				return;
			}

			// Filter contacts
			const filtered = this.allContacts.filter(contact => {
				const name = (contact.name || '').toLowerCase();
				const email = (contact.email || '').toLowerCase();
				const phone = (contact.phone || '').toLowerCase();
				const organization = (contact.organization || '').toLowerCase();

				return name.includes(term) ||
					email.includes(term) ||
					phone.includes(term) ||
					organization.includes(term);
			});

			if (filtered.length === 0) {
				listEl.innerHTML = '';
				emptyEl.style.display = 'block';
			} else {
				emptyEl.style.display = 'none';
				this.renderFilteredContacts(filtered);
			}
		},

		renderFilteredContacts: function (contacts) {
			const listEl = document.getElementById('contacts-list');

			let html = '<div class="contacts-list">';
			contacts.forEach(contact => {
				html += `
					<div class="contact-item" data-contact-id="${this.escapeHtml(contact.id)}">
						<div class="contact-info">
							<div class="contact-name">${this.escapeHtml(contact.name)}</div>
							<div class="contact-details">
								${contact.email ? `<span>📧 ${this.escapeHtml(contact.email)}</span>` : ''}
								${contact.phone ? `<span>📞 ${this.escapeHtml(contact.phone)}</span>` : ''}
								${contact.organization ? `<span>🏢 ${this.escapeHtml(contact.organization)}</span>` : ''}
							</div>
						</div>
						<button class="btn btn-primary btn-sm select-contact-btn">Seç</button>
					</div>
				`;
			});
			html += '</div>';
			listEl.innerHTML = html;

			// Event listeners
			listEl.querySelectorAll('.select-contact-btn').forEach(btn => {
				btn.addEventListener('click', () => {
					const contactItem = btn.closest('.contact-item');
					const contactId = contactItem.dataset.contactId;
					const contact = contacts.find(c => c.id === contactId);
					if (contact) {
						this.selectContact(contact);
					}
				});
			});
		},

		selectContact: function (contact) {
			// Fill client form with contact data
			document.getElementById('client-name').value = contact.name || '';
			document.getElementById('client-email').value = contact.email || '';
			document.getElementById('client-phone').value = contact.phone || '';
			document.getElementById('client-notes').value = (contact.organization ? 'Kurum: ' + contact.organization + '\n' : '') + (contact.notes || '');

			// Close contacts modal
			this.closeModal('contacts-modal');

			// Focus on name field
			document.getElementById('client-name').focus();
		},

		showDomainModal: function (id = null) {
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

		showHostingModal: function (id = null) {
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

		showWebsiteModal: function (id = null) {
			const modal = document.getElementById('website-modal');
			const form = document.getElementById('website-form');
			const title = document.getElementById('website-modal-title');
			const notesEditor = document.getElementById('website-notes');

			form.reset();
			document.getElementById('website-id').value = '';
			this.updateClientSelects();
			this.updateDomainSelect();
			this.updateHostingSelect();

			// Initialize rich text editor toolbar
			this.initRichTextEditor('website-notes');

			if (id) {
				title.textContent = 'Website Düzenle';
				const website = this.websites.find(w => w.id == id);
				if (website) {
					document.getElementById('website-id').value = website.id;
					document.getElementById('website-client-id').value = website.clientId || '';
					document.getElementById('website-domain-id').value = website.domainId || '';
					document.getElementById('website-hosting-id').value = website.hostingId || '';
					document.getElementById('website-name').value = website.name || '';
					document.getElementById('website-software').value = website.software || '';
					document.getElementById('website-version').value = website.version || '';
					document.getElementById('website-status').value = website.status || 'active';
					document.getElementById('website-installation-date').value = website.installationDate || '';
					document.getElementById('website-url').value = website.url || '';
					document.getElementById('website-admin-url').value = website.adminUrl || '';
					document.getElementById('website-admin-notes').value = website.adminNotes || '';
					// Set HTML content for rich text editor
					if (notesEditor) {
						notesEditor.innerHTML = website.notes || '';
					}
				}
			} else {
				title.textContent = 'Website Ekle';
				// Clear rich text editor
				if (notesEditor) {
					notesEditor.innerHTML = '';
				}
			}

			modal.style.display = 'block';
		},

		updateDomainSelect: function () {
			const select = document.getElementById('website-domain-id');
			select.innerHTML = '<option value="">None</option>';
			this.domains.forEach(domain => {
				const option = document.createElement('option');
				option.value = domain.id;
				option.textContent = domain.domainName;
				select.appendChild(option);
			});
		},

		updateHostingSelect: function () {
			const select = document.getElementById('website-hosting-id');
			select.innerHTML = '<option value="">None</option>';
			this.hostings.forEach(hosting => {
				const option = document.createElement('option');
				option.value = hosting.id;
				option.textContent = hosting.provider + (hosting.plan ? ' - ' + hosting.plan : '');
				select.appendChild(option);
			});
		},

		closeModal: function (modalId) {
			document.getElementById(modalId).style.display = 'none';
		},

		saveClient: function () {
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

		saveDomain: function () {
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

		saveHosting: function () {
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

		saveWebsite: function () {
			const id = document.getElementById('website-id').value;
			const clientId = document.getElementById('website-client-id').value;
			const domainId = document.getElementById('website-domain-id').value;
			const hostingId = document.getElementById('website-hosting-id').value;
			const name = document.getElementById('website-name').value;
			const software = document.getElementById('website-software').value;
			const version = document.getElementById('website-version').value;
			const status = document.getElementById('website-status').value;
			const installationDate = document.getElementById('website-installation-date').value;
			const url = document.getElementById('website-url').value;
			const adminUrl = document.getElementById('website-admin-url').value;
			const adminNotes = document.getElementById('website-admin-notes').value;
			// Get HTML content from rich text editor
			const notesEditor = document.getElementById('website-notes');
			const notes = notesEditor ? notesEditor.innerHTML : '';

			console.log('saveWebsite:', { id, clientId, name, software });

			const apiUrl = id ? `${this.apiBase}/websites/${id}` : `${this.apiBase}/websites`;
			const method = id ? 'PUT' : 'POST';

			const params = new URLSearchParams();
			params.append('clientId', clientId);
			params.append('domainId', domainId);
			params.append('hostingId', hostingId);
			params.append('name', name);
			params.append('software', software);
			params.append('version', version);
			params.append('status', status);
			params.append('installationDate', installationDate);
			params.append('url', url);
			params.append('adminUrl', adminUrl);
			params.append('adminNotes', adminNotes);
			params.append('notes', notes);

			fetch(apiUrl, {
				method: method,
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'requesttoken': OC.requestToken
				},
				body: params.toString()
			})
				.then(response => response.json())
				.then(result => {
					console.log('Website save result:', result);
					if (result.error) {
						this.showError('Website kaydedilemedi: ' + result.error);
					} else {
						this.closeModal('website-modal');
						const detailOpen = this.currentWebsiteId;
						this.loadWebsites();
						if (detailOpen) {
							setTimeout(() => this.showWebsiteDetail(detailOpen), 500);
						}
						this.showSuccess('Website başarıyla kaydedildi');
					}
				})
				.catch(error => {
					console.error('Error saving website:', error);
					this.showError('Website kaydedilemedi: ' + error.message);
				});
		},

		editClient: function (id) {
			this.showClientModal(id);
		},

		editDomain: function (id) {
			this.showDomainModal(id);
		},

		editHosting: function (id) {
			this.showHostingModal(id);
		},

		editWebsite: function (id) {
			this.showWebsiteModal(id);
		},

		deleteClient: function (id) {
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

		deleteDomain: function (id) {
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

		deleteHosting: function (id) {
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

		deleteWebsite: function (id) {
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

		isExpiringSoon: function (expirationDate) {
			if (!expirationDate) return false;
			const exp = new Date(expirationDate);
			const now = new Date();
			const daysUntilExpiration = Math.ceil((exp - now) / (1000 * 60 * 60 * 24));
			return daysUntilExpiration <= 30 && daysUntilExpiration >= 0;
		},

		escapeHtml: function (text) {
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		},

		// ===== ALERT PANELS =====
		updateAlertPanels: function () {
			this.loadOverdueInvoices();
			this.loadUpcomingPayments();
			this.loadUpcomingTasks();
		},

		loadMonthlyIncome: function () {
			fetch(this.apiBase + '/payments/monthly-total', {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(data => {
					const el = document.getElementById('stat-monthly-income');
					if (el) el.textContent = (data.total || 0).toFixed(2);
				})
				.catch(() => { });
		},

		loadOverdueInvoices: function () {
			fetch(this.apiBase + '/invoices/overdue', {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(invoices => {
					const list = document.getElementById('overdue-invoices-list');
					const count = document.getElementById('overdue-count');
					if (count) count.textContent = invoices.length;
					if (!list) return;

					if (invoices.length === 0) {
						list.innerHTML = '<p class="empty-message">Geciken ödeme yok</p>';
						return;
					}

					let html = '';
					invoices.forEach(inv => {
						const client = this.clients.find(c => c.id == inv.clientId);
						const daysOverdue = Math.ceil((new Date() - new Date(inv.dueDate)) / (1000 * 60 * 60 * 24));
						html += `
						<div class="alert-item" data-id="${inv.id}">
							<div class="alert-item__info">
								<div class="alert-item__title">${inv.invoiceNumber}</div>
								<div class="alert-item__subtitle">${client ? client.name : 'Bilinmeyen'} - ${inv.totalAmount} ${inv.currency}</div>
							</div>
							<span class="alert-item__badge alert-item__badge--danger">${daysOverdue} gün gecikti</span>
						</div>
					`;
					});
					list.innerHTML = html;
				})
				.catch(() => { });
		},

		loadUpcomingPayments: function () {
			fetch(this.apiBase + '/invoices/upcoming', {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(invoices => {
					const list = document.getElementById('upcoming-payments-list');
					const count = document.getElementById('upcoming-count');
					if (count) count.textContent = invoices.length;
					if (!list) return;

					if (invoices.length === 0) {
						list.innerHTML = '<p class="empty-message">Yaklaşan ödeme yok</p>';
						return;
					}

					let html = '';
					invoices.forEach(inv => {
						const client = this.clients.find(c => c.id == inv.clientId);
						const daysLeft = Math.ceil((new Date(inv.dueDate) - new Date()) / (1000 * 60 * 60 * 24));
						html += `
						<div class="alert-item" data-id="${inv.id}">
							<div class="alert-item__info">
								<div class="alert-item__title">${inv.invoiceNumber}</div>
								<div class="alert-item__subtitle">${client ? client.name : 'Bilinmeyen'} - ${inv.totalAmount} ${inv.currency}</div>
							</div>
							<span class="alert-item__badge alert-item__badge--warning">${daysLeft} gün</span>
						</div>
					`;
					});
					list.innerHTML = html;
				})
				.catch(() => { });
		},

		loadUpcomingTasks: function () {
			fetch(this.apiBase + '/tasks/approaching-deadline', {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(tasks => {
					const list = document.getElementById('upcoming-tasks-list');
					const count = document.getElementById('upcoming-tasks-count');
					if (count) count.textContent = tasks.length;
					if (!list) return;

					if (tasks.length === 0) {
						list.innerHTML = '<p class="empty-message">Yaklaşan görev yok</p>';
						return;
					}

					let html = '';
					tasks.forEach(task => {
						const project = this.projects.find(p => p.id == task.projectId);
						const daysLeft = Math.ceil((new Date(task.dueDate) - new Date()) / (1000 * 60 * 60 * 24));
						html += `
						<div class="alert-item" data-id="${task.id}">
							<div class="alert-item__info">
								<div class="alert-item__title">${this.escapeHtml(task.title)}</div>
								<div class="alert-item__subtitle">${project ? project.name : 'Genel'}</div>
							</div>
							<span class="alert-item__badge alert-item__badge--info">${daysLeft} gün</span>
						</div>
					`;
					});
					list.innerHTML = html;
				})
				.catch(() => { });
		},

		// ===== SERVICE TYPES =====
		loadServiceTypes: function () {
			fetch(this.apiBase + '/service-types', {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(data => {
					this.serviceTypes = Array.isArray(data) ? data : [];
					this.updateServiceTypeSelects();
				})
				.catch(() => { this.serviceTypes = []; });
		},

		updateServiceTypeSelects: function () {
			const select = document.getElementById('service-type-select');
			if (!select) return;
			select.innerHTML = '<option value="">Seçin veya özel girin</option>';
			this.serviceTypes.forEach(st => {
				select.innerHTML += `<option value="${st.id}">${this.escapeHtml(st.name)}</option>`;
			});
		},

		showServiceTypesModal: function () {
			const modal = document.getElementById('service-types-modal');
			const list = document.getElementById('service-types-list');
			if (!modal || !list) return;

			let html = '';
			this.serviceTypes.forEach(st => {
				html += `
				<div class="list-item">
					<div class="list-item__content">
						<div class="list-item__title">${this.escapeHtml(st.name)}</div>
						<div class="list-item__meta">${st.defaultPrice || 0} ${st.defaultCurrency} / ${st.renewalInterval}</div>
					</div>
					<div class="list-item__actions">
						<button class="btn btn-sm btn-secondary btn-edit-service-type" data-id="${st.id}">✏️</button>
						<button class="btn btn-sm btn-danger btn-delete-service-type" data-id="${st.id}">🗑️</button>
					</div>
				</div>
			`;
			});
			list.innerHTML = html || '<p class="empty-state">Henüz hizmet türü yok</p>';

			// Event delegation for edit/delete buttons
			list.querySelectorAll('.btn-edit-service-type').forEach(btn => {
				btn.addEventListener('click', (e) => {
					e.stopPropagation();
					const id = btn.getAttribute('data-id');
					this.showServiceTypeModal(parseInt(id));
				});
			});
			list.querySelectorAll('.btn-delete-service-type').forEach(btn => {
				btn.addEventListener('click', (e) => {
					e.stopPropagation();
					const id = btn.getAttribute('data-id');
					this.deleteServiceType(parseInt(id));
				});
			});

			modal.style.display = 'block';
		},

		showServiceTypeModal: function (id = null) {
			const modal = document.getElementById('service-type-modal');
			const form = document.getElementById('service-type-form');
			if (!modal || !form) return;

			form.reset();
			document.getElementById('service-type-id').value = '';
			document.getElementById('service-type-modal-title').textContent = 'Hizmet Türü Ekle';

			if (id) {
				const st = this.serviceTypes.find(s => s.id == id);
				if (st) {
					document.getElementById('service-type-id').value = st.id;
					document.getElementById('service-type-name').value = st.name || '';
					document.getElementById('service-type-description').value = st.description || '';
					document.getElementById('service-type-price').value = st.defaultPrice || '';
					document.getElementById('service-type-currency').value = st.defaultCurrency || 'USD';
					document.getElementById('service-type-interval').value = st.renewalInterval || 'monthly';
					document.getElementById('service-type-modal-title').textContent = 'Hizmet Türü Düzenle';
				}
			}
			modal.style.display = 'block';
		},

		saveServiceType: function () {
			const id = document.getElementById('service-type-id').value;
			const data = new URLSearchParams({
				name: document.getElementById('service-type-name').value,
				description: document.getElementById('service-type-description').value,
				defaultPrice: document.getElementById('service-type-price').value,
				defaultCurrency: document.getElementById('service-type-currency').value,
				renewalInterval: document.getElementById('service-type-interval').value
			});

			const url = id ? `${this.apiBase}/service-types/${id}` : `${this.apiBase}/service-types`;
			const method = id ? 'PUT' : 'POST';

			fetch(url, {
				method,
				headers: {
					'requesttoken': OC.requestToken,
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.closeModal('service-type-modal');
					this.loadServiceTypes();
					this.showServiceTypesModal();
					this.showSuccess('Hizmet türü kaydedildi');
				})
				.catch(e => this.showError('Kaydetme hatası: ' + e.message));
		},

		deleteServiceType: function (id) {
			if (!confirm('Bu hizmet türünü silmek istediğinize emin misiniz?')) return;
			fetch(`${this.apiBase}/service-types/${id}`, {
				method: 'DELETE',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(() => {
					this.loadServiceTypes();
					this.showServiceTypesModal();
					this.showSuccess('Hizmet türü silindi');
				})
				.catch(e => this.showError('Silme hatası'));
		},

		initPredefinedServiceTypes: function () {
			fetch(`${this.apiBase}/service-types/init`, {
				method: 'POST',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(() => {
					this.loadServiceTypes();
					this.showServiceTypesModal();
					this.showSuccess('Hazır hizmet türleri eklendi');
				})
				.catch(e => this.showError('Hata'));
		},

		// ===== SERVICES =====
		loadServices: function () {
			fetch(this.apiBase + '/services', {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(data => {
					this.services = Array.isArray(data) ? data : [];
					this.renderServices();
				})
				.catch(e => {
					console.error('Error loading services:', e);
					this.services = [];
				});
		},

		renderServices: function () {
			const list = document.getElementById('services-list');
			if (!list) return;

			if (this.services.length === 0) {
				list.innerHTML = '<p class="empty-state">Henüz hizmet yok</p>';
				return;
			}

			let html = '';
			this.services.forEach(svc => {
				const client = this.clients.find(c => c.id == svc.clientId);
				const statusClass = svc.status === 'active' ? 'status-badge--paid' : 'status-badge--cancelled';
				const daysLeft = svc.expirationDate ? this.calculateDaysLeft(svc.expirationDate) : null;
				const expiryBadge = daysLeft !== null && daysLeft <= 30 ?
					`<span class="status-badge ${daysLeft <= 7 ? 'status-badge--overdue' : 'status-badge--draft'}">${daysLeft} gün</span>` : '';

				html += `
				<div class="list-item service-item" data-id="${svc.id}">
					<div class="list-item__content">
						<div class="list-item__title">${this.escapeHtml(svc.name)}</div>
						<div class="list-item__meta">${client ? client.name : 'Bilinmeyen'} • ${svc.expirationDate || '-'}</div>
					</div>
					<div class="list-item__stats">
						<span>${svc.price || 0} ${svc.currency}</span>
						${expiryBadge}
						<span class="status-badge ${statusClass}">${this.getStatusText(svc.status)}</span>
					</div>
					<div class="list-item__actions">
						<button class="btn btn-sm btn-secondary btn-view-service" data-id="${svc.id}" title="Detay">👁️</button>
						<button class="btn btn-sm btn-success btn-invoice-service" data-id="${svc.id}" title="Fatura Oluştur">📄</button>
					</div>
				</div>
			`;
			});
			list.innerHTML = html;

			// Event delegation for service buttons
			list.querySelectorAll('.btn-view-service').forEach(btn => {
				btn.addEventListener('click', (e) => {
					e.stopPropagation();
					const id = btn.getAttribute('data-id');
					this.showServiceDetail(parseInt(id));
				});
			});
			list.querySelectorAll('.btn-invoice-service').forEach(btn => {
				btn.addEventListener('click', (e) => {
					e.stopPropagation();
					const id = btn.getAttribute('data-id');
					this.createInvoiceFromService(parseInt(id));
				});
			});
			// Click on row also shows detail
			list.querySelectorAll('.service-item').forEach(item => {
				item.addEventListener('click', () => {
					const id = item.getAttribute('data-id');
					this.showServiceDetail(parseInt(id));
				});
			});
		},

		getStatusText: function (status) {
			const texts = {
				active: 'Aktif',
				paused: 'Durduruldu',
				cancelled: 'İptal',
				expired: 'Süresi Doldu'
			};
			return texts[status] || status;
		},

		calculateDaysLeft: function (dateStr) {
			if (!dateStr) return null;
			const expiry = new Date(dateStr);
			const today = new Date();
			today.setHours(0, 0, 0, 0);
			const diffTime = expiry.getTime() - today.getTime();
			return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
		},

		showServiceDetail: function (id) {
			const svc = this.services.find(s => s.id == id);
			if (!svc) return;

			this.currentServiceId = id;
			document.getElementById('services-list-view').style.display = 'none';
			document.getElementById('service-detail-view').style.display = 'block';

			const client = this.clients.find(c => c.id == svc.clientId);
			const daysLeft = svc.expirationDate ? this.calculateDaysLeft(svc.expirationDate) : null;
			const serviceType = svc.serviceTypeId ? this.serviceTypes.find(t => t.id == svc.serviceTypeId) : null;

			document.getElementById('service-detail-name').textContent = svc.name;
			document.getElementById('service-detail-client').textContent = client ? client.name : '-';
			document.getElementById('service-detail-price').textContent = `${svc.price || 0} ${svc.currency}`;

			// Expiration date - tek seferlik hizmetler için özel gösterim
			const isOneTime = svc.renewalInterval === 'one-time';
			document.getElementById('service-detail-expiry').textContent = isOneTime ? '🔄 Tek Seferlik' : (svc.expirationDate || '-');

			// Additional info
			document.getElementById('service-detail-start')?.textContent && (document.getElementById('service-detail-start').textContent = svc.startDate || '-');
			document.getElementById('service-detail-interval')?.textContent && (document.getElementById('service-detail-interval').textContent = this.getIntervalText(svc.renewalInterval));
			document.getElementById('service-detail-type')?.textContent && (document.getElementById('service-detail-type').textContent = serviceType ? serviceType.name : '-');

			// Status with badge
			const statusEl = document.getElementById('service-detail-status');
			const statusClass = svc.status === 'active' ? 'status-badge--paid' : 'status-badge--cancelled';
			statusEl.innerHTML = `<span class="status-badge ${statusClass}">${this.getStatusText(svc.status)}</span>`;

			// Show days left if applicable (sadece periyodik hizmetler için)
			if (!isOneTime && daysLeft !== null && daysLeft <= 30) {
				const daysClass = daysLeft <= 0 ? 'status-badge--overdue' : (daysLeft <= 7 ? 'status-badge--draft' : 'status-badge--sent');
				statusEl.innerHTML += ` <span class="status-badge ${daysClass}">${daysLeft <= 0 ? 'Süresi doldu!' : daysLeft + ' gün kaldı'}</span>`;
			}

			// Süreyi Uzat butonunu tek seferlik hizmetler için gizle
			const extendBtn = document.getElementById('service-extend-btn');
			if (extendBtn) {
				extendBtn.style.display = isOneTime ? 'none' : 'inline-block';
			}

			document.getElementById('service-detail-notes').textContent = svc.notes || '-';
		},

		getIntervalText: function (interval) {
			const texts = {
				'one-time': '🔄 Tek Seferlik',
				monthly: 'Aylık',
				quarterly: '3 Aylık',
				yearly: 'Yıllık',
				biennial: '2 Yıllık'
			};
			return texts[interval] || interval;
		},

		updateServiceExpirationDate: function (interval) {
			const expirationInput = document.getElementById('service-expiration-date');
			if (!expirationInput) return;

			const expirationLabel = expirationInput.closest('.form-group')?.querySelector('label');

			if (interval === 'one-time') {
				// Tek seferlik hizmetler için expiration date opsiyonel
				expirationInput.value = '';
				expirationInput.required = false;
				if (expirationLabel) {
					expirationLabel.innerHTML = 'Bitiş (Opsiyonel)';
				}
			} else {
				// Periyodik hizmetler için expiration date hesapla
				const startDateInput = document.getElementById('service-start-date');
				const startDate = startDateInput?.value ? new Date(startDateInput.value) : new Date();

				const intervalMonths = {
					monthly: 1,
					quarterly: 3,
					yearly: 12,
					biennial: 24
				};

				const months = intervalMonths[interval] || 12;
				const expirationDate = new Date(startDate);
				expirationDate.setMonth(expirationDate.getMonth() + months);

				expirationInput.value = expirationDate.toISOString().split('T')[0];
				expirationInput.required = false;
				if (expirationLabel) {
					expirationLabel.innerHTML = 'Bitiş';
				}
			}
		},

		showServiceExtendModal: function (id) {
			const svc = this.services.find(s => s.id == id);
			if (!svc) return;

			// Tek seferlik hizmetler için uzatma yapılamaz
			if (svc.renewalInterval === 'one-time') {
				this.showError('Tek seferlik hizmetler için süre uzatma yapılamaz');
				return;
			}

			// For now, just extend the service by one period and show confirmation
			const intervalMonths = {
				monthly: 1,
				quarterly: 3,
				yearly: 12,
				biennial: 24
			};
			const months = intervalMonths[svc.renewalInterval] || 12;

			if (!confirm(`"${svc.name}" hizmetinin süresini ${months} ay uzatmak istediğinize emin misiniz?`)) return;

			// Calculate new expiration date
			let expDate = svc.expirationDate ? new Date(svc.expirationDate) : new Date();
			expDate.setMonth(expDate.getMonth() + months);
			const newExpiry = expDate.toISOString().split('T')[0];

			// Update service
			const data = new URLSearchParams({ expirationDate: newExpiry });

			fetch(`${this.apiBase}/services/${id}`, {
				method: 'PUT',
				headers: {
					'requesttoken': OC.requestToken,
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.loadServices();
					this.showServiceDetail(id);
					this.showSuccess('Hizmet süresi uzatıldı');
				})
				.catch(e => this.showError('Uzatma hatası: ' + e.message));
		},

		createInvoiceFromService: function (serviceId) {
			const svc = this.services.find(s => s.id == serviceId);
			if (!svc) return;

			// Open invoice modal with pre-filled data
			this.showInvoiceModal();

			// Wait for modal to open, then fill data
			setTimeout(() => {
				document.getElementById('invoice-client-id').value = svc.clientId || '';
				document.getElementById('invoice-total').value = svc.price || '';
				document.getElementById('invoice-currency').value = svc.currency || 'USD';

				// Set a note about the service
				document.getElementById('invoice-notes').value = `Hizmet: ${svc.name}`;
			}, 100);
		},

		hideServiceDetail: function () {
			document.getElementById('services-list-view').style.display = 'block';
			document.getElementById('service-detail-view').style.display = 'none';
			this.currentServiceId = null;
		},

		showServiceModal: function (id = null) {
			const modal = document.getElementById('service-modal');
			const form = document.getElementById('service-form');
			if (!modal || !form) return;

			form.reset();
			document.getElementById('service-id').value = '';
			this.updateClientSelect('service-client-id');
			this.updateServiceTypeSelect();

			// Set default dates
			const today = new Date().toISOString().split('T')[0];
			document.getElementById('service-start-date').value = today;

			// Setup service type change handler
			const typeSelect = document.getElementById('service-type-select');
			if (typeSelect) {
				typeSelect.onchange = () => {
					const stId = typeSelect.value;
					if (stId) {
						const st = this.serviceTypes.find(t => t.id == stId);
						if (st) {
							document.getElementById('service-name').value = st.name || '';
							document.getElementById('service-price').value = st.defaultPrice || '';
							document.getElementById('service-currency').value = st.defaultCurrency || 'USD';
							document.getElementById('service-interval').value = st.renewalInterval || 'monthly';

							// Update expiration date based on interval
							this.updateServiceExpirationDate(st.renewalInterval);
						}
					}
				};
			}

			// Setup interval change handler
			const intervalSelect = document.getElementById('service-interval');
			if (intervalSelect) {
				intervalSelect.onchange = () => {
					const interval = intervalSelect.value;
					this.updateServiceExpirationDate(interval);
				};
			}

			if (id) {
				const svc = this.services.find(s => s.id == id);
				if (svc) {
					document.getElementById('service-id').value = svc.id;
					document.getElementById('service-client-id').value = svc.clientId || '';
					document.getElementById('service-type-select').value = svc.serviceTypeId || '';
					document.getElementById('service-name').value = svc.name || '';
					document.getElementById('service-price').value = svc.price || '';
					document.getElementById('service-currency').value = svc.currency || 'USD';
					document.getElementById('service-interval').value = svc.renewalInterval || 'monthly';
					document.getElementById('service-start-date').value = svc.startDate || '';
					document.getElementById('service-expiration-date').value = svc.expirationDate || '';
					document.getElementById('service-status').value = svc.status || 'active';
					document.getElementById('service-notes').value = svc.notes || '';
				}
			}
			modal.style.display = 'block';
		},

		updateServiceTypeSelect: function () {
			const select = document.getElementById('service-type-select');
			if (!select) return;
			select.innerHTML = '<option value="">Seçin veya özel girin</option>';
			this.serviceTypes.forEach(st => {
				select.innerHTML += `<option value="${st.id}">${this.escapeHtml(st.name)} (${st.defaultPrice || 0} ${st.defaultCurrency})</option>`;
			});
		},

		saveService: function () {
			const id = document.getElementById('service-id').value;
			const data = new URLSearchParams({
				clientId: document.getElementById('service-client-id').value,
				name: document.getElementById('service-name').value,
				price: document.getElementById('service-price').value,
				currency: document.getElementById('service-currency').value,
				renewalInterval: document.getElementById('service-interval').value,
				startDate: document.getElementById('service-start-date').value,
				expirationDate: document.getElementById('service-expiration-date').value,
				status: document.getElementById('service-status').value,
				notes: document.getElementById('service-notes').value
			});

			const url = id ? `${this.apiBase}/services/${id}` : `${this.apiBase}/services`;
			fetch(url, {
				method: id ? 'PUT' : 'POST',
				headers: { 'requesttoken': OC.requestToken, 'Content-Type': 'application/x-www-form-urlencoded' },
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.closeModal('service-modal');
					this.loadServices();
					this.showSuccess('Hizmet kaydedildi');
				})
				.catch(e => this.showError('Kaydetme hatası: ' + e.message));
		},

		deleteService: function (id) {
			fetch(`${this.apiBase}/services/${id}`, {
				method: 'DELETE',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(() => {
					this.loadServices();
					this.showSuccess('Hizmet silindi');
				})
				.catch(e => this.showError('Silme hatası'));
		},

		// ===== INVOICES =====
		loadInvoices: function () {
			return fetch(this.apiBase + '/invoices', {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(data => {
					this.invoices = Array.isArray(data) ? data : [];
					this.renderInvoices();
					this.updateDashboard();
					return this.invoices;
				})
				.catch(e => {
					console.error('Error loading invoices:', e);
					this.invoices = [];
					return [];
				});
		},

		renderInvoices: function (filter = 'all') {
			const list = document.getElementById('invoices-list');
			if (!list) return;

			let filtered = this.invoices;
			if (filter === 'unpaid') filtered = filtered.filter(i => ['draft', 'sent'].includes(i.status));
			else if (filter === 'overdue') filtered = filtered.filter(i => i.status === 'overdue' || (new Date(i.dueDate) < new Date() && i.status !== 'paid'));
			else if (filter === 'paid') filtered = filtered.filter(i => i.status === 'paid');

			if (filtered.length === 0) {
				list.innerHTML = '<p class="empty-state">Fatura yok</p>';
				return;
			}

			let html = '';
			filtered.forEach(inv => {
				const client = this.clients.find(c => c.id == inv.clientId);
				const remaining = (inv.totalAmount || 0) - (inv.paidAmount || 0);
				const statusText = this.getInvoiceStatusText(inv.status);
				html += `
				<div class="list-item invoice-item" data-id="${inv.id}">
					<div class="list-item__content">
						<div class="list-item__title">${inv.invoiceNumber}</div>
						<div class="list-item__meta">${client ? client.name : 'Bilinmeyen'} • Vade: ${inv.dueDate || '-'}</div>
					</div>
					<div class="list-item__stats">
						<div class="list-item__stat">
							<div class="list-item__stat-label">Toplam</div>
							<div class="list-item__stat-value">${inv.totalAmount || 0} ${inv.currency}</div>
						</div>
						<div class="list-item__stat">
							<div class="list-item__stat-label">Kalan</div>
							<div class="list-item__stat-value">${remaining.toFixed(2)} ${inv.currency}</div>
						</div>
						<span class="status-badge status-badge--${inv.status}">${statusText}</span>
					</div>
				</div>
			`;
			});
			list.innerHTML = html;

			// Event delegation for invoice items
			list.querySelectorAll('.invoice-item').forEach(item => {
				item.addEventListener('click', () => {
					const id = item.getAttribute('data-id');
					this.showInvoiceDetail(parseInt(id));
				});
			});
		},

		showInvoiceDetail: function (id) {
			const inv = this.invoices.find(i => i.id == id);
			if (!inv) return;

			this.currentInvoiceId = id;
			document.getElementById('invoices-list-view').style.display = 'none';
			document.getElementById('invoice-detail-view').style.display = 'block';

			const client = this.clients.find(c => c.id == inv.clientId);
			const total = parseFloat(inv.totalAmount) || 0;
			const paid = parseFloat(inv.paidAmount) || 0;
			const remaining = Math.max(0, total - paid);
			const paymentPercent = total > 0 ? Math.round((paid / total) * 100) : 0;

			// Calculate due days
			const dueDate = inv.dueDate ? new Date(inv.dueDate) : null;
			const today = new Date();
			today.setHours(0, 0, 0, 0);
			let dueDaysText = '';
			if (dueDate) {
				const dueDays = Math.ceil((dueDate - today) / (1000 * 60 * 60 * 24));
				if (inv.status === 'paid') {
					dueDaysText = '';
				} else if (dueDays < 0) {
					dueDaysText = `<span class="status-badge status-badge--overdue">${Math.abs(dueDays)} gün geçti!</span>`;
				} else if (dueDays === 0) {
					dueDaysText = '<span class="status-badge status-badge--draft">Bugün!</span>';
				} else if (dueDays <= 7) {
					dueDaysText = `<span class="status-badge status-badge--sent">${dueDays} gün kaldı</span>`;
				}
			}

			document.getElementById('invoice-detail-number').textContent = inv.invoiceNumber;
			document.getElementById('invoice-detail-client').textContent = client ? client.name : '-';
			document.getElementById('invoice-detail-total').textContent = `${total.toFixed(2)} ${inv.currency}`;
			document.getElementById('invoice-detail-paid').textContent = `${paid.toFixed(2)} ${inv.currency}`;
			document.getElementById('invoice-detail-remaining').textContent = `${remaining.toFixed(2)} ${inv.currency}`;
			document.getElementById('invoice-detail-issue-date').textContent = inv.issueDate || '-';

			// Due date with remaining days
			const dueDateEl = document.getElementById('invoice-detail-due-date');
			dueDateEl.innerHTML = `${inv.dueDate || '-'} ${dueDaysText}`;

			// Status badge with color
			const statusEl = document.getElementById('invoice-detail-status');
			statusEl.innerHTML = `<span class="status-badge status-badge--${inv.status}">${this.getInvoiceStatusText(inv.status)}</span>`;

			// Payment progress bar
			const progressEl = document.getElementById('invoice-payment-progress');
			if (progressEl) {
				const progressColor = paymentPercent === 100 ? 'var(--color-success)' :
					(paymentPercent >= 50 ? 'var(--color-warning)' : 'var(--color-primary-element)');
				progressEl.innerHTML = `
				<div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
					<span><strong>Ödeme Durumu:</strong></span>
					<span><strong>${paymentPercent}%</strong> (${paid.toFixed(2)} / ${total.toFixed(2)} ${inv.currency})</span>
				</div>
				<div class="progress-bar" style="height: 12px; background: var(--color-background-dark); border-radius: 6px; overflow: hidden;">
					<div style="width: ${paymentPercent}%; height: 100%; background: ${progressColor}; transition: width 0.3s;"></div>
				</div>
			`;
			}

			// Load invoice items, payments, and files
			this.loadInvoiceItems(id);
			this.loadInvoicePayments(id);
			this.loadInvoiceFiles(id);
		},

		getInvoiceStatusText: function (status) {
			const statusTexts = {
				draft: 'Taslak',
				sent: 'Gönderildi',
				paid: 'Ödendi',
				overdue: 'Gecikmiş',
				cancelled: 'İptal',
				partial: 'Kısmi Ödeme'
			};
			return statusTexts[status] || status;
		},

		changeInvoiceStatus: function (newStatus) {
			if (!this.currentInvoiceId) return;

			const data = new URLSearchParams({ status: newStatus });
			fetch(`${this.apiBase}/invoices/${this.currentInvoiceId}`, {
				method: 'PUT',
				headers: {
					'requesttoken': OC.requestToken,
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					// Update local data
					const inv = this.invoices.find(i => i.id == this.currentInvoiceId);
					if (inv) inv.status = newStatus;
					// Refresh UI
					this.showInvoiceDetail(this.currentInvoiceId);
					this.renderInvoices();
					this.showSuccess('Fatura durumu güncellendi');
				})
				.catch(e => this.showError('Durum değiştirme hatası: ' + e.message));
		},

		loadInvoiceItems: function (invoiceId) {
			fetch(`${this.apiBase}/invoices/${invoiceId}/items`, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(items => {
					const container = document.getElementById('invoice-detail-items');
					if (!container) return;

					if (!items || items.length === 0) {
						container.innerHTML = `
				<p class="empty-message">Henüz fatura kalemi yok</p>
				<button class="btn btn-secondary btn-sm add-invoice-item-btn">+ Kalem Ekle</button>
			`;
						container.querySelector('.add-invoice-item-btn')?.addEventListener('click', () => this.showInvoiceItemModal());
						return;
					}

					let html = `
				<table class="invoice-items-table">
					<thead>
						<tr>
							<th>Açıklama</th>
							<th>Tür</th>
							<th>Miktar</th>
							<th>Birim Fiyat</th>
							<th>Toplam</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
			`;

					items.forEach(item => {
						const total = (item.quantity || 1) * (item.unitPrice || 0);
						const itemTypeLabels = { manual: 'Manuel', domain: 'Domain', hosting: 'Hosting', website: 'Website', service: 'Hizmet', project: 'Proje' };

						// Get related item name
						let relatedName = '';
						if (item.itemId && item.itemType !== 'manual') {
							const relatedItem = this.getRelatedItem(item.itemType, item.itemId);
							relatedName = relatedItem ? `<br><small class="text-muted">${this.escapeHtml(relatedItem.name || relatedItem.domainName || relatedItem.provider || '')}</small>` : '';
						}

						html += `
				<tr>
					<td>${this.escapeHtml(item.description)}${relatedName}</td>
					<td><span class="status-badge status-badge--${item.itemType}">${itemTypeLabels[item.itemType] || item.itemType}</span></td>
					<td>${item.quantity || 1}</td>
					<td>${item.unitPrice || 0} ${item.currency || ''}</td>
					<td><strong>${total.toFixed(2)} ${item.currency || ''}</strong></td>
					<td style="white-space: nowrap;">
						<button class="btn btn-sm btn-secondary edit-item-btn" data-id="${item.id}">✏️</button>
						<button class="btn btn-sm btn-danger delete-item-btn" data-id="${item.id}">🗑️</button>
					</td>
				</tr>
			`;
					});

					html += `
				</tbody>
			</table>
			<div style="margin-top: 12px;">
				<button class="btn btn-secondary btn-sm add-invoice-item-btn">+ Kalem Ekle</button>
			</div>
		`;
					container.innerHTML = html;

					// Event delegation for invoice item buttons
					container.querySelector('.add-invoice-item-btn')?.addEventListener('click', () => this.showInvoiceItemModal());
					container.querySelectorAll('.edit-item-btn').forEach(btn => {
						btn.addEventListener('click', () => this.editInvoiceItem(parseInt(btn.dataset.id)));
					});
					container.querySelectorAll('.delete-item-btn').forEach(btn => {
						btn.addEventListener('click', () => this.deleteInvoiceItem(parseInt(btn.dataset.id)));
					});
				})
				.catch(e => {
					console.error('Error loading invoice items:', e);
				});
		},

		loadInvoicePayments: function (invoiceId) {
			fetch(`${this.apiBase}/invoices/${invoiceId}/payments`, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(payments => {
					const container = document.getElementById('invoice-detail-payments');
					if (!container) return;

					if (!payments || payments.length === 0) {
						container.innerHTML = '<p class="empty-message">Henüz ödeme yok</p>';
						return;
					}

					let html = '<div class="payment-history-list">';
					payments.forEach(p => {
						const methodTexts = { cash: 'Nakit', bank: 'Havale', card: 'Kart', other: 'Diğer' };
						html += `
					<div class="history-item">
						<div class="history-date">${p.paymentDate || '-'}</div>
						<div class="history-content">
							<strong>${parseFloat(p.amount).toFixed(2)} ${p.currency}</strong>
							<span class="status-badge">${methodTexts[p.paymentMethod] || p.paymentMethod}</span>
							${p.reference ? `<span class="history-detail">Ref: ${this.escapeHtml(p.reference)}</span>` : ''}
							${p.notes ? `<div class="history-note">${this.escapeHtml(p.notes)}</div>` : ''}
						</div>
						<div class="history-actions" style="white-space: nowrap;">
							<button class="btn btn-sm btn-secondary edit-payment-btn" data-id="${p.id}">✏️</button>
							<button class="btn btn-sm btn-danger delete-payment-btn" data-id="${p.id}">🗑️</button>
						</div>
					</div>
				`;
					});
					html += '</div>';
					container.innerHTML = html;

					// Event delegation for payment buttons
					container.querySelectorAll('.edit-payment-btn').forEach(btn => {
						btn.addEventListener('click', () => this.editPayment(parseInt(btn.dataset.id)));
					});
					container.querySelectorAll('.delete-payment-btn').forEach(btn => {
						btn.addEventListener('click', () => this.deletePayment(parseInt(btn.dataset.id)));
					});
				})
				.catch(e => {
					console.error('Error loading payments:', e);
				});
		},

		editPayment: function (id) {
			fetch(`${this.apiBase}/payments/${id}`, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(payment => {
					if (payment.error) throw new Error(payment.error);

					const modal = document.getElementById('payment-modal');
					const form = document.getElementById('payment-form');
					if (!modal || !form) return;

					document.getElementById('payment-id').value = payment.id;
					document.getElementById('payment-invoice-id').value = payment.invoiceId || '';
					document.getElementById('payment-client-id').value = payment.clientId || '';
					document.getElementById('payment-amount').value = payment.amount || 0;
					document.getElementById('payment-currency').value = payment.currency || 'USD';
					document.getElementById('payment-date').value = payment.paymentDate || '';
					document.getElementById('payment-method').value = payment.paymentMethod || 'cash';
					document.getElementById('payment-reference').value = payment.reference || '';
					document.getElementById('payment-notes').value = payment.notes || '';

					document.getElementById('payment-modal-title').textContent = 'Ödeme Düzenle';
					modal.style.display = 'block';
				})
				.catch(e => this.showError('Veri yükleme hatası: ' + e.message));
		},

		deletePayment: function (id) {
			if (!confirm('Bu ödemeyi silmek istediğinize emin misiniz?')) return;
			fetch(`${this.apiBase}/payments/${id}`, {
				method: 'DELETE',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(() => {
					this.loadPayments();
					this.loadInvoices();
					if (this.currentInvoiceId) {
						this.showInvoiceDetail(this.currentInvoiceId);
					}
					this.showSuccess('Ödeme silindi');
				})
				.catch(e => this.showError('Silme hatası'));
		},

		// ===== INVOICE ITEMS =====
		showInvoiceItemModal: function (id = null) {
			const modal = document.getElementById('invoice-item-modal');
			const form = document.getElementById('invoice-item-form');
			if (!modal || !form) return;

			form.reset();
			document.getElementById('invoice-item-id').value = '';
			document.getElementById('invoice-item-invoice-id').value = this.currentInvoiceId || '';
			document.getElementById('invoice-item-quantity').value = '1';
			document.getElementById('invoice-item-type').value = 'manual';
			document.getElementById('invoice-item-ref-group').style.display = 'none';
			document.getElementById('invoice-item-discount').value = '0';
			document.getElementById('invoice-item-discount-type').value = 'fixed';
			document.getElementById('invoice-item-modal-title').textContent = 'Fatura Kalemi Ekle';

			// Get current invoice currency and client
			if (this.currentInvoiceId) {
				const inv = this.invoices.find(i => i.id == this.currentInvoiceId);
				if (inv) {
					document.getElementById('invoice-item-currency').value = inv.currency || 'USD';
				}
			}

			// Setup item type change handler
			const typeSelect = document.getElementById('invoice-item-type');
			typeSelect.onchange = () => this.onInvoiceItemTypeChange(typeSelect.value);

			modal.style.display = 'block';
		},

		onInvoiceItemTypeChange: function (type) {
			const refGroup = document.getElementById('invoice-item-ref-group');
			const refSelect = document.getElementById('invoice-item-ref-id');

			if (type === 'manual') {
				refGroup.style.display = 'none';
				return;
			}

			refGroup.style.display = 'block';
			refSelect.innerHTML = '<option value="">Seçin</option>';

			let items = [];
			switch (type) {
				case 'domain':
					items = this.domains;
					break;
				case 'hosting':
					items = this.hostings;
					break;
				case 'website':
					items = this.websites;
					break;
				case 'service':
					items = this.services;
					break;
				case 'project':
					items = this.projects;
					break;
			}

			// Store items for later reference
			this._currentItemList = items;

			items.forEach(item => {
				const name = item.name || item.domainName || item.provider || 'Unknown';
				const price = item.price || item.budget || 0;
				const currency = item.currency || 'USD';
				const client = this.clients.find(c => c.id == item.clientId);
				const clientName = client ? ` - ${client.name}` : '';
				refSelect.innerHTML += `<option value="${item.id}">${this.escapeHtml(name)}${clientName} (${price} ${currency})</option>`;
			});

			// Auto-fill details when selecting
			refSelect.onchange = () => {
				const selectedId = refSelect.value;
				if (selectedId) {
					const item = this._currentItemList.find(i => i.id == selectedId);
					if (item) {
						const name = item.name || item.domainName || item.provider || '';
						const price = item.price || item.budget || 0;
						const currency = item.currency || 'USD';

						document.getElementById('invoice-item-unit-price').value = price;
						document.getElementById('invoice-item-currency').value = currency;
						document.getElementById('invoice-item-description').value = `${name} - Yenileme`;

						// Set period dates based on item type
						const today = new Date();
						const todayStr = today.toISOString().split('T')[0];
						document.getElementById('invoice-item-start-date').value = item.expirationDate || todayStr;

						// Calculate end date based on renewal interval
						const interval = item.renewalInterval || 'yearly';
						const months = { monthly: 1, quarterly: 3, yearly: 12, biennial: 24 };
						const addMonths = months[interval] || 12;

						let endDate = new Date(item.expirationDate || today);
						endDate.setMonth(endDate.getMonth() + addMonths);
						document.getElementById('invoice-item-end-date').value = endDate.toISOString().split('T')[0];
					}
				}
			};
		},

		saveInvoiceItem: function () {
			const itemId = document.getElementById('invoice-item-id').value;
			const invoiceId = document.getElementById('invoice-item-invoice-id').value;

			if (!invoiceId) {
				this.showError('Fatura ID bulunamadı');
				return;
			}

			const data = new URLSearchParams({
				invoiceId: invoiceId,
				description: document.getElementById('invoice-item-description').value,
				itemType: document.getElementById('invoice-item-type').value,
				itemId: document.getElementById('invoice-item-ref-id').value || '0',
				quantity: document.getElementById('invoice-item-quantity').value || '1',
				unitPrice: document.getElementById('invoice-item-unit-price').value || '0',
				currency: document.getElementById('invoice-item-currency').value,
				periodStart: document.getElementById('invoice-item-start-date').value || '',
				periodEnd: document.getElementById('invoice-item-end-date').value || '',
				discount: document.getElementById('invoice-item-discount').value || '0',
				discountType: document.getElementById('invoice-item-discount-type').value
			});

			const url = itemId ? `${this.apiBase}/invoice-items/${itemId}` : `${this.apiBase}/invoice-items`;
			const method = itemId ? 'PUT' : 'POST';

			fetch(url, {
				method: method,
				headers: {
					'requesttoken': OC.requestToken,
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.closeModal('invoice-item-modal');
					this.loadInvoiceItems(invoiceId);
					this.loadInvoices(); // Reload to get updated totals
					if (this.currentInvoiceId) this.showInvoiceDetail(this.currentInvoiceId);
					this.showSuccess(itemId ? 'Fatura kalemi güncellendi' : 'Fatura kalemi eklendi');
				})
				.catch(e => this.showError('Kaydetme hatası: ' + e.message));
		},

		getRelatedItem: function (type, itemId) {
			switch (type) {
				case 'domain': return this.domains.find(d => d.id == itemId);
				case 'hosting': return this.hostings.find(h => h.id == itemId);
				case 'website': return this.websites.find(w => w.id == itemId);
				case 'service': return this.services.find(s => s.id == itemId);
				case 'project': return this.projects.find(p => p.id == itemId);
				default: return null;
			}
		},

		editInvoiceItem: function (id) {
			// Fetch item data first
			fetch(`${this.apiBase}/invoice-items/${id}`, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(item => {
					if (item.error) throw new Error(item.error);

					const modal = document.getElementById('invoice-item-modal');
					const form = document.getElementById('invoice-item-form');
					if (!modal || !form) return;

					// Fill form with item data
					document.getElementById('invoice-item-id').value = item.id;
					document.getElementById('invoice-item-invoice-id').value = item.invoiceId;
					document.getElementById('invoice-item-type').value = item.itemType || 'manual';
					document.getElementById('invoice-item-description').value = item.description || '';
					document.getElementById('invoice-item-quantity').value = item.quantity || 1;
					document.getElementById('invoice-item-unit-price').value = item.unitPrice || 0;
					document.getElementById('invoice-item-currency').value = item.currency || 'USD';
					document.getElementById('invoice-item-start-date').value = item.periodStart || '';
					document.getElementById('invoice-item-end-date').value = item.periodEnd || '';
					document.getElementById('invoice-item-discount').value = item.discount || 0;
					document.getElementById('invoice-item-discount-type').value = item.discountType || 'fixed';

					// Setup item type change handler and trigger it
					const typeSelect = document.getElementById('invoice-item-type');
					typeSelect.onchange = () => this.onInvoiceItemTypeChange(typeSelect.value);
					this.onInvoiceItemTypeChange(item.itemType || 'manual');

					// Set the related item after dropdown is populated
					setTimeout(() => {
						if (item.itemId) {
							document.getElementById('invoice-item-ref-id').value = item.itemId;
						}
					}, 100);

					document.getElementById('invoice-item-modal-title').textContent = 'Fatura Kalemi Düzenle';
					modal.style.display = 'block';
				})
				.catch(e => this.showError('Veri yükleme hatası: ' + e.message));
		},

		deleteInvoiceItem: function (id) {
			if (!confirm('Bu fatura kalemini silmek istediğinize emin misiniz?')) return;

			fetch(`${this.apiBase}/invoice-items/${id}`, {
				method: 'DELETE',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(() => {
					if (this.currentInvoiceId) {
						this.loadInvoiceItems(this.currentInvoiceId);
						this.recalculateInvoiceTotal(this.currentInvoiceId);
					}
					this.showSuccess('Fatura kalemi silindi');
				})
				.catch(e => this.showError('Silme hatası'));
		},

		recalculateInvoiceTotal: function (invoiceId) {
			fetch(`${this.apiBase}/invoices/${invoiceId}/items`, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(items => {
					let total = 0;
					items.forEach(item => {
						let itemTotal = (item.quantity || 1) * (item.unitPrice || 0);
						// Apply discount
						if (item.discount && item.discount > 0) {
							if (item.discountType === 'percent') {
								itemTotal -= itemTotal * (item.discount / 100);
							} else {
								itemTotal -= item.discount;
							}
						}
						total += itemTotal;
					});

					// Update invoice total
					const inv = this.invoices.find(i => i.id == invoiceId);
					if (inv) {
						this.updateInvoiceTotal(invoiceId, total, inv.currency);
					}
				});
		},

		updateInvoiceTotal: function (invoiceId, total, currency) {
			const data = new URLSearchParams({ totalAmount: total.toFixed(2) });

			fetch(`${this.apiBase}/invoices/${invoiceId}`, {
				method: 'PUT',
				headers: {
					'requesttoken': OC.requestToken,
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: data.toString()
			})
				.then(r => r.json())
				.then(() => {
					this.loadInvoices();
					// Update detail view
					document.getElementById('invoice-detail-total').textContent = `${total.toFixed(2)} ${currency}`;
					const inv = this.invoices.find(i => i.id == invoiceId);
					const paid = inv ? (inv.paidAmount || 0) : 0;
					document.getElementById('invoice-detail-remaining').textContent = `${(total - paid).toFixed(2)} ${currency}`;
				});
		},

		hideInvoiceDetail: function () {
			document.getElementById('invoices-list-view').style.display = 'block';
			document.getElementById('invoice-detail-view').style.display = 'none';
			this.currentInvoiceId = null;
		},

		showInvoiceModal: function (id = null) {
			const modal = document.getElementById('invoice-modal');
			const form = document.getElementById('invoice-form');
			if (!modal || !form) return;

			form.reset();
			document.getElementById('invoice-id').value = '';
			document.getElementById('invoice-issue-date').value = new Date().toISOString().split('T')[0];
			document.getElementById('invoice-due-date').value = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
			this.updateClientSelect('invoice-client-id');

			if (id) {
				const inv = this.invoices.find(i => i.id == id);
				if (inv) {
					document.getElementById('invoice-id').value = inv.id;
					document.getElementById('invoice-client-id').value = inv.clientId || '';
					document.getElementById('invoice-number').value = inv.invoiceNumber || '';
					document.getElementById('invoice-issue-date').value = inv.issueDate || '';
					document.getElementById('invoice-due-date').value = inv.dueDate || '';
					document.getElementById('invoice-total').value = inv.totalAmount || '';
					document.getElementById('invoice-currency').value = inv.currency || 'USD';
					document.getElementById('invoice-status').value = inv.status || 'draft';
					document.getElementById('invoice-notes').value = inv.notes || '';
				}
			}
			modal.style.display = 'block';
		},

		saveInvoice: function () {
			const id = document.getElementById('invoice-id').value;
			const data = new URLSearchParams({
				clientId: document.getElementById('invoice-client-id').value,
				invoiceNumber: document.getElementById('invoice-number').value,
				issueDate: document.getElementById('invoice-issue-date').value,
				dueDate: document.getElementById('invoice-due-date').value,
				totalAmount: document.getElementById('invoice-total').value,
				currency: document.getElementById('invoice-currency').value,
				status: document.getElementById('invoice-status').value,
				notes: document.getElementById('invoice-notes').value
			});

			const url = id ? `${this.apiBase}/invoices/${id}` : `${this.apiBase}/invoices`;
			fetch(url, {
				method: id ? 'PUT' : 'POST',
				headers: { 'requesttoken': OC.requestToken, 'Content-Type': 'application/x-www-form-urlencoded' },
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.closeModal('invoice-modal');
					this.loadInvoices();
					this.showSuccess('Fatura kaydedildi');
				})
				.catch(e => this.showError('Kaydetme hatası: ' + e.message));
		},

		deleteInvoice: function (id) {
			fetch(`${this.apiBase}/invoices/${id}`, {
				method: 'DELETE',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(() => {
					this.loadInvoices();
					this.showSuccess('Fatura silindi');
				})
				.catch(e => this.showError('Silme hatası'));
		},

		// ===== PAYMENTS =====
		loadPayments: function () {
			fetch(this.apiBase + '/payments', {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(data => {
					this.payments = Array.isArray(data) ? data : [];
				})
				.catch(e => {
					console.error('Error loading payments:', e);
					this.payments = [];
				});
		},

		showPaymentModal: function (id = null, invoiceId = null) {
			const modal = document.getElementById('payment-modal');
			const form = document.getElementById('payment-form');
			if (!modal || !form) return;

			form.reset();
			document.getElementById('payment-id').value = '';
			document.getElementById('payment-invoice-id').value = invoiceId || '';
			document.getElementById('payment-date').value = new Date().toISOString().split('T')[0];
			this.updateClientSelect('payment-client-id');

			// If invoice is provided, set client and amount
			if (invoiceId) {
				const inv = this.invoices.find(i => i.id == invoiceId);
				if (inv) {
					document.getElementById('payment-client-id').value = inv.clientId;
					document.getElementById('payment-amount').value = (inv.totalAmount - (inv.paidAmount || 0));
					document.getElementById('payment-currency').value = inv.currency;
				}
			}

			modal.style.display = 'block';
		},

		savePayment: function () {
			const id = document.getElementById('payment-id').value;
			const data = new URLSearchParams({
				invoiceId: document.getElementById('payment-invoice-id').value,
				clientId: document.getElementById('payment-client-id').value,
				amount: document.getElementById('payment-amount').value,
				currency: document.getElementById('payment-currency').value,
				paymentDate: document.getElementById('payment-date').value,
				paymentMethod: document.getElementById('payment-method').value,
				reference: document.getElementById('payment-reference').value,
				notes: document.getElementById('payment-notes').value
			});

			const url = id ? `${this.apiBase}/payments/${id}` : `${this.apiBase}/payments`;
			fetch(url, {
				method: id ? 'PUT' : 'POST',
				headers: { 'requesttoken': OC.requestToken, 'Content-Type': 'application/x-www-form-urlencoded' },
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.closeModal('payment-modal');
					this.loadPayments();
					this.loadInvoices();
					this.updateDashboard();
					this.showSuccess('Ödeme kaydedildi');
				})
				.catch(e => this.showError('Kaydetme hatası: ' + e.message));
		},

		// ===== PROJECTS =====
		loadProjects: function () {
			fetch(this.apiBase + '/projects', {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(data => {
					this.projects = Array.isArray(data) ? data : [];
					this.renderProjects();
					this.updateProjectSelects();
				})
				.catch(e => {
					console.error('Error loading projects:', e);
					this.projects = [];
				});
		},

		updateProjectSelects: function () {
			const select = document.getElementById('task-project-id');
			if (!select) return;
			select.innerHTML = '<option value="">Proje Seçin (opsiyonel)</option>';
			this.projects.forEach(p => {
				select.innerHTML += `<option value="${p.id}">${this.escapeHtml(p.name)}</option>`;
			});
		},

		renderProjects: function (filter = 'all') {
			const list = document.getElementById('projects-list');
			if (!list) return;

			let filtered = this.projects;
			if (filter !== 'all') filtered = filtered.filter(p => p.status === filter);

			if (filtered.length === 0) {
				list.innerHTML = '<p class="empty-state">Proje yok</p>';
				return;
			}

			const projectTypeLabels = {
				website: '🌐 Web Sitesi',
				ecommerce: '🛒 E-Ticaret',
				webapp: '📱 Web Uygulaması',
				theme: '🎨 Tema/Modül',
				design: '🖼️ Grafik Tasarım',
				server: '🖥️ Sunucu',
				email: '📧 Mail',
				hosting: '☁️ Hosting',
				device: '📟 Cihaz',
				support: '🛠️ Destek',
				seo: '📈 SEO',
				other: '📦 Diğer'
			};

			let html = '';
			filtered.forEach(proj => {
				const client = this.clients.find(c => c.id == proj.clientId);
				const statusTexts = { active: 'Aktif', on_hold: 'Beklemede', completed: 'Tamamlandı', cancelled: 'İptal' };
				const daysLeft = proj.deadline ? this.calculateDaysLeft(proj.deadline) : null;
				const deadlineBadge = daysLeft !== null && daysLeft <= 7 && daysLeft >= 0 ?
					`<span class="status-badge status-badge--draft">${daysLeft} gün</span>` : '';
				const typeLabel = proj.projectType ? projectTypeLabels[proj.projectType] || proj.projectType : '';

				// Calculate progress
				const projectTasks = this.tasks.filter(t => t.projectId == proj.id);
				const totalActive = projectTasks.filter(t => t.status !== 'cancelled').length;
				const doneTasks = projectTasks.filter(t => t.status === 'done').length;
				const progress = totalActive > 0 ? Math.round((doneTasks / totalActive) * 100) : 0;

				html += `
				<div class="list-item project-item" data-id="${proj.id}" style="position: relative;">
					<div class="list-item__content">
						<div class="list-item__title">${this.escapeHtml(proj.name)}</div>
						<div class="list-item__meta">
							${typeLabel ? `<span class="project-type-badge">${typeLabel}</span> • ` : ''}
							${client ? `<strong>${client.name}</strong>` : 'Bilinmeyen'} • Deadline: ${proj.deadline || '-'}
						</div>
					</div>
					
					<div class="project-progress-mini" style="flex-shrink: 0; margin: 0 40px; text-align: right;">
						<div style="font-size: 11px; margin-bottom: 4px; color: var(--color-text-maxcontrast);">İlerleme: ${doneTasks}/${totalActive}</div>
						<div class="progress-bar" style="width: 120px; height: 8px; background: var(--color-background-dark); border-radius: 4px; overflow: hidden;">
							<div style="width: ${progress}%; height: 100%; background: ${progress === 100 ? 'var(--color-success)' : 'var(--color-primary-element)'}; transition: width 0.3s;"></div>
						</div>
					</div>

					<div class="list-item__stats">
						${proj.budget ? `<span style="font-weight: 600;">${proj.budget} ${proj.currency || 'USD'}</span>` : ''}
						${deadlineBadge}
						<span class="status-badge project-status--${proj.status}">${statusTexts[proj.status] || proj.status}</span>
					</div>
				</div>
			`;
			});
			list.innerHTML = html;

			// Event delegation
			list.querySelectorAll('.project-item').forEach(item => {
				item.addEventListener('click', () => {
					const id = item.getAttribute('data-id');
					this.showProjectDetail(parseInt(id));
				});
			});
		},

		showProjectDetail: function (id) {
			const proj = this.projects.find(p => p.id == id);
			if (!proj) return;

			this.currentProjectId = id;
			document.getElementById('projects-list-view').style.display = 'none';
			document.getElementById('project-detail-view').style.display = 'block';

			const client = this.clients.find(c => c.id == proj.clientId);
			const statusTexts = { active: 'Aktif', on_hold: 'Beklemede', completed: 'Tamamlandı', cancelled: 'İptal' };
			const daysLeft = proj.deadline ? this.calculateDaysLeft(proj.deadline) : null;

			const projectTypeLabels = {
				website: '🌐 Web Sitesi',
				ecommerce: '🛒 E-Ticaret',
				webapp: '📱 Web Uygulaması',
				theme: '🎨 Tema/Modül',
				design: '🖼️ Grafik Tasarım',
				server: '🖥️ Sunucu',
				email: '📧 Mail',
				hosting: '☁️ Hosting',
				device: '📟 Cihaz',
				support: '🛠️ Destek',
				seo: '📈 SEO',
				other: '📦 Diğer'
			};

			document.getElementById('project-detail-name').textContent = proj.name;
			document.getElementById('project-detail-client').textContent = client ? client.name : '-';
			document.getElementById('project-detail-type').textContent = proj.projectType ? (projectTypeLabels[proj.projectType] || proj.projectType) : '-';

			// Status with badge
			const statusEl = document.getElementById('project-detail-status');
			statusEl.innerHTML = `<span class="status-badge project-status--${proj.status}">${statusTexts[proj.status] || proj.status}</span>`;

			// Deadline with days left
			const deadlineEl = document.getElementById('project-detail-deadline');
			if (proj.deadline) {
				const deadlineClass = daysLeft <= 0 ? 'status-badge--overdue' : (daysLeft <= 7 ? 'status-badge--draft' : '');
				deadlineEl.innerHTML = `${proj.deadline} ${daysLeft !== null ? `<span class="status-badge ${deadlineClass}">${daysLeft <= 0 ? 'Geçti!' : daysLeft + ' gün'}</span>` : ''}`;
			} else {
				deadlineEl.textContent = '-';
			}

			// Start date
			const startEl = document.getElementById('project-detail-start');
			if (startEl) startEl.textContent = proj.startDate || '-';

			document.getElementById('project-detail-budget').textContent = proj.budget ? `${proj.budget} ${proj.currency}` : '-';
			document.getElementById('project-detail-description').textContent = proj.description || '-';

			// Notes
			const notesEl = document.getElementById('project-detail-notes');
			if (notesEl) notesEl.textContent = proj.notes || '-';

			// Load project tasks with progress
			this.loadProjectTasks(id);

			// Load linked items
			this.loadProjectItems(id);

			// Load project invoices and calculate financials
			this.loadProjectFinancials(id);

			// Load time tracking
			this.loadTimeTracking(id);
		},

		loadProjectFinancials: function (projectId) {
			const proj = this.projects.find(p => p.id == projectId);
			if (!proj) return;

			const container = document.getElementById('project-financials');
			if (!container) return;

			const budget = parseFloat(proj.budget) || 0;

			// Find invoices related to this project (by checking notes for project name)
			const projectInvoices = (this.invoices || []).filter(inv => {
				if (inv.clientId != proj.clientId) return false;
				// Check if invoice notes contains project name
				if (inv.notes && inv.notes.includes(`Proje: ${proj.name}`)) {
					return true;
				}
				return false;
			});

			// Calculate totals
			let totalInvoiced = 0;
			let totalPaid = 0;
			let totalPending = 0;

			projectInvoices.forEach(inv => {
				const total = parseFloat(inv.totalAmount) || 0;
				const paid = parseFloat(inv.paidAmount) || 0;
				totalInvoiced += total;
				totalPaid += paid;
				totalPending += (total - paid);
			});

			const statusTexts = {
				draft: 'Taslak',
				sent: 'Gönderildi',
				paid: 'Ödendi',
				overdue: 'Gecikmiş',
				cancelled: 'İptal'
			};

			const statusColors = {
				draft: '#6b7280',
				sent: '#3b82f6',
				paid: '#10b981',
				overdue: '#ef4444',
				cancelled: '#9ca3af'
			};

			let invoicesHtml = '';
			if (projectInvoices.length > 0) {
				invoicesHtml = `
					<div class="project-invoices-list" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--color-border);">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
							<strong style="font-size: 13px; color: var(--color-main-text);">Proje Faturaları</strong>
							<span style="font-size: 11px; color: var(--color-text-maxcontrast);">${projectInvoices.length} fatura</span>
						</div>
						<div style="max-height: 300px; overflow-y: auto;">
							${projectInvoices.map(inv => {
								const statusColor = statusColors[inv.status] || '#6b7280';
								const statusText = statusTexts[inv.status] || inv.status;
								return `
									<div class="project-invoice-item" data-invoice-id="${inv.id}" style="padding: 12px; border: 1px solid var(--color-border); border-radius: 8px; margin-bottom: 8px; cursor: pointer; transition: all 0.2s;">
										<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 6px;">
											<div>
												<div style="font-weight: 600; font-size: 13px; color: var(--color-main-text); margin-bottom: 4px;">
													${this.escapeHtml(inv.invoiceNumber || 'Fatura #' + inv.id)}
												</div>
												<div style="font-size: 11px; color: var(--color-text-maxcontrast);">
													${inv.issueDate || '-'}
												</div>
											</div>
											<span style="font-size: 10px; padding: 4px 8px; border-radius: 12px; background: ${statusColor}20; color: ${statusColor}; font-weight: 600;">
												${statusText}
											</span>
										</div>
										<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
											<div style="font-size: 12px; color: var(--color-text-maxcontrast);">
												Toplam: <strong style="color: var(--color-main-text);">${(parseFloat(inv.totalAmount) || 0).toFixed(2)} ${inv.currency || 'USD'}</strong>
											</div>
											<div style="font-size: 12px; color: var(--color-text-maxcontrast);">
												Ödenen: <strong style="color: #10b981;">${(parseFloat(inv.paidAmount) || 0).toFixed(2)} ${inv.currency || 'USD'}</strong>
											</div>
										</div>
									</div>
								`;
							}).join('')}
						</div>
					</div>
				`;
			}

			container.innerHTML = `
				<div class="financial-summary">
					<div class="financial-item" style="margin-bottom: 12px;">
						<span class="financial-label">Bütçe:</span>
						<span class="financial-value">${budget.toFixed(2)} ${proj.currency || 'USD'}</span>
					</div>
					${totalInvoiced > 0 ? `
						<div class="financial-item" style="margin-bottom: 12px;">
							<span class="financial-label">Toplam Faturalanan:</span>
							<span class="financial-value">${totalInvoiced.toFixed(2)} ${proj.currency || 'USD'}</span>
						</div>
						<div class="financial-item" style="margin-bottom: 12px;">
							<span class="financial-label">Ödenen:</span>
							<span class="financial-value" style="color: #10b981;">${totalPaid.toFixed(2)} ${proj.currency || 'USD'}</span>
						</div>
						${totalPending > 0 ? `
							<div class="financial-item">
								<span class="financial-label">Bekleyen:</span>
								<span class="financial-value" style="color: #f59e0b;">${totalPending.toFixed(2)} ${proj.currency || 'USD'}</span>
							</div>
						` : ''}
					` : ''}
				</div>
				${invoicesHtml}
				<div style="margin-top: 16px;">
					<button class="btn btn-sm btn-primary create-project-invoice-btn" style="width: 100%;">
						📄 Fatura Oluştur
					</button>
				</div>
			`;

			container.querySelector('.create-project-invoice-btn')?.addEventListener('click', () => {
				this.createInvoiceFromProject(projectId);
			});

			// Add click listeners for invoice items
			container.querySelectorAll('.project-invoice-item').forEach(item => {
				item.addEventListener('click', () => {
					const invoiceId = parseInt(item.dataset.invoiceId);
					if (invoiceId) {
						this.hideProjectDetail();
						this.switchTab('invoices');
						setTimeout(() => {
							this.showInvoiceDetail(invoiceId);
						}, 300);
					}
				});
			});
		},

		createInvoiceFromProject: function (projectId) {
			const proj = this.projects.find(p => p.id == projectId);
			if (!proj) return;

			// Create invoice and stay on project detail page
			const data = new URLSearchParams({
				clientId: proj.clientId,
				currency: proj.currency || 'USD',
				status: 'draft',
				notes: `Proje: ${proj.name}`
			});

			fetch(`${this.apiBase}/invoices`, {
				method: 'POST',
				headers: {
					'requesttoken': OC.requestToken,
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					// Reload invoices to get the new one
					this.loadInvoices().then(() => {
						// Reload project financials to show the new invoice
						this.loadProjectFinancials(projectId);
						this.showSuccess('Fatura oluşturuldu ve proje finansal bilgilerine eklendi.');
					});
				})
				.catch(e => this.showError('Fatura oluşturma hatası: ' + e.message));
		},

		loadProjectTasks: function (projectId) {
			fetch(`${this.apiBase}/projects/${projectId}/tasks`, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(tasks => {
					const container = document.getElementById('project-detail-tasks');
					if (!container) return;

					if (!tasks || tasks.length === 0) {
						container.innerHTML = `
					<p class="empty-message">Bu projede henüz görev yok</p>
					<button class="btn btn-primary btn-sm add-project-task-btn">+ Görev Ekle</button>
				`;
						container.querySelector('.add-project-task-btn')?.addEventListener('click', () => this.showTaskModalForProject(projectId));
						return;
					}

					// Calculate progress
					const totalTasks = tasks.length;
					const doneTasks = tasks.filter(t => t.status === 'done').length;
					const cancelledTasks = tasks.filter(t => t.status === 'cancelled').length;
					const activeTasks = totalTasks - cancelledTasks;
					const progress = activeTasks > 0 ? Math.round((doneTasks / activeTasks) * 100) : 0;

					const priorityTexts = { low: 'Düşük', medium: 'Orta', high: 'Yüksek' };
					const statusTexts = { todo: 'Yapılacak', in_progress: 'Devam Ediyor', done: 'Tamamlandı', cancelled: 'İptal Edildi' };

					let html = `
				<div class="project-progress" style="margin-bottom: 20px;">
					<div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
						<span><strong>İlerleme:</strong> ${doneTasks}/${activeTasks} görev tamamlandı ${cancelledTasks > 0 ? `(${cancelledTasks} iptal)` : ''}</span>
						<span><strong>${progress}%</strong></span>
					</div>
				<div class="progress-bar" style="height: 12px; background: var(--color-background-dark); border-radius: 6px; overflow: hidden;">
					<div style="width: ${progress}%; height: 100%; background: ${progress === 100 ? 'var(--color-success)' : 'var(--color-primary-element)'}; transition: width 0.3s;"></div>
				</div>
			</div>
			<button class="btn btn-primary btn-sm add-project-task-btn" style="margin-bottom: 16px;">+ Görev Ekle</button>
		`;

					tasks.forEach(task => {
						const overdue = task.dueDate && this.calculateDaysLeft(task.dueDate) < 0 && task.status !== 'done' && task.status !== 'cancelled';
						html += `
					<div class="task-list-item ${overdue ? 'status-critical' : ''} ${task.status === 'cancelled' ? 'task--cancelled' : ''}" data-task-id="${task.id}">
						<input type="checkbox" class="project-task-cb" data-id="${task.id}" ${task.status === 'done' ? 'checked' : ''} ${task.status === 'cancelled' ? 'disabled' : ''} style="margin-right: 12px; width: 20px; height: 20px;">
						<div style="flex: 1;">
							<span class="task-list-item__title task-status--${task.status}">${this.escapeHtml(task.title)}</span>
							<div class="list-item__meta" style="font-size: 12px; margin-top: 4px;">
								<span class="priority-badge priority-badge--${task.priority}">${priorityTexts[task.priority] || task.priority}</span>
								<span style="margin-left: 8px;">${task.dueDate || 'Tarih yok'}</span>
							</div>
						</div>
						<span class="status-badge task-status--${task.status}">${statusTexts[task.status] || task.status}</span>
					</div>
				`;
					});
					container.innerHTML = html;

					// Event listeners
					container.querySelectorAll('.task-list-item').forEach(item => {
						item.addEventListener('click', (e) => {
							if (e.target.classList.contains('project-task-cb')) return;
							const tId = item.getAttribute('data-task-id');
							if (tId) {
								this.hideProjectDetail();
								this.switchTab('tasks');
								setTimeout(() => this.showTaskDetail(parseInt(tId)), 100);
							}
						});
					});

					container.querySelectorAll('.project-task-cb').forEach(cb => {
						cb.addEventListener('click', (e) => {
							e.stopPropagation();
							const tId = cb.getAttribute('data-id');
							this.toggleTaskStatusAndReload(parseInt(tId), projectId);
						});
					});

					// Add task button
					container.querySelector('.add-project-task-btn')?.addEventListener('click', () => this.showTaskModalForProject(projectId));
				})
				.catch(e => {
					console.error('Error loading project tasks:', e);
				});
		},

		toggleTaskStatusAndReload: function (taskId, projectId) {
			const task = this.tasks.find(t => t.id == taskId);
			if (!task) return;

			const newStatus = task.status === 'done' ? 'todo' : 'done';

			const data = new URLSearchParams({ status: newStatus });
			fetch(`${this.apiBase}/tasks/${taskId}`, {
				method: 'PUT',
				headers: {
					'requesttoken': OC.requestToken,
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					// Update local data
					if (task) task.status = newStatus;
					// Reload project tasks
					this.loadProjectTasks(projectId);
					this.loadTasks();
				})
				.catch(e => this.showError('Güncelleme hatası'));
		},

		showTaskModalForProject: function (projectId) {
			this.showTaskModal();
			setTimeout(() => {
				document.getElementById('task-project-id').value = projectId;
			}, 100);
		},

		hideProjectDetail: function () {
			// Stop timer display if running
			if (this.timerInterval) {
				clearInterval(this.timerInterval);
				this.timerInterval = null;
			}
			
			document.getElementById('projects-list-view').style.display = 'block';
			document.getElementById('project-detail-view').style.display = 'none';
			this.currentProjectId = null;
			this.currentRunningEntry = null;
			this.timerButtonsSetup = false; // Reset for next project
		},

		showProjectModal: function (id = null) {
			const modal = document.getElementById('project-modal');
			const form = document.getElementById('project-form');
			if (!modal || !form) return;

			form.reset();
			document.getElementById('project-id').value = '';
			document.getElementById('project-start-date').value = new Date().toISOString().split('T')[0];
			this.updateClientSelect('project-client-id');

			if (id) {
				const proj = this.projects.find(p => p.id == id);
				if (proj) {
					document.getElementById('project-id').value = proj.id;
					document.getElementById('project-client-id').value = proj.clientId || '';
					document.getElementById('project-type').value = proj.projectType || '';
					document.getElementById('project-name').value = proj.name || '';
					document.getElementById('project-description').value = proj.description || '';
					document.getElementById('project-status').value = proj.status || 'active';
					document.getElementById('project-start-date').value = proj.startDate || '';
					document.getElementById('project-deadline').value = proj.deadline || '';
					document.getElementById('project-budget').value = proj.budget || '';
					document.getElementById('project-currency').value = proj.currency || 'USD';
					document.getElementById('project-notes').value = proj.notes || '';
				}
			}
			modal.style.display = 'block';
		},

		saveProject: function () {
			const id = document.getElementById('project-id').value;
			const data = new URLSearchParams({
				clientId: document.getElementById('project-client-id').value,
				projectType: document.getElementById('project-type').value,
				name: document.getElementById('project-name').value,
				description: document.getElementById('project-description').value,
				status: document.getElementById('project-status').value,
				startDate: document.getElementById('project-start-date').value,
				deadline: document.getElementById('project-deadline').value,
				budget: document.getElementById('project-budget').value,
				currency: document.getElementById('project-currency').value,
				notes: document.getElementById('project-notes').value
			});

			const url = id ? `${this.apiBase}/projects/${id}` : `${this.apiBase}/projects`;
			fetch(url, {
				method: id ? 'PUT' : 'POST',
				headers: { 'requesttoken': OC.requestToken, 'Content-Type': 'application/x-www-form-urlencoded' },
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.closeModal('project-modal');
					this.loadProjects();
					this.showSuccess('Proje kaydedildi');
				})
				.catch(e => this.showError('Kaydetme hatası: ' + e.message));
		},

		deleteProject: function (id) {
			fetch(`${this.apiBase}/projects/${id}`, {
				method: 'DELETE',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(() => {
					this.loadProjects();
					this.showSuccess('Proje silindi');
				})
				.catch(e => this.showError('Silme hatası'));
		},

		showProjectItemModal: function (projectId) {
			const modal = document.getElementById('project-item-modal');
			if (!modal) return;

			document.getElementById('project-item-project-id').value = projectId;
			document.getElementById('project-item-type').value = '';
			document.getElementById('project-item-select').innerHTML = '<option value="">Önce türü seçin</option>';

			// Setup type change handler
			const typeSelect = document.getElementById('project-item-type');
			typeSelect.onchange = () => this.onProjectItemTypeChange(typeSelect.value);

			modal.style.display = 'block';
		},

		onProjectItemTypeChange: function (type) {
			const select = document.getElementById('project-item-select');
			select.innerHTML = '<option value="">Seçin</option>';

			if (!type) return;

			let items = [];
			switch (type) {
				case 'domain':
					items = this.domains || [];
					items.forEach(d => {
						const client = this.clients.find(c => c.id == d.clientId);
						select.innerHTML += `<option value="${d.id}">${this.escapeHtml(d.domainName)} ${client ? '(' + client.name + ')' : ''}</option>`;
					});
					break;
				case 'hosting':
					items = this.hostings || [];
					items.forEach(h => {
						const client = this.clients.find(c => c.id == h.clientId);
						select.innerHTML += `<option value="${h.id}">${this.escapeHtml(h.provider)} - ${h.plan || 'N/A'} ${client ? '(' + client.name + ')' : ''}</option>`;
					});
					break;
				case 'website':
					items = this.websites || [];
					items.forEach(w => {
						const client = this.clients.find(c => c.id == w.clientId);
						select.innerHTML += `<option value="${w.id}">${this.escapeHtml(w.name)} ${client ? '(' + client.name + ')' : ''}</option>`;
					});
					break;
				case 'service':
					items = this.services || [];
					items.forEach(s => {
						const client = this.clients.find(c => c.id == s.clientId);
						select.innerHTML += `<option value="${s.id}">${this.escapeHtml(s.name)} ${client ? '(' + client.name + ')' : ''}</option>`;
					});
					break;
			}
		},

		saveProjectItem: function () {
			const projectId = document.getElementById('project-item-project-id').value;
			const itemType = document.getElementById('project-item-type').value;
			const itemId = document.getElementById('project-item-select').value;

			if (!projectId || !itemType || !itemId) {
				this.showError('Lütfen tüm alanları doldurun');
				return;
			}

			const data = new URLSearchParams({ itemType, itemId });

			fetch(`${this.apiBase}/projects/${projectId}/items`, {
				method: 'POST',
				headers: {
					'requesttoken': OC.requestToken,
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.closeModal('project-item-modal');
					this.loadProjectItems(projectId);
					this.showSuccess('Öğe projeye bağlandı');
				})
				.catch(e => this.showError('Bağlama hatası: ' + e.message));
		},

		loadProjectItems: function (projectId) {
			fetch(`${this.apiBase}/projects/${projectId}/items`, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(items => {
					const container = document.getElementById('project-linked-items');
					if (!container) return;

					if (!items || items.length === 0) {
						container.innerHTML = '<p class="empty-message">Bağlı öğe yok</p>';
						return;
					}

					const typeLabels = { domain: 'Domain', hosting: 'Hosting', website: 'Website', service: 'Hizmet' };
					const typeIcons = { domain: '🌐', hosting: '🖥️', website: '🌍', service: '🛠️' };

					let html = '<div class="linked-items-list">';
					items.forEach(item => {
						const itemData = this.getRelatedItem(item.itemType, item.itemId);
						const itemName = itemData ? (itemData.name || itemData.domainName || itemData.provider || 'N/A') : 'Bulunamadı';

						html += `
						<div class="linked-item">
							<span class="linked-item__icon">${typeIcons[item.itemType] || '📎'}</span>
							<span class="linked-item__type">${typeLabels[item.itemType] || item.itemType}</span>
							<span class="linked-item__name">${this.escapeHtml(itemName)}</span>
							<button class="btn btn-sm btn-danger remove-project-item-btn" data-id="${item.id}" data-project="${projectId}">🗑️</button>
						</div>
					`;
					});
					html += '</div>';
					container.innerHTML = html;

					// Event listeners
					container.querySelectorAll('.remove-project-item-btn').forEach(btn => {
						btn.addEventListener('click', () => {
							const itemId = btn.dataset.id;
							const projId = btn.dataset.project;
							this.removeProjectItem(projId, itemId);
						});
					});
				})
				.catch(e => console.error('Error loading project items:', e));
		},

		removeProjectItem: function (projectId, itemId) {
			if (!confirm('Bu öğeyi projeden kaldırmak istediğinize emin misiniz?')) return;

			fetch(`${this.apiBase}/projects/${projectId}/items/${itemId}`, {
				method: 'DELETE',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.loadProjectItems(projectId);
					this.showSuccess('Öğe projeden kaldırıldı');
				})
				.catch(e => this.showError('Kaldırma hatası'));
		},

		// ===== TASKS =====
		loadTasks: function () {
			return fetch(this.apiBase + '/tasks', {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(data => {
					this.tasks = Array.isArray(data) ? data : [];
					this.renderTasks();
					this.updateDashboard();
					this.updateTaskFilterCounts();
				})
				.catch(e => {
					console.error('Error loading tasks:', e);
					this.tasks = [];
				});
		},

		updateTaskFilterCounts: function () {
			const counts = {
				all: this.tasks.filter(t => !t.parentId).length,
				todo: this.tasks.filter(t => !t.parentId && t.status === 'todo').length,
				in_progress: this.tasks.filter(t => !t.parentId && t.status === 'in_progress').length,
				done: this.tasks.filter(t => !t.parentId && t.status === 'done').length,
				cancelled: this.tasks.filter(t => !t.parentId && t.status === 'cancelled').length
			};

			document.querySelectorAll('#tasks-tab .btn-filter').forEach(btn => {
				const filter = btn.getAttribute('data-filter');
				if (counts[filter] !== undefined) {
					const label = btn.textContent.split('(')[0].trim();
					btn.textContent = `${label} (${counts[filter]})`;
				}
			});
		},

		renderTasks: function (filter = 'all') {
			const list = document.getElementById('tasks-list');
			if (!list) return;

			let filtered = this.tasks.filter(t => !t.parentId); // Only show top-level tasks in list
			if (filter !== 'all') filtered = filtered.filter(t => t.status === filter);

			if (filtered.length === 0) {
				list.innerHTML = '<p class="empty-state">Görev yok</p>';
				return;
			}

			const statusTexts = { todo: 'Yapılacak', in_progress: 'Devam Ediyor', done: 'Tamamlandı', cancelled: 'İptal Edildi' };
			const priorityTexts = { low: 'Düşük', medium: 'Orta', high: 'Yüksek' };

			let html = '';
			filtered.forEach(task => {
				const project = this.projects.find(p => p.id == task.projectId);
				const daysLeft = task.dueDate ? this.calculateDaysLeft(task.dueDate) : null;
				const overdue = daysLeft !== null && daysLeft < 0 && task.status !== 'done' && task.status !== 'cancelled';
				const subtasks = this.tasks.filter(t => t.parentId == task.id);
				const completedSubtasks = subtasks.filter(t => t.status === 'done').length;

				const priorityClass = task.priority === 'high' ? 'priority-high-bg' : (task.priority === 'medium' ? 'priority-medium-bg' : 'priority-low-bg');

				const priorityTexts = { low: 'Düşük', medium: 'Orta', high: 'Yüksek' };
				const priorityIcons = { low: '📉', medium: '📊', high: '🔥' };

				const today = new Date().toISOString().split('T')[0];
				const isToday = task.dueDate === today;

				html += `
				<div class="list-item task-item ${overdue ? 'status-critical' : ''} ${task.status === 'cancelled' ? 'task--cancelled' : ''}" data-id="${task.id}" style="position: relative; overflow: hidden;">
					<div class="priority-indicator ${priorityClass}"></div>
					<div class="list-item__content">
						<div style="display: flex; align-items: flex-start;">
							<input type="checkbox" class="task-checkbox" data-id="${task.id}" ${task.status === 'done' ? 'checked' : ''} ${task.status === 'cancelled' ? 'disabled' : ''} style="margin-right: 18px; width: 22px; height: 22px; margin-top: 4px;">
							<div>
								<div class="list-item__title task-status--${task.status}">
									${this.escapeHtml(task.title)}
									${isToday ? '<span class="status-badge status-badge--draft" style="margin-left: 8px; font-size: 10px;">BUGÜN</span>' : ''}
								</div>
								<div class="list-item__meta">
									<span style="color: var(--color-primary-element); font-weight: 500;">${project ? project.name : 'Genel'}</span>
									 • ${task.dueDate || 'Tarih yok'}
									${subtasks.length > 0 ? ` • <span class="subtask-count">📋 ${completedSubtasks}/${subtasks.length} alt görev</span>` : ''}
								</div>
							</div>
						</div>
					</div>
					<div class="list-item__stats">
						<span class="priority-badge priority-badge--${task.priority}">${priorityIcons[task.priority]} ${priorityTexts[task.priority] || task.priority}</span>
						<span class="status-badge task-status--${task.status}">${statusTexts[task.status] || task.status}</span>
					</div>
				</div>
			`;
			});
			list.innerHTML = html;

			// Event delegation for task items
			list.querySelectorAll('.task-item').forEach(item => {
				item.addEventListener('click', (e) => {
					if (e.target.classList.contains('task-checkbox')) return;
					const id = item.getAttribute('data-id');
					this.showTaskDetail(parseInt(id));
				});
			});

			// Checkbox toggle
			list.querySelectorAll('.task-checkbox').forEach(cb => {
				cb.addEventListener('click', (e) => {
					e.stopPropagation();
					const id = cb.getAttribute('data-id');
					this.toggleTaskStatus(parseInt(id));
				});
			});
		},

		toggleTaskStatus: function (id) {
			const task = this.tasks.find(t => t.id == id);
			if (!task) return;

			const newStatus = task.status === 'done' ? 'todo' : 'done';

			const data = new URLSearchParams({ status: newStatus });
			fetch(`${this.apiBase}/tasks/${id}`, {
				method: 'PUT',
				headers: {
					'requesttoken': OC.requestToken,
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.loadTasks();
					this.showSuccess(newStatus === 'done' ? 'Görev tamamlandı!' : 'Görev yeniden açıldı');
				})
				.catch(e => this.showError('Güncelleme hatası'));
		},

		showTaskDetail: function (id) {
			const task = this.tasks.find(t => t.id == id);
			if (!task) return;

			this.currentTaskId = id;
			document.getElementById('tasks-list-view').style.display = 'none';
			document.getElementById('task-detail-view').style.display = 'block';

			const project = this.projects.find(p => p.id == task.projectId);
			const client = this.clients.find(c => c.id == task.clientId);
			const parent = this.tasks.find(t => t.id == task.parentId);
			const statusTexts = { todo: 'Yapılacak', in_progress: 'Devam Ediyor', done: 'Tamamlandı', cancelled: 'İptal Edildi' };
			const priorityTexts = { low: 'Düşük', medium: 'Orta', high: 'Yüksek' };
			const daysLeft = task.dueDate ? this.calculateDaysLeft(task.dueDate) : null;

			document.getElementById('task-detail-title').textContent = task.title;
			document.getElementById('task-detail-project').textContent = parent ? `Alt Görev (${parent.title})` : (project ? project.name : (client ? client.name : 'Genel'));

			// Status with badge
			const statusEl = document.getElementById('task-detail-status');
			statusEl.innerHTML = `<span class="status-badge task-status--${task.status}">${statusTexts[task.status] || task.status}</span>`;

			// Priority with badge
			const priorityEl = document.getElementById('task-detail-priority');
			priorityEl.innerHTML = `<span class="priority-badge priority-badge--${task.priority}">${priorityTexts[task.priority] || task.priority}</span>`;

			// Due date with days left
			const dueDateEl = document.getElementById('task-detail-due-date');
			if (task.dueDate) {
				const overdue = daysLeft !== null && daysLeft < 0 && task.status !== 'done' && task.status !== 'cancelled';
				const dueClass = overdue ? 'status-badge--overdue' : (daysLeft <= 3 ? 'status-badge--draft' : '');
				dueDateEl.innerHTML = `${task.dueDate} ${daysLeft !== null ? `<span class="status-badge ${dueClass}">${daysLeft < 0 ? 'Geçti!' : daysLeft + ' gün'}</span>` : ''}`;
			} else {
				dueDateEl.textContent = '-';
			}

			document.getElementById('task-detail-description').innerHTML = task.description ? task.description.replace(/\n/g, '<br>') : '-';
			document.getElementById('task-detail-notes').innerHTML = task.notes ? task.notes.replace(/\n/g, '<br>') : '<p class="text-muted">Not eklenmemiş.</p>';

			this.renderSubtasks(id);
		},

		renderSubtasks: function (parentId) {
			const container = document.getElementById('subtasks-list');
			if (!container) return;

			const subtasks = this.tasks.filter(t => t.parentId == parentId);
			if (subtasks.length === 0) {
				container.innerHTML = '<p class="text-muted">Alt görev bulunmuyor.</p>';
				return;
			}

			let html = '<div class="subtask-items">';
			subtasks.forEach(st => {
				html += `
					<div class="subtask-item ${st.status === 'done' ? 'done' : ''} ${st.status === 'cancelled' ? 'cancelled' : ''}">
						<input type="checkbox" onclick="DomainControl.toggleTaskStatus(${st.id})" ${st.status === 'done' ? 'checked' : ''} ${st.status === 'cancelled' ? 'disabled' : ''}>
						<span class="subtask-item__title" onclick="DomainControl.showTaskDetail(${st.id})">${this.escapeHtml(st.title)}</span>
						<span class="subtask-item__date">${st.dueDate || ''}</span>
					</div>
				`;
			});
			html += '</div>';
			container.innerHTML = html;
		},

		postponeTask: function (id) {
			const newDate = prompt('Yeni tarihi girin (YYYY-MM-DD):', date('Y-m-d'));
			if (!newDate) return;

			const task = this.tasks.find(t => t.id == id);
			if (!task) return;

			const data = new URLSearchParams({ dueDate: newDate });
			fetch(`${this.apiBase}/tasks/${id}`, {
				method: 'PUT',
				headers: { 'requesttoken': OC.requestToken, 'Content-Type': 'application/x-www-form-urlencoded' },
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					this.loadTasks();
					this.showTaskDetail(id);
					this.showSuccess('Zaman planı güncellendi');
				});
		},

		cancelTask: function (id) {
			if (!confirm('Bu görevi iptal etmek istediğinize emin misiniz?')) return;

			const data = new URLSearchParams({ status: 'cancelled' });
			fetch(`${this.apiBase}/tasks/${id}`, {
				method: 'PUT',
				headers: { 'requesttoken': OC.requestToken, 'Content-Type': 'application/x-www-form-urlencoded' },
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					this.loadTasks();
					this.showTaskDetail(id);
					this.showSuccess('Görev iptal edildi');
				});
		},

		hideTaskDetail: function () {
			document.getElementById('tasks-list-view').style.display = 'block';
			document.getElementById('task-detail-view').style.display = 'none';
			this.currentTaskId = null;
		},

		showTaskModal: function (id = null, projectId = null, parentId = null) {
			const modal = document.getElementById('task-modal');
			const form = document.getElementById('task-form');
			if (!modal || !form) return;

			form.reset();
			document.getElementById('task-id').value = '';
			this.updateClientSelect('task-client-id');
			this.updateProjectSelects();

			const clientSelect = document.getElementById('task-client-id');
			const projectSelect = document.getElementById('task-project-id');
			const parentSelect = document.getElementById('task-parent-id');

			if (clientSelect) clientSelect.disabled = false;
			if (projectSelect) projectSelect.disabled = false;

			// Populate Parent task select
			if (parentSelect) {
				parentSelect.innerHTML = '<option value="">Üst Görev Yok</option>';
				this.tasks.filter(t => !t.parentId && t.id != id).forEach(p => {
					parentSelect.innerHTML += `<option value="${p.id}">${this.escapeHtml(p.title)}</option>`;
				});
			}

			if (parentId) {
				const parentTask = this.tasks.find(t => t.id == parentId);
				if (parentTask) {
					if (parentSelect) parentSelect.value = parentId;
					if (projectSelect) {
						projectSelect.value = parentTask.projectId || '';
						projectSelect.disabled = true;
					}
					if (clientSelect) {
						clientSelect.value = parentTask.clientId || '';
						clientSelect.disabled = true;
					}
				}
			} else if (projectId) {
				if (projectSelect) projectSelect.value = projectId;
				const proj = this.projects.find(p => p.id == projectId);
				if (proj && clientSelect) {
					clientSelect.value = proj.clientId || '';
					clientSelect.disabled = true;
				}
			}

			if (id) {
				const task = this.tasks.find(t => t.id == id);
				if (task) {
					document.getElementById('task-id').value = task.id;
					if (projectSelect) projectSelect.value = task.projectId || '';
					if (clientSelect) clientSelect.value = task.clientId || '';
					if (parentSelect) parentSelect.value = task.parentId || '';
					document.getElementById('task-title').value = task.title || '';
					document.getElementById('task-description').value = task.description || '';
					document.getElementById('task-notes').value = task.notes || '';
					document.getElementById('task-status').value = task.status || 'todo';
					document.getElementById('task-priority').value = task.priority || 'medium';
					document.getElementById('task-due-date').value = task.dueDate || '';

					if (task.parentId) {
						if (projectSelect) projectSelect.disabled = true;
						if (clientSelect) clientSelect.disabled = true;
					}
				}
			}
			modal.style.display = 'block';
		},

		saveTask: function () {
			const id = document.getElementById('task-id').value;

			// Re-enable disabled fields for form submission if they are not picked up by URLSearchParams
			// Or just pull the values directly
			const data = new URLSearchParams({
				projectId: document.getElementById('task-project-id').value,
				clientId: document.getElementById('task-client-id').value,
				parentId: document.getElementById('task-parent-id').value,
				title: document.getElementById('task-title').value,
				description: document.getElementById('task-description').value,
				notes: document.getElementById('task-notes').value,
				status: document.getElementById('task-status').value,
				priority: document.getElementById('task-priority').value,
				dueDate: document.getElementById('task-due-date').value
			});

			const url = id ? `${this.apiBase}/tasks/${id}` : `${this.apiBase}/tasks`;
			fetch(url, {
				method: id ? 'PUT' : 'POST',
				headers: { 'requesttoken': OC.requestToken, 'Content-Type': 'application/x-www-form-urlencoded' },
				body: data.toString()
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.closeModal('task-modal');
					this.loadTasks().then(() => {
						if (this.currentTaskId) this.showTaskDetail(this.currentTaskId);
						if (this.currentProjectId) this.loadProjectTasks(this.currentProjectId);
					});
					this.showSuccess('Görev kaydedildi');
				})
				.catch(e => this.showError('Kaydetme hatası: ' + e.message));
		},

		deleteTask: function (id) {
			fetch(`${this.apiBase}/tasks/${id}`, {
				method: 'DELETE',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(() => {
					this.loadTasks();
					this.showSuccess('Görev silindi');
				})
				.catch(e => this.showError('Silme hatası'));
		},

		toggleTaskStatus: function (id) {
			fetch(`${this.apiBase}/tasks/${id}/toggle`, {
				method: 'POST',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.loadTasks();
					if (this.currentProjectId) this.loadProjectTasks(this.currentProjectId);
					if (this.currentTaskId == id) {
						const task = this.tasks.find(t => t.id == id);
						if (task) document.getElementById('task-detail-status').textContent = task.status;
					}
				})
				.catch(e => this.showError('Durum değiştirme hatası'));
		},

		// ===== TIME TRACKING =====
		timerInterval: null,
		currentRunningEntry: null,

		loadTimeTracking: function (projectId) {
			// Setup timer buttons only once
			if (!this.timerButtonsSetup) {
				this.setupTimerButtons(projectId);
				this.timerButtonsSetup = true;
			}

			// Load time entries
			fetch(`${this.apiBase}/projects/${projectId}/time-entries`, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(data => {
					if (data.error) throw new Error(data.error);
					this.renderTimeEntries(data.entries || []);
					this.updateTotalTime(data.totalDuration || 0);
				})
				.catch(e => {
					console.error('Time entries load error:', e);
					this.renderTimeEntries([]);
					this.updateTotalTime(0);
				});

			// Check for running timer
			fetch(`${this.apiBase}/projects/${projectId}/time-entries/running`, {
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(running => {
					if (running && running.id) {
						this.currentRunningEntry = running;
						this.startTimerDisplay(running);
					} else {
						this.stopTimerDisplay();
						this.currentRunningEntry = null;
					}
				})
				.catch(e => {
					console.error('Running timer check error:', e);
					this.stopTimerDisplay();
					this.currentRunningEntry = null;
				});
		},

		setupTimerButtons: function (projectId) {
			const startBtn = document.getElementById('timer-start-btn');
			const stopBtn = document.getElementById('timer-stop-btn');

			// Remove all existing listeners by cloning and replacing
			if (startBtn) {
				const newStartBtn = startBtn.cloneNode(true);
				startBtn.parentNode.replaceChild(newStartBtn, startBtn);
				newStartBtn.addEventListener('click', (e) => {
					e.preventDefault();
					e.stopPropagation();
					if (newStartBtn.disabled) return;
					newStartBtn.disabled = true;
					this.startTimer(projectId).finally(() => {
						newStartBtn.disabled = false;
					});
				});
			}

			if (stopBtn) {
				const newStopBtn = stopBtn.cloneNode(true);
				stopBtn.parentNode.replaceChild(newStopBtn, stopBtn);
				newStopBtn.addEventListener('click', (e) => {
					e.preventDefault();
					e.stopPropagation();
					if (newStopBtn.disabled) return;
					newStopBtn.disabled = true;
					this.stopTimer(projectId).finally(() => {
						newStopBtn.disabled = false;
					});
				});
			}
		},

		startTimer: function (projectId) {
			// Check if already running
			if (this.currentRunningEntry) {
				this.showError('Zaten çalışan bir zaman kaydı var');
				return Promise.resolve();
			}

			const description = document.getElementById('timer-description-input')?.value || '';

			const data = new URLSearchParams({
				description: description
			});

			return fetch(`${this.apiBase}/projects/${projectId}/time-entries/start`, {
				method: 'POST',
				headers: {
					'requesttoken': OC.requestToken,
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: data.toString()
			})
				.then(r => r.json())
				.then(entry => {
					if (entry.error) throw new Error(entry.error);
					this.currentRunningEntry = entry;
					this.startTimerDisplay(entry);
					this.showSuccess('Zaman takibi başlatıldı');
					// Clear description input
					const descInput = document.getElementById('timer-description-input');
					if (descInput) descInput.value = '';
					// Reload to get updated list
					this.loadTimeTracking(projectId);
				})
				.catch(e => {
					this.showError('Zaman başlatma hatası: ' + e.message);
					throw e;
				});
		},

		stopTimer: function (projectId) {
			if (!this.currentRunningEntry) {
				this.showError('Çalışan zaman kaydı bulunamadı');
				return Promise.resolve();
			}

			return fetch(`${this.apiBase}/projects/${projectId}/time-entries/stop`, {
				method: 'POST',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(entry => {
					if (entry.error) throw new Error(entry.error);
					this.stopTimerDisplay();
					this.currentRunningEntry = null;
					this.showSuccess('Zaman takibi durduruldu');
					// Reload time entries
					this.loadTimeTracking(projectId);
				})
				.catch(e => {
					this.showError('Zaman durdurma hatası: ' + e.message);
					throw e;
				});
		},

		startTimerDisplay: function (entry) {
			const startBtn = document.getElementById('timer-start-btn');
			const stopBtn = document.getElementById('timer-stop-btn');

			if (startBtn) {
				startBtn.style.display = 'none';
				startBtn.disabled = false;
			}
			if (stopBtn) {
				stopBtn.style.display = 'flex';
				stopBtn.disabled = false;
			}
			this.updateTimerStatus(true);

			// Start interval to update display
			if (this.timerInterval) clearInterval(this.timerInterval);

			// Parse start time as UTC to avoid timezone issues
			// PHP returns 'Y-m-d H:i:s' in UTC, so we need to treat it as UTC
			const startTimeStr = entry.startTime;
			// If it doesn't have timezone info, assume UTC
			const startTime = startTimeStr.includes('+') || startTimeStr.includes('Z') 
				? new Date(startTimeStr).getTime()
				: new Date(startTimeStr + ' UTC').getTime();
			
			this.timerInterval = setInterval(() => {
				const now = Date.now();
				const elapsed = Math.floor((now - startTime) / 1000);
				this.updateTimerDisplay(elapsed);
			}, 1000);

			// Initial update
			const elapsed = Math.floor((Date.now() - startTime) / 1000);
			this.updateTimerDisplay(elapsed);
		},

		stopTimerDisplay: function () {
			const startBtn = document.getElementById('timer-start-btn');
			const stopBtn = document.getElementById('timer-stop-btn');

			if (startBtn) {
				startBtn.style.display = 'flex';
				startBtn.disabled = false;
			}
			if (stopBtn) {
				stopBtn.style.display = 'none';
				stopBtn.disabled = false;
			}
			this.updateTimerStatus(false);

			if (this.timerInterval) {
				clearInterval(this.timerInterval);
				this.timerInterval = null;
			}

			this.updateTimerDisplay(0);
		},

		updateTimerDisplay: function (seconds) {
			const display = document.getElementById('timer-display');
			if (!display) return;

			const hours = Math.floor(seconds / 3600);
			const minutes = Math.floor((seconds % 3600) / 60);
			const secs = seconds % 60;

			display.textContent = 
				String(hours).padStart(2, '0') + ':' +
				String(minutes).padStart(2, '0') + ':' +
				String(secs).padStart(2, '0');
		},

		updateTimerStatus: function (isRunning) {
			const statusEl = document.getElementById('timer-status');
			if (statusEl) {
				statusEl.textContent = isRunning ? 'Çalışıyor...' : 'Durduruldu';
			}
		},

		renderTimeEntries: function (entries) {
			const container = document.getElementById('time-entries-container');
			const countEl = document.getElementById('entries-count');
			
			if (!container) return;

			// Update count
			if (countEl) {
				const count = entries ? entries.length : 0;
				countEl.textContent = count + ' kayıt';
			}

			if (!entries || entries.length === 0) {
				container.innerHTML = '<p class="empty-message-premium">Henüz zaman kaydı yok</p>';
				return;
			}

			let html = '';
			entries.forEach(entry => {
				const startTime = new Date(entry.startTime);
				const endTime = entry.endTime ? new Date(entry.endTime) : null;
				const duration = entry.duration || 0;
				const hours = Math.floor(duration / 3600);
				const minutes = Math.floor((duration % 3600) / 60);
				const secs = duration % 60;
				const durationStr = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

				const dateStr = startTime.toLocaleDateString('tr-TR', { 
					day: '2-digit', 
					month: '2-digit', 
					year: 'numeric' 
				});
				const timeStr = startTime.toLocaleTimeString('tr-TR', { 
					hour: '2-digit', 
					minute: '2-digit' 
				});
				const endTimeStr = endTime ? endTime.toLocaleTimeString('tr-TR', { 
					hour: '2-digit', 
					minute: '2-digit' 
				}) : '';

				html += `
					<div class="time-entry-item">
						<div class="time-entry-item-content">
							<div class="time-entry-description">
								${this.escapeHtml(entry.description || 'Açıklama yok')}
							</div>
							<div class="time-entry-date">
								${dateStr} ${timeStr}${endTimeStr ? ' - ' + endTimeStr : ''}
							</div>
						</div>
						<div class="time-entry-duration">
							${durationStr}
						</div>
						<button class="btn btn-sm btn-danger time-entry-delete delete-time-entry-btn" data-id="${entry.id}" title="Sil">
							🗑️
						</button>
					</div>
				`;
			});

			container.innerHTML = html;

			// Add delete button listeners
			container.querySelectorAll('.delete-time-entry-btn').forEach(btn => {
				btn.addEventListener('click', () => {
					const entryId = parseInt(btn.dataset.id);
					if (confirm('Bu zaman kaydını silmek istediğinize emin misiniz?')) {
						this.deleteTimeEntry(entryId);
					}
				});
			});
		},

		deleteTimeEntry: function (entryId) {
			fetch(`${this.apiBase}/time-entries/${entryId}`, {
				method: 'DELETE',
				headers: { 'requesttoken': OC.requestToken }
			})
				.then(r => r.json())
				.then(result => {
					if (result.error) throw new Error(result.error);
					this.showSuccess('Zaman kaydı silindi');
					if (this.currentProjectId) {
						this.loadTimeTracking(this.currentProjectId);
					}
				})
				.catch(e => this.showError('Silme hatası: ' + e.message));
		},

		updateTotalTime: function (totalSeconds) {
			const display = document.getElementById('total-time-display');
			if (!display) return;

			const hours = Math.floor(totalSeconds / 3600);
			const minutes = Math.floor((totalSeconds % 3600) / 60);
			const secs = totalSeconds % 60;

			display.textContent = 
				String(hours).padStart(2, '0') + ':' +
				String(minutes).padStart(2, '0') + ':' +
				String(secs).padStart(2, '0');
		},

		// ===== FILTER =====
		applyFilter: function (filter) {
			if (this.currentTab === 'invoices') this.renderInvoices(filter);
			else if (this.currentTab === 'projects') this.renderProjects(filter);
			else if (this.currentTab === 'tasks') this.renderTasks(filter);
		},

		// ===== HELPER =====
		updateClientSelect: function (selectId) {
			const select = document.getElementById(selectId);
			if (!select) return;
			const currentValue = select.value;
			select.innerHTML = '<option value="">Müşteri Seçin</option>';
			this.clients.forEach(c => {
				select.innerHTML += `<option value="${c.id}">${this.escapeHtml(c.name)}</option>`;
			});
			if (currentValue) select.value = currentValue;
		},

		showError: function (message) {
			OC.Notification.showTemporary(message, { type: 'error' });
		},

		showSuccess: function (message) {
			OC.Notification.showTemporary(message, { type: 'success' });
		},

		initRichTextEditor: function (editorId) {
			const editor = document.getElementById(editorId);
			if (!editor) return;

			const toolbar = editor.parentElement.querySelector('.rich-text-toolbar');
			if (!toolbar) return;

			// Remove existing listeners
			const buttons = toolbar.querySelectorAll('.toolbar-btn');
			buttons.forEach(btn => {
				const newBtn = btn.cloneNode(true);
				btn.parentNode.replaceChild(newBtn, btn);
			});

			// Add event listeners
			toolbar.querySelectorAll('.toolbar-btn').forEach(btn => {
				btn.addEventListener('click', (e) => {
					e.preventDefault();
					const command = btn.dataset.command;

					// Focus editor
					editor.focus();

					switch (command) {
						case 'bold':
							document.execCommand('bold', false, null);
							this.updateToolbarState(editor);
							break;
						case 'italic':
							document.execCommand('italic', false, null);
							this.updateToolbarState(editor);
							break;
						case 'underline':
							document.execCommand('underline', false, null);
							this.updateToolbarState(editor);
							break;
						case 'insertEmoji':
							this.insertEmoji(editor);
							break;
						case 'insertLineBreak':
							document.execCommand('insertHTML', false, '<br>');
							break;
					}
				});
			});

			// Update toolbar state on selection change
			editor.addEventListener('keyup', () => this.updateToolbarState(editor));
			editor.addEventListener('mouseup', () => this.updateToolbarState(editor));
		},

		updateToolbarState: function (editor) {
			const toolbar = editor.parentElement.querySelector('.rich-text-toolbar');
			if (!toolbar) return;

			// Check which commands are active
			const boldBtn = toolbar.querySelector('[data-command="bold"]');
			const italicBtn = toolbar.querySelector('[data-command="italic"]');
			const underlineBtn = toolbar.querySelector('[data-command="underline"]');

			if (boldBtn) {
				boldBtn.classList.toggle('active', document.queryCommandState('bold'));
			}
			if (italicBtn) {
				italicBtn.classList.toggle('active', document.queryCommandState('italic'));
			}
			if (underlineBtn) {
				underlineBtn.classList.toggle('active', document.queryCommandState('underline'));
			}
		},

		insertEmoji: function (editor) {
			const emojis = [
				'😊', '😀', '😁', '😂', '🤣', '😃', '😄', '😅', '😆', '😉',
				'👍', '👎', '❤️', '💛', '💚', '💙', '💜', '🧡', '🖤', '🤍',
				'✅', '❌', '⚠️', '💡', '📝', '🔗', '📧', '📱', '💻', '🌐',
				'⚡', '🎉', '🎊', '🔥', '⭐', '🌟', '💯', '🔔', '📢', '📣',
				'🎯', '🏆', '💰', '💎', '🚀', '🎨', '🎭', '🎪', '🎬', '🎮'
			];

			// Create emoji picker
			const picker = document.createElement('div');
			picker.className = 'emoji-picker';
			picker.style.cssText = `
				position: absolute;
				background: var(--color-main-background);
				border: 1px solid var(--color-border);
				border-radius: 8px;
				padding: 8px;
				display: grid;
				grid-template-columns: repeat(8, 1fr);
				gap: 4px;
				z-index: 10000;
				box-shadow: 0 4px 12px rgba(0,0,0,0.15);
				max-width: 300px;
			`;

			emojis.forEach(emoji => {
				const btn = document.createElement('button');
				btn.textContent = emoji;
				btn.style.cssText = `
					background: transparent;
					border: none;
					padding: 6px;
					cursor: pointer;
					font-size: 18px;
					border-radius: 4px;
					transition: background 0.2s;
				`;
				btn.onmouseover = () => btn.style.background = 'var(--color-background-hover)';
				btn.onmouseout = () => btn.style.background = 'transparent';
				btn.onclick = () => {
					editor.focus();
					document.execCommand('insertText', false, emoji);
					picker.remove();
				};
				picker.appendChild(btn);
			});

			// Position picker near editor
			const rect = editor.getBoundingClientRect();
			picker.style.top = (rect.bottom + 5) + 'px';
			picker.style.left = rect.left + 'px';

			// Remove existing picker
			document.querySelectorAll('.emoji-picker').forEach(p => p.remove());

			// Add to body
			document.body.appendChild(picker);

			// Close on outside click
			setTimeout(() => {
				document.addEventListener('click', function closePicker(e) {
					if (!picker.contains(e.target) && e.target !== editor) {
						picker.remove();
						document.removeEventListener('click', closePicker);
					}
				});
			}, 100);
		},


		testEmailReminders: function () {
			const days = prompt('Kaç gün içinde süresi dolacak domainler için e-posta gönderilsin?', '30');
			if (!days || isNaN(days)) {
				return;
			}

			const btn = document.getElementById('test-email-btn');
			if (btn) {
				btn.disabled = true;
				btn.textContent = '📧 Gönderiliyor...';
			}

			fetch(this.apiBase + '/domains/send-reminders?days=' + parseInt(days), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'requesttoken': OC.requestToken
				}
			})
				.then(response => response.json())
				.then(data => {
					if (data.error) {
						alert('Hata: ' + data.error);
						console.error('E-posta gönderme hatası:', data.error);
						if (data.trace) {
							console.error('Hata detayı:', data.trace);
						}
					} else {
						let message = 'E-posta testi tamamlandı!\n\n' +
							'Gönderilen e-posta sayısı: ' + data.sent_count + '\n';

						if (data.stats) {
							message += 'İncelenen toplam domain: ' + data.stats.total_found + '\n';
							if (data.stats.no_client > 0) message += '⚠️ Müşterisi olmayan: ' + data.stats.no_client + '\n';
							if (data.stats.no_email > 0) message += '⚠️ E-postası olmayan müşteri: ' + data.stats.no_email + '\n';
							if (data.stats.errors > 0) message += '❌ Hatalı: ' + data.stats.errors + '\n';
						}

						if (data.emails && data.emails.length > 0) {
							message += '\nE-posta gönderilen adresler:\n' + data.emails.join('\n');
						} else if (data.stats && data.stats.total_found > 0) {
							message += '\nDetaylar:\n' + (data.stats.details ? data.stats.details.join('\n') : 'Detay yok');
						} else {
							message += '\n' + days + ' gün içinde süresi dolacak domain bulunamadı.';
						}

						alert(message);
						console.log('E-posta test sonucu:', data);
					}
				})
				.catch(error => {
					console.error('E-posta test hatası:', error);
					alert('E-posta gönderme sırasında bir hata oluştu. Konsolu kontrol edin.');
				})
				.finally(() => {
					if (btn) {
						btn.disabled = false;
						btn.innerHTML = '📧 E-posta Test Et';
					}
				});
		}
	};

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => DomainControl.init());
	} else {
		DomainControl.init();
	}

	// Make DomainControl available globally
	window.DomainControl = DomainControl;
})();