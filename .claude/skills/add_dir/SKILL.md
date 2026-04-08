---
name: add_dir
description: Add an external directory as a reference context for the current conversation. Loads structure, docs, and key files.
argument-hint: "[directory-path]"
allowed-tools: Read Grep Glob Bash
---

# Add Directory Context

Add the directory `$ARGUMENTS` as reference context.

## Steps

1. Verify directory:
```!
ls "$ARGUMENTS" >/dev/null 2>&1 && echo "OK: $ARGUMENTS" || echo "ERROR: not found"
```

2. Project docs:
```!
cat "$ARGUMENTS/CLAUDE.md" 2>/dev/null; cat "$ARGUMENTS/README.md" 2>/dev/null; echo "---"
```

3. Structure:
```!
find "$ARGUMENTS" -maxdepth 2 -not -path '*/node_modules/*' -not -path '*/.git/*' -not -path '*/vendor/*' -not -path '*/__pycache__/*' -not -path '*/.next/*' -not -path '*/dist/*' -not -path '*/.venv/*' | head -80
```

4. Config:
```!
for f in package.json pyproject.toml go.mod Cargo.toml composer.json Gemfile requirements.txt tsconfig.json Makefile .env.example; do
  [ -f "$ARGUMENTS/$f" ] && echo "=== $f ===" && cat "$ARGUMENTS/$f" && echo ""
done
```

## After loading

Keep this directory's context available for the rest of the conversation. Summarize what the repo contains and note its path for future reference when the user asks questions that involve it.
