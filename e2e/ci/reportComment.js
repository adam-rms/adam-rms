// Builds the PR comment body from Playwright's JSON reporter output (playwright-report/results.json).
// Used by .github/workflows/e2e-tests.yml.
const MAX_LISTED = 20;

function collectTests(suite, path, out) {
  for (const spec of suite.specs ?? []) {
    for (const test of spec.tests) {
      const last = test.results[test.results.length - 1];
      const error = last?.errors?.[0]?.message ?? "";
      out.push({
        id: spec.id,
        title: [test.projectName, ...path, spec.title].join(" › "),
        status: test.status, // expected | unexpected | flaky | skipped
        // The summary above Playwright's call log, e.g. the failed assertion, locator, expected and received
        error: error
          .replace(/\u001b\[[0-9;]*m/g, "")
          .split("\n\nCall log:")[0]
          .split("\n")
          .filter((line) => line.trim())
          .slice(0, 6)
          .join("\n"),
      });
    }
  }
  for (const child of suite.suites ?? []) collectTests(child, [...path, child.title], out);
  return out;
}

function formatDuration(ms) {
  const seconds = Math.round(ms / 1000);
  return seconds >= 60 ? `${Math.floor(seconds / 60)}m ${seconds % 60}s` : `${seconds}s`;
}

function listTests(heading, tests, reportUrl, withError) {
  if (tests.length === 0) return "";
  const lines = tests.slice(0, MAX_LISTED).map((t) => {
    const link = `[${t.title}](${reportUrl}#?testId=${t.id})`;
    return withError && t.error ? `- ${link}\n  \`\`\`text\n  ${t.error.replace(/\n/g, "\n  ")}\n  \`\`\`` : `- ${link}`;
  });
  if (tests.length > MAX_LISTED) lines.push(`- …and ${tests.length - MAX_LISTED} more`);
  return `\n\n### ${heading} (${tests.length})\n${lines.join("\n")}`;
}

module.exports = function buildReportComment({ results, passed, reportUrl, runUrl }) {
  let body = `## Playwright E2E Test Report\n\n${passed ? "✅ Tests passed" : "❌ Tests failed"}`;
  if (results) {
    const { expected, unexpected, flaky, skipped, duration } = results.stats;
    body += ` · ${expected} passed · ${unexpected} failed · ${flaky} flaky · ${skipped} skipped · ${formatDuration(duration)}`;
    const tests = results.suites.flatMap((suite) => collectTests(suite, [suite.title], []));
    body += listTests("❌ Failed", tests.filter((t) => t.status === "unexpected"), reportUrl, true);
    body += listTests("⚠️ Flaky (passed on retry)", tests.filter((t) => t.status === "flaky"), reportUrl, false);
  }
  return `${body}\n\n📊 **[View HTML Report](${reportUrl})**\n🔗 [GitHub Actions Run](${runUrl})`;
};
