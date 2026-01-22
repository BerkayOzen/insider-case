# Insider Case: League Simulation

This repository is a modular monolith with a Laravel API and a Vue 3 (Vite) frontend. It implements a 4-team league simulation case study with a week-by-week fixture, standings, match simulation, prediction odds, and editing controls.

## What’s Implemented
- League simulation with double round-robin fixtures
- Week-by-week and play-all simulation (win=3, draw=1, loss=0)
- Power-based match simulation with upsets
- Standings recalculation and sorting rules
- Championship probability prediction (Monte Carlo)
- Match edit + team update (power)
- Fixture listing with bye week support
- League reset flow
- Minimal Vue UI with fixtures, standings, match/team modals
- Unit/feature tests for core actions and services

## Prerequisites
- PHP 8.2+
- Composer
- Node.js 20+
- SQLite or another supported database

## Local Setup
### Backend (Laravel)
```bash
cd apps/api
cp .env.example .env
# configure DB in .env (SQLite or MySQL)
php artisan key:generate
php artisan migrate
composer test
php artisan serve
```

### Frontend (Vue 3 + Vite)
```bash
cd apps/web
npm install
# ensure VITE_API_BASE_URL matches your API URL
npm run dev
```

## Usage
- Open the frontend in your browser (Vite dev server)
- Initialize a league, play weeks, edit results, and view standings
- Use the reset button to clear league data

## Notes
- API base URL is configured via `VITE_API_BASE_URL` in `apps/web/.env`
- League data is stored under the Laravel API database
