# ClientHub for Nextcloud

Comprehensive client, project, and business management system. Manage all your business processes in one place.

## Features

### 📊 Dashboard
- General statistics and summary information
- Upcoming expiration date tracking
- Payment status overview
- Active project and task tracking

### 👥 Client Management
- Client information and contact details
- Notes and special information
- View all records specific to a client

### 🌐 Domain Management
- Domain registration tracking
- Expiration date tracking
- Renewal reminders
- Client-based domain listing

### 🖥️ Hosting Management
- Hosting account management
- Server details and information
- Renewal date tracking
- Hosting-based website association

### 🌍 Website Management
- Website records
- Installed software tracking
- Website configurations
- Hosting and client association

### 🛠️ Service Management
- Service type definitions (Domain, Hosting, SSL, etc.)
- Client-specific service records
- Service renewal date tracking
- Upcoming expiration date alerts
- Automatic service extension

### 📄 Invoice Management
- Create and edit invoices
- Invoice item management
- Unpaid invoice tracking
- Overdue invoice alerts
- Upcoming payment date tracking
- Client-based invoice listing

### 💰 Payment Tracking
- Payment records
- Invoice-payment association
- Monthly total revenue tracking
- Client-based payment history

### 📁 Project Management
- Create and track projects
- Project item management
- Project status tracking (Active, Completed, On Hold)
- Upcoming deadline alerts
- Client-based project listing

### ✅ Task Management
- Create and track tasks
- Task status (Pending, In Progress, Completed)
- Task priority levels
- Overdue task alerts
- Upcoming deadline tracking
- Project and client-based task filtering

## Requirements

- Nextcloud 25 or higher
- PHP 8.0 or higher

## Installation

1. Clone or download this repository
2. Copy the `domaincontrol` folder to your Nextcloud `apps/` directory
3. Enable the app via command line:
   ```bash
   cd /path/to/nextcloud
   php occ app:enable domaincontrol
   ```
4. The app will appear in your Nextcloud navigation menu

## Usage

1. Click on "ClientHub" in the Nextcloud navigation menu
2. View the general status from the Dashboard
3. Add new records from the relevant tab (Clients, Domains, Hosting, etc.)
4. You can enter detailed information for each record and track them

## Contributing

Contributions are welcome! Feel free to:
- Report bugs
- Suggest new features
- Submit pull requests
- Improve documentation

## License

AGPL-3.0

## Author

ClientHub Team
