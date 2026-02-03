# Sampling

Sampling strategies control what percentage of monitoring events are recorded. This reduces
overhead in high-traffic environments.

## Strategies

| Strategy | Class | Description |
| -------- | ----- | ----------- |
| `fixed_rate` | `FixedRateSampling` | Records a fixed percentage of events (default) |
| `dynamic_rate` | `DynamicRateSampling` | Adjusts rate based on system load |
| `interval` | `IntervalSampling` | Records events at fixed time intervals |
| `peak_load` | `PeakLoadSampling` | Reduces sampling during peak load |

## Configuration Parameters

| Parameter | Default | Description |
| --------- | ------- | ----------- |
| `sampling.strategy` | `fixed_rate` | Active strategy name |
| `sampling.config.sampling_rate` | `0.1` | Percentage of events to record (0.0 to 1.0) |
| `sampling.config.base_rate` | `0.05` | Base rate for dynamic strategy |
| `sampling.config.load_factor` | `1.0` | Multiplier for dynamic rate |
| `sampling.config.interval_seconds` | `60` | Interval for interval-based sampling |

## Strategy Details

### Fixed Rate

Records a fixed percentage of events. Set `sampling_rate` to `1.0` to record everything, or `0.1` to record 10%.

### Dynamic Rate

Starts at `base_rate` and adjusts based on `load_factor`. Useful when traffic varies significantly throughout the day.

### Interval

Records one event per `interval_seconds`. Events arriving between intervals are dropped.

### Peak Load

Reduces the sampling rate when system load is high, preventing monitoring from adding overhead during peak periods.

## Admin UI

Sampling settings are available under the **Sampling** section of the settings form at `/admin/config/system/nadi`.

## Next Steps

- [Drivers](01-drivers.md) for transport configuration
- [Reference](03-reference.md) for the complete config schema
