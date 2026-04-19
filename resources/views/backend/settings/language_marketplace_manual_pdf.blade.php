<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Language Marketplace Manual</title>
    <style>
        @page {
            margin: 78px 42px 62px 42px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.55;
        }
        .doc-header {
            position: fixed;
            top: -62px;
            left: 0;
            right: 0;
            border-bottom: 1px solid #d8e1e8;
            padding-bottom: 8px;
        }
        .doc-header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .doc-header-left {
            width: 66%;
            vertical-align: top;
        }
        .doc-header-right {
            width: 34%;
            vertical-align: top;
            text-align: right;
            font-size: 10px;
            color: #5b6a76;
        }
        .logo {
            max-height: 36px;
            max-width: 190px;
            margin-bottom: 4px;
        }
        .doc-footer {
            position: fixed;
            bottom: -42px;
            left: 0;
            right: 0;
            border-top: 1px solid #d8e1e8;
            padding-top: 6px;
            font-size: 10px;
            color: #5b6a76;
        }
        .doc-footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        h1 {
            font-size: 24px;
            margin: 0 0 8px 0;
            color: #0f3a5d;
        }
        h2 {
            font-size: 14px;
            margin: 0 0 8px 0;
            color: #0f3a5d;
            border-bottom: 1px solid #d8e1e8;
            padding-bottom: 5px;
        }
        h3 {
            font-size: 12px;
            margin: 10px 0 6px 0;
            color: #123f66;
        }
        p {
            margin: 0 0 8px 0;
        }
        ul, ol {
            margin: 0 0 10px 18px;
            padding: 0;
        }
        li {
            margin-bottom: 4px;
        }
        .meta {
            font-size: 10px;
            color: #5b6a76;
            margin-bottom: 12px;
        }
        .note {
            background: #f4f8fb;
            border: 1px solid #d8e1e8;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .small {
            font-size: 10px;
            color: #556470;
        }
        .section-gap {
            margin-top: 4px;
        }
        .cover {
            border: 1px solid #d8e1e8;
            background: #f7fafc;
            padding: 18px 20px;
            margin-bottom: 16px;
        }
        .cover-subtitle {
            font-size: 12px;
            color: #4b5d6b;
            margin-bottom: 10px;
        }
        .cover-tag {
            display: inline-block;
            border: 1px solid #c8d8e5;
            color: #0f3a5d;
            background: #eef5fa;
            padding: 3px 8px;
            font-size: 10px;
            margin-right: 6px;
        }
        .summary-table,
        .env-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 0 0;
        }
        .summary-table td,
        .env-table td,
        .env-table th {
            border: 1px solid #d8e1e8;
            padding: 7px 8px;
            vertical-align: top;
        }
        .summary-label {
            width: 28%;
            font-weight: bold;
            color: #123f66;
            background: #f7fafc;
        }
        .section {
            margin-bottom: 14px;
            padding: 12px 14px;
            border: 1px solid #e1e8ee;
            background: #ffffff;
        }
        .step-box {
            border-left: 3px solid #0f3a5d;
            background: #f9fbfd;
            padding: 8px 10px;
            margin: 8px 0 10px 0;
        }
        .result-line {
            font-size: 10px;
            color: #4d6070;
            margin-top: 4px;
        }
        .env-table th {
            background: #f0f6fa;
            color: #0f3a5d;
            text-align: left;
            font-size: 10px;
        }
        .env-name {
            width: 30%;
            font-weight: bold;
            color: #123f66;
        }
        .keep {
            page-break-inside: avoid;
        }
        .two-col {
            width: 100%;
            border-collapse: collapse;
        }
        .two-col td {
            width: 50%;
            vertical-align: top;
            padding-right: 8px;
        }
    </style>
</head>
<body>
    <div class="doc-header">
        <table class="doc-header-table">
            <tr>
                <td class="doc-header-left">
                    @if (!empty($logoDataUri))
                        <img src="{{ $logoDataUri }}" alt="Logo" class="logo">
                    @endif
                </td>
                <td class="doc-header-right">
                    <div><strong>Language Marketplace Manual</strong></div>
                    <div>Version: {{ $manualVersion ?? 'v1.0' }}</div>
                    <div>Generated: {{ $generatedAt->format('Y-m-d H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="doc-footer">
        <table class="doc-footer-table">
            <tr>
                <td>{{ config('app.name', 'Application') }} - Translation Governance Document</td>
                <td style="text-align:right;"></td>
            </tr>
        </table>
    </div>

    <div class="cover keep">
        <h1>Language Marketplace Manual</h1>
        <div class="cover-subtitle">Controlled translation workflow for external contributors, internal approval, and GitHub publication governance.</div>
        <div class="meta">Version {{ $manualVersion ?? 'v1.0' }} - Generated: {{ $generatedAt->format('Y-m-d H:i') }}</div>
        <div>
            <span class="cover-tag">External Contributors</span>
            <span class="cover-tag">Admin Approval</span>
            <span class="cover-tag">GitHub Sync</span>
            <span class="cover-tag">PR Governance</span>
        </div>

        <table class="summary-table">
            <tr>
                <td class="summary-label">Primary goal</td>
                <td>Allow external translators to submit language updates without giving them direct publishing access.</td>
            </tr>
            <tr>
                <td class="summary-label">Approval model</td>
                <td>Every submission is reviewed by an admin before import and publication.</td>
            </tr>
            <tr>
                <td class="summary-label">Publication model</td>
                <td>Approved packages can be synchronized locally, committed directly, or published through Pull Requests.</td>
            </tr>
        </table>
    </div>

    <div class="section keep">
        <h2>1. Scope and Purpose</h2>
        <p>The Language Marketplace allows external translators to contribute safely while preserving full internal control over what is imported and published.</p>
        <ul>
            <li>Contributors cannot publish directly.</li>
            <li>All submissions pass through admin review.</li>
            <li>Approved content can be synchronized to GitHub with traceability.</li>
        </ul>
    </div>

    <div class="section keep">
        <h2>2. Roles</h2>
        <table class="two-col">
            <tr>
                <td>
                    <h3>Admin</h3>
                    <ul>
                        <li>Publishes source packages.</li>
                        <li>Invites contributors via tokenized links.</li>
                        <li>Reviews and approves or rejects submissions.</li>
                        <li>Triggers GitHub synchronization automatically or manually.</li>
                    </ul>
                </td>
                <td>
                    <h3>Contributor</h3>
                    <ul>
                        <li>Accesses only the public invitation page.</li>
                        <li>Downloads source package.</li>
                        <li>Uploads or pastes translated payload.</li>
                        <li>Submits translation for admin review.</li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>

    <div class="section keep">
        <h2>3. Translation Sources Coverage</h2>
        <p>Source aggregation and missing-label checks include the following sources:</p>
        <ul>
            <li>PHP translation modules: <span class="small">resources/lang/&lt;locale&gt;/*.php</span></li>
            <li>JSON translations: <span class="small">resources/lang/&lt;locale&gt;.json</span></li>
            <li>Database translations: <span class="small">ltm_translations</span></li>
        </ul>
    </div>

    <div class="section">
        <h2>4. Operational Workflow</h2>

        <div class="step-box keep">
            <h3>Step 1: Publish Source Package</h3>
            <ol>
                <li>Open Admin Settings &gt; Language.</li>
                <li>Select the source locale.</li>
                <li>Click <strong>Publish source package</strong>.</li>
            </ol>
            <div class="result-line">Result: a versioned source package is stored and a docs copy is generated.</div>
        </div>

        <div class="step-box keep">
            <h3>Step 2: Invite Contributor</h3>
            <ol>
                <li>Set target locale and contributor email.</li>
                <li>Create invite.</li>
                <li>Share the generated public contribution link.</li>
            </ol>
        </div>

        <div class="step-box keep">
            <h3>Step 3: Contributor Submission</h3>
            <ol>
                <li>Contributor opens the invite link.</li>
                <li>Downloads source package.</li>
                <li>Uploads translated JSON or pastes payload.</li>
                <li>Submits translation with an optional note.</li>
            </ol>
        </div>

        <div class="step-box keep">
            <h3>Step 4: Review and Decision</h3>
            <ul>
                <li><strong>Approve</strong>: imports translation package and publishes it.</li>
                <li><strong>Reject</strong>: keeps submission out of production and stores review notes.</li>
            </ul>
        </div>

        <div class="step-box keep">
            <h3>Step 5: GitHub Synchronization</h3>
            <ul>
                <li>Automatic after approval if enabled.</li>
                <li>Manual via <strong>Sync GitHub</strong> button otherwise.</li>
            </ul>
        </div>
    </div>

    <div class="section keep">
        <h2>5. GitHub Publishing Modes</h2>
        <h3>Direct Commit Mode</h3>
        <p>Writes the language file directly to the configured base branch.</p>

        <h3>Pull Request Mode (Recommended)</h3>
        <ol>
            <li>Create a dedicated branch for the package.</li>
            <li>Commit the language file update.</li>
            <li>Open a Pull Request to the base branch.</li>
        </ol>
        <p>This mode is preferred for external contributor governance and auditability.</p>
    </div>

    <div class="section">
        <h2>6. Required Environment Variables</h2>
        <p>The following environment variables control how approved language packages are synchronized to GitHub.</p>
        <table class="env-table">
            <thead>
                <tr>
                    <th>Variable</th>
                    <th>Purpose</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="env-name">GITHUB_DOCS_SYNC_ENABLED</td>
                    <td>Enables or disables GitHub synchronization entirely. If false, approved packages remain local and no GitHub action is attempted.</td>
                </tr>
                <tr>
                    <td class="env-name">GITHUB_DOCS_AUTO_SYNC_APPROVED</td>
                    <td>If true, synchronization starts automatically right after admin approval. If false, admins must trigger sync manually.</td>
                </tr>
                <tr>
                    <td class="env-name">GITHUB_DOCS_USE_PULL_REQUESTS</td>
                    <td>If true, the system creates a branch and opens a Pull Request instead of committing directly to the base branch.</td>
                </tr>
                <tr>
                    <td class="env-name">GITHUB_DOCS_TOKEN</td>
                    <td>GitHub API token used for authentication. It must allow repository content updates and, in PR mode, branch and PR creation.</td>
                </tr>
                <tr>
                    <td class="env-name">GITHUB_DOCS_OWNER</td>
                    <td>Repository owner or organization where language files are synchronized.</td>
                </tr>
                <tr>
                    <td class="env-name">GITHUB_DOCS_REPO</td>
                    <td>Target repository name that stores the translation library files.</td>
                </tr>
                <tr>
                    <td class="env-name">GITHUB_DOCS_BRANCH</td>
                    <td>Base branch used as publication target. In PR mode this is the branch that receives the Pull Request.</td>
                </tr>
                <tr>
                    <td class="env-name">GITHUB_DOCS_PATH_PREFIX</td>
                    <td>Folder path inside the repository where language JSON files are written, for example <span class="small">docs/language-library</span>.</td>
                </tr>
                <tr>
                    <td class="env-name">GITHUB_DOCS_PR_TITLE_PREFIX</td>
                    <td>Standard prefix used for automatically created Pull Request titles to keep repository history clean and searchable.</td>
                </tr>
                <tr>
                    <td class="env-name">GITHUB_DOCS_COMMITTER_NAME</td>
                    <td>Display name used in GitHub commits created by the automation.</td>
                </tr>
                <tr>
                    <td class="env-name">GITHUB_DOCS_COMMITTER_EMAIL</td>
                    <td>Email address associated with the automated commit author.</td>
                </tr>
            </tbody>
        </table>
        <div class="note">
            Recommended production setup for external contributors: enable GitHub sync, enable automatic sync after approval, and keep Pull Request mode enabled so every approved translation still passes through repository review rules.
        </div>
    </div>

    <div class="section keep">
        <h2>7. Governance Policy</h2>
        <ul>
            <li>Keep PR mode enabled for external contributions.</li>
            <li>Protect base branch and require reviews.</li>
            <li>Use least-privilege bot token for GitHub automation.</li>
            <li>Reject inconsistent terminology and unresolved placeholders.</li>
        </ul>
    </div>

    <div class="section keep">
        <h2>8. Admin Checklist</h2>
        <table class="two-col">
            <tr>
                <td>
                    <h3>Before Inviting</h3>
                    <ol>
                        <li>Publish current source package.</li>
                        <li>Validate source locale completeness.</li>
                        <li>Confirm target locale configuration.</li>
                    </ol>
                </td>
                <td>
                    <h3>Before Approving</h3>
                    <ol>
                        <li>Download and inspect submission package.</li>
                        <li>Review critical modules such as auth, validation, and UI messages.</li>
                        <li>Confirm style consistency and no placeholder values.</li>
                    </ol>
                </td>
            </tr>
        </table>
    </div>

    <div class="section keep">
        <h2>9. Troubleshooting</h2>
        <ul>
            <li><strong>No source package found</strong>: publish source package first.</li>
            <li><strong>Invite invalid or expired</strong>: generate a new invite.</li>
            <li><strong>GitHub authentication failure</strong>: check token validity and scopes.</li>
            <li><strong>PR creation failure</strong>: verify branch and PR permissions in the repository.</li>
            <li><strong>Locale not visible after import</strong>: ensure the locale is enabled.</li>
        </ul>
    </div>

    <div class="section keep">
        <h2>10. Audit and Traceability</h2>
        <p>Each package stores synchronization metadata for diagnostics and accountability:</p>
        <ul>
            <li>sync status</li>
            <li>sync SHA</li>
            <li>sync URL (file or PR)</li>
            <li>sync error</li>
            <li>sync timestamp</li>
        </ul>
    </div>

    <p class="small">Version note: update this manual whenever workflow steps, approval rules, or GitHub sync behavior changes.</p>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font('DejaVu Sans', 'normal');
            $pdf->page_text(500, 812, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 10, [0.36, 0.42, 0.46]);
        }
    </script>
</body>
</html>
