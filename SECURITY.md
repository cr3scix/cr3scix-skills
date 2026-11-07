# Security

Please report vulnerabilities privately to **hello@cr3scix.com** with steps to reproduce. We aim to reply within 3 working days and will credit you in the release notes if you like.

What matters most here:

- **Server-side request forgery** in tools that fetch websites (`SafeFetch`). Private, loopback and link-local addresses must stay unreachable.
- **Cross-site scripting** in the dashboard or any app. All user text must reach the page as text, never as HTML.
- **Denial of service** through expensive inputs (regex, diff, large files) or bypassing rate limits.

Please don't test against cr3scix.com in ways that degrade it for others. Run your own copy with Docker instead.
