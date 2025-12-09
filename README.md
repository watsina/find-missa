# Find Missa

A web application designed to help Catholics find **Mass**, **Confession**, and **Adoration** times at churches across different dioceses. The name "Missa" comes from the Latin word for Mass.

## Target Audience

- **Public Users** - Anyone looking for Catholic church service times
- **Church Administrators** - Diocese/parish staff who manage church schedules
- **Super Admins** - Platform administrators managing teams and users

## Features

### For Public Users (No Login Required)

- Search for activities by type, date, time, location, or church name
- View church details with complete weekly schedules
- Share results via social media

### For Authenticated Users (Backoffice)

- Hierarchical church management: Dioceses → Parishes → Sub-Parishes → Churches
- Activity schedule management (one-time, daily, or weekly recurring)
- Exception handling (per-activity and global exceptions)
- Priest assignments to parishes/churches
- Bulk data import via Excel/CSV
- Data export capabilities
- Audit logging of all changes

## Tech Stack

| Layer              | Technology                                 |
| ------------------ | ------------------------------------------ |
| **Backend**        | Laravel 12 (PHP 8.4)                       |
| **Frontend**       | Vue 3 + Vuetify 3 (SPA)                    |
| **Database**       | MySQL                                      |
| **Authentication** | Laravel Fortify + Sanctum (2FA supported)  |
| **Authorization**  | Spatie Permission (RBAC with team support) |
| **Search**         | Laravel Scout                              |
| **Analytics**      | PostHog                                    |
| **Maps**           | Leaflet + Google Maps                      |

## Architecture

### Multi-Tenancy Model

- Each **Team** represents a **country**
- All data is scoped to a team
- Users can belong to multiple teams and switch between them

### Domain Entities

| Entity            | Description                                           |
| ----------------- | ----------------------------------------------------- |
| **Diocese**       | Top-level church administrative unit                  |
| **Parish**        | A church community under a diocese                    |
| **Sub-Parish**    | A smaller church community under a parish             |
| **Church**        | Physical building (Cathedral, Basilica, Chapel, etc.) |
| **Activity Time** | A scheduled Mass, Confession, or Adoration            |
| **Priest**        | Clergy member assigned to a parish                    |

## Development

```bash
# Start all services (server, queue, logs, vite)
composer run dev

# Run tests
composer run test

# Code linting
composer run lint:fix   # PHP
npm run lint:fix        # JS/Vue
```

## Deployment

- **CI/CD**: GitHub Actions
- **Staging**: Auto-deploys from `main` branch
- **Production**: Manual deployment trigger
