

# Additional Reports for TYPO3

[![Latest stable version](https://img.shields.io/packagist/v/apen/additional_reports?label=stable)](https://packagist.org/packages/apen/additional_reports)
[![Total downloads](https://img.shields.io/packagist/dt/apen/additional_reports)](https://packagist.org/packages/apen/additional_reports)
[![TYPO3 13](https://img.shields.io/badge/TYPO3-13.4-f49700.svg)](https://get.typo3.org/version/13)
[![TYPO3 14](https://img.shields.io/badge/TYPO3-14-f49700.svg)](https://get.typo3.org/version/14)
[![CI](https://github.com/Apen/additional_reports/actions/workflows/ci.yaml/badge.svg)](https://github.com/Apen/additional_reports/actions/workflows/ci.yaml)

Additional Reports extends TYPO3's Reports backend module with practical
diagnostics for maintenance, audits and migrations.

## Requirements

| Component | Supported versions |
| --- | --- |
| TYPO3 | 13.4 and 14.x |
| PHP | 8.2, 8.3, 8.4 and 8.5 |
| Installation | Composer mode and classic mode |

## Reports

| Report | Purpose |
| --- | --- |
| Commands | Lists registered Symfony console commands and legacy command controllers. |
| eID | Lists registered eID entry points. |
| Event dispatcher | Lists PSR-14 events and their listeners. |
| Extensions | Shows installed extensions, database tables and fields, available updates and file comparisons. |
| Hooks | Lists registered legacy hooks and their implementations. |
| Log errors | Groups backend log errors by occurrence and last seen date. |
| Middlewares | Lists frontend and backend PSR-15 middleware stacks. |
| Plugins and content types | Locates used and cached plugins/content types, with filters and backend links. |
| System status | Summarizes TYPO3, environment, PHP, database and scheduler information. |
| Website configuration | Summarizes sites, domains, templates and page statistics. |
| XCLASS | Lists registered XCLASS overrides. |

The reports are intended as diagnostic aids. They complement TYPO3's native
system reports and do not replace a security or compatibility audit.

## Installation

Install the extension with Composer:

```bash
composer require apen/additional_reports
```

It is also available from the
[TYPO3 Extension Repository](https://extensions.typo3.org/extension/additional_reports/)
and the [GitHub releases](https://github.com/Apen/additional_reports/releases).

After installation, clear the TYPO3 caches if necessary, then open
**System > Reports** in the backend and select an Additional Reports entry.

## Screenshots

### Commands and eID

![Registered commands](Resources/Public/Images/commands.png)

![Registered eID entry points](Resources/Public/Images/eid.png)

### Plugins and content types

![Plugin usage](Resources/Public/Images/plugins.png)

![Content type usage](Resources/Public/Images/ctypes.png)

![Plugin and content type summary](Resources/Public/Images/summary.png)

### System diagnostics

![System status](Resources/Public/Images/status1.png)

![Detailed system status](Resources/Public/Images/status2.png)

![Grouped log errors](Resources/Public/Images/logs.png)

![Website configuration](Resources/Public/Images/websites.png)

### Extensions and TYPO3 registrations

![Installed extensions](Resources/Public/Images/extensions.png)

![Unified extension diff](Resources/Public/Images/extensions-diff.png)

![Registered hooks](Resources/Public/Images/hooks.png)

![Registered XCLASS overrides](Resources/Public/Images/xclass.png)

### PSR-14 and PSR-15

![PSR-14 event listeners](Resources/Public/Images/eventdispatcher.png)

![PSR-15 middlewares](Resources/Public/Images/middlewares.png)

## Development and quality

Install the development dependencies and run the complete local pipeline:

```bash
composer install
composer quality
```

Generate an HTML coverage report in `.Build/public/coverage/html` with:

```bash
composer coverage
```

Rector and Fractor suggestions are deliberately kept outside the blocking
quality pipeline so that migrations can be reviewed before applying them:

```bash
composer quality:migrations
```

Network-dependent TYPO3 API and TER tests are disabled by default. Enable
them explicitly with `RUN_NETWORK_TESTS=1`.

## License

This extension is licensed under the
[GNU General Public License v2.0 or later](LICENSE.txt).
