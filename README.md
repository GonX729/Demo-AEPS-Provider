# AEPS Demo Provider Project

This is a full-stack project demonstrating an AEPS (Aadhaar Enabled Payment System) integration flow. 

## Project Structure

- **`backend/`** - Laravel 11 API that handles the transaction logic, AEPS provider integration, and wallet management.
- **`frontend/`** - Next.js (React) application that provides the user interface for submitting transactions.

## Setup Instructions

### Backend (Laravel)
1. Open the `backend` folder.
2. Run `composer install` to install PHP dependencies.
3. Copy `.env.example` to `.env` and configure the database (defaults to SQLite for easy setup).
4. Run `php artisan migrate:fresh --seed` to create the database and seed initial test data.
5. Run `php artisan serve --port=8123` to start the backend server.

### Frontend (Next.js)
1. Open the `frontend` folder.
2. Run `npm install` to install Node dependencies.
3. Run `npm run dev` to start the frontend.
4. Open `http://localhost:3000` in your browser.

## Features implemented
- Cash Withdrawal (CW) with automatic AEPS wallet updates.
- Balance Enquiry (BE) and Mini Statement (MS) simulation.
- Field validation (e.g., 12-digit Aadhaar requirement).
