# Getting Started

How to install, configure, and verify the Nadi Drupal module.

## Requirements

- PHP ^8.1
- Drupal ^10.1 or ^11
- Composer

## Installation

```bash
composer require nadi-pro/nadi-drupal
drush en nadi
```

The module creates a log directory at `private://nadi` during installation if the log driver is configured.

## Initial Setup

After enabling the module, navigate to
**Administration > Configuration > System > Nadi Monitoring** (`/admin/config/system/nadi`).

### Minimum Configuration

1. Ensure **Enable Nadi monitoring** is checked
2. Select a **Transport driver**
3. Configure the driver-specific settings (see below)

### Driver Quick Setup

**Log driver** (default, no external dependencies):

- Set the log directory path (default: `private://nadi`)
- Ensure the directory is writable

**HTTP driver** (sends to Nadi API):

- Enter your **API key** (Sanctum token from [nadi.pro](https://nadi.pro))
- Enter your **Application key**
- Set the **API endpoint** (default: `https://nadi.pro/api`)

**OpenTelemetry driver** (exports as OTel spans):

- Set the **OTel collector endpoint** (default: `http://localhost:4318`)
- Configure service name, version, and environment

## Verify Installation

### Admin UI

Use the **Test Connection** and **Verify Configuration** buttons on the settings form.

### Drush Commands

```bash
drush nadi:verify    # Check configuration is valid
drush nadi:test      # Test the monitoring connection
drush nadi:install   # Install shipper binary and show setup instructions
```

## Permissions

The `administer nadi` permission controls access to the settings form. Grant it to roles that
need to manage monitoring configuration.

## Status Report

The module registers requirements checks visible at **Administration > Reports > Status report**:

- **Nadi Monitoring**: Shows enabled/disabled status and active driver
- **Nadi Log Path**: Shows log directory path and writability (log driver only)
- **Nadi HTTP Configuration**: Warns if API/App keys are missing (HTTP driver only)

## Next Steps

- [Testing](02-testing.md) for running and writing tests
- [Configuration Reference](../03-configuration/README.md) for all options
- [Architecture Overview](../01-architecture/01-overview.md) for how it works
