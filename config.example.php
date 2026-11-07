<?php
// Copy to config.php and change what you need. Real environment variables take priority.
return [
    // Set to false to turn off skills that fetch web pages (agent-readiness-check, llms-txt-generator).
    'SKILLS_ENABLE_NETWORK' => true,
    // Requests per minute per visitor.
    'SKILLS_RATE_GENERAL' => 60,
    'SKILLS_RATE_NETWORK' => 10,
    // Set to true only if the site sits behind Cloudflare, so the real visitor IP is used for rate limits.
    'SKILLS_TRUST_CLOUDFLARE' => false,
];
