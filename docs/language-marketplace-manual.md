# Language Marketplace Manual

## 1. Overview
The Language Marketplace is the controlled translation pipeline for external contributors.

It is designed to ensure that:
- translators can contribute without admin panel access,
- every change is reviewed by an internal admin,
- production language files are updated only after approval,
- GitHub publishing can be enforced through Pull Requests.

This keeps translation quality, terminology consistency, and release governance under internal control.

## 2. Functional Goals
The feature supports five main goals:
1. Publish a source package for a locale.
2. Invite external contributors with a tokenized link.
3. Collect translated payloads from contributors.
4. Review and approve or reject submissions.
5. Sync approved packages to GitHub (direct commit or PR workflow).

## 3. Roles and Permissions
### Admin
- Publishes source packages.
- Creates contributor invitations.
- Reviews all submitted packages.
- Approves or rejects submissions.
- Triggers GitHub sync manually when needed.
- Downloads the manual from the Settings page.

### Contributor (external)
- Accesses only the invitation page.
- Downloads source package.
- Uploads or pastes translated package.
- Adds optional reviewer note.
- Cannot approve, publish, or sync to GitHub.

## 4. Data and Sources
When building source and detecting missing labels, the system aggregates translations from:
- PHP language module files in resources/lang/<locale>/*.php
- JSON language files in resources/lang/<locale>.json
- Database-backed translations in ltm_translations

This provides broader coverage and avoids blind spots in untranslated key detection.

## 5. End-to-End Workflow
### Step 1: Publish source package
1. Open Admin > Settings > Language.
2. In Language Marketplace Workflow, choose source locale.
3. Click Publish source package.

Result:
- A versioned source package is stored in marketplace storage.
- A docs copy is generated under docs/language-library/<locale>.json.

### Step 2: Invite contributor
1. Select target language.
2. Enter contributor name and email.
3. Click Create invite.

Result:
- A secure tokenized invitation is created.
- Admin shares the generated public contribution link.

### Step 3: Contributor submission
1. Contributor opens invite link.
2. Contributor downloads source package.
3. Contributor fills missing labels and uploads JSON or pastes payload.
4. Contributor submits translation.

Result:
- Submission package is stored with status submitted.
- It appears in admin review queue.

### Step 4: Admin review
Admin can:
- Download submission package.
- Validate terminology, formatting, and completeness.
- Reject with reviewer note.
- Approve and publish.

On approval:
- Translations are imported into language sources.
- Locale can be marked enabled.
- Invitation is updated as approved.

### Step 5: GitHub sync
Two options are supported:

Automatic sync:
- Runs right after approval when auto sync is enabled.

Manual sync:
- Admin clicks Sync GitHub from the published packages table.

## 6. GitHub Publishing Modes
### Mode A: Direct commit
The package file is committed directly to the configured branch.

### Mode B: Pull Request workflow (recommended)
The system:
1. Creates a dedicated branch for the package.
2. Commits the updated docs language file.
3. Opens a Pull Request toward the base branch.

Use this mode for external translator governance and formal review.

## 7. Environment Configuration
Set the following variables in .env:

- GITHUB_DOCS_SYNC_ENABLED: enables or disables GitHub synchronization entirely.
- GITHUB_DOCS_AUTO_SYNC_APPROVED: automatically starts sync right after admin approval.
- GITHUB_DOCS_USE_PULL_REQUESTS: creates a branch and Pull Request instead of committing directly to base branch.
- GITHUB_DOCS_TOKEN: GitHub API token used for authentication and repository operations.
- GITHUB_DOCS_OWNER: GitHub repository owner or organization.
- GITHUB_DOCS_REPO: repository name used for translation publication.
- GITHUB_DOCS_BRANCH: base branch used for direct publish or PR target.
- GITHUB_DOCS_PATH_PREFIX: repository folder where language files are written.
- GITHUB_DOCS_PR_TITLE_PREFIX: title prefix for automatic Pull Requests.
- GITHUB_DOCS_COMMITTER_NAME: commit author name used by the automation.
- GITHUB_DOCS_COMMITTER_EMAIL: commit author email used by the automation.

Recommended baseline:
- GITHUB_DOCS_SYNC_ENABLED=true
- GITHUB_DOCS_AUTO_SYNC_APPROVED=true
- GITHUB_DOCS_USE_PULL_REQUESTS=true

## 8. GitHub Token Requirements
The token used for sync should have minimum required permissions:
- Read/write repository contents
- Create branches
- Create Pull Requests

Avoid using personal admin tokens. Prefer a dedicated bot token.

## 9. Approval Policy Recommendations
For external translation quality control:
- Keep all contributor submissions in review-first flow.
- Require at least one internal linguistic review before approval.
- Enforce branch protection and required PR reviews on base branch.
- Reject submissions with inconsistent key naming or mixed terminology.
- Keep rejection notes explicit so contributors can fix quickly.

## 10. Operational Checklist
Before inviting contributors:
1. Publish latest source package.
2. Confirm untranslated labels detection is populated.
3. Confirm target locale exists.

Before approving a submission:
1. Download and inspect submission package.
2. Spot-check key critical modules (auth, validation, UI labels).
3. Confirm no placeholder text remains.
4. Add review note when rejecting.

Before syncing to GitHub:
1. Confirm repo and branch config are correct.
2. Confirm token scopes are valid.
3. Prefer PR mode for traceability.

## 11. Audit and Traceability
Each published package keeps sync metadata:
- sync status
- sync sha
- sync url (file or PR link)
- sync error message
- sync timestamp

Use these fields to diagnose failed syncs and to track what was published.

## 12. Troubleshooting
### No source package available
Cause: source was never published.
Fix: publish source package first.

### Contributor cannot proceed
Cause: invite expired or invalid token.
Fix: create a new invitation.

### Missing untranslated labels
Cause: source locale data not present in files/DB.
Fix: verify source locale has data in PHP, JSON, and DB tables.

### GitHub sync fails with auth error
Cause: invalid token or missing scopes.
Fix: rotate token and re-check permissions.

### PR creation fails
Cause: token cannot create branch/PR or repo policy blocks branch.
Fix: grant proper permissions and verify branch naming policies.

### Package imports but locale not visible
Cause: locale disabled.
Fix: enable locale from Language settings table.

## 13. Best Practices
- Keep source package publication frequent and versioned.
- Use clear contributor instructions and style guide per language.
- Review high-impact modules first (authentication, forms, error messages).
- Keep PR titles standardized for easier repository filtering.
- Periodically clean stale invitations and rejected drafts.

## 14. Versioning Note
This manual describes the Language Marketplace flow with admin-gated approval and GitHub PR-capable sync.
Update this document whenever workflow states, required fields, or sync behavior changes.
