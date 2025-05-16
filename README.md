# Aero WP API Plugin (WordPress Plugin)

A custom WordPress plugin built to power the **[FastTrackAero.com](https://fasttrackaero.com)** platform — a modern Platform for Fast Track Airport services in Morocco's Airports. This plugin adds advanced REST API capabilities and a modular backend architecture to support Fast Track Aero's booking, city, order and payment systems.

> Designed to bring Laravel/NestJS-style architecture into WordPress: modular, testable, scalable, and API-first.

---

## 🌍 Why This Plugin Exists

Fast Track Aero is a platform built on Next.js (Frontend) and WordPress (backend). WordPress alone lacked the API structure, dependency injection, and modularity needed to scale — so this plugin was developed to:

- ✅ Create custom REST API endpoints
- ✅ Register modules dynamically
- ✅ Implement a Laravel-style Service Container and DI system
- ✅ Separate business logic by domain (Booking, City, Order, etc.)
- ✅ Remove hardcoded spaghetti logic from `functions.php`

---

## 🧠 How It Works

The plugin introduces:

- **Modular architecture**: Each domain has its own module and controller.
- **Service container**: Dependencies are injected instead of manually instantiated.
- **REST API layer**: Each controller registers its own routes.
- **Environment-specific logic**: WooCommerce permissions are bypassed in local dev.

This plugin is part of the **backend system of a full-stack headless app**, and not meant as a drop-in plugin for classic WordPress usage.

---

## 🛠 Requirements

- PHP 8.1+
- WordPress 6.0+
- WooCommerce
- WooCommerce Booking Extension
- Must be installed in a headless WordPress backend with custom frontend integration (e.g. React, Next.js)
