# Telemetron Changelog

All notable changes to this project will be documented in this file.

## 3.1.0 - 2023-07-20

### Removed

- All calls to Airtable have been removed. This plugin no longer interacts with Airtable.
- Base ID, Table Name, and Sync Enabled settings have all been removed.
- Daily Sync removed as it's no longer applicable.
- `controllers/SyncController`

### Added

- `RemoteUpdates` model to calculate the number of plugins that are expired, abandoned, or have update breakpoints.
- `DataController` with `/data` endpoint to provide telemetry packet in JSON format. This is how the plugin will connect to the Craft Remote service.

### Changed

- `models/Plugin` was changed to provide more specific information about a given plugin.
- `models/Packet` was rewritten to provide a larger amount of information in a more structured manner.

## 3.0.1 - 2022-11-8

### Fixed
- Settings were not properly interpreting environment variables in older Craft versions.
- SMTP settings were getting duplicated instead of writing to the existing row.

## 3.0.0 - 2022-11-14
### Added
- Initial Craft 3.0 release
