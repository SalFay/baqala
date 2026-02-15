# Baqala POS - Claude Instructions

## Core Principles
**ALWAYS apply DRY (Don't Repeat Yourself) and KISS (Keep It Simple Stupid)**
- Remove duplicate code
- Keep solutions minimal and straightforward
- Avoid over-engineering
- Reuse existing code and patterns

## Package Manager
**ALWAYS use `yarn` instead of `npm`** for all package management operations.

## Project Structure
- Main Laravel app: root directory
- Standalone POS app: `resources/js/pos-app/`

## POS App Commands
```bash
# Install dependencies
cd resources/js/pos-app && yarn install

# Development server
cd resources/js/pos-app && yarn dev

# Build for production
cd resources/js/pos-app && yarn build
```

## Main App Commands
```bash
# Install dependencies
yarn install

# Build assets
yarn build
```

## Key Patterns (SparkCRM-inspired)
- **No TypeScript** - Use JavaScript/JSX only
- **No API prefix** - Use `/pos/*` web routes with session auth
- **Recoil for state** - Future migration from Zustand
- **Ant Design** - Primary UI framework
- **Session auth** - `withCredentials: true` for CSRF

## Routes
- POS routes: `/pos/*` (JSON responses for standalone app)
- Web routes: Direct paths with Inertia responses
