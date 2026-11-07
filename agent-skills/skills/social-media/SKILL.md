---
name: social-media
description: Plan, write, adapt and review social media content for any brand on any platform (X, LinkedIn, Instagram, Facebook, Threads, Bluesky, Mastodon, TikTok, YouTube, Pinterest, Google Business Profile, Reddit). Use for writing a post, a thread, a carousel or a short video script, turning one idea or a long piece into posts for several platforms, planning a content calendar, drafting replies, checking a draft against platform limits, or reading post analytics to see what works.
license: MIT
metadata:
  author: cr3scix
  version: "1.0.0"
  facts-checked: "2026-11"
---

# Social media

Works for any brand, person or business and any platform. Brand facts come from a brand profile, platform facts from the table below, and everything else from the user. Nothing here is tied to one company, tool or scheduler.

## Modes

Pick the mode from what the user asks. Several can run in one request (plan, then write Monday).

| Mode | Asked as |
| --- | --- |
| **profile** | "set up my brand voice", first run with no profile |
| **write** | "write a post about X", "a LinkedIn post", "a thread", "hooks for this" |
| **repurpose** | "turn this blog/video/podcast into posts", "post this everywhere" |
| **format** | "make a carousel", "short video script", "a poll" |
| **plan** | "plan my week", "content calendar", "what should I post" |
| **engage** | "reply to these comments", "what do I say to this DM" |
| **review** | "check this post", "is this too long for X", "does this sound like AI" |
| **audit** | pasted analytics or past posts, "what's working", "why did this flop" |

## Ground rules (every mode)

1. **Whose account.** Confirm which brand or person this is for when it is not obvious. Shared accounts and agencies run several voices; never mix their facts, proof or tone.
2. **Never invent.** No made-up numbers, clients, testimonials, quotes, results or "studies show". If a figure is needed and unknown, write `{{your number}}` and say so. Every statistic carries its source in the post or in a note to the user.
3. **Check what changes.** App menus, prices, features and news change. Verify how-to steps and any news item on the web before writing, when you can. Say plainly when you could not check.
4. **The user publishes.** Draft and prepare only. Post or schedule only when the user explicitly asks and a connected scheduler or official API is available, and show the final text first. Never automate a platform through browser scripts or scraping; several platforms ban it in their terms and restrict accounts for it.
5. **Write like a person.** No em dashes. Short words. Specific nouns and numbers over adjectives. Cut stock phrases (delve, leverage, seamless, game-changing, unlock, elevate, "in today's fast-paced world", "let that sink in", Moreover, Furthermore). Avoid "it's not just X, it's Y", reflexive lists of three, and one-word question lines ("The result?").
6. **Respect people.** Alt text on every image. Captions on every video. CamelCase multi-word hashtags (#SmallBusiness). No content that targets a private person.

## Brand profile

Every mode reads a brand profile first. Look for `brand-profile.md` in the project or a file the user names. If none exists, run **profile** mode: ask in one batch, then save the answers as `brand-profile.md` and show the path.

```markdown
# Brand profile: <name>
Who we are: one or two lines
Who we write for: audience, their job, what keeps them up at night
What we sell: offers, price range if public, the one action we want
Platforms: which ones, handle on each, and which matter most
Voice: 3 adjectives; we/I; formal or casual; emoji yes/no; sentence length
Words we use / never use:
Topics we own (3 to 5 pillars):
Off-limits: topics, claims, competitors we never name
Proof we can use: real numbers, named clients who agreed, awards (with dates)
Calls to action: in order of preference
Visual: colors, fonts, logo rules, how we show people or products
Three posts we liked (ours or others), and why:
```

If the user gives three past posts instead, infer voice from them and confirm the inference in two lines before using it. Never invent a voice from nothing.

## Platform guide

Limits checked November 2026 (sources at the end). Platforms change them; re-check before anything high stakes, and update `scripts/check_post.py` with what you find.

| Platform | Text limit | Shows before "more" | Hashtags | Links | Media that works | What the ranking rewards |
| --- | --- | --- | --- | --- | --- | --- |
| X | 280 (25,000 Premium); URLs count 23, emoji and CJK count 2 | whole post | 0 to 2 | In body cut reach; put the link in the first reply | 16:9, 1:1 or 9:16 image or video | Replies far above likes, then reposts and profile clicks. Mutes and blocks hurt. Daily reach per author is capped, so more posts is not more reach |
| LinkedIn | 3,000; comments 1,250 | about 2 to 3 lines (~140 chars on mobile) | 0 to 3, at the end | Early links limit reach; link in first comment | Text, native video (3 s to 10 min), document (PDF) carousels | Comments over likes, time spent reading, activity in the first hour, consistent posting |
| Instagram | 2,200 caption | ~125 chars | up to 5 (cap reported 2026); they help search, not reach | Not clickable in captions; bio or story link | Carousel 4:5 (1080x1350) or 3:4, up to 20 in app (10 via API); Reels 9:16 (1080x1920), keep under 3 min for new audiences | Watch time, likes per reach, sends per reach. Original content over reposts. Trial Reels test with non-followers |
| Facebook | 5,000 | ~125 chars | 0 to 3 | Allowed | Reels 9:16 (1080x1920); images 4:5 or 1:1 | Comments, shares and Reels watch time (no official weights published) |
| Threads | 500; one topic tag | whole post | 1 topic | Allowed | Image or video (up to 5 min), carousel 2 to 20 | Replies and conversation. End on a real question |
| Bluesky | 300 | whole post | 0 to 2 | Allowed (link card) | Images with alt text, short video | Following feed is chronological; custom feeds choose posts by their own rules, often topic words |
| Mastodon | 500 by default (servers differ) | whole post | 2 to 4, CamelCase | Allowed | Images with alt text (expected by the community) | No algorithm. Boosts and hashtags carry reach. Use content warnings for heavy topics |
| TikTok | 2,200 caption | ~100 chars | 3 to 5 descriptive | Not clickable | 9:16 (1080x1920); 10 to 60 s is the safe range | Completion and rewatches, shares and saves. Search reads captions, on-screen text and speech, so say and show the keyword |
| YouTube Shorts | description 5,000 | title plus ~1 line | up to 3 | In description | 9:16, up to 3 min | Viewed vs swiped away, watch time. Title and first second do the work |
| Pinterest | 500 description | ~100 chars | keywords instead | Every pin links | 2:3 vertical | Search. Write the description like a search query |
| Google Business Profile | 1,500 | ~150 chars | none | One button link | One clear photo | Posts support the profile; local ranking depends mostly on relevance, distance and reviews |
| Reddit | Each community sets its own rules | title | none | Often restricted | Varies | Upvotes and comment depth. Read the community rules first; promotion is usually banned. Draft only |

Accounts with paid tiers, older servers or regional rules may differ. When the user's own experience disagrees with this table, trust their data and say so.

## Mode: write

1. Find the **one specific true thing**. If the idea is thin ("post about AI"), ask one batched question: what happened, to whom, what it cost or saved, what you'd tell a friend. One idea per post; two ideas are two posts.
2. Offer **three hooks** built on different patterns from the hook list below. Say which you'd ship and why in one sentence.
3. Draft on the strongest hook, shaped for the platform:
   - **Line 1** is the hook and must make sense alone in the preview length above.
   - **Line 2** pays off line 1. It is never setup for line 3.
   - **Body** in short paragraphs (1 to 3 lines) with a blank line between them on LinkedIn, Instagram and Facebook. Tighter on X, Threads and Bluesky.
   - **Close** with one specific question or one instruction. Never both. No "Thoughts?" or "Agree?".
4. Run **review** on the draft (below) and fix what it finds.
5. Hand over a copy-ready block per platform, then a short status line:

```
READY  LinkedIn  1,140/3,000 chars  hook: Number reveal  hashtags: 2  link: in first comment
alt text: "Bar chart of weekly enquiries rising from 3 to 11"
```

### Hook patterns

Use the pattern, never the example words. A hook that would still work with another brand's name on it is too generic.

1. **Number reveal**: "We did X for N days. Here is what happened." Lead with the number.
2. **Mistake with a cost**: "This one mistake cost us {amount or time}."
3. **Before and after**: a specific low point, a specific now, and the one thing that changed.
4. **Contrarian**: the common advice, then why your experience says otherwise. Only if you can back it.
5. **Myth bust**: "{Popular explanation} is not why {problem} happens." Name the real cause in line 2.
6. **Direct value**: "The exact {template, checklist, script} we use for {outcome}. Take it." Give it in the post.
7. **Warning**: "{Common habit} is quietly costing you {specific thing}." Include the fix.
8. **Time anchor**: "{Task} used to take {long}. Now it takes {short}." Keep the ratio believable.
9. **Question with a real fork**: a scenario with a cost and two reasonable answers.
10. **Cold open**: a line of real dialogue or a moment, then who, when and why it mattered.
11. **List promise**: "N things we'd tell ourselves before {milestone}." N of 10 or fewer.
12. **Comparison**: "{Expensive option} vs {cheap option}." Be fair; concede what the expensive one does better.
13. **News that affects you**: "{What changed} on {date}. Here's who it affects and who it doesn't."
14. **Behind the scenes**: how something is actually made, with one detail outsiders never see.
15. **Pattern interrupt**: a very short flat statement. Use rarely.

## Mode: repurpose

1. Read the whole source. If you cannot get a transcript, ask the user to paste it.
2. **Extract, don't summarise**: claims someone could argue with, numbers, stories (person, scene, cost), how-it-works explanations, mistakes, quotable lines. Show what you found with counts. Fewer than four usable items means the source is thin; say so.
3. Map each item to the platform that suits it: a story to LinkedIn or Facebook, a sharp claim to X, Threads or Bluesky, a process to a carousel, a moment to a short video, a how-to to Pinterest or YouTube.
4. Each post stands alone. Never "as I said in my video". Use a different hook pattern for each.
5. Show the plan as a list (day, platform, pattern, angle), then draft on request or all at once if asked.

For **one idea on many platforms**, write the strongest version for the most demanding platform first, then adapt. Don't copy and paste: X and Bluesky need the point in one or two lines, Threads wants a question, LinkedIn wants the story, Instagram wants a carousel or Reel with a caption that adds to it, Mastodon wants CamelCase tags and alt text, TikTok and Shorts need a script.

## Mode: format

**Carousel** (Instagram, LinkedIn as PDF, Threads). Use only when the idea has a sequence: steps, a countdown, a before and after, or a framework with parts.
```
1      Cover: hook in 8 words or fewer, plus a one-line promise
2      Why it matters, in one sentence
3..N   One idea per slide: headline of 3 to 7 words, 25 words or fewer under it
N+1    Recap of the whole thing (the slide people save)
Last   One call to action
```
Number slides, keep one accent color and one type scale, and put sources on the slide that uses the number. Give slide copy plus caption, and alt text per slide.

**Short video script** (Reels, TikTok, Shorts). 15 to 45 seconds unless the topic needs more.
```
0-2 s   Hook: say it and show it as on-screen text
2-n s   One beat per 3 to 5 seconds; on-screen text carries the keyword
Last    Payoff, then one action. Loop back to the first line if you can
```
Give: spoken lines, on-screen text, shot notes, caption, and cover text.

**Thread** (X, Threads, Bluesky). First post works on its own. One point per post. Number them only if order matters. Last post sums up and gives one action. Check each post against its limit.

**Poll.** A real question with balanced options and no "it depends" choice. Follow up with what the results showed.

## Mode: plan

Ask for (or read from the profile): pillars, offer, platforms, how much time they really have, and what actually happened recently (a job done, a number, a mistake, a question customers keep asking). Don't repeat an angle from the last month; check past posts if available.

Three to five posts a week done well beats daily posts done badly. Spread them out; several posts in a row can hide each other. Mix:

| Type | Share | Job |
| --- | --- | --- |
| Teach | 2 a week | One thing the reader can do today |
| Proof | 1 a week | Something that happened, with a real number |
| Opinion | 1 a week | A position someone could disagree with |
| Story | 1 a fortnight | A scene with people, a moment and a cost |
| Offer | 1 a fortnight | What you sell, plainly, with one action |

Each slot gets: date, platform, type, pillar, hook pattern, and a specific angle (an angle is "the invoice we lost because our form broke", a topic is "websites"). Posting times: start from when the audience is free in **their** time zone, then let the user's own analytics decide. Say plainly that the first line matters more than the hour.

Output a table, and offer to save it as a calendar file or to the user's planning tool if one is connected. Nothing is scheduled unless asked.

## Mode: engage

Draft replies, never send them. For each comment or message:
- Reply to the point the person made, using their words. Add one useful thing.
- Questions get a direct answer first. Complaints get acknowledgement, the fix or next step, and a move to private messages when details are personal.
- Never argue with trolls, never reveal private details, never promise refunds or outcomes the user hasn't approved.
- Flag anything legal, medical, safety-related or abusive for the user instead of answering it.

## Mode: review

Run the checker if Python is available:

```bash
python3 scripts/check_post.py draft.txt --platform linkedin,x,threads
python3 scripts/check_post.py --bundle posts.json      # {"x": "...", "linkedin": "..."}
```

It reports length against each platform (with X's weighted counting), hashtag count, links that cut reach or don't work, a first line too long for the preview, em dashes, stock phrases, engagement bait, invisible characters and unfilled placeholders. Exit code 1 means a hard limit is broken.

Without the script, check the same things by hand, then read the draft aloud in your head: would a busy person in the audience stop for line 1, understand it without context, and know what to do at the end? Fix the weakest of those first.

Report problems as a short list with the fix for each, then the corrected draft.

## Mode: audit

Input: exported analytics (CSV or screenshots) or posts with their numbers. Compute and show the working:

| Metric | How | Tells you |
| --- | --- | --- |
| Engagement rate | (reactions + comments + shares + saves) / impressions | Did it earn its reach |
| Conversation rate | comments / reactions | Did it start something |
| Reach multiple | impressions / followers | Did it travel beyond followers |
| Share or send rate | (shares + sends) / reach | Strong sign of future reach on most platforms |
| Completion | average watch time / length | For video, the number that matters most |
| Click rate | link clicks / impressions | Did it move people off platform |

Rank by rate, not raw impressions. Put the top 5 and bottom 5 side by side and look for what separates them: hook pattern, format, length, topic, first-hour replies, and posting time last. State each finding as a claim with the evidence and how sure you are. With fewer than 10 posts, say there's no reliable pattern yet. End with: keep doing, stop doing, test next.

## Visuals and video

Generate or design images only with tools the user has. Otherwise, write a clear brief: size and ratio from the table, headline text, one focal image, brand colors, and where the logo sits. Keep text off the outer 10 percent of vertical video (platform buttons cover it). Never put a real person's photo or a brand's logo into a design without the user confirming they have the rights.

## Sources for the platform guide

- Character limits: Buffer help center, "Character limits for each social network" (updated 21 July 2026)
- Image and video specs: Sprout Social, social media video specs guide (updated 2 November 2026)
- Instagram ranking: Buffer, "How the Instagram algorithm works" (October 2026), citing Adam Mosseri
- LinkedIn ranking: Metricool, "The LinkedIn algorithm in 2026" (updated 1 July 2026)
- X ranking: SocialPilot, "How the X algorithm works" (2026), and X's published algorithm code (January 2026)
- TikTok ranking: Darkroom, "TikTok algorithm guide 2026"

Treat ranking notes as the best public understanding, not guarantees. Platforms rarely publish weights.
