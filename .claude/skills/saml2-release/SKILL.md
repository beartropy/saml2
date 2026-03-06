---
name: saml2-release
description: Release a new version of Beartropy SAML2 — bumps version, updates changelog, commits, tags, and pushes
version: 1.0.0
author: Beartropy
tags: [beartropy, saml2, release, versioning, git, changelog]
---

# Release Beartropy SAML2

You are releasing a new version of the Beartropy SAML2 package. The user invokes this skill with `/release {type}` where `{type}` is one of: `major`, `minor`, or `patch`.

**Working directory:** The root of the `beartropy/saml2` package.

---

## Step-by-step procedure

### 1. Validate the argument

The first argument MUST be one of: `major`, `minor`, `patch`.

If missing or invalid, stop and ask the user:
> Please specify a release type: `/release major`, `/release minor`, or `/release patch`

### 2. Determine the new version

Read the current version from `composer.json` (the `"version"` field). Parse it as `MAJOR.MINOR.PATCH` and bump according to the argument:

| Type | Rule | Example (from 1.0.0) |
|---|---|---|
| `major` | MAJOR+1, reset MINOR and PATCH to 0 | `2.0.0` |
| `minor` | MINOR+1, reset PATCH to 0 | `1.1.0` |
| `patch` | PATCH+1 | `1.0.1` |

The tag name is `v{NEW_VERSION}` (e.g., `v1.0.1`).

### 3. Gather unreleased changes

Run `git log` from the last tag to HEAD to collect commit messages:

```bash
git log $(git describe --tags --abbrev=0)..HEAD --oneline --no-merges
```

Present the commits to the user and ask them to confirm or edit the changelog entry. Group changes under the appropriate heading:

- `### Added` — new features
- `### Changed` — modifications to existing behavior
- `### Fixed` — bug fixes
- `### Removed` — removed features or deprecated code

Only include headings that have entries. Follow the existing CHANGELOG.md format exactly:

```markdown
## [vX.Y.Z] - YYYY-MM-DD

### Added
- Description of what was added.

### Fixed
- Description of what was fixed.
```

### 4. Update CHANGELOG.md

Insert the new version entry at the top of the file, immediately after the `# Changelog` header and the "All notable changes" line. Preserve all existing entries below.

### 5. Update composer.json

Change the `"version"` field to the new version string (without the `v` prefix).

### 6. Run the test suite

Run the tests to ensure everything passes before releasing:

```bash
vendor/bin/pest
```

If any tests fail, stop and report the failures. Do NOT proceed with the release.

### 7. Review changes with the user

Show the user a summary of what will be committed:
- The new version number
- The changelog entry
- The composer.json version change
- Test results (all passing)

Ask for confirmation before proceeding with git operations.

### 8. Configure git and GitHub identity

Set the git user for the commit and ensure the `beartropy` GitHub account is active:

```bash
git config user.name "beartropy" && git config user.email "beartropy@gmail.com" && gh auth switch --user beartropy
```

### 9. Commit all changes

The commit message MUST follow the project convention. Use the version as the first line:

```
vX.Y.Z: Brief summary of the release

- Bullet points matching changelog entries
```

Use a heredoc to preserve formatting:

```bash
git add -A && git commit -m "$(cat <<'EOF'
vX.Y.Z: Brief summary

- Change 1
- Change 2
EOF
)"
```

### 10. Push the commit

```bash
git push
```

### 11. Create and push the tag

```bash
git tag vX.Y.Z && git push origin vX.Y.Z
```

### 12. Create a GitHub release

Create a GitHub release using `gh`, with the changelog entry as the release notes:

```bash
gh release create vX.Y.Z --title "vX.Y.Z" --notes "$(cat <<'EOF'
{paste the exact changelog body here — the ### Added/Changed/Fixed sections}
EOF
)"
```

### 13. Confirm success

Report the release summary:
- Version: `vX.Y.Z`
- Tag: pushed
- GitHub release: created
- Changelog: updated

Remind the user that the new version will be available on Packagist after it syncs (usually within minutes). Consumers should run `composer update beartropy/saml2` to pull the new version.
