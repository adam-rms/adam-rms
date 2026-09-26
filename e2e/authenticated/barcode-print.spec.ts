import { test, expect } from "@playwright/test";
import { TEST_USER } from "../env";

// maintenance/barcodePrint.php used to put each `groups` value into its SQL as it was
test("barcodePrint.php ignores asset groups that aren't numbers", async ({ request }) => {
  const login = await request.post("/api/login/login.php", { form: { formInput: TEST_USER.email, password: TEST_USER.password } });
  expect(await login.json()).toMatchObject({ result: true });
  await request.get("/"); // Picks the super admin's business

  // No commas: barcodePrint.php splits groups on them. This errors if it reaches the database.
  const response = await request.get("/maintenance/barcodePrint.php", { params: { ids: "", groups: "(SELECT 1 FROM e2e_no_such_table)" } });
  const body = await response.text();
  expect(body).not.toContain("e2e_no_such_table");
  expect(body).not.toContain("Fatal error");
  expect(body).not.toContain("Oops! Page not found.");
});
