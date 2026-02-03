# Admin Form

The settings form at `/admin/config/system/nadi` provides a web interface for all Nadi configuration. Defined in `src/Form/NadiSettingsForm.php`.

## Access

- **Route:** `/admin/config/system/nadi`
- **Menu:** Administration > Configuration > System > Nadi Monitoring
- **Permission:** `administer nadi`

## Form Sections

### General

- **Enable Nadi monitoring** - Master toggle checkbox
- **Transport driver** - Select: Log, HTTP, or OpenTelemetry

### Log Driver Settings

Visible when the Log driver is selected (uses Drupal `#states` for conditional display):

- **Log directory path** - Text field for the log directory

### HTTP Driver Settings

Visible when the HTTP driver is selected:

- **API key** - Sanctum token
- **Application key** - Application identifier
- **API endpoint** - URL field

### OpenTelemetry Driver Settings

Visible when the OpenTelemetry driver is selected:

- **OTel collector endpoint** - URL field
- **Service name** - Text field
- **Service version** - Text field
- **Deployment environment** - Text field
- **Suppress OTel errors** - Checkbox

### Query Monitoring

- **Slow query threshold (ms)** - Number field (minimum: 0)

### HTTP Monitoring

- **Hidden request headers** - Textarea, one header per line
- **Hidden parameters** - Textarea, one parameter per line
- **Ignored status codes** - Textarea, one code or range per line (e.g., `200-307`)

### Sampling

- **Sampling strategy** - Select: Fixed rate, Dynamic rate, Interval, Peak load
- **Sampling rate** - Number (0 to 1, step 0.01)
- **Base rate** - Number (0 to 1, step 0.01)
- **Load factor** - Number (minimum: 0, step 0.1)
- **Interval (seconds)** - Number (minimum: 1)

## Action Buttons

In addition to the standard **Save configuration** button, the form includes:

- **Test Connection** - Tests the transporter connection with the current saved config
- **Verify Configuration** - Validates the configuration is complete and correct

Both buttons provide immediate feedback via Drupal's messenger service.

## Next Steps

- [Drush Commands](01-drush-commands.md) for CLI management
- [Services](03-services.md) for programmatic access
