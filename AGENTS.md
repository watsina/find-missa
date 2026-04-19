<laravel-boost-guidelines>
=== .ai/project-guidelines rules ===

# GitHub Copilot Instructions for the Find Missa Project

## Project Overview

Find Missa Project is a modern Laravel application with Vue.js frontend for managing mass, confession and adoration time in the different parishes and sub-parishes in each diocese. It is made up of a Laravel 12 backend API that serves a Vue 3 + Vuetify 3 single-page application (SPA). The project emphasizes code quality, maintainability, and modern development practices.

## Tech Stack & Architecture

### Backend (Laravel 12)

- **Framework**: Laravel 12 with PHP 8.4+
- **Database**: MySQL with Redis for caching and user activity tracking
- **Queue System**: Database-backed queues with Redis
- **Search**: Laravel Scout for full-text search
- **Authentication**: Laravel Sanctum for API token authentication
- **Testing**: PestPHP with Paratest for parallel execution

### Frontend (Vue 3 + Vuetify)

- **Framework**: Vue 3 with Composition API (preferred)
- **UI Library**: Vuetify 3 (Material Design components)
- **State Management**: Pinia stores
- **Routing**: Vue Router 4
- **Build Tool**: Vite with Laravel integration
- **Styling**: SCSS with Tailwind CSS utilities

### Development Tools

- **Code Style**: Laravel Pint (PSR-12 based)
- **Static Analysis**: PHPStan (Level 1)
- **Code Modernization**: Rector (PHP 8.4)
- **Frontend Linting**: ESLint + Prettier
- **Package Management**: Composer + npm

### Version Management & Updates

**🔄 Keeping Tech Stack Current**: When dependency versions change, update the following:

1. **This file** (`.github/copilot-instructions.md`) - Update version numbers in the Tech Stack section above
2. **Source files to monitor for changes**:
   - `composer.json` - Laravel framework version, PHP version requirement
   - `package.json` - Vue, Vuetify, Vite versions
   - `phpstan.neon` - PHPStan level configuration
   - `rector.php` - PHP target version (LevelSetList)

**📋 Update Checklist** when versions change:
- [ ] Update version numbers in this instructions file
- [ ] Update any version-specific code examples or configurations
- [ ] Review and update coding standards if framework updates introduce new patterns
- [ ] Test that all code quality tools work with new versions
- [ ] Update environment requirements (PHP version, Node.js version)

**⚡ Quick Version Check Commands**:
```bash

# Check current versions

php --version
node --version
php artisan --version
composer show laravel/framework
npm list vue vuetify vite
```

## Project Structure

### Frontend Organization

```
ui/
├── components/            # Reusable Vue components

├── pages/                # Route-level components

├── layouts/              # Layout components

├── stores/               # Pinia stores (useXxxStore pattern)

├── plugins/              # Vue plugins & configurations

├── styles/               # SCSS styles and Vuetify themes

├── utils/                # Utility functions

└── main.js               # Application entry point

```

## Coding Standards & Conventions

### PHP/Laravel Standards

- **PSR-12** code style enforced by Laravel Pint
- **camelCase** for method names (including tests)
- **PascalCase** for class names
- Use **type hints** and **return types** everywhere
- Follow **Laravel naming conventions** for models, controllers, etc.
- Use **Eloquent relationships** over manual joins
- **Events and Listeners** for decoupled functionality

### Vue.js Standards

- **Composition API** preferred over Options API
- **`<script setup>`** syntax for new components
- **Script-first structure**: Always place `<script setup>` before `<template>`
- **PascalCase** for component names
- **kebab-case** for component file names
- **Pinia stores** follow `useXxxStore` naming pattern
- **Props validation** with proper types
- **Emit definitions** for component events

### Vue Icons (MDI)

- **Always import icons from `@mdi/js`** - never use text-based icon names like `mdi-check-circle`
- Use the `:icon` binding prop to pass imported icons to Vuetify components
- Import only the icons you need to keep bundle size small

**Icon Usage Example:**
```vue
<script setup>
  import { mdiCheckCircle, mdiClose, mdiPencilOutline } from '@mdi/js';
</script>

<template>
  <!-- Correct: Using imported icon with :icon binding -->
  <v-icon :icon="mdiCheckCircle" color="success" />
  <v-btn :icon="mdiPencilOutline" variant="text" />
  <v-btn :prepend-icon="mdiClose">Cancel</v-btn>

  <!-- WRONG: Text-based icons don't work in this setup -->
  <v-icon>mdi-check-circle</v-icon>
</template>
```

### Vue HTTP Requests

- **Always use the http plugin** (`ui/plugins/http.js`) for all API calls
- **Never import axios directly** - always use `import { http } from '@/plugins'`
- The http plugin is a pre-configured axios instance with:
  - Proper headers (`Accept: application/json`, `X-Requested-With: XMLHttpRequest`)
  - Credentials and XSRF token handling enabled
  - Helper method `http.getErrorMessageFromResponse(error)` for error handling
- Use `http.get()`, `http.post()`, `http.put()`, `http.delete()` for API requests

**HTTP Plugin Example:**
```vue
<script setup>
  import { http } from '@/plugins';
  import { ref } from 'vue';

  const items = ref([]);
  const loading = ref(false);

  async function fetchItems() {
    loading.value = true;
    try {
      const response = await http.get('/api/v1/items', {
        params: { per_page: 100 },
      });
      items.value = response.data.data;
    } catch (error) {
      console.error('Failed to fetch items:', http.getErrorMessageFromResponse(error));
    } finally {
      loading.value = false;
    }
  }
</script>
```

### Vue Form Handling

- **Always use the form plugin** (`ui/plugins/form.js`) for all form-related functionality
- Import as `useForm` from `@/plugins/form`
- Leverage built-in form plugin features:
  - `form.processing` - Loading state during submission
  - `form.errors` - Automatic error mapping to fields via `:error-messages="form.errors.fieldName"`
  - `form.hasErrors` - Boolean indicating validation errors
  - `form.wasSuccessful` - Boolean indicating successful submission
  - `form.isDirty` - Boolean indicating if form has been modified
  - `form.reset()` - Reset form to default values
  - `form.clearErrors()` - Clear all validation errors
  - `form.defaults()` - Set current values as new defaults
  - `form.submit(method, url, options)` - Submit with structured callbacks
- Use `onSuccess` and `onError` callbacks in `form.submit()` options
- Bind Vuetify field errors with `:error-messages="form.errors.fieldName"`
- Disable fields during processing with `:disabled="form.processing"`
- Show loading state on buttons with `:loading="form.processing"`

**Form Plugin Example:**
```vue
<script setup>
  import useForm from '@/plugins/form';
  import notify from '@/utils/notify';

  const formRef = ref(null);

  const form = useForm({
    name: '',
    email: '',
    amount: 0,
  });

  async function submitForm() {
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    form.submit('post', '/api/endpoint', {
      onSuccess: (response) => {
        notify.success('Created successfully');
        emit('created', response);
        closeDialog();
      },
      onError: (errors) => {
        notify.error('Validation error. Please check your input.');
      },
    });
  }
</script>

<template>
  <v-form ref="formRef">
    <v-text-field
      v-model="form.name"
      :error-messages="form.errors.name"
      :disabled="form.processing"
      label="Name"
      variant="outlined"
      density="compact"
    />
    <v-btn
      :loading="form.processing"
      :disabled="form.processing"
      @click="submitForm"
    >
      Submit
    </v-btn>
  </v-form>
</template>
```

### Testing Standards

- **Feature tests** for integration testing
- **Unit tests** for isolated logic
- **Test naming**: `testWhenSomethingHappensThenExpectedResult()`
- Use **factories** for test data creation
- **Mock external services** (AWS, APIs)

## Development Workflow

### Setup Commands

```bash

# Development environment

composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Start all services

composer run dev  # Recommended (starts server, queue, logs, vite)

```

### Code Quality Commands

```bash

# PHP

composer run lint           # Check code style

composer run lint:fix       # Fix code style

composer run check-phpstan  # Static analysis

composer run test           # Run tests

# Frontend

npm run lint                # Check JS/Vue code

npm run lint:fix           # Fix JS/Vue code

npm run build              # Build for production

```

### Common Artisan Commands

```bash
php artisan migrate         # Run migrations

php artisan db:seed        # Seed database

php artisan queue:listen   # Start queue worker

php artisan pail           # Monitor logs

php artisan test           # Run tests

```

## Environment Configuration

### Required Services

- **MySQL**: Database storage
- **Redis**: Caching and user activity tracking (required!)
- **GitHub**: Repository integration

## Common Patterns & Best Practices

### Laravel Patterns

- **Service Classes** for complex business logic
- **Action Classes** for single-purpose operations
- **Repository Pattern** for data access
- **Event/Listener** for decoupled operations
- **Form Requests** for validation
- **Policies** for authorization logic

### Vue.js Patterns

- **Composables** for reusable logic
- **Pinia Stores** for state management
- **Provide/Inject** for dependency injection
- **Teleport** for modals and overlays
- **Suspense** for async components

### GraphQL Patterns

- Use **Lighthouse directives** (`@paginate`, `@can`, `@guard`)
- **Type definitions** in separate schema files
- **Resolvers** in `app/GraphQL/` directory
- **Playground** available at `/graphql-playground`

## Troubleshooting Common Issues

### Redis Connection Errors

- Start Redis: `brew services start redis`
- Check status: `redis-cli ping`
- Required for user activity tracking functionality

### Database Issues

- Ensure MySQL is running
- Check credentials in `.env`
- Run migrations: `php artisan migrate`

### Frontend Build Issues

- Clear caches: `npm run build` or `npx vite --force`
- Reinstall dependencies: `rm -rf node_modules && npm install`

### Queue Processing

- Start worker: `php artisan queue:listen`
- Check Redis connection
- Monitor with: `php artisan pail`

## File Editing Guidelines

When editing files, always:
1. **Follow existing patterns** and conventions
2. **Add proper type hints** and documentation
3. **Include tests** for new functionality
4. **Update relevant configuration** files
5. **Maintain backward compatibility**
6. **Use dependency injection** where appropriate
7. **Follow SOLID principles**

## Security Considerations

- **Never commit** sensitive data (`.env` files)
- **Validate all inputs** using Form Requests
- **Use authorization** policies and gates
- **Sanitize GraphQL** queries and responses
- **Follow OWASP** guidelines for web security
- **Implement CSRF** protection for forms

## Performance Guidelines

- **Use eager loading** to prevent N+1 queries
- **Cache expensive** operations appropriately
- **Optimize database** queries with indexes
- **Use queue jobs** for heavy operations
- **Minimize bundle size** in frontend builds
- **Implement pagination** for large datasets

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/breeze (BREEZE) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/nightwatch (NIGHTWATCH) - v1
- laravel/pail (PAIL) - v1
- laravel/pennant (PENNANT) - v1
- laravel/prompts (PROMPTS) - v0
- laravel/pulse (PULSE) - v1
- laravel/sanctum (SANCTUM) - v4
- laravel/scout (SCOUT) - v10
- livewire/livewire (LIVEWIRE) - v4
- tightenco/ziggy (ZIGGY) - v1
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `fortify-development` — ACTIVATE when the user works on authentication in Laravel. This includes login, registration, password reset, email verification, two-factor authentication (2FA/TOTP/QR codes/recovery codes), profile updates, password confirmation, or any auth-related routes and controllers. Activate when the user mentions Fortify, auth, authentication, login, register, signup, forgot password, verify email, 2FA, or references app/Actions/Fortify/, CreateNewUser, UpdateUserProfileInformation, FortifyServiceProvider, config/fortify.php, or auth guards. Fortify is the frontend-agnostic authentication backend for Laravel that registers all auth routes and controllers. Also activate when building SPA or headless authentication, customizing login redirects, overriding response contracts like LoginResponse, or configuring login throttling. Do NOT activate for Laravel Passport (OAuth2 API tokens), Socialite (OAuth social login), or non-auth Laravel features.
- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `configure-nightwatch` — Configures Laravel Nightwatch data collection, sampling rates, filtering rules, and redaction policies. Use when setting up Nightwatch, managing data volume, protecting sensitive data (PII), or optimizing event collection for production workloads.
- `pennant-development` — Use when working with Laravel Pennant the official Laravel feature flag package. Trigger whenever the query mentions Pennant by name or involves feature flags or feature toggles in a Laravel project. Tasks include defining feature flags checking whether features are active creating class based features in `app/Features` using Blade `@feature` directives scoping flags to users or teams building custom Pennant storage drivers protecting routes with feature flags testing feature flags with Pest or PHPUnit and implementing A B testing or gradual rollouts with feature flags. Do not trigger for generic Laravel configuration authorization policies authentication or non Pennant feature management systems.
- `pulse-development` — Handles Laravel Pulse setup, configuration, and custom card development. Activates when installing Pulse; configuring the dashboard or authorization gate; setting up recorders and filtering; building custom Livewire cards; optimizing with Redis ingest or sampling; or when the user mentions /pulse, pulse:check, pulse:work, Pulse::record(), or application monitoring.
- `scout-development` — Develops full-text search with Laravel Scout. Activates when installing or configuring Scout; choosing a search engine (Algolia, Meilisearch, Typesense, Database, Collection); adding the Searchable trait to models; customizing toSearchableArray or searchableAs; importing or flushing search indexes; writing search queries with where clauses, pagination, or soft deletes; configuring index settings; troubleshooting search results; or when the user mentions Scout, full-text search, search indexing, or search engines in a Laravel project. Make sure to use this skill whenever the user works with search functionality in Laravel, even if they don't explicitly mention Scout.
- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit) or architecture tests. Covers: test()/it()/expect() syntax, datasets, mocking, browser testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 3 features. Do not use for editing factories, seeders, migrations, controllers, models, or non-test PHP code.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
