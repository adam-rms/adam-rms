import { execFileSync } from "child_process";
import path from "path";
import { expect, test } from "@playwright/test";
import { PHP_BINARY } from "../env";

/**
 * Checks on the source that need no server or database (see setup/lint.php). They catch a broken
 * file wherever it is, including pages and templates no other test happens to load.
 */
const problems: Record<string, string[]> = JSON.parse(
  execFileSync(PHP_BINARY, [path.join(__dirname, "..", "setup", "lint.php")], { encoding: "utf8", maxBuffer: 16 * 1024 * 1024 }),
);

test("every PHP file parses", () => {
  expect(problems.php).toEqual([]);
});

test("every require/include of a file relative to __DIR__ finds that file", () => {
  expect(problems.includes).toEqual([]);
});

test("every Twig template compiles, with only filters, functions and tags that exist", () => {
  expect(problems.twig).toEqual([]);
});

test("every template named in a render() call or an extends/include/embed/import tag exists", () => {
  expect(problems.templates).toEqual([]);
});
