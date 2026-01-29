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

- php - 8.4.16
- laravel/breeze (BREEZE) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/nightwatch (NIGHTWATCH) - v1
- laravel/prompts (PROMPTS) - v0
- laravel/pulse (PULSE) - v1
- laravel/sanctum (SANCTUM) - v4
- laravel/scout (SCOUT) - v10
- livewire/livewire (LIVEWIRE) - v4
- tightenco/ziggy (ZIGGY) - v1
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `pest-testing` — Tests applications using the Pest 3 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, architecture testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.
- `developing-with-fortify` — Laravel Fortify headless authentication backend development. Activate when implementing authentication features including login, registration, password reset, email verification, two-factor authentication (2FA/TOTP), profile updates, headless auth, authentication scaffolding, or auth guards in Laravel applications.

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

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd and will be available at: `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs for the user.
- You must not run any commands to make the site available via HTTP(S). It is always available through Laravel Herd.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

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
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

=== laravel/fortify rules ===

# Laravel Fortify

- Fortify is a headless authentication backend that provides authentication routes and controllers for Laravel applications.
- IMPORTANT: Always use the `search-docs` tool for detailed Laravel Fortify patterns and documentation.
- IMPORTANT: Activate `developing-with-fortify` skill when working with Fortify authentication features.
</laravel-boost-guidelines>
