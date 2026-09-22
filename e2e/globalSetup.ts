import { execFileSync } from "child_process";
import path from "path";
import { APP_ENV, PHP_BINARY } from "./env";

/**
 * Brings the database to a known state before the suite runs: applies every
 * migration, runs the Phinx seeders (default positions etc.), then writes the
 * config the first-run setup form would otherwise ask for and makes sure the
 * test super admin exists with a known password (see setup/seed.php).
 *
 * Requires a running MySQL reachable with the credentials in ./env.ts.
 */
export default function globalSetup() {
  const repoRoot = path.resolve(__dirname, "..");
  const run = (args: string[]) =>
    execFileSync(PHP_BINARY, args, {
      cwd: repoRoot,
      env: { ...process.env, ...APP_ENV },
      stdio: ["ignore", "inherit", "inherit"],
    });

  run(["vendor/bin/phinx", "migrate", "-q"]);
  run(["vendor/bin/phinx", "seed:run", "-q"]);
  run(["e2e/setup/seed.php"]);
}
