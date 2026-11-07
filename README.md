# Cr3scix Skills

An open-source dashboard of small, useful skills for businesses and developers. Run them in the browser, call them over a REST API, or connect the whole set to an AI assistant through MCP.

It is a plain PHP app with no database and no build step, so it runs on ordinary shared hosting.

## What is in the box

| Skill | Category | What it does |
| --- | --- | --- |
| Agent readiness check | AI agent | Scores a website out of 100 on how easy it is for AI agents to read, with a fix for each failed check |
| llms.txt generator | AI agent | Reads a sitemap and drafts an `llms.txt` file |
| Manual work estimator | Business | Turns hours of repetitive work into a yearly cost and a realistic automation saving |
| JSON-LD business generator | Developer | Builds schema.org markup for an organization or local business |

## One app, three ways in

- **Dashboard** at `/`: sidebar, search, favorites, recent runs, readable results.
- **REST API**: `POST /api/skills/<name>` with a JSON body. `GET /api/skills` lists everything.
- **MCP**: every skill is a tool at `/mcp` (Streamable HTTP), with a server card at `/.well-known/mcp/server-card.json`. `/llms.txt` is generated from the installed skills.

```bash
curl -X POST https://your-site/api/skills/manual-work-estimator \
  -H "Content-Type: application/json" \
  -d '{"teamSize":4,"hoursPerWeekEach":5,"hourlyCost":30}'
```

## Install

Requirements: PHP 8.1 or newer with the `curl` and `json` extensions.

**On Hostinger (or any Apache/LiteSpeed host):** upload the files, or connect this repository through Git deploy. Point the domain at the folder. Nothing else is needed.

**Locally:**

```bash
php -S 127.0.0.1:8080 index.php
```

Then open http://127.0.0.1:8080. Run the tests with `php tests/run.php`.

Optional settings go in environment variables or a `config.php` copied from `config.example.php`.

| Setting | Default | Meaning |
| --- | --- | --- |
| `SKILLS_ENABLE_NETWORK` | on | Turn off skills that fetch web pages |
| `SKILLS_RATE_GENERAL` | 60 | Requests per minute per visitor |
| `SKILLS_RATE_NETWORK` | 10 | Web-fetching runs per minute per visitor |
| `SKILLS_TRUST_CLOUDFLARE` | off | Use `CF-Connecting-IP` for rate limits when behind Cloudflare |

## A note on safety

Skills that fetch web pages only reach public sites. Private, loopback and link-local addresses are refused, redirects are checked at every hop, responses are size-capped, and runs are rate limited. If you deploy this publicly and do not want visitors triggering outbound requests from your server, set `SKILLS_ENABLE_NETWORK=0`.

The app stores nothing about visitors. Favorites and recent runs live in the visitor's own browser.

## Add your own skill

Create a folder in `skills/`. The folder name is the skill name (lowercase, dashes).

`skills/my-skill/skill.json`

```json
{
  "name": "my-skill",
  "title": "My skill",
  "description": "One or two sentences on what it does.",
  "category": "business",
  "inputSchema": {
    "type": "object",
    "properties": {
      "text": { "type": "string", "title": "Text", "maxLength": 2000 }
    },
    "required": ["text"]
  }
}
```

`skills/my-skill/handler.php`

```php
<?php
return function (array $input): array {
    return ['length' => strlen($input['text'])];
};
```

That is all. The skill appears in the dashboard, the REST API, MCP and `llms.txt` automatically. Categories are `agent`, `business` and `developer`. Add `"network": true` if the skill fetches web pages, and use `SafeFetch::get()` for that, never raw `curl`. Throw a `RuntimeException` with a friendly message for problems the user can fix.

Supported input types: `string`, `integer`, `number`, `boolean`, with `enum`, `default`, `minimum`, `maximum`, `maxLength`, and `format` of `url` or `email`. Add `"x-multiline": true` for a text area.

## Layout

```
index.php        routes: dashboard, API, MCP, discovery files
src/             registry, validation, MCP server, safe fetcher, dashboard page
skills/          one folder per skill
tests/run.php    dependency-free test suite
```

## License

MIT
