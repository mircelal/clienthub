# ClientHub - Changelog

## Version 3.7.940 (Current)

### 🎯 New Features

#### 📊 Project Management Improvements
- **Project Status Management**: Added project status change feature
  - Active, On Hold, Completed, Cancelled statuses
  - Quick status change from project detail page
- **Project Financial Widgets**:
  - **Net Profit**: Total Budget - Total Expenses
  - **Remaining Receivable**: Total Invoiced - Total Paid
  - Color-coded display (for positive/negative values)

#### 💰 Invoice and Payment Integration
- **Automatic Income Recording**: Invoice payments are automatically recorded in income/expense section
- **Project Connection**: Project information is automatically added to invoice payments
- **Invoice Link**: Direct link to invoice in income/expense details
- **Project Expense Link**: Direct link to project in expense details

#### 💵 Currency Management
- **Default Currency**: The default currency selected in settings is used throughout the system
- **Currency Symbols**: Currency symbols are displayed instead of names (₺, $, €, etc.)
- **Invoice Modals**: Currency selection inputs removed, automatic default currency usage
- **Dashboard Currency**: Dashboard displays the default currency selected in settings

#### 🎨 User Interface Improvements
- **Settings Navigation**: Single-click access to settings
- **Dashboard Quick Actions**: All quick action buttons are now functional
- **Recent Clients**: Clicking on recent clients in dashboard opens client profile directly
- **Invoice Navigation**: Clicking on invoice in income/expense section opens invoice details directly

#### 📝 Task Management Improvements
- **In-Project Task Addition**: When adding tasks from within a project, project and client selection is automatic
- **Smart Form**: Unnecessary inputs are hidden when adding tasks in project context

#### 📅 Debts Section
- **Date Fields Fix**: Debt issue date, last payment date fields fixed
- **Empty Date Management**: Empty date fields are processed correctly

#### 🎨 Icon System
- **Material Icon Support**: Missing icons added
  - `arrow-up`, `arrow-down` (for income/expense)
  - `arrow-up-circle`, `arrow-down-circle` (for debt/credit)
  - `minus` (for expense operations)
  - `history` (for payment history)
  - `information-outline` (for info messages)

### 🔧 Technical Improvements

#### Backend
- **PaymentController**: Automatic transaction creation for invoice payments
- **Transaction-Invoice Connection**: Invoice ID storage in transactions
- **Project-Transaction Connection**: Automatic project information addition in invoice payments

#### Frontend
- **Vue.js Component Communication**: Improved navigation between components
- **Window.DomainControl API**: Extended global navigation API
  - `selectClient(clientId)`: Select client
  - `selectProject(projectId)`: Select project
  - `selectInvoice(invoiceId)`: Select invoice
- **Settings Event System**: Automatic refresh when settings are updated

### 🐛 Bug Fixes

- Fixed dashboard expenses showing as 0
- Fixed dashboard quick action buttons not working
- Fixed double menu opening when clicking Settings
- Fixed redirect to dashboard after saving settings
- Fixed invoice list opening when clicking invoice link
- Fixed client list opening when clicking recent clients
- Fixed date fields not displaying in debts section
- Fixed missing icons

### 📋 Module List

ClientHub includes the following modules:

1. **Dashboard** - General statistics and summary information
2. **Clients** - Client information and contact details
3. **Domains** - Domain registration tracking and renewal reminders
4. **Hosting** - Hosting account management and server details
5. **Websites** - Website records and software tracking
6. **Services** - Service type definitions and client-based service records
7. **Invoices** - Invoice creation, editing, and tracking
8. **Projects** - Project management, status tracking, and financial information
9. **Tasks** - Task creation, status tracking, and priority management
10. **Income/Expense** - Income and expense transactions, category management
11. **Debts/Receivables** - Debt and receivable tracking, payment plans
12. **Reports** - Business reports and analytics
13. **Settings** - System settings, currency management, module activation

### 🔄 Integrations

- **Nextcloud Contacts**: Client information integrated with Nextcloud Contacts
- **Nextcloud Files**: File management integrated with Nextcloud Files
- **Vue.js 3**: Modern Vue.js 3 framework usage
- **Nextcloud Vue Components**: Nextcloud's official Vue components

### 📦 Dependencies

- Nextcloud 25 or higher
- PHP 8.0 or higher
- Vue.js 3
- Nextcloud Vue Components

---

## Installation

Since ClientHub is not available in the Nextcloud App Store, you need to install it manually from GitHub:

### Prerequisites
- Nextcloud 25 or higher
- PHP 8.0 or higher
- Administrator access to your Nextcloud instance

### Installation Steps

1. **Clone or download the repository**
   ```bash
   git clone https://github.com/mircelal/clienthub.git
   ```
   
   Or download the ZIP file from GitHub and extract it.

2. **Copy to Nextcloud apps directory**
   ```bash
   # Navigate to your Nextcloud installation directory
   cd /path/to/nextcloud
   
   # Copy the domaincontrol folder to the apps directory
   cp -r /path/to/clienthub/domaincontrol apps/
   ```
   
   **Note**: The folder name must be `domaincontrol` (not `clienthub`).

3. **Set proper permissions**
   ```bash
   # Make sure the web server user owns the app directory
   chown -R www-data:www-data apps/domaincontrol
   chmod -R 755 apps/domaincontrol
   ```

4. **Enable the app**
   
   **Option A: Via Nextcloud Admin Interface**
   - Log in as an administrator
   - Go to **Settings** → **Administration** → **Apps**
   - Find "ClientHub" in the **Not enabled** section
   - Click **Enable**

   **Option B: Via Command Line**
   ```bash
   cd /path/to/nextcloud
   php occ app:enable domaincontrol
   ```

5. **Access the application**
   - After enabling, "ClientHub" will appear in your Nextcloud navigation menu
   - Click on it to start using the application

### Troubleshooting

- **App not appearing**: Make sure the folder is named `domaincontrol` and is in the `apps/` directory
- **Permission errors**: Check that the web server user has read/write access to the app directory
- **Dependencies missing**: Run `composer install` in the app directory if needed
- **Build required**: If you're using the development version, run `npm install && npm run build` in the app directory

---

## Upcoming Features

- [ ] Extended multi-language support
- [ ] Advanced reporting and charts
- [ ] Email notifications
- [ ] Mobile app support
- [ ] API documentation
- [ ] Bulk operations
- [ ] Advanced search and filtering

---

## Notes

- All currency operations use the default currency selected in settings
- Invoice payments are automatically recorded in the income/expense section
- Project expenses are automatically associated with the project owner's client
- All statistics on the dashboard are updated in real-time

