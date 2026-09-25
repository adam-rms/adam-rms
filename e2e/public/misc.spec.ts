import { test, expect } from "@playwright/test";

test("api/onlineCheck.php reports the database is reachable", async ({ request }) => {
  const response = await request.get("/api/onlineCheck.php");
  expect(response.status()).toBe(200);
  expect(await response.text()).toBe("OK");
});

test("public/embed/jobs.php shows nothing without a business", async ({ request }) => {
  expect(await (await request.get("/public/embed/jobs.php")).text()).toBe("");
});
