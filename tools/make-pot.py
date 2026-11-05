#!/usr/bin/env python3
"""Build languages/remotive.pot from the theme's PHP.

Reads every tracked .php file outside tests/, finds the calls to the WordPress translation functions that
use the text domain 'remotive', and writes one entry per string with its file:line references, any
`/* translators: */` note that sits right before the call, and the php-format flag where the string has a
placeholder.

    python3 tools/make-pot.py            write languages/remotive.pot
    python3 tools/make-pot.py --check    exit 1 if the file on disk lists different strings

Only calls whose text is a plain quoted string are read. A call that builds its text from a variable
cannot be extracted by any tool and is skipped.
"""
import re
import subprocess
import sys
from collections import OrderedDict
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
POT = ROOT / "languages" / "remotive.pot"
DOMAIN = "remotive"

# function name -> (index of the text, index of the plural or None, index of the context or None, index of the domain)
FUNCTIONS = {
    "__": (0, None, None, 1),
    "_e": (0, None, None, 1),
    "esc_html__": (0, None, None, 1),
    "esc_attr__": (0, None, None, 1),
    "esc_html_e": (0, None, None, 1),
    "esc_attr_e": (0, None, None, 1),
    "_x": (0, None, 1, 2),
    "esc_html_x": (0, None, 1, 2),
    "esc_attr_x": (0, None, 1, 2),
    "_n": (0, 1, None, 3),
    "_nx": (0, 1, 3, 4),
    "_n_noop": (0, 1, None, 2),
    "_nx_noop": (0, 1, 2, 3),
}
CALL = re.compile(r"(?<![\w$>:])(" + "|".join(sorted(FUNCTIONS, key=len, reverse=True)) + r")\s*\(")
# One translators comment that ends right where the call (or the wrapper calls around it) begins. The text of
# the comment may not contain a closing "*/", so it can never reach back across earlier code or comments.
NOTE = re.compile(r"/\*\s*translators:\s*((?:(?!\*/).)*?)\*/\s*$", re.S)
# What may sit between that comment and the call: wrapper calls (sprintf( esc_html( ), an array key ('label' =>),
# an assignment ($x = ) or return/echo.
WRAPPERS = re.compile(r"(?:(?:['\"][\w.-]+['\"]\s*=>\s*)|(?:\$[\w\[\]'\"]+\s*\.?=\s*)|(?:(?:return|echo|print)\s+)|(?:[A-Za-z_]\w*\s*\(\s*))+$")


def read_string(text, pos):
    """The PHP quoted string starting at pos, as (value, end), or None."""
    quote = text[pos]
    if quote not in "'\"":
        return None
    out = []
    i = pos + 1
    while i < len(text):
        c = text[i]
        if c == "\\" and i + 1 < len(text):
            n = text[i + 1]
            if quote == "'":
                out.append(n if n in "\\'" else "\\" + n)
            else:
                out.append({"n": "\n", "t": "\t", '"': '"', "\\": "\\", "$": "$"}.get(n, "\\" + n))
            i += 2
            continue
        if c == quote:
            return "".join(out), i + 1
        out.append(c)
        i += 1
    return None


def split_args(text, pos):
    """The top-level arguments of the call whose '(' is just before pos, as a list of source strings."""
    args, depth, start, i = [], 0, pos, pos
    while i < len(text):
        c = text[i]
        if c in "'\"":
            got = read_string(text, i)
            if not got:
                return None
            i = got[1]
            continue
        if c in "([{":
            depth += 1
        elif c in ")]}":
            if depth == 0:
                args.append(text[start:i])
                return args
            depth -= 1
        elif c == "," and depth == 0:
            args.append(text[start:i])
            start = i + 1
        i += 1
    return None


def literal(src):
    """The value of a source fragment that is exactly one quoted string, else None."""
    src = re.sub(r"/\*.*?\*/", "", src, flags=re.S).strip()
    got = read_string(src, 0) if src else None
    return got[0] if got and got[1] == len(src) else None


def tracked_php():
    out = subprocess.check_output(["git", "ls-files", "*.php"], cwd=ROOT, text=True).split()
    return sorted(f for f in out if not f.startswith("tests/"))


def extract():
    entries = OrderedDict()
    for rel in tracked_php():
        text = (ROOT / rel).read_text(encoding="utf-8")
        for m in CALL.finditer(text):
            args = split_args(text, m.end())
            idx = FUNCTIONS[m.group(1)]
            if not args or len(args) <= idx[3] or literal(args[idx[3]]) != DOMAIN:
                continue
            msgid = literal(args[idx[0]])
            if not msgid:
                continue
            plural = literal(args[idx[1]]) if idx[1] is not None else None
            context = literal(args[idx[2]]) if idx[2] is not None else None
            line = text.count("\n", 0, m.start()) + 1
            before = text[max(0, m.start() - 400):m.start()].rstrip()
            note = NOTE.search(before) or NOTE.search(WRAPPERS.sub("", before).rstrip())
            key = (context, msgid, plural)
            e = entries.setdefault(key, {"refs": [], "notes": []})
            e["refs"].append("%s:%d" % (rel, line))
            if note:
                n = " ".join(note.group(1).split())
                if n not in e["notes"]:
                    e["notes"].append(n)
    return entries


def quote(s):
    return '"' + s.replace("\\", "\\\\").replace('"', '\\"').replace("\n", "\\n").replace("\t", "\\t") + '"'


def render(entries, version):
    lines = [
        "# Copyright (C) 2026 Remotive Media Asia Pte. Ltd.",
        "# This file is distributed under the GNU General Public License v2 or later.",
        'msgid ""',
        'msgstr ""',
        '"Project-Id-Version: Remotive Media %s\\n"' % version,
        '"Report-Msgid-Bugs-To: https://remotivemedia.asia/\\n"',
        '"POT-Creation-Date: %s\\n"' % datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%S+00:00"),
        '"MIME-Version: 1.0\\n"',
        '"Content-Type: text/plain; charset=UTF-8\\n"',
        '"Content-Transfer-Encoding: 8bit\\n"',
        '"Language-Team: \\n"',
        '"X-Domain: remotive\\n"',
        "",
    ]
    for (context, msgid, plural), e in sorted(entries.items(), key=lambda kv: (kv[0][1], kv[0][0] or "")):
        for n in e["notes"]:
            lines.append("#. translators: " + n)
        lines.append("#: " + " ".join(e["refs"]))
        if re.search(r"%(?:\d+\$)?[sdfu]", msgid) or (plural and re.search(r"%(?:\d+\$)?[sdfu]", plural)):
            lines.append("#, php-format")
        if context:
            lines.append("msgctxt " + quote(context))
        lines.append("msgid " + quote(msgid))
        if plural:
            lines.append("msgid_plural " + quote(plural))
            lines.append('msgstr[0] ""')
            lines.append('msgstr[1] ""')
        else:
            lines.append('msgstr ""')
        lines.append("")
    return "\n".join(lines)


def theme_version():
    m = re.search(r"^Version:\s*(\S+)", (ROOT / "style.css").read_text(encoding="utf-8"), re.M)
    return m.group(1) if m else "0"


def ids_on_disk():
    if not POT.exists():
        return set()
    text = POT.read_text(encoding="utf-8")
    found = set()
    for block in text.split("\n\n")[1:]:
        ctx = re.search(r'^msgctxt "(.*)"$', block, re.M)
        mid = re.search(r'^msgid "(.*)"$', block, re.M)
        pl = re.search(r'^msgid_plural "(.*)"$', block, re.M)
        if mid:
            found.add((ctx.group(1) if ctx else None, mid.group(1), pl.group(1) if pl else None))
    return found


def main():
    entries = extract()
    if "--check" in sys.argv:
        want = {(c, quote(m)[1:-1], quote(p)[1:-1] if p else None) for (c, m, p) in entries}
        want = {((quote(c)[1:-1] if c else None), m, p) for (c, m, p) in want}
        have = ids_on_disk()
        missing, extra = sorted(want - have, key=str), sorted(have - want, key=str)
        for kind, rows in (("in the code but not in languages/remotive.pot", missing), ("in languages/remotive.pot but no longer in the code", extra)):
            for row in rows[:10]:
                print("%s: %s" % (kind, row[1][:100]))
            if len(rows) > 10:
                print("... and %d more %s" % (len(rows) - 10, kind))
        header = re.search(r'^"Project-Id-Version: Remotive Media (\S+?)\\n"', POT.read_text(encoding="utf-8") if POT.exists() else "", re.M)
        stale = header.group(1) if header else "none"
        if stale != theme_version():
            print("the header of languages/remotive.pot says version %s but the theme is %s" % (stale, theme_version()))
        if missing or extra or stale != theme_version():
            print("Run: python3 tools/make-pot.py")
            return 1
        print("languages/remotive.pot lists all %d strings and carries version %s." % (len(want), theme_version()))
        return 0
    POT.write_text(render(entries, theme_version()), encoding="utf-8")
    print("Wrote %s with %d strings." % (POT.relative_to(ROOT), len(entries)))
    return 0


if __name__ == "__main__":
    sys.exit(main())
