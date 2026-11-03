# Find Missa — Liturgical Schedule Management Platform

Find Missa is a high-performance, full-stack web application designed to centralize and simplify the discovery of **Mass, Confession, and Adoration** schedules. Built with a modern Laravel 12 backend and a Vue 3 + Vuetify 3 Single Page Application (SPA) frontend, it provides a seamless experience for both the public and church administrators.

## 🌟 Key Features

### For the Public
*   **Intelligent Search:** Full-text search powered by Laravel Scout to find activities by type, date, location, or church name.
*   **Interactive Maps:** Visual discovery of churches using Leaflet and Google Maps integration.
*   **Comprehensive Schedules:** Detailed weekly views of liturgical services, including one-time events and recurring schedules.
*   **Social Sharing:** Easy sharing of service times to community networks.

### For Administrators (Backoffice)
*   **Multi-Tenant Architecture:** Team-based scoping where each team represents a country, allowing for global scalability.
*   **Hierarchical Management:** Robust management of Dioceses, Parishes, Sub-Parishes, and individual Churches.
*   **Advanced Scheduling Engine:** Support for daily/weekly recurring patterns, priest assignments, and global/per-activity exceptions.
*   **Security & Audit:** Secure authentication via Laravel Fortify (2FA supported) and granular RBAC (Role-Based Access Control) using Spatie Permissions. Includes full audit logging of all data changes.
*   **Data Operations:** Bulk import/export capabilities via Excel and CSV for managing large liturgical datasets.

## 🛠 Tech Stack

*   **Backend:** PHP 8.4, Laravel 12 (Service Layers, Action Classes, Eloquent API Resources)
*   **Frontend:** Vue 3 (Composition API), Vuetify 3, Pinia, Vite
*   **Database & Cache:** MySQL, Redis (User activity tracking & Caching)
*   **Search Engine:** Laravel Scout
*   **Testing:** PestPHP with Paratest (Parallel execution)
*   **Monitoring:** PostHog Analytics & PHP Artisan Pail (Log monitoring)

## 💡 Motivation

Finding accurate and up-to-date sacramental schedules across different parishes often involves navigating fragmented and outdated websites. **Find Missa** was created to bridge this gap, providing a unified, user-friendly platform for the faithful while offering church administrators a sophisticated tool to manage complex liturgical data. 

The project demonstrates a professional implementation of multi-tenancy, complex scheduling logic, and modern frontend-backend separation, showcasing the ability to build scalable, real-world enterprise applications.

## 🚀 Quick Start

### Prerequisites
*   PHP 8.4+ & Composer
*   Node.js & NPM
*   MySQL & Redis

### Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/yourusername/find-missa.git
    cd find-missa
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    npm install
    ```

3.  **Environment Setup:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Database Migration & Seeding:**
    ```bash
    php artisan migrate --seed
    ```

5.  **Run Development Environment:**
    ```bash
    composer run dev
    ```

## 📖 Usage

### Public Interface
Simply navigate to the homepage to search for nearby churches or specific service times. No registration is required for public discovery.

### Administrative Access
Admin users can log in to the backoffice to:
1.  **Switch Teams:** Manage data for different countries via the team switcher.
2.  **Manage Hierarchy:** Create and link Dioceses down to individual Churches.
3.  **Update Schedules:** Use the scheduling dashboard to set Mass or Confession times.
4.  **Monitor Activity:** View audit logs and user activity via the admin dashboard.

## 🤝 Contributing

Thank you for your interest in Find Missa! At this time, the project is **not open to public contributions** as it is currently under active private development. This repository serves as a showcase of technical implementation and architectural patterns.

## ⚖️ License & Copyright

Copyright © 2026 Eugene Owak. All rights reserved.

This project is **proprietary software**. While the source code is visible for review and portfolio evaluation purposes, it is **not** licensed for use, modification, or redistribution. No part of this repository may be copied or used in other projects without explicit permission.
