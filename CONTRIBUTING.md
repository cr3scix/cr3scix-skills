# Contributing

Thanks for helping. The easiest and most useful contribution is a new tool.

## Add a tool in five minutes

```bash
php scripts/new-app.php my-tool "My tool" business --agent
php -S 127.0.0.1:8080 index.php
# open http://127.0.0.1:8080/#/app/my-tool
```

That creates `apps/my-tool/` with:

- `app.json`: name, title, one-sentence description, category (`agent`, `business`, `marketing` or `developer`) and, for agent tools, an `inputSchema`
- `app.js`: the browser app. Helpers on `CS`: `el`, `inp`, `area`, `sel`, `add`, `out` (result box with copy button), `value` (renders data as a table or list), `money`, `download`, `load` (loads a file from the app's `vendor/` folder)
- `tool.php` (with `--agent`): the same job on the server, so AI agents can call it over MCP and REST

## What makes a good tool

- **One job, done well.** If it needs a manual, split it.
- **Private by default.** Browser apps never send data anywhere. No analytics, no external requests, no CDN scripts. Put third-party libraries in `apps/<name>/vendor/` with their license.
- **Plain words.** Labels a non-technical small business owner understands. No em dashes, no hype.
- **Honest output.** Calculators say what they assume. Nothing pretends to be advice it isn't.
- **Same answer twice.** If you add `tool.php`, it must give the same result as `app.js`. Add a test for it in `tests/run.php`.
- **Accessible.** Every input has a label. Images get alt text. Works with a keyboard and at phone width.

## Before you open a pull request

```bash
php tests/run.php                    # unit tests
php -S 127.0.0.1:8081 index.php &
node tests/browser.js                # opens every app (needs Playwright)
```

One tool per pull request makes review fast. Describe who the tool is for in the PR.

## Translations

Interface strings live in `locales/<language>.json`, keyed by the English text. Tamil and Hindi were drafted by AI and need native review; corrections are very welcome. New languages: copy `locales/hi.json`, translate the values, set `_name`.

## Agent skills

Portable skills live in `agent-skills/skills/<name>/SKILL.md` and follow the [Agent Skills specification](https://agentskills.io/specification): lowercase dashed name matching the folder, a description under 1,024 characters that says when to use it, and a body under 500 lines.

## Code of conduct

Be kind, be specific, assume good intent. Harassment of any kind gets you removed. Report problems to hello@cr3scix.com.
