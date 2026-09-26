import { test, expect, mentions } from "../tenants";

test("404.php shows the not found page", async ({ asA }) => {
  expect(mentions(await asA.page("/404.php"), "Oops! Page not found.")).toBe(true);
});

test("api/icons/getIcons.php searches the icon list", async ({ asA }) => {
  const response = await asA.api("/api/icons/getIcons.php", { search: "camera" });
  expect(response.json.result).toBe(true);
  const icons = Object.values(response.json.response) as { code: string }[];
  expect(icons.length).toBeGreaterThan(0);
  expect(icons.length).toBeLessThanOrEqual(20);
  for (const icon of icons) expect(icon.code).toContain("camera");
});
