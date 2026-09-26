import crypto from "crypto";
import fs from "fs";
import path from "path";
import type { BrowserContext } from "@playwright/test";
import { BASE_URL } from "./env";

/**
 * Every page loads jQuery, Bootstrap and ~30 other libraries from CDNs. Fetching them for every page
 * visit is slow, and one that fails to load (a CDN or proxy hiccup) breaks every script on the page,
 * which looks just like a bug in the app. So the browser fetches each file once and keeps it in
 * .cdn-cache/, and serves it from there after that, in this run and later ones.
 *
 * Requests to any other outside site (the YouTube player API, analytics) are blocked, so what a page
 * does doesn't depend on a service the tests don't control.
 */
const CDN_HOSTS = /^https:\/\/(cdnjs\.cloudflare\.com|cdn\.jsdelivr\.net|fonts\.googleapis\.com|fonts\.gstatic\.com)\//;
const cacheDir = path.join(__dirname, ".cdn-cache");

type Cached = { status: number; headers: Record<string, string>; body: Buffer };
const memory = new Map<string, Cached>();

function read(url: string): Cached | undefined {
  if (memory.has(url)) return memory.get(url);
  const file = path.join(cacheDir, crypto.createHash("sha256").update(url).digest("hex"));
  if (!fs.existsSync(file)) return undefined;
  const { status, headers } = JSON.parse(fs.readFileSync(`${file}.json`, "utf8"));
  const cached = { status, headers, body: fs.readFileSync(file) };
  memory.set(url, cached);
  return cached;
}

function write(url: string, cached: Cached) {
  memory.set(url, cached);
  const file = path.join(cacheDir, crypto.createHash("sha256").update(url).digest("hex"));
  fs.mkdirSync(cacheDir, { recursive: true });
  fs.writeFileSync(`${file}.json`, JSON.stringify({ url, status: cached.status, headers: cached.headers }));
  fs.writeFileSync(file, cached.body);
}

export async function cacheCdn(context: BrowserContext) {
  await context.route(/^https?:\/\//, async (route) => {
    const url = route.request().url();
    if (url.startsWith(BASE_URL)) return route.fallback();
    if (!CDN_HOSTS.test(url)) return route.abort("blockedbyclient");
    const cached = read(url);
    if (cached) return route.fulfill(cached);
    for (let attempt = 1; ; attempt++) {
      try {
        const response = await route.fetch({ timeout: 30_000 });
        const headers = response.headers();
        // The browser checks the SRI hash, and needs this to read the body cross-origin
        const kept: Record<string, string> = { "access-control-allow-origin": "*" };
        if (headers["content-type"]) kept["content-type"] = headers["content-type"];
        const fetched = { status: response.status(), headers: kept, body: await response.body() };
        if (fetched.status === 200) write(url, fetched);
        return route.fulfill(fetched);
      } catch {
        if (attempt === 3) return route.abort();
      }
    }
  });
}
