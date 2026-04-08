---
name: add-context
description: Load context from an external directory/repository including CLAUDE.md, README, and project structure.
argument-hint: "[directory-path]"
allowed-tools: Read Grep Glob Bash
---

# Add External Repo Context

Load context from the repository at: `$ARGUMENTS`

## Steps

1. Verify the directory exists:
```!
ls "$ARGUMENTS" >/dev/null 2>&1 && echo "Directory found: $ARGUMENTS" || echo "ERROR: Directory not found"
```

2. Read **CLAUDE.md** if present (project conventions and instructions):
```!
cat "$ARGUMENTS/CLAUDE.md" 2>/dev/null || echo "No CLAUDE.md found"
```

3. Read **README.md** if present (project overview):
```!
cat "$ARGUMENTS/README.md" 2>/dev/null || echo "No README.md found"
```

4. Show **project structure** (top 2 levels):
```!
find "$ARGUMENTS" -maxdepth 2 -not -path '*/node_modules/*' -not -path '*/.git/*' -not -path '*/vendor/*' -not -path '*/__pycache__/*' -not -path '*/.next/*' -not -path '*/dist/*' | head -80
```

5. Show **key config files** if present:
```!
for f in package.json pyproject.toml go.mod Cargo.toml composer.json Gemfile requirements.txt tsconfig.json Makefile .env.example; do
  [ -f "$ARGUMENTS/$f" ] && echo "=== $f ===" && cat "$ARGUMENTS/$f" && echo ""
done
echo "Done scanning config files."
```

## After loading

Summarize what you learned about the external repo concisely, then ask the user how they'd like to use this context in the current project.
