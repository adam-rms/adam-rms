#!/usr/bin/env node
/**
 * Generates e2e/COVERAGE.md: every page controller and API endpoint in src/, the permission
 * checks it makes, the parameters it takes that identify a record, and which spec files
 * exercise it. Run from e2e/ with `npm run coverage` and commit the result.
 *
 * Everything in the table is found by regex over the PHP source, so treat it as a checklist
 * rather than an audit: a file that calls a helper doing its own checks will look unchecked.
 * "Tests" lists every spec that mentions the file's path in quotes, e.g. "/api/projects/data.php"
 * or file: "project/index.php".
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const e2eDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const srcDir = path.resolve(e2eDir, "../src");

function walk(dir, filter) {
  return fs.readdirSync(dir, { withFileTypes: true }).flatMap((entry) => {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) return walk(full, filter);
    return filter(full) ? [full] : [];
  });
}
const rel = (file, base) => path.relative(base, file).split(path.sep).join("/");

// Files under src/api/ that are included by endpoints rather than requested directly
const API_HELPERS = new Set([
  "api/apiHead.php",
  "api/apiHeadSecure.php",
  "api/openApiYAMLHeaders.php",
  "api/login/loginAjaxHead.php",
  "api/notifications/main.php",
  "api/notifications/notificationTypes.php",
  "api/notifications/email/email.php",
]);

const apiFiles = walk(path.join(srcDir, "api"), (f) => f.endsWith(".php"))
  .map((f) => rel(f, srcDir))
  .filter((f) => !API_HELPERS.has(f));
const pageFiles = walk(srcDir, (f) => f.endsWith(".php"))
  .map((f) => rel(f, srcDir))
  .filter((f) => !f.startsWith("api/") && !f.startsWith("common/"))
  .filter((f) => /\$TWIG->render/.test(fs.readFileSync(path.join(srcDir, f), "utf8")));

// Parameter names that identify a record (and so could point at another business's data)
const ID_PARAM =
  /(^id$|^ids$|_id$|_ids$|Id$|userid$|^uid$|^userto$|^userby$|^assetsAssignments$|^maintenanceJobs_assets$|^maintenanceJobs_user_tagged|^projects_parent_project_id$|^assets_linkedTo$|^projects_manager$)/;
// instances_id on its own selects the caller's current business (Auth::setInstance), so isn't listed
const NOT_ID = new Set(["instances_id"]);

function analyse(file) {
  const code = fs.readFileSync(path.join(srcDir, file), "utf8").replace(/\/\*\*[\s\S]*?\*\//g, ""); // drop OpenAPI docblocks
  const uniq = (re) => [...new Set([...code.matchAll(re)].map((m) => m[1]))].sort();
  const instancePerms = uniq(/instancePermissionCheck\(\s*["']([^"']+)["']/g);
  const serverPerms = uniq(/serverPermissionCheck\(\s*["']([^"']+)["']/g);
  const params = uniq(/\$(?:_POST|_GET|_REQUEST|array)\[\s*['"]([A-Za-z0-9_]+)['"]\s*\]/g);
  const idParams = params.filter((p) => ID_PARAM.test(p) && !NOT_ID.has(p));

  let auth;
  if (/apiHeadSecure\.php|common\/headSecure\.php|\/headSecure\.php/.test(code)) auth = "login";
  else if (/loginAjaxHead\.php|apiHead\.php|common\/head\.php|require_once 'head\.php'/.test(code)) auth = "public";
  else auth = "?";
  if (auth === "?" && /\/\.\.\/apiHeadSecure/.test(code)) auth = "login";

  const instanceFilter = /\['instance'\]\['instances_id'\]|\["instance"\]\["instances_id"\]|\['instance_ids'\]|\["instance_ids"\]|\$PAGEDATA\['INSTANCE'\]\['instances_id'\]/.test(code);
  return { file, auth, instancePerms, serverPerms, idParams, instanceFilter };
}

const MODULES = [
  ["Assets", /^(api\/(assets|barcodes|categories|groups|manufacturer)\/|asset\.php|assets\.php|newAsset\.php|manufacturers\.php|instances\/importAssets\.php)/],
  ["Projects", /^(api\/projects\/|project\/|ledger\.php)/],
  ["Clients", /^(api\/clients\/|clients\.php)/],
  ["Locations", /^(api\/locations\/|location\/)/],
  ["Maintenance", /^(api\/maintenance\/|maintenance\/)/],
  ["CMS", /^(api\/cms\/|cms\/)/],
  ["Training", /^(api\/(modules|training)\/|training\/)/],
  ["Files", /^api\/(file|s3files)\//],
  ["Instances (business settings, users, permissions)", /^(api\/(instances|permissions)\/|instances\/)/],
  ["Server administration", /^(api\/server\/|server\/)/],
  ["Account & login", /^(api\/(account|login)\/|login\/|user\.php)/],
  ["Search", /^(api\/search\/|search\.php)/],
  ["Public embeds", /^public\//],
  ["Other", /./],
];

const specFiles = walk(e2eDir, (f) => f.endsWith(".spec.ts") && !f.includes("node_modules"));
const specs = specFiles.map((f) => ({ name: rel(f, e2eDir), code: fs.readFileSync(f, "utf8") }));
const escapeRe = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
function coveredBy(file) {
  const re = new RegExp(`["'\`]/?${escapeRe(file)}(?:\\?[^"'\`]*)?["'\`]`);
  return specs.filter((s) => re.test(s.code)).map((s) => s.name);
}
// A case marked test.fixme for a leak has its `fixme:` note (or a *_LEAK constant) on the same line as the path
function knownLeak(file) {
  const re = new RegExp(`["'\`]/?${escapeRe(file)}(?:\\?[^"'\`]*)?["'\`]`);
  return specs.some((s) => s.code.split("\n").some((line) => re.test(line) && /fixme:|\w+_LEAK\b/.test(line)));
}

const rows = [...pageFiles.map((f) => ({ ...analyse(f), kind: "page" })), ...apiFiles.map((f) => ({ ...analyse(f), kind: "api" }))].map(
  (r) => ({ ...r, module: MODULES.find(([, re]) => re.test(r.file))[0], tests: coveredBy(r.file), leak: knownLeak(r.file) }),
);

const code = (list) => (list.length ? list.map((x) => `\`${x}\``).join(", ") : "");
function permCell(r) {
  const parts = [];
  if (r.instancePerms.length) parts.push(`instance: ${code(r.instancePerms)}`);
  if (r.serverPerms.length) parts.push(`server: ${code(r.serverPerms)}`);
  if (!parts.length) parts.push(r.auth === "public" ? "**none (public)**" : r.auth === "login" ? "login only" : "?");
  return parts.join("<br>");
}

const total = rows.length;
const tested = rows.filter((r) => r.tests.length).length;
let md = `# E2E coverage inventory

Generated by \`e2e/scripts/coverage.mjs\` (\`npm run coverage\` in \`e2e/\`) — don't edit by hand; re-run it after adding tests.

Every page controller (\`src/**/*.php\` outside \`src/api/\` and \`src/common/\` that renders a Twig template) and every API endpoint (\`src/api/**/*.php\`, excluding the included helpers listed at the bottom), grouped by module.

- **Permission checks** — the \`instancePermissionCheck\` / \`serverPermissionCheck\` keys the file mentions. "login only" means it includes \`headSecure.php\`/\`apiHeadSecure.php\` but checks no permission itself; "none (public)" means it doesn't require a session.
- **ID params** — request parameters that identify a record (\`*_id\`, \`id\`, \`userid\`, …). These are where a user in one business could point at another business's data. \`instances_id\` is left out: on its own it selects the caller's current business, which \`Auth::setInstance\` limits to businesses they belong to.
- **Instance filter** — ✔ if the file references the current business (\`$AUTH->data['instance']['instances_id']\` / \`instance_ids\`) anywhere. ✘ doesn't mean it leaks (it may filter by joining through an already-scoped record) but it's the first place to look.
- **Tests** — spec files that mention the path. ⬜ = untested. ⚠️ **known leak** = a case for it is marked \`test.fixme\` because it currently lets one business read or change another's data (or skips a permission check); the note on the case says how.

This is a regex scan, so it's a checklist, not an audit. Work through it one module at a time: add isolation cases to \`authenticated/tenant-isolation.spec.ts\` and permission cases to \`authenticated/permissions.spec.ts\` (or a module spec), then re-run the generator.

**${tested} of ${total}** entries have at least one test, and **${rows.filter((r) => r.leak).length}** have a known leak (${rows.filter((r) => r.kind === "page").length} pages, ${rows.filter((r) => r.kind === "api").length} API endpoints).

| Module | Entries | Tested |
| --- | ---: | ---: |
`;
for (const [module] of MODULES) {
  const inModule = rows.filter((r) => r.module === module);
  if (inModule.length) md += `| ${module} | ${inModule.length} | ${inModule.filter((r) => r.tests.length).length} |\n`;
}

for (const [module] of MODULES) {
  const inModule = rows.filter((r) => r.module === module);
  if (!inModule.length) continue;
  md += `\n## ${module}\n\n| | Path | Permission checks | ID params | Instance filter | Tests |\n| --- | --- | --- | --- | :---: | --- |\n`;
  for (const r of inModule.sort((a, b) => (a.kind === b.kind ? a.file.localeCompare(b.file) : a.kind === "page" ? -1 : 1))) {
    const status = (r.tests.length ? `✅ ${r.tests.map((t) => `\`${t}\``).join(", ")}` : "⬜") + (r.leak ? " ⚠️ **known leak** (`test.fixme`)" : "");
    md += `| ${r.kind === "page" ? "page" : "API"} | \`${r.file}\` | ${permCell(r)} | ${code(r.idParams)} | ${r.instanceFilter ? "✔" : "✘"} | ${status} |\n`;
  }
}

md += `\n## Not listed\n\nIncluded by other files rather than requested directly: ${[...API_HELPERS].map((f) => `\`${f}\``).join(", ")}, \`public/embed/head.php\`, \`assets/widgets/statsWidgets.php\` and everything in \`common/\`.\n`;

fs.writeFileSync(path.join(e2eDir, "COVERAGE.md"), md);
console.log(`Wrote COVERAGE.md: ${total} entries, ${tested} tested`);
