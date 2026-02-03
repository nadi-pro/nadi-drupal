# Drush Commands

The module provides four Drush commands for managing Nadi monitoring from the command line.
Commands are defined in `src/Commands/NadiCommands.php` and registered via `drush.services.yml`.

## Commands

### nadi:install

Install the shipper binary and display setup instructions.

```bash
drush nadi:install
```

**Alias:** `nadi-install`

This command:

1. Downloads and installs the shipper binary to `vendor/bin`
2. Displays configuration instructions for the admin UI
3. Shows where to get API credentials

### nadi:test

Test the monitoring connection using the currently configured driver.

```bash
drush nadi:test
```

**Alias:** `nadi-test`

Returns success or failure based on whether the transporter can reach its destination.

### nadi:verify

Verify that the current configuration is valid and complete.

```bash
drush nadi:verify
```

**Alias:** `nadi-verify`

Checks:

- Whether monitoring is enabled (warns if disabled)
- Active driver name
- Driver-specific requirements (API keys for HTTP, endpoint for OTel)
- Transporter verification

### nadi:update-shipper

Update the shipper binary to the latest version.

```bash
drush nadi:update-shipper
```

**Alias:** `nadi-update-shipper`

Checks if an update is available and downloads the new version if needed.

## Next Steps

- [Admin Form](02-admin-form.md) for the web UI
- [Services](03-services.md) for programmatic access
