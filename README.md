<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<h1 align="center">CCIS-ANNOUNCEMENT-BOARD</h1>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.2-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/PostgreSQL-4169E1?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL">
  <img src="https://img.shields.io/badge/Supabase_Storage-3ECF8E?style=for-the-badge&logo=supabase&logoColor=white" alt="Supabase">
</p>

## About CCIS-ANNOUNCEMENT-BOARD

**CCIS-ANNOUNCEMENT-BOARD** is a modern, full-stack web application designed for high performance and scalability. It utilizes **Laravel 12** as a robust API orchestrator and **PostgreSQL** as the primary database. **Supabase** is integrated as an S3-compatible cloud storage solution to handle file and media uploads. The frontend is a reactive, type-safe interface built with **Vue 3** and **Vite**.

### Core Technologies
- **Backend:** Laravel 12.0 (PHP 8.2+)
- **Database:** PostgreSQL (`miracis_db`)
- **File Storage:** Supabase S3-Compatible Storage
- **Frontend:** Vue 3.5, Vite, TypeScript
- **Styling:** Tailwind CSS 4.2, DaisyUI, Flowbite
- **Utilities:** Lucide Icons, Material Symbols, OpenMeteo API

---

## Prerequisites

Before starting, ensure you have the following installed:
- **PHP 8.2+** & **Composer**
- **Node.js 20+** & **NPM**
- **PostgreSQL** (running locally on port 5432)
- A **Supabase Account** (with storage bucket configured)

---

## Installation & Setup

### 1. Clone & Install Dependencies
```bash
# Clone the repository
git clone <your-repo-url>
cd ccis-announcement-board

# Install backend dependencies
composer install

# Install frontend dependencies
npm install
