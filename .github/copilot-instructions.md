# Copilot instructions — Kite (quick reference)

Purpose: help an AI coding agent become productive in this repository by pointing to the
important architecture pieces, developer workflows, project conventions, and integration
hotspots with concrete examples.

**Quick Start**
- **Install dependencies:** run `composer install` in repo root. See `composer.json`.
- **Run CLI:** `php bin/kite --help` (or make `bin/kite` executable and run `bin/kite`). See `bin/kite` and `README.md`.
- **PHAR:** releases provide `kite.phar` (see README). Use `mv kite.phar /usr/local/bin/kite` and `chmod a+x`.
- **Tests:** a `phpunit.xml` exists; run `php vendor/bin/phpunit` from repo root.

**Big picture (important files & flow)**
- Core bootstrap: `app/boot.php` — defines `BASE_PATH`, loads Composer autoloader and sets path aliases.
- Application façade: `app/Kite.php` — global registry: `Kite::box()`, `Kite::config()`, `Kite::cliApp()` and helpers.
- CLI entry: `bin/kite` -> creates `Inhere\\Kite\\Console\\CliApplication` (see `app/Console/CliApplication.php`).
- CLI wiring: `CliApplication::init()` loads env/config, registers services (requires `app/Console/services.php`) and `loadRoutes()` (requires `app/Console/routes.php`). See those files for how commands are registered.
- Web mode: `Kite::webApp()` and `Inhere\\Kite\\Http\\WebApplication` are the web entry points (search for `webui` in `config/config.php`).

**Plugin & script system (how to add/inspect plugins)**
- Plugin manager: `app/Console/Plugin/PluginManager.php`. Plugins are plain PHP files loaded from configured plugin dirs.
- Default plugin dirs are declared in `config/config.php` (`pluginManager.pluginDirs`) — common locations: `plugin/` and `custom/plugin`.
- Plugin shape: a plugin file defines a class extending `AbstractPlugin` (see `app/Console/Plugin/AbstractPlugin.php`). The manager maps file path -> plugin name; partial and multi-word name matching is supported.
- Example: `plugin/demo-plugin.php` declares `DemoPlugin` and demonstrates `exec()` usage.

**Code & configuration conventions**
- PSR-4: namespace `Inhere\\Kite\\` maps to `app/`. Additional global helpers are in `app/func.php` (autoloaded via `files` in `composer.json`).
- Service registration: services are registered into an `ObjectBox` via `Kite::box()` and then accessed with `Kite::get()` / `Kite::cliApp()`; see `app/Kite.php` and `app/Console/CliApplication.php` (registerServices).
- Config: runtime config is loaded into a `ConfigBox` and accessible via `Kite::config()`; default config files are in `config/` (e.g. `config.php`, `config.cli.php`).

**Developer workflows & debugging**
- Install: `composer install` then `chmod a+x bin/kite` (Unix). On Windows use `bin/kite.bat`.
- Run a command: `php bin/kite git status` or `php bin/kite list` to view available commands.
- Generate completions / helpers: see `resource/templates/completion` and the README section "generate auto completion".
- Logs: logger is configured in `config/config.php` (log path via `OS::userCacheDir('kite.log')`); runtime logs appear under `tmp/` (e.g. `tmp/kite-*.log`).

**What to look at when changing behavior**
- To add CLI commands: update `app/Console/routes.php` or add controllers under `app/Console/Controller` (routes file wires controller classes).
- To add services: update `app/Console/services.php` which is required by `CliApplication::registerServices`.
- To add plugins: drop a PHP file into `plugin/` or `custom/plugin` following `AbstractPlugin` contract; PluginManager will auto-discover.

**External integrations**
- Key deps are listed in `composer.json`: `inhere/console` (CLI framework), `phppkg/*` utilities, `toolkit/*` helpers, `guzzlehttp` and `knplabs/github-api`. Expect network calls and credential-based configs for Jenkins/GitHub/GitLab features (see `config.php` for env-based settings).

If anything here is unclear or you want more detail (examples for adding a command, a service, or a plugin), tell me which area and I will expand with concrete, editable snippets.
