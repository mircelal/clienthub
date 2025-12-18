# Domain Control for Nextcloud

Personal domain, hosting, and client management system for Nextcloud.

## Features

- **Client Management**: Store client information, contact details, and notes
- **Domain Tracking**: Track domain registrations, expiration dates, and renewals
- **Hosting Management**: Manage hosting accounts, server details, and renewal schedules
- **Website/Software Tracking**: Track installed software and their configurations

## Requirements

- Nextcloud 25 or higher
- PHP 8.0 or higher

## Installation

### From Source

1. Clone or download this repository
2. Copy the `domaincontrol` folder to your Nextcloud `apps/` directory
3. Enable the app via command line:
   ```bash
   cd /path/to/nextcloud
   php occ app:enable domaincontrol
   ```
4. The app will appear in your Nextcloud navigation menu

## Usage

1. Click on "Domain Control" in the Nextcloud navigation menu
2. Add your clients using the "Add Client" button
3. For each client, you can add:
   - Domains with expiration tracking
   - Hosting accounts with renewal dates
   - Websites and installed software

## Development

### Structure

```
domaincontrol/
├── appinfo/
│   ├── info.xml          # App metadata
│   └── routes.php        # API routes
├── lib/
│   ├── AppInfo/
│   │   └── Application.php
│   ├── Controller/       # API controllers
│   ├── Db/              # Database entities and mappers
│   └── Migration/       # Database migrations
├── templates/           # Frontend templates
├── js/                 # JavaScript files
├── css/                # Stylesheets
└── img/                # Images and icons
```

### API Endpoints

#### Clients
- `GET /api/clients` - List all clients
- `GET /api/clients/{id}` - Get client details
- `POST /api/clients` - Create new client
- `PUT /api/clients/{id}` - Update client
- `DELETE /api/clients/{id}` - Delete client

#### Domains
- `GET /api/domains` - List all domains
- `GET /api/domains/{id}` - Get domain details
- `GET /api/clients/{clientId}/domains` - Get domains for a client
- `POST /api/domains` - Create new domain
- `PUT /api/domains/{id}` - Update domain
- `DELETE /api/domains/{id}` - Delete domain

#### Hosting
- `GET /api/hostings` - List all hosting accounts
- `GET /api/hostings/{id}` - Get hosting details
- `GET /api/clients/{clientId}/hostings` - Get hosting for a client
- `POST /api/hostings` - Create new hosting
- `PUT /api/hostings/{id}` - Update hosting
- `DELETE /api/hostings/{id}` - Delete hosting

#### Websites
- `GET /api/websites` - List all websites
- `GET /api/websites/{id}` - Get website details
- `GET /api/clients/{clientId}/websites` - Get websites for a client
- `POST /api/websites` - Create new website
- `PUT /api/websites/{id}` - Update website
- `DELETE /api/websites/{id}` - Delete website

## License

AGPL-3.0

## Author

Domain Control Team
