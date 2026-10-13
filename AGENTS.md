## Token-efficient code navigation

Before reading large files or directories:

1. Use `rg` to locate relevant symbols, routes, classes, methods, config keys, error strings, and filenames.
2. Read only the smallest relevant section around each match.
3. Do not scan entire repositories or large generated directories unless absolutely necessary.
4. Exclude:
   - node_modules
   - vendor
   - .git
   - storage/logs
   - storage/framework
   - bootstrap/cache
   - build
   - dist
   - .next
   - coverage
   - tmp
   - cache directories
5. Prefer:
   - `rg -n`
   - `rg -l`
   - `git diff`
   - `git status --short`
   - `git log -n`
   - targeted `sed -n 'START,ENDp'`
   - targeted `head` / `tail`
   before opening complete files.

## Context preservation

Treat confirmed results from earlier steps as persistent facts for the current task.

Do not repeat checks that already succeeded unless:
- new evidence contradicts them,
- the relevant file/config changed,
- a command may have invalidated the result,
- or verification is necessary before a destructive operation.

Examples:
- If PHP 8.3 was already confirmed, do not check PHP version again.
- If the database host was already confirmed, do not ask for it again.
- If a file was already confirmed to exist, do not repeatedly run `ls` for it.
- If Composer installation succeeded, do not rerun Composer merely to verify it.
- If a route/class/config was already inspected and has not changed, reuse that result.

## Incremental debugging

Always continue from the latest known state instead of restarting troubleshooting.

Before proposing commands:
1. Identify the last confirmed successful step.
2. Identify the first unresolved failure.
3. Investigate only that failure.
4. Do not revisit already resolved earlier failures.

Use the pattern:

Known working:
- ...
- ...

Current failure:
- ...

Next smallest diagnostic:
- ...

Do not repeat the full troubleshooting history unless explicitly requested.

## Minimal command execution

Prefer one targeted diagnostic command over many broad commands.

Bad:
```bash
ls -la
find .
cat composer.json
cat .env
php -v
composer --version
git status
