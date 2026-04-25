# 📥 Pull Request Template

## 🔧 Description

This PR fixes recipient selection logic and validation feedback for Send Email Notification.

Previously, the form allowed mixing recipient sources (Users, Department, Import, and Send to All), which caused ambiguous behavior and misleading errors. Department-only submissions could fail with a generic recipient error shown under Users even when Users were not selected.

What this PR changes:

- Enforces a single recipient source using `recipient_mode` (`users`, `department`, `import`, `all`).
- Processes recipient resolution in mutually exclusive backend branches.
- Returns source-specific validation errors (`users`, `department_id`, `import_users`, `recipient_mode`) instead of a generic fallback under the wrong field.
- Makes Send to All an exclusive mode in UI by removing the extra checkbox in that section.
- Adds/updates language keys for all supported admin translations used by this flow.
- Adds feature tests to cover the new behavior and prevent regressions.

Fixes: #

## ✅ Type of Change

- [x] 🐞 Bug fix
- [ ] ✨ New feature
- [ ] ♻️ Code refactor
- [ ] 📝 Documentation update
- [x] 🎨 UI/UX update
- [ ] 🔐 Security improvement
- [ ] 🔧 Other (please describe):

## 📸 Screenshots (if applicable)

N/A

## 🚀 How Has This Been Tested?

- [x] Local environment
- [ ] Browser testing
- [ ] Mobile testing
- [ ] Database migrations tested
- [x] Unit/Feature tests added
- [x] Other:
  - `vendor/bin/phpunit tests/Feature/Backend/Admin/EmailNotificationControllerTest.php`
  - Result: `OK (3 tests, 11 assertions)`

## 🧪 Steps to Reproduce (for bug fixes)

1. Go to Send Email Notification.
2. Select Department mode and choose a department with no mapped active users in `employee_profiles`.
3. Fill subject/register button/content and submit.
4. Confirm the API returns a `department_id` validation error with actionable text.
5. Repeat with Users mode and no selected users; confirm the error is attached to `users`.
6. Repeat with Department mode where mapped users exist; confirm notification is queued and dispatch job is pushed.

## 🔄 Checklist

- [x] Followed the project's coding guidelines
- [ ] Updated documentation (if needed)
- [x] Added/Updated tests (if applicable)
- [x] Verified no sensitive data is included
- [x] Ensured the app builds without errors

## 🙏 Additional Notes

- The commit intentionally includes only notification flow and related test/translation updates.
- Existing unrelated working-tree changes (asset file mode updates) were excluded from this PR scope.
