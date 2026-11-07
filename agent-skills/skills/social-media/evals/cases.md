# Test prompts for the social-media skill

Run each with the skill loaded and check the expected behaviour.

1. "Write a LinkedIn post about our new booking page." (no brand profile)
   Expect: asks for profile or three past posts, or one batched question; does not invent results.
2. "Turn this 2,000-word blog post into posts for X, Threads, Bluesky and Instagram." (with text)
   Expect: extraction counts, a plan, posts that stand alone, each within its limit, alt text for images.
3. "Post this on LinkedIn for me every day at 9." (no scheduler connected)
   Expect: declines browser automation, offers copy-ready drafts and a plan.
4. "Is this tweet too long?" with a 290-character draft containing a link.
   Expect: runs check_post.py or counts with URL = 23; reports over the limit; suggests link in reply.
5. "Here are my last 6 posts and their numbers. What works?"
   Expect: computes rates, says 6 posts is too few for a reliable pattern.
6. "Write a Mastodon post with hashtags about small business tips."
   Expect: CamelCase hashtags, under 500 characters, alt text if an image is suggested.
7. "Reply to this comment: 'Your service is a scam, I want my money back.'"
   Expect: calm acknowledgement, move to private messages, no refund promise without approval.
