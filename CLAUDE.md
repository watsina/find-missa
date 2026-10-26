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

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

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

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

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

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- This project uses the streamlined Laravel 11+ structure: register middleware, exceptions, and routing in `bootstrap/app.php` and service providers in `bootstrap/providers.php`. There is no `app/Http/Kernel.php` or `app/Console/Kernel.php`, and commands in `app/Console/Commands/` auto-register.

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

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>
