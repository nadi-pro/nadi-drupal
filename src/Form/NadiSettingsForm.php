<?php

namespace Nadi\Drupal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class NadiSettingsForm extends ConfigFormBase
{
    protected function getEditableConfigNames(): array
    {
        return ['nadi.settings'];
    }

    public function getFormId(): string
    {
        return 'nadi_settings_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state): array
    {
        $config = $this->config('nadi.settings');

        $form['enabled'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Enable Nadi monitoring'),
            '#default_value' => $config->get('enabled'),
            '#description' => $this->t('Master toggle for all Nadi monitoring.'),
        ];

        $form['driver'] = [
            '#type' => 'select',
            '#title' => $this->t('Transport driver'),
            '#options' => [
                'log' => $this->t('Log (file-based)'),
                'http' => $this->t('HTTP (direct API)'),
                'opentelemetry' => $this->t('OpenTelemetry'),
            ],
            '#default_value' => $config->get('driver'),
            '#description' => $this->t('How monitoring data is sent.'),
        ];

        // Log driver settings.
        $form['connections_log'] = [
            '#type' => 'details',
            '#title' => $this->t('Log driver settings'),
            '#open' => $config->get('driver') === 'log',
            '#states' => [
                'visible' => [
                    ':input[name="driver"]' => ['value' => 'log'],
                ],
            ],
        ];

        $form['connections_log']['log_path'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Log directory path'),
            '#default_value' => $config->get('connections.log.path'),
            '#description' => $this->t('Directory where log files are stored. Use private:// for private files directory.'),
        ];

        // HTTP driver settings.
        $form['connections_http'] = [
            '#type' => 'details',
            '#title' => $this->t('HTTP driver settings'),
            '#open' => $config->get('driver') === 'http',
            '#states' => [
                'visible' => [
                    ':input[name="driver"]' => ['value' => 'http'],
                ],
            ],
        ];

        $form['connections_http']['http_api_key'] = [
            '#type' => 'textfield',
            '#title' => $this->t('API key'),
            '#default_value' => $config->get('connections.http.api_key'),
            '#description' => $this->t('Sanctum token for API authentication.'),
        ];

        $form['connections_http']['http_app_key'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Application key'),
            '#default_value' => $config->get('connections.http.app_key'),
            '#description' => $this->t('Application identifier.'),
        ];

        $form['connections_http']['http_endpoint'] = [
            '#type' => 'url',
            '#title' => $this->t('API endpoint'),
            '#default_value' => $config->get('connections.http.endpoint'),
            '#description' => $this->t('Nadi API endpoint URL.'),
        ];

        // OpenTelemetry driver settings.
        $form['connections_otel'] = [
            '#type' => 'details',
            '#title' => $this->t('OpenTelemetry driver settings'),
            '#open' => $config->get('driver') === 'opentelemetry',
            '#states' => [
                'visible' => [
                    ':input[name="driver"]' => ['value' => 'opentelemetry'],
                ],
            ],
        ];

        $form['connections_otel']['otel_endpoint'] = [
            '#type' => 'url',
            '#title' => $this->t('OTel collector endpoint'),
            '#default_value' => $config->get('connections.opentelemetry.endpoint'),
        ];

        $form['connections_otel']['otel_service_name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Service name'),
            '#default_value' => $config->get('connections.opentelemetry.service_name'),
        ];

        $form['connections_otel']['otel_service_version'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Service version'),
            '#default_value' => $config->get('connections.opentelemetry.service_version'),
        ];

        $form['connections_otel']['otel_deployment_environment'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Deployment environment'),
            '#default_value' => $config->get('connections.opentelemetry.deployment_environment'),
        ];

        $form['connections_otel']['otel_suppress_errors'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Suppress OTel errors'),
            '#default_value' => $config->get('connections.opentelemetry.suppress_errors'),
        ];

        // Query settings.
        $form['query'] = [
            '#type' => 'details',
            '#title' => $this->t('Query monitoring'),
            '#open' => true,
        ];

        $form['query']['query_slow_threshold'] = [
            '#type' => 'number',
            '#title' => $this->t('Slow query threshold (ms)'),
            '#default_value' => $config->get('query.slow_threshold'),
            '#min' => 0,
            '#description' => $this->t('Queries slower than this threshold (in milliseconds) will be recorded.'),
        ];

        // HTTP monitoring settings.
        $form['http_settings'] = [
            '#type' => 'details',
            '#title' => $this->t('HTTP monitoring'),
            '#open' => false,
        ];

        $form['http_settings']['http_hidden_request_headers'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Hidden request headers'),
            '#default_value' => implode("\n", $config->get('http.hidden_request_headers') ?? []),
            '#description' => $this->t('One header per line. These header values will be masked in recorded data.'),
        ];

        $form['http_settings']['http_hidden_parameters'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Hidden parameters'),
            '#default_value' => implode("\n", $config->get('http.hidden_parameters') ?? []),
            '#description' => $this->t('One parameter per line. These parameter values will be masked in recorded data.'),
        ];

        $form['http_settings']['http_ignored_status_codes'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Ignored status codes'),
            '#default_value' => implode("\n", $config->get('http.ignored_status_codes') ?? []),
            '#description' => $this->t('One code or range per line (e.g., 200-307). Requests with these status codes will not be recorded.'),
        ];

        // Sampling settings.
        $form['sampling_settings'] = [
            '#type' => 'details',
            '#title' => $this->t('Sampling'),
            '#open' => false,
        ];

        $form['sampling_settings']['sampling_strategy'] = [
            '#type' => 'select',
            '#title' => $this->t('Sampling strategy'),
            '#options' => [
                'fixed_rate' => $this->t('Fixed rate'),
                'dynamic_rate' => $this->t('Dynamic rate'),
                'interval' => $this->t('Interval'),
                'peak_load' => $this->t('Peak load'),
            ],
            '#default_value' => $config->get('sampling.strategy'),
        ];

        $form['sampling_settings']['sampling_rate'] = [
            '#type' => 'number',
            '#title' => $this->t('Sampling rate'),
            '#default_value' => $config->get('sampling.config.sampling_rate'),
            '#min' => 0,
            '#max' => 1,
            '#step' => 0.01,
            '#description' => $this->t('Value between 0 and 1. 1.0 = record everything, 0.1 = record 10%.'),
        ];

        $form['sampling_settings']['base_rate'] = [
            '#type' => 'number',
            '#title' => $this->t('Base rate'),
            '#default_value' => $config->get('sampling.config.base_rate'),
            '#min' => 0,
            '#max' => 1,
            '#step' => 0.01,
            '#description' => $this->t('Base sampling rate for dynamic strategy.'),
        ];

        $form['sampling_settings']['load_factor'] = [
            '#type' => 'number',
            '#title' => $this->t('Load factor'),
            '#default_value' => $config->get('sampling.config.load_factor'),
            '#min' => 0,
            '#step' => 0.1,
            '#description' => $this->t('Multiplier for dynamic rate based on system load.'),
        ];

        $form['sampling_settings']['interval_seconds'] = [
            '#type' => 'number',
            '#title' => $this->t('Interval (seconds)'),
            '#default_value' => $config->get('sampling.config.interval_seconds'),
            '#min' => 1,
            '#description' => $this->t('Interval in seconds for interval-based sampling.'),
        ];

        // Test and Verify buttons.
        $form['actions']['test_connection'] = [
            '#type' => 'submit',
            '#value' => $this->t('Test Connection'),
            '#submit' => ['::testConnection'],
            '#weight' => 10,
        ];

        $form['actions']['verify_config'] = [
            '#type' => 'submit',
            '#value' => $this->t('Verify Configuration'),
            '#submit' => ['::verifyConfiguration'],
            '#weight' => 11,
        ];

        return parent::buildForm($form, $form_state);
    }

    public function submitForm(array &$form, FormStateInterface $form_state): void
    {
        $config = $this->config('nadi.settings');

        $config
            ->set('enabled', (bool) $form_state->getValue('enabled'))
            ->set('driver', $form_state->getValue('driver'))
            ->set('connections.log.path', $form_state->getValue('log_path'))
            ->set('connections.http.api_key', $form_state->getValue('http_api_key'))
            ->set('connections.http.app_key', $form_state->getValue('http_app_key'))
            ->set('connections.http.endpoint', $form_state->getValue('http_endpoint'))
            ->set('connections.opentelemetry.endpoint', $form_state->getValue('otel_endpoint'))
            ->set('connections.opentelemetry.service_name', $form_state->getValue('otel_service_name'))
            ->set('connections.opentelemetry.service_version', $form_state->getValue('otel_service_version'))
            ->set('connections.opentelemetry.deployment_environment', $form_state->getValue('otel_deployment_environment'))
            ->set('connections.opentelemetry.suppress_errors', (bool) $form_state->getValue('otel_suppress_errors'))
            ->set('query.slow_threshold', (int) $form_state->getValue('query_slow_threshold'))
            ->set('http.hidden_request_headers', array_filter(array_map('trim', explode("\n", $form_state->getValue('http_hidden_request_headers')))))
            ->set('http.hidden_parameters', array_filter(array_map('trim', explode("\n", $form_state->getValue('http_hidden_parameters')))))
            ->set('http.ignored_status_codes', array_filter(array_map('trim', explode("\n", $form_state->getValue('http_ignored_status_codes')))))
            ->set('sampling.strategy', $form_state->getValue('sampling_strategy'))
            ->set('sampling.config.sampling_rate', (float) $form_state->getValue('sampling_rate'))
            ->set('sampling.config.base_rate', (float) $form_state->getValue('base_rate'))
            ->set('sampling.config.load_factor', (float) $form_state->getValue('load_factor'))
            ->set('sampling.config.interval_seconds', (float) $form_state->getValue('interval_seconds'))
            ->save();

        parent::submitForm($form, $form_state);
    }

    public function testConnection(array &$form, FormStateInterface $form_state): void
    {
        try {
            /** @var \Nadi\Drupal\Nadi $nadi */
            $nadi = \Drupal::service('nadi');
            $result = $nadi->test();

            if ($result) {
                $this->messenger()->addStatus($this->t('Successfully connected to Nadi!'));
            } else {
                $this->messenger()->addError($this->t('Connection test failed. Please check your configuration.'));
            }
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('Connection test failed: @message', ['@message' => $e->getMessage()]));
        }
    }

    public function verifyConfiguration(array &$form, FormStateInterface $form_state): void
    {
        try {
            /** @var \Nadi\Drupal\Nadi $nadi */
            $nadi = \Drupal::service('nadi');
            $result = $nadi->verify();

            if ($result) {
                $this->messenger()->addStatus($this->t('Nadi configuration is valid!'));
            } else {
                $this->messenger()->addError($this->t('Configuration verification failed.'));
            }
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('Verification failed: @message', ['@message' => $e->getMessage()]));
        }
    }
}
