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

