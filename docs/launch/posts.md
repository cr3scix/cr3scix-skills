# Launch posts (drafts)

Checked with the social post checker. Read each community's rules before posting; some ban self-promotion or need a specific flair. Post from the Cr3scix accounts and reply to every comment in the first hours.

## Show HN

**Title:** Show HN: Cr3scix Toolkit – 31 browser tools that your AI assistant can use too

We run a two-person studio in Chennai building websites and automations for small businesses. We kept rebuilding the same small tools for clients: GST and EMI calculators, review request messages, UTM links, a post checker for social media.

So we put them in one open-source dashboard. Two things make it different from other tool collections:

1. Every tool runs in your browser. Files you resize or merge never leave your device.
2. 26 of the tools are also exposed over MCP and a REST API with the same inputs, so Claude, Cursor or any MCP client can use them. A person checks one post in the browser; an agent checks fifty over MCP.

It's plain PHP, no database, no build step. Runs on cheap shared hosting or with `docker compose up`. Adding a tool is one folder.

Live: https://cr3scix.com/skills/
Code: https://github.com/cr3scix/toolkit

Happy to hear which tools you'd want next.

## r/selfhosted

**Title:** Cr3scix Toolkit: self-hosted dashboard of 31 small-business and dev tools, also usable by AI agents over MCP

Single Docker container, no database, no telemetry. Browser tools process files locally (PDF merge, image resize, QR codes). Server tools are rate limited and can't reach private addresses. MIT licensed.

`git clone https://github.com/cr3scix/toolkit && docker compose up -d`

Screenshots and the full tool list are in the README. Feedback on what to add is very welcome.

## LinkedIn (Cr3scix page)

We open-sourced the tools we use every week with small business clients.

31 of them, free, in one place: invoice maker, GST and EMI calculators, review request messages, WhatsApp links, a social post checker, QR codes, PDF merge and more.

The part we're most excited about: 26 of them also work inside AI assistants like Claude. You can check one post yourself, or ask your assistant to check fifty.

No sign-up, nothing tracked, and your files never leave your browser.

Try it at cr3scix.com/skills (link in the first comment). Which tool should we build next?

#SmallBusiness #OpenSource

## X

We open-sourced 31 small-business and dev tools.

Each one works in your browser, and 26 also work inside your AI assistant over MCP.

No sign-up, no tracking.

(link in reply)

## Product Hunt

**Tagline (60 chars max):** Free tools for small business, usable by you and your AI

**Description:** 31 browser tools for invoices, GST, EMI, social posts, QR codes, PDFs, SEO and dev work. 26 of them are also MCP tools, so AI assistants can use them. Open source, self-hostable, no tracking.

## Awesome lists and directories

- awesome-selfhosted (Miscellaneous or Software Development, needs a stable release and a demo)
- awesome-mcp-servers and MCP server directories
- Agent Skills directories for the social-media skill
- awesome-indie, awesome-privacy (client-side tools)

Entry text: `[Cr3scix Toolkit](https://github.com/cr3scix/toolkit) - Dashboard of 31 small-business and developer tools that run in the browser and over MCP for AI agents. ([Demo](https://cr3scix.com/skills/)) MIT PHP/Docker`
