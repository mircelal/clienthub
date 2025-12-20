/**
 * ClientHub - Reports Module
 * Handles all reporting and analytics functionality
 * Uses Chart.js for visualizations
 */
(function() {
	'use strict';

	const Reports = {
		charts: {},
		currentPeriod: 'month',
		startDate: null,
		endDate: null,

		init: function() {
			console.log('Reports module initialized');
			this.setupFilters();
		},

		setupFilters: function() {
			const periodSelect = document.getElementById('report-period');
			const customDateGroup = document.getElementById('custom-date-group');
			const customDateGroupEnd = document.getElementById('custom-date-group-end');
			const applyBtn = document.getElementById('apply-filter-btn');

			if (periodSelect) {
				periodSelect.addEventListener('change', (e) => {
					if (e.target.value === 'custom') {
						customDateGroup.style.display = 'block';
						customDateGroupEnd.style.display = 'block';
					} else {
						customDateGroup.style.display = 'none';
						customDateGroupEnd.style.display = 'none';
						this.currentPeriod = e.target.value;
					}
				});
			}

			if (applyBtn) {
				applyBtn.addEventListener('click', () => {
					if (this.currentPeriod === 'custom') {
						const startDate = document.getElementById('report-start-date').value;
						const endDate = document.getElementById('report-end-date').value;
						if (startDate && endDate) {
							this.startDate = new Date(startDate);
							this.endDate = new Date(endDate);
						}
					}
					this.loadReports();
				});
			}
		},

		loadReports: function() {
			console.log('Loading reports data...');
			
			// Get data from DomainControl global object
			const DomainControl = window.DomainControl;
			if (!DomainControl) {
				console.error('DomainControl not found');
				return;
			}

			const clients = DomainControl.clients || [];
			const payments = DomainControl.payments || [];
			const invoices = DomainControl.invoices || [];
			const projects = DomainControl.projects || [];
			const services = DomainControl.services || [];
			const serviceTypes = DomainControl.serviceTypes || [];
			const domains = DomainControl.domains || [];
			const hostings = DomainControl.hostings || [];
			const websites = DomainControl.websites || [];
			const transactions = DomainControl.transactions || [];
			const debts = DomainControl.debts || [];

			// Calculate filtered data based on period
			const filteredPayments = this.filterByPeriod(payments, 'paymentDate');
			const filteredInvoices = this.filterByPeriod(invoices, 'invoiceDate');

			// Calculate total income
			const totalIncome = payments.reduce((sum, p) => {
				return sum + (parseFloat(p.amount) || 0);
			}, 0);
			
			// Calculate monthly income
			const now = new Date();
			const currentMonth = now.getMonth();
			const currentYear = now.getFullYear();
			const monthlyIncome = payments.reduce((sum, p) => {
				if (!p.paymentDate) return sum;
				const paymentDate = new Date(p.paymentDate);
				if (paymentDate.getMonth() === currentMonth && paymentDate.getFullYear() === currentYear) {
					return sum + (parseFloat(p.amount) || 0);
				}
				return sum;
			}, 0);
			
			// Calculate pending income
			const pendingIncome = invoices.reduce((sum, i) => {
				if (['draft', 'sent', 'overdue'].includes(i.status)) {
					const total = parseFloat(i.totalAmount) || 0;
					const paid = parseFloat(i.paidAmount) || 0;
					return sum + (total - paid);
				}
				return sum;
			}, 0);
			
			// Update report stats
			this.updateElement('report-total-income', this.formatCurrency(totalIncome) + ' ₼');
			this.updateElement('report-monthly-income', this.formatCurrency(monthlyIncome) + ' ₼');
			this.updateElement('report-pending-income', this.formatCurrency(pendingIncome) + ' ₼');
			
			// Invoice stats
			const totalInvoices = invoices.length;
			const paidInvoices = invoices.filter(i => i.status === 'paid').length;
			const overdueInvoices = invoices.filter(i => i.status === 'overdue').length;
			const pendingInvoices = invoices.filter(i => ['draft', 'sent'].includes(i.status)).length;
			
			this.updateElement('report-total-invoices', totalInvoices);
			this.updateElement('report-paid-invoices', paidInvoices);
			this.updateElement('report-overdue-invoices', overdueInvoices);
			this.updateElement('report-pending-invoices', pendingInvoices);
			
			// Client stats
			const totalClients = clients.length;
			const activeClients = clients.filter(c => {
				const hasActiveItems = domains.some(d => d.clientId === c.id) ||
									   hostings.some(h => h.clientId === c.id) ||
									   websites.some(w => w.clientId === c.id) ||
									   services.some(s => s.clientId === c.id && s.status === 'active');
				return hasActiveItems;
			}).length;
			
			this.updateElement('report-total-clients', totalClients);
			this.updateElement('report-active-clients', activeClients);
			this.updateElement('report-active-clients-text', activeClients + ' aktif');

			// Average client income
			const avgClientIncome = totalClients > 0 ? totalIncome / totalClients : 0;
			this.updateElement('report-avg-client-income', this.formatCurrency(avgClientIncome) + ' ₼');
			
			// Top clients by income
			this.renderTopClients(clients, payments);
			
			// Project stats
			const activeProjects = projects.filter(p => p.status === 'active').length;
			const completedProjects = projects.filter(p => p.status === 'completed').length;
			const onHoldProjects = projects.filter(p => p.status === 'on_hold').length;
			
			this.updateElement('report-active-projects', activeProjects);
			this.updateElement('report-completed-projects', completedProjects);
			this.updateElement('report-onhold-projects', onHoldProjects);

			// Service stats
			const activeServices = services.filter(s => s.status === 'active').length;
			this.updateElement('report-active-services', activeServices);
			
			// Service expiration stats
			const nowDate = new Date();
			const thirtyDaysLater = new Date(nowDate.getTime() + 30 * 24 * 60 * 60 * 1000);
			const expiringSoon = services.filter(s => {
				if (!s.expirationDate || s.renewalInterval === 'one-time') return false;
				const expDate = new Date(s.expirationDate);
				return expDate >= nowDate && expDate <= thirtyDaysLater;
			}).length;
			
			const expiredServices = services.filter(s => {
				if (!s.expirationDate || s.renewalInterval === 'one-time') return false;
				const expDate = new Date(s.expirationDate);
				return expDate < nowDate;
			}).length;
			
			this.updateElement('report-expiring-soon', expiringSoon);
			this.updateElement('report-expired-services', expiredServices);
			
			// Render expiring services list
			this.renderExpiringServices(services, clients);

			// Transaction stats
			const transactionIncome = transactions.filter(t => t.type === 'income').reduce((sum, t) => sum + (parseFloat(t.amount) || 0), 0);
			const transactionExpense = transactions.filter(t => t.type === 'expense').reduce((sum, t) => sum + (parseFloat(t.amount) || 0), 0);
			const netTransaction = transactionIncome - transactionExpense;
			
			this.updateElement('report-total-transaction-income', this.formatCurrency(transactionIncome) + ' ₼');
			this.updateElement('report-total-transaction-expense', this.formatCurrency(transactionExpense) + ' ₼');
			this.updateElement('report-net-transaction', this.formatCurrency(netTransaction) + ' ₼');
			
			// Debt stats
			const totalDebts = debts.filter(d => d.type === 'debt' && d.status === 'active').reduce((sum, d) => {
				const remaining = parseFloat(d.totalAmount || 0) - parseFloat(d.paidAmount || 0);
				return sum + remaining;
			}, 0);
			const totalCredits = debts.filter(d => d.type === 'credit' && d.status === 'active').reduce((sum, d) => {
				const remaining = parseFloat(d.totalAmount || 0) - parseFloat(d.paidAmount || 0);
				return sum + remaining;
			}, 0);
			
			const today = new Date();
			today.setHours(0, 0, 0, 0);
			const thirtyDaysFromToday = new Date(today);
			thirtyDaysFromToday.setDate(thirtyDaysFromToday.getDate() + 30);
			
			const upcomingDebtPayments = debts.filter(d => {
				if (d.status !== 'active' || !d.nextPaymentDate) return false;
				const nextDate = new Date(d.nextPaymentDate);
				return nextDate >= today && nextDate <= thirtyDaysFromToday;
			}).length;
			
			const overdueDebts = debts.filter(d => {
				if (d.status !== 'active') return false;
				if (d.nextPaymentDate) {
					const nextDate = new Date(d.nextPaymentDate);
					return nextDate < today;
				}
				if (d.dueDate) {
					const dueDate = new Date(d.dueDate);
					return dueDate < today;
				}
				return false;
			}).length;
			
			this.updateElement('report-total-debts', this.formatCurrency(totalDebts) + ' ₼');
			this.updateElement('report-total-credits', this.formatCurrency(totalCredits) + ' ₼');
			this.updateElement('report-upcoming-debt-payments', upcomingDebtPayments);
			this.updateElement('report-overdue-debts', overdueDebts);

			// Render charts
			this.renderIncomeTrendChart(payments);
			this.renderInvoiceStatusChart(invoices);
			this.renderTopClientsChart(clients, payments);
			this.renderProjectStatusChart(projects);
			this.renderServiceTypeIncomeChart(services, serviceTypes, payments);
			this.renderPaymentTrendChart(payments);
			this.renderIncomeExpenseChart(transactions);
			this.renderExpenseCategoryChart(transactions);
			this.renderCashFlowChart(transactions);
			this.renderDebtStatusChart(debts);
		},

		filterByPeriod: function(data, dateField) {
			if (!this.startDate || !this.endDate) {
				return data;
			}
			return data.filter(item => {
				if (!item[dateField]) return false;
				const itemDate = new Date(item[dateField]);
				return itemDate >= this.startDate && itemDate <= this.endDate;
			});
		},

		renderIncomeTrendChart: function(payments) {
			const ctx = document.getElementById('income-trend-chart');
			if (!ctx) return;

			// Destroy existing chart
			if (this.charts.incomeTrend) {
				this.charts.incomeTrend.destroy();
			}

			// Group payments by month
			const monthlyData = {};
			payments.forEach(p => {
				if (!p.paymentDate) return;
				const date = new Date(p.paymentDate);
				const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
				if (!monthlyData[monthKey]) {
					monthlyData[monthKey] = 0;
				}
				monthlyData[monthKey] += parseFloat(p.amount) || 0;
			});

			// Sort by date and get last 12 months
			const sortedMonths = Object.keys(monthlyData).sort().slice(-12);
			const labels = sortedMonths.map(m => {
				const [year, month] = m.split('-');
				return `${month}/${year}`;
			});
			const data = sortedMonths.map(m => monthlyData[m]);

			this.charts.incomeTrend = new Chart(ctx, {
				type: 'line',
				data: {
					labels: labels,
					datasets: [{
						label: 'Aylık Gelir (₼)',
						data: data,
						borderColor: 'rgb(75, 192, 192)',
						backgroundColor: 'rgba(75, 192, 192, 0.1)',
						tension: 0.4,
						fill: true
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: true,
							position: 'top'
						}
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								callback: function(value) {
									return value.toLocaleString('az-AZ') + ' ₼';
								}
							}
						}
					}
				}
			});
		},

		renderInvoiceStatusChart: function(invoices) {
			const ctx = document.getElementById('invoice-status-chart');
			if (!ctx) return;

			if (this.charts.invoiceStatus) {
				this.charts.invoiceStatus.destroy();
			}

			const paid = invoices.filter(i => i.status === 'paid').length;
			const overdue = invoices.filter(i => i.status === 'overdue').length;
			const pending = invoices.filter(i => ['draft', 'sent'].includes(i.status)).length;

			this.charts.invoiceStatus = new Chart(ctx, {
				type: 'doughnut',
				data: {
					labels: ['Ödenen', 'Gecikmiş', 'Bekleyen'],
					datasets: [{
						data: [paid, overdue, pending],
						backgroundColor: [
							'rgba(75, 192, 192, 0.8)',
							'rgba(255, 99, 132, 0.8)',
							'rgba(255, 206, 86, 0.8)'
						]
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'bottom'
						}
					}
				}
			});
		},

		renderTopClientsChart: function(clients, payments) {
			const ctx = document.getElementById('top-clients-chart');
			if (!ctx) return;

			if (this.charts.topClients) {
				this.charts.topClients.destroy();
			}

			// Calculate income per client
			const clientIncome = {};
			payments.forEach(payment => {
				if (!clientIncome[payment.clientId]) {
					clientIncome[payment.clientId] = 0;
				}
				clientIncome[payment.clientId] += parseFloat(payment.amount) || 0;
			});

			// Get top 5 clients
			const topClients = Object.entries(clientIncome)
				.map(([clientId, income]) => ({
					client: clients.find(c => c.id == clientId),
					income: income
				}))
				.filter(item => item.client)
				.sort((a, b) => b.income - a.income)
				.slice(0, 5);

			if (topClients.length === 0) return;

			const labels = topClients.map(item => item.client.name);
			const data = topClients.map(item => item.income);

			this.charts.topClients = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: labels,
					datasets: [{
						label: 'Gelir (₼)',
						data: data,
						backgroundColor: 'rgba(54, 162, 235, 0.8)'
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: false
						}
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								callback: function(value) {
									return value.toLocaleString('az-AZ') + ' ₼';
								}
							}
						}
					}
				}
			});
		},

		renderProjectStatusChart: function(projects) {
			const ctx = document.getElementById('project-status-chart');
			if (!ctx) return;

			if (this.charts.projectStatus) {
				this.charts.projectStatus.destroy();
			}

			const active = projects.filter(p => p.status === 'active').length;
			const completed = projects.filter(p => p.status === 'completed').length;
			const onHold = projects.filter(p => p.status === 'on_hold').length;

			this.charts.projectStatus = new Chart(ctx, {
				type: 'pie',
				data: {
					labels: ['Aktif', 'Tamamlanan', 'Beklemede'],
					datasets: [{
						data: [active, completed, onHold],
						backgroundColor: [
							'rgba(153, 102, 255, 0.8)',
							'rgba(75, 192, 192, 0.8)',
							'rgba(255, 206, 86, 0.8)'
						]
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'bottom'
						}
					}
				}
			});
		},

		renderServiceTypeIncomeChart: function(services, serviceTypes, payments) {
			const ctx = document.getElementById('service-type-income-chart');
			if (!ctx) return;

			if (this.charts.serviceTypeIncome) {
				this.charts.serviceTypeIncome.destroy();
			}

			// Calculate service count by type
			const typeCount = {};
			services.forEach(service => {
				if (!service.serviceTypeId) return;
				const type = serviceTypes.find(t => t.id === service.serviceTypeId);
				if (!type) return;
				
				if (!typeCount[type.name]) {
					typeCount[type.name] = 0;
				}
				typeCount[type.name]++;
			});

			const labels = Object.keys(typeCount);
			const data = Object.values(typeCount);

			if (labels.length === 0) {
				ctx.parentElement.innerHTML = '<p class="empty-message">Hizmet türü verisi yok</p>';
				return;
			}

			this.charts.serviceTypeIncome = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: labels,
					datasets: [{
						label: 'Hizmet Sayısı',
						data: data,
						backgroundColor: 'rgba(255, 159, 64, 0.8)'
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: false
						}
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								stepSize: 1
							}
						}
					}
				}
			});
		},

		renderPaymentTrendChart: function(payments) {
			const ctx = document.getElementById('payment-trend-chart');
			if (!ctx) return;

			if (this.charts.paymentTrend) {
				this.charts.paymentTrend.destroy();
			}

			// Group payments by month
			const monthlyData = {};
			payments.forEach(p => {
				if (!p.paymentDate) return;
				const date = new Date(p.paymentDate);
				const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
				if (!monthlyData[monthKey]) {
					monthlyData[monthKey] = 0;
				}
				monthlyData[monthKey] += parseFloat(p.amount) || 0;
			});

			const sortedMonths = Object.keys(monthlyData).sort().slice(-12);
			const labels = sortedMonths.map(m => {
				const [year, month] = m.split('-');
				return `${month}/${year}`;
			});
			const data = sortedMonths.map(m => monthlyData[m]);

			this.charts.paymentTrend = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: labels,
					datasets: [{
						label: 'Aylık Ödemeler (₼)',
						data: data,
						backgroundColor: 'rgba(54, 162, 235, 0.8)'
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: true,
							position: 'top'
						}
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								callback: function(value) {
									return value.toLocaleString('az-AZ') + ' ₼';
								}
							}
						}
					}
				}
			});
		},

		updateElement: function(id, value) {
			const element = document.getElementById(id);
			if (element) {
				element.textContent = value;
			}
		},

		renderTopClients: function(clients, payments) {
			const topClientsList = document.getElementById('top-clients-list');
			if (!topClientsList) return;
			
			// Calculate income per client
			const clientIncome = {};
			payments.forEach(payment => {
				if (!clientIncome[payment.clientId]) {
					clientIncome[payment.clientId] = 0;
				}
				clientIncome[payment.clientId] += parseFloat(payment.amount) || 0;
			});
			
			// Sort clients by income
			const sortedClients = Object.entries(clientIncome)
				.map(([clientId, income]) => ({
					client: clients.find(c => c.id == clientId),
					income: income
				}))
				.filter(item => item.client)
				.sort((a, b) => b.income - a.income)
				.slice(0, 10);
			
			if (sortedClients.length === 0) {
				topClientsList.innerHTML = '<p class="empty-message">Henüz ödeme kaydı yok</p>';
				return;
			}
			
			topClientsList.innerHTML = sortedClients.map((item, index) => {
				return `
					<div class="report-item">
						<div class="report-item-rank">${index + 1}</div>
						<div class="report-item-info">
							<h4>${this.escapeHtml(item.client.name)}</h4>
							<p class="report-item-meta">${item.client.email || 'E-posta yok'}</p>
						</div>
						<div class="report-item-value">
							<strong>${this.formatCurrency(item.income)} ₼</strong>
						</div>
					</div>
				`;
			}).join('');
		},

		renderExpiringServices: function(services, clients) {
			const expiringList = document.getElementById('expiring-services-list');
			if (!expiringList) return;
			
			const nowDate = new Date();
			const thirtyDaysLater = new Date(nowDate.getTime() + 30 * 24 * 60 * 60 * 1000);
			
			const expiringServices = services
				.filter(s => {
					if (!s.expirationDate || s.renewalInterval === 'one-time') return false;
					const expDate = new Date(s.expirationDate);
					return expDate >= nowDate && expDate <= thirtyDaysLater;
				})
				.sort((a, b) => new Date(a.expirationDate) - new Date(b.expirationDate))
				.slice(0, 10);
			
			if (expiringServices.length === 0) {
				expiringList.innerHTML = '<p class="empty-message">Yakında bitecek hizmet yok</p>';
				return;
			}
			
			expiringList.innerHTML = expiringServices.map(service => {
				const client = clients.find(c => c.id === service.clientId);
				const daysLeft = Math.ceil((new Date(service.expirationDate) - nowDate) / (1000 * 60 * 60 * 24));
				const urgencyClass = daysLeft <= 7 ? 'urgent' : daysLeft <= 15 ? 'warning' : '';
				return `
					<div class="report-item ${urgencyClass}">
						<div class="report-item-icon">🛠️</div>
						<div class="report-item-info">
							<h4>${this.escapeHtml(service.name)}</h4>
							<p class="report-item-meta">${client ? this.escapeHtml(client.name) : 'Bilinmeyen Müşteri'}</p>
						</div>
						<div class="report-item-value">
							<strong>${daysLeft} gün</strong>
							<small>${this.formatDate(service.expirationDate)}</small>
						</div>
					</div>
				`;
			}).join('');
		},

		formatCurrency: function(amount) {
			if (!amount) return '0.00';
			return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
		},

		formatDate: function(dateString) {
			if (!dateString) return '';
			const date = new Date(dateString);
			return date.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
		},

		escapeHtml: function(text) {
			if (!text) return '';
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		},

		renderIncomeExpenseChart: function(transactions) {
			const ctx = document.getElementById('income-expense-chart');
			if (!ctx) return;

			if (this.charts.incomeExpense) {
				this.charts.incomeExpense.destroy();
			}

			const monthlyData = {};
			transactions.forEach(t => {
				if (!t.transactionDate) return;
				const date = new Date(t.transactionDate);
				const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
				if (!monthlyData[monthKey]) {
					monthlyData[monthKey] = { income: 0, expense: 0 };
				}
				if (t.type === 'income') {
					monthlyData[monthKey].income += parseFloat(t.amount) || 0;
				} else {
					monthlyData[monthKey].expense += parseFloat(t.amount) || 0;
				}
			});

			const sortedMonths = Object.keys(monthlyData).sort().slice(-12);
			const labels = sortedMonths.map(m => {
				const [year, month] = m.split('-');
				return `${month}/${year}`;
			});

			this.charts.incomeExpense = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: labels,
					datasets: [{
						label: 'Gelir (₼)',
						data: sortedMonths.map(m => monthlyData[m].income),
						backgroundColor: 'rgba(16, 185, 129, 0.6)',
						borderColor: 'rgb(16, 185, 129)',
						borderWidth: 1
					}, {
						label: 'Gider (₼)',
						data: sortedMonths.map(m => monthlyData[m].expense),
						backgroundColor: 'rgba(239, 68, 68, 0.6)',
						borderColor: 'rgb(239, 68, 68)',
						borderWidth: 1
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: true, position: 'top' }
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								callback: function(value) {
									return value.toLocaleString('az-AZ') + ' ₼';
								}
							}
						}
					}
				}
			});
		},

		renderExpenseCategoryChart: function(transactions) {
			const ctx = document.getElementById('expense-category-chart');
			if (!ctx) return;

			if (this.charts.expenseCategory) {
				this.charts.expenseCategory.destroy();
			}

			const categoryData = {};
			transactions.filter(t => t.type === 'expense').forEach(t => {
				const categoryId = t.categoryId || 'other';
				if (!categoryData[categoryId]) {
					categoryData[categoryId] = 0;
				}
				categoryData[categoryId] += parseFloat(t.amount) || 0;
			});

			const DomainControl = window.DomainControl;
			const categories = DomainControl?.transactionCategories || [];
			const labels = Object.keys(categoryData).map(id => {
				const cat = categories.find(c => c.id == id);
				return cat ? cat.name : 'Diğer';
			});
			const data = Object.values(categoryData);

			this.charts.expenseCategory = new Chart(ctx, {
				type: 'doughnut',
				data: {
					labels: labels,
					datasets: [{
						data: data,
						backgroundColor: [
							'rgba(239, 68, 68, 0.6)',
							'rgba(245, 158, 11, 0.6)',
							'rgba(59, 130, 246, 0.6)',
							'rgba(139, 92, 246, 0.6)',
							'rgba(236, 72, 153, 0.6)',
							'rgba(6, 182, 212, 0.6)',
							'rgba(34, 197, 94, 0.6)',
							'rgba(251, 146, 60, 0.6)'
						]
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: true, position: 'right' }
					}
				}
			});
		},

		renderCashFlowChart: function(transactions) {
			const ctx = document.getElementById('cash-flow-chart');
			if (!ctx) return;

			if (this.charts.cashFlow) {
				this.charts.cashFlow.destroy();
			}

			const monthlyData = {};
			transactions.forEach(t => {
				if (!t.transactionDate) return;
				const date = new Date(t.transactionDate);
				const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
				if (!monthlyData[monthKey]) {
					monthlyData[monthKey] = 0;
				}
				if (t.type === 'income') {
					monthlyData[monthKey] += parseFloat(t.amount) || 0;
				} else {
					monthlyData[monthKey] -= parseFloat(t.amount) || 0;
				}
			});

			const sortedMonths = Object.keys(monthlyData).sort().slice(-12);
			const labels = sortedMonths.map(m => {
				const [year, month] = m.split('-');
				return `${month}/${year}`;
			});
			const data = sortedMonths.map(m => monthlyData[m]);

			this.charts.cashFlow = new Chart(ctx, {
				type: 'line',
				data: {
					labels: labels,
					datasets: [{
						label: 'Nakit Akışı (₼)',
						data: data,
						borderColor: 'rgb(59, 130, 246)',
						backgroundColor: 'rgba(59, 130, 246, 0.1)',
						tension: 0.4,
						fill: true
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: true, position: 'top' }
					},
					scales: {
						y: {
							ticks: {
								callback: function(value) {
									return value.toLocaleString('az-AZ') + ' ₼';
								}
							}
						}
					}
				}
			});
		},

		renderDebtStatusChart: function(debts) {
			const ctx = document.getElementById('debt-status-chart');
			if (!ctx) return;

			if (this.charts.debtStatus) {
				this.charts.debtStatus.destroy();
			}

			const activeDebts = debts.filter(d => d.type === 'debt' && d.status === 'active').length;
			const activeCredits = debts.filter(d => d.type === 'credit' && d.status === 'active').length;
			const paidDebts = debts.filter(d => d.status === 'paid').length;
			const overdueDebts = debts.filter(d => d.status === 'overdue').length;

			this.charts.debtStatus = new Chart(ctx, {
				type: 'doughnut',
				data: {
					labels: ['Aktif Borçlar', 'Aktif Alacaklar', 'Ödenen', 'Gecikmiş'],
					datasets: [{
						data: [activeDebts, activeCredits, paidDebts, overdueDebts],
						backgroundColor: [
							'rgba(239, 68, 68, 0.6)',
							'rgba(16, 185, 129, 0.6)',
							'rgba(59, 130, 246, 0.6)',
							'rgba(245, 158, 11, 0.6)'
						]
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: true, position: 'right' }
					}
				}
			});
		}
	};

	// Make Reports available globally
	window.Reports = Reports;

	// Auto-initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => Reports.init());
	} else {
		Reports.init();
	}
})();
