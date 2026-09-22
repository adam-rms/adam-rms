/**
 * Environment shared by the PHP server Playwright starts and the DB setup script.
 * Defaults match .devcontainer/docker-compose.yml and the CI MySQL service; override
 * any of them by setting the variable before running the tests.
 */
/** Must match production (PHP 8.3) — Twig 3.7 produces invalid PHP on 8.4. */
export const PHP_BINARY = process.env.PHP_BINARY ?? "php";

export const PORT = process.env.E2E_PORT ?? "8080";
export const BASE_URL = `http://127.0.0.1:${PORT}`;

export const APP_ENV: Record<string, string> = {
  DB_HOSTNAME: process.env.DB_HOSTNAME ?? "127.0.0.1",
  DB_DATABASE: process.env.DB_DATABASE ?? "db",
  DB_USERNAME: process.env.DB_USERNAME ?? "user",
  DB_PASSWORD: process.env.DB_PASSWORD ?? "pass",
  DB_PORT: process.env.DB_PORT ?? "3306",
  DEV_MODE: "true",
  CONFIG_ROOTURL: BASE_URL,
};

/** Seeded by db/seeds/DefaultUserSeeder.php */
export const TEST_USER = { email: "test@example.com", password: "password!" };
