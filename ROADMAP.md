# Roadmap

> **Update, v1.0 (November 2026):** renamed to Cr3scix Toolkit, stayed on PHP, focus on small businesses first. 31 tools, 26 usable by agents, Docker, CI and Tamil/Hindi interface are done. The Hostinger partner program is not being pursued for now. Remaining: public demo link, social media pack extras, launch.

Where this project is going and why. Written November 2026.

## The idea in one line

**One toolbox that people and AI agents can both use.** Every tool works in the browser for a person, and the same tool is available to an AI assistant over MCP and as an Agent Skill.

That is the gap. The popular tool collections are built for humans only, and the popular skill collections are built for agents only:

| Project | Stars (late 2026) | What it is | What it lacks |
| --- | --- | --- | --- |
| [CorentinTh/it-tools](https://github.com/CorentinTh/it-tools) | ~41k | Developer tools in the browser, great UX, Docker | No agent access, developers only |
| [iib0011/omni-tools](https://github.com/iib0011/omni-tools) | ~10k | Everyday tools, all client-side, Docker | No agent access |
| [obra/superpowers](https://github.com/obra/superpowers) | ~41k | Agent skills for coding | No UI for people |
| [social-media-skills/skills](https://github.com/social-media-skills/skills) | ~160 | 106 social media agent skills | Tied to one scheduler, no Bluesky or Mastodon, no tools |

What the winners share: a live demo with no sign-up, privacy (work happens on the device), one-command self-hosting, a clean UI, and a contributor path where adding a tool is one folder.

## Honest state today (v0.2)

Good:
- Works with no build step and no database
- 4 server skills reachable over REST and MCP, 11 browser apps, 1 portable Agent Skill (social media)
- Privacy by design: browser apps send nothing anywhere
- Safe network fetching (private addresses blocked, rate limits)

Not yet good enough to get famous:
1. **Browser apps are invisible to agents.** The JSON formatter works for a person but not for an MCP client. The core promise breaks here.
2. **Too few tools.** it-tools has dozens. People star collections they will come back to.
3. **Deploy story doesn't match Hostinger's program.** The Hostinger open-source deploy button supports Node.js apps and Docker Compose on VPS, not PHP shared hosting. A Docker setup fixes eligibility now; a Node.js version would fit the program best.
4. **Name.** "Cr3scix Skills" says who made it, not what it does. Projects that spread have names people can guess the purpose of.
5. **No public demo, screenshots or GIF.** The README is the landing page. Most visitors decide in seconds.

## Decisions to make

1. **Stack.** Stay on PHP (runs anywhere, cheapest hosting) and add Docker, or move to TypeScript/Node so each tool is written once and runs in the browser, on the server and in MCP. The second is more work and the stronger product.
2. **Name.** Keep the cr3scix org, give the project a descriptive name. Options to test: "Toolkit for Humans and Agents", "AgentKit Tools", "Both Tools". Check GitHub, npm and domains before choosing.
3. **Scope.** Small businesses and freelancers (less competition, fits Cr3scix's audience) or developers (bigger, more crowded). Recommendation: small business first, developer tools as a supporting set.

## Plan

### Phase 1: make it real (2 weeks)
- [x] Dockerfile and docker-compose.yml, tested, so it qualifies for the Hostinger deploy button
- [x] One tool definition for both worlds: each browser app gets a `run(input)` function that MCP can call too
- [ ] Public demo at cr3scix.com/skills, linked at the top of the README
- [x] README rewrite: one-line promise, GIF, demo link, deploy buttons, "add a tool in 5 minutes"
- [x] Agent Skills folder installable with common skill installers, plus a Claude Code plugin manifest
- [x] CONTRIBUTING.md, issue templates, a "good first tool" list

### Phase 2: depth (weeks 3 to 6)
- [x] 30+ tools (proposal builder still to do). Highest-demand gaps for small businesses: QR code maker, image resizer and compressor, PDF merge and split, proposal builder, email signature maker, WhatsApp link maker, review request message writer, meeting time zone planner
- [ ] Social media pack built on the social-media skill: post checker app (same limits as the skill's checker), caption formatter, hashtag case fixer, carousel slide maker
- [x] Tests for every tool, run in CI on every pull request
- [x] Translations framework (Tamil and Hindi first, then community)

### Phase 3: launch
- [ ] Make the repo public, apply to the Hostinger open-source partner program, add the deploy button
- [ ] Launch posts: Show HN, r/selfhosted, r/smallbusiness (read each community's rules first), Product Hunt, LinkedIn and X from the Cr3scix accounts
- [ ] Submit to awesome lists (awesome-selfhosted, awesome agent skills lists, MCP server directories)
- [ ] A short demo video: one tool used by a person, then the same tool used by an AI assistant

## How we'll know it's working

- Stars and forks, but mainly: weekly demo users, deploys through the Hostinger button, and outside contributors adding tools
- A tool earns its place if people use it twice

## Sources

- Hostinger open-source partners program: https://www.hostinger.com/open-source-partners
- Agent Skills specification: https://agentskills.io/specification
- it-tools stats: https://gittrend.io/repo/CorentinTh/it-tools
- omni-tools: https://github.com/iib0011/omni-tools
- Skill directory overview: https://fast.io/resources/awesome-claude-skills-directory-2026/
- social-media-skills: https://github.com/social-media-skills/skills
