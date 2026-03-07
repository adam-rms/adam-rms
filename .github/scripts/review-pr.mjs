// @ts-check
import { OpenAI } from "openai";

const client = new OpenAI({
  baseURL: "https://models.inference.ai.azure.com",
  apiKey: process.env.GITHUB_TOKEN,
});

const PR_NUMBER = process.env.PR_NUMBER;
const REPO = process.env.REPO;
const GITHUB_TOKEN = process.env.GITHUB_TOKEN;

const [owner, repoName] = REPO.split("/");

async function githubRequest(path, options = {}) {
  const url = `https://api.github.com${path}`;
  const res = await fetch(url, {
    ...options,
    headers: {
      Authorization: `Bearer ${GITHUB_TOKEN}`,
      Accept: "application/vnd.github+json",
      "X-GitHub-Api-Version": "2022-11-28",
      "Content-Type": "application/json",
      ...(options.headers ?? {}),
    },
  });
  if (!res.ok) {
    const text = await res.text();
    throw new Error(`GitHub API error ${res.status}: ${text}`);
  }
  return res.json();
}

async function main() {
  // Fetch current PR data directly from the API to safely handle special characters
  const pr = await githubRequest(
    `/repos/${owner}/${repoName}/pulls/${PR_NUMBER}`
  );
  const originalTitle = pr.title;
  const originalBody = pr.body ?? "";

  console.log(`Reviewing PR #${PR_NUMBER}: "${originalTitle}"`);

  const systemPrompt = `You are a technical writing assistant reviewing GitHub pull request metadata for AdamRMS — an advanced open-source Rental Management System for Theatre, AV & Broadcast (PHP / MySQL / Twig).

Your task is to review the PR title and description and improve them for clarity if needed.

Guidelines:
- Titles should be concise and use the imperative mood (e.g. "Add asset export" not "Added asset export" or "Adding asset export")
- Titles should describe the change, not the symptom (e.g. "Fix duplicate billing entry on project save" not "Fix bug")
- Descriptions should briefly cover: what changed, why, and (if non-obvious) how to test it
- Preserve any existing structure: checklists, links, screenshots, issue references (#123)
- Do not invent content that isn't implied by the title/description
- Only suggest changes when they genuinely improve clarity or completeness
- If the PR has no description, only add one if the title strongly implies what it should say

Respond with a JSON object only — no markdown fences, no extra text:
{
  "needs_improvement": boolean,
  "improved_title": string | null,
  "improved_body": string | null,
  "changes_summary": string
}

Set "needs_improvement" to false and both improved fields to null when no changes are warranted.
"changes_summary" must always be a short human-readable sentence or two explaining what you changed and why (or why nothing needed changing).`;

  const userMessage = `Pull request title:\n${originalTitle}\n\nPull request description:\n${originalBody || "(none)"}`;

  const completion = await client.chat.completions.create({
    model: "gpt-4o",
    messages: [
      { role: "system", content: systemPrompt },
      { role: "user", content: userMessage },
    ],
    temperature: 0.2,
    response_format: { type: "json_object" },
  });

  const raw = completion.choices[0].message.content ?? "{}";

  let result;
  try {
    result = JSON.parse(raw);
  } catch {
    console.error("Failed to parse Claude response as JSON:", raw);
    process.exit(1);
  }

  if (!result.needs_improvement) {
    console.log("No improvements needed:", result.changes_summary);
    return;
  }

  // Build the update payload with only the fields that changed
  const updatePayload = {};
  if (result.improved_title && result.improved_title !== originalTitle) {
    updatePayload.title = result.improved_title;
  }
  if (result.improved_body !== null && result.improved_body !== originalBody) {
    updatePayload.body = result.improved_body;
  }

  if (Object.keys(updatePayload).length === 0) {
    console.log("Suggested improvements were identical to the original — skipping update.");
    return;
  }

  await githubRequest(`/repos/${owner}/${repoName}/pulls/${PR_NUMBER}`, {
    method: "PATCH",
    body: JSON.stringify(updatePayload),
  });

  const changedFields = Object.keys(updatePayload).join(" and ");
  const commentBody =
    `> [!NOTE]\n` +
    `> **PR ${changedFields} updated for clarity by GitHub Copilot**\n>\n` +
    `> ${result.changes_summary}`;

  await githubRequest(
    `/repos/${owner}/${repoName}/issues/${PR_NUMBER}/comments`,
    {
      method: "POST",
      body: JSON.stringify({ body: commentBody }),
    }
  );

  console.log(`Updated PR ${changedFields}.`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
