#!/usr/bin/env python3
"""Check a social media draft against platform limits and common quality problems.

No dependencies. Examples:
  python3 check_post.py draft.txt --platform x
  python3 check_post.py draft.txt --platform linkedin,threads,bluesky
  python3 check_post.py --bundle posts.json          # {"x": "...", "linkedin": "..."}
  echo "text" | python3 check_post.py - --platform instagram --json

Exit code 0 when nothing blocks posting, 1 when any draft breaks a hard limit.
Limits were checked in November 2026. Platforms change them; update PLATFORMS when they do.
"""
import argparse
import json
import re
import sys
import unicodedata

# limit: hard character limit for the post text
# hashtags: soft maximum we recommend (None = no advice)
# link_ok: False when links in the body are unclickable or tend to cut reach
# preview: roughly how many characters show before "more" (approximate, varies by device)
PLATFORMS = {
    "x":         {"name": "X",                       "limit": 280,  "hashtags": 2,    "link_ok": False, "preview": 280},
    "x-premium": {"name": "X (Premium)",             "limit": 25000, "hashtags": 2,   "link_ok": False, "preview": 280},
    "linkedin":  {"name": "LinkedIn",                "limit": 3000, "hashtags": 3,    "link_ok": False, "preview": 140},
    "instagram": {"name": "Instagram",               "limit": 2200, "hashtags": 5,    "link_ok": False, "preview": 125},
    "facebook":  {"name": "Facebook",                "limit": 5000, "hashtags": 3,    "link_ok": True,  "preview": 125},
    "threads":   {"name": "Threads",                 "limit": 500,  "hashtags": 1,    "link_ok": True,  "preview": 500},
    "bluesky":   {"name": "Bluesky",                 "limit": 300,  "hashtags": 2,    "link_ok": True,  "preview": 300},
    "mastodon":  {"name": "Mastodon (default)",      "limit": 500,  "hashtags": 4,    "link_ok": True,  "preview": 500},
    "tiktok":    {"name": "TikTok caption",          "limit": 2200, "hashtags": 5,    "link_ok": False, "preview": 100},
    "youtube":   {"name": "YouTube description",     "limit": 5000, "hashtags": 3,    "link_ok": True,  "preview": 120},
    "pinterest": {"name": "Pinterest description",   "limit": 500,  "hashtags": None, "link_ok": True,  "preview": 100},
    "gbp":       {"name": "Google Business Profile", "limit": 1500, "hashtags": 0,    "link_ok": True,  "preview": 150},
}

SLOP = [
    "delve", "leverage", "seamless", "robust", "game-changer", "game changing", "game-changing", "unlock",
    "elevate", "revolutionize", "revolutionise", "cutting-edge", "cutting edge", "next level", "next-level",
    "in today's fast-paced", "in today's digital", "ever-evolving", "landscape", "tapestry", "testament to",
    "moreover", "furthermore", "in conclusion", "let that sink in", "here's the thing", "it's not just",
    "supercharge", "harness the power", "dive into", "deep dive", "embark", "navigate the", "realm",
]
BAIT = ["thoughts?", "agree?", "who else?", "comment yes", "like if", "tag someone", "follow for more", "smash that"]
URL_RE = re.compile(r"https?://\S+|www\.\S+", re.I)
TAG_RE = re.compile(r"(?<!\w)#[\wÀ-￿]+")


def x_weight(text):
    """X weighted length: URLs count 23, most Latin text 1, other characters (CJK, emoji) 2."""
    rest = URL_RE.sub("\x00", text)
    total = 23 * len(URL_RE.findall(text))
    for ch in rest:
        if ch == "\x00":
            continue
        cp = ord(ch)
        light = cp <= 4351 or 8192 <= cp <= 8205 or 8208 <= cp <= 8223 or 8242 <= cp <= 8247
        total += 1 if light else 2
    return total


def length(text, key):
    if key.startswith("x"):
        return x_weight(text)
    return len(unicodedata.normalize("NFC", text))


def check(text, key):
    p = PLATFORMS[key]
    out = {"platform": p["name"], "key": key, "length": length(text, key), "limit": p["limit"], "errors": [], "warnings": []}
    if out["length"] > p["limit"]:
        out["errors"].append(f"{out['length'] - p['limit']} characters over the {p['limit']} limit")
    if not text.strip():
        out["errors"].append("empty draft")
    tags = TAG_RE.findall(text)
    if p["hashtags"] is not None and len(tags) > p["hashtags"]:
        out["warnings"].append(f"{len(tags)} hashtags; {p['hashtags']} or fewer is the safer choice here")
    if key == "mastodon":
        low = [t for t in tags if t[1:].islower() and len(t) > 8]
        if low:
            out["warnings"].append("use CamelCase in long hashtags so screen readers can read them: " + ", ".join(low[:3]))
    urls = URL_RE.findall(text)
    if urls and not p["link_ok"]:
        if key == "instagram" or key == "tiktok":
            out["warnings"].append("links in captions are not clickable here; point to the bio or a link sticker")
        else:
            out["warnings"].append("a link in the post body tends to reduce reach here; consider the first comment or reply")
    first = text.strip().split("\n", 1)[0]
    if len(first) > p["preview"]:
        out["warnings"].append(f"first line is {len(first)} characters; only about {p['preview']} show before 'more'")
    if "—" in text or "–" in text:
        out["warnings"].append("contains an em or en dash, a common sign of AI-written text")
    low = text.lower()
    hits = sorted({w for w in SLOP if w in low})
    if hits:
        out["warnings"].append("stock phrases: " + ", ".join(hits))
    bait = sorted({b for b in BAIT if b in low})
    if bait:
        out["warnings"].append("engagement bait some platforms demote: " + ", ".join(bait))
    if re.search(r"[​-‏⁠﻿]", text):
        out["warnings"].append("contains invisible characters")
    if re.search(r"\{\{[^}]+\}\}|\[(your|insert)[^\]]*\]", text, re.I):
        out["errors"].append("unfilled placeholder left in the draft")
    return out


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("file", nargs="?", help="draft text file, or - for stdin")
    ap.add_argument("--platform", default="all", help="comma list: " + ",".join(PLATFORMS) + " or all")
    ap.add_argument("--bundle", help='JSON file mapping platform key to text, e.g. {"x": "...", "linkedin": "..."}')
    ap.add_argument("--json", action="store_true", help="print JSON instead of a table")
    a = ap.parse_args()

    jobs = []
    if a.bundle:
        with open(a.bundle, encoding="utf-8") as f:
            data = json.load(f)
        for k, v in data.items():
            if k not in PLATFORMS:
                sys.exit(f"unknown platform '{k}'. Use one of: {', '.join(PLATFORMS)}")
            jobs.append((k, v))
    else:
        if not a.file:
            ap.error("give a draft file, - for stdin, or --bundle")
        text = sys.stdin.read() if a.file == "-" else open(a.file, encoding="utf-8").read()
        keys = list(PLATFORMS) if a.platform == "all" else [k.strip() for k in a.platform.split(",")]
        for k in keys:
            if k not in PLATFORMS:
                sys.exit(f"unknown platform '{k}'. Use one of: {', '.join(PLATFORMS)}")
            jobs.append((k, text))

    results = [check(t, k) for k, t in jobs]
    if a.json:
        print(json.dumps(results, indent=2, ensure_ascii=False))
    else:
        for r in results:
            state = "FAIL" if r["errors"] else ("CHECK" if r["warnings"] else "OK")
            print(f"{state:5}  {r['platform']:<24} {r['length']:>6}/{r['limit']}")
            for e in r["errors"]:
                print(f"         error:   {e}")
            for w in r["warnings"]:
                print(f"         warning: {w}")
    sys.exit(1 if any(r["errors"] for r in results) else 0)


if __name__ == "__main__":
    main()
