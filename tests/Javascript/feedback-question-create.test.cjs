// Run with: node --test tests/Javascript/feedback-question-create.test.cjs
const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { join } = require('node:path');
const { runInNewContext } = require('node:vm');

const view = readFileSync(join(__dirname,
    '../../resources/views/backend/feedback/feedback-question-create.blade.php'), 'utf8');

function saveRequest(hasCourse) {
    // Execute the real save function, replacing only its server-rendered values.
    const start = view.indexOf('function sendData(data) {');
    const end = view.indexOf("$(document).on('change', '#question_type'", start);
    assert.ok(start >= 0 && end > start, 'The feedback save function is available');
    const script = view.slice(start, end)
        .replace(/@if\(isset\(\$course->id\)\)([\s\S]*?)@else([\s\S]*?)@endif/,
            (_, course, standalone) => hasCourse ? course : standalone)
        .replace(/\{\{\s*URL::to\('user\/course-feedback-create'\)\s*\}\}/g,
            '/user/course-feedback-create')
        .replace(/\{\{\s*route\('admin.feedback_question.index'\)\s*\}\}/g,
            '/user/feedback-questions')
        .replace(/\{\{\s*\$course->id\s*\}\}/g, '42')
        .replace(/@json\(__\('([^']+)'\)\)/g,
            (_, key) => JSON.stringify('Translation: ' + key));

    const notifications = [];
    const redirects = [];
    const alerts = [];
    let request;
    let dismissNotification;
    const notificationClosed = new Promise(resolve => { dismissNotification = resolve; });

    runInNewContext(script + '\nsendData();', {
        dataCollection: () => ({ question: 'Test Question?', options: '[]' }),
        question_submit_url: '/user/feedback-question-multiple-store',
        $: { ajax: options => { request = options; } },
        Swal: { fire: options => { notifications.push(options); return notificationClosed; } },
        window: { location: { replace: url => redirects.push(url) } },
        alert: message => alerts.push(message),
    });

    assert.ok(request, 'Submitting the form starts the AJAX request');
    assert.equal(notifications.length, 0, 'Success is not shown before the response');
    assert.deepEqual(redirects, []);
    return { request, notifications, redirects, alerts, dismissNotification };
}

for (const hasCourse of [false, true]) {
    const context = hasCourse ? 'from a course' : 'standalone';

    test(`creating a question ${context} shows success before redirecting`, async () => {
        const result = saveRequest(hasCourse);
        result.request.success(JSON.stringify({ code: 200, message: 'Question Inserted' }));

        assert.equal(result.notifications.length, 1);
        const notification = result.notifications[0];
        assert.equal(notification.icon, 'success');
        assert.ok(notification.text, 'The notification explains the successful save');
        assert.ok(notification.timer > 0, 'The notification dismisses automatically');
        assert.equal(notification.showConfirmButton, false);
        assert.deepEqual(result.redirects, [], 'Navigation waits until the notification closes');
        assert.deepEqual(result.alerts, []);

        result.dismissNotification();
        await Promise.resolve();

        assert.deepEqual(result.redirects, [hasCourse
            ? '/user/course-feedback-create?course_id=42'
            : '/user/feedback-questions']);
        assert.equal(result.notifications.length, 1);
    });

    test(`a rejected save ${context} preserves the error alert without success`, () => {
        const result = saveRequest(hasCourse);
        result.request.success(JSON.stringify({ code: 422, message: 'Save failed' }));

        assert.deepEqual(result.alerts, ['Save failed']);
        assert.equal(result.notifications.length, 0);
        assert.deepEqual(result.redirects, []);
    });

}
