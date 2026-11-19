// Run with: node --test tests/Javascript/assessment-fullscreen-toggle.test.cjs
const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { join } = require('node:path');
const { runInNewContext } = require('node:vm');

const views = [
    'frontend/assignment/question_set.blade.php',
    'frontend/assignment/manual_question_set.blade.php',
].map((p) => ({
    name: p,
    source: readFileSync(join(__dirname, '../../resources/views', p), 'utf8'),
}));

function makeEnv({ supported = true, prefixed = false } = {}) {
    const listeners = {};
    const button = {
        hidden: true,
        textContent: '',
        title: '',
        attrs: {},
        clickHandler: null,
        setAttribute(k, v) { this.attrs[k] = String(v); },
        addEventListener(type, fn) { if (type === 'click') this.clickHandler = fn; },
    };
    const root = { requested: 0 };
    const doc = {
        fullscreenElement: null,
        webkitFullscreenElement: null,
        fullscreenEnabled: supported,
        webkitFullscreenEnabled: supported,
        exitCalls: 0,
        documentElement: root,
        getElementById(id) { return id === 'assessment-fullscreen-toggle' ? button : null; },
        addEventListener(type, fn) { (listeners[type] = listeners[type] || []).push(fn); },
        fire(type) { (listeners[type] || []).forEach((fn) => fn()); },
    };
    const pending = [];
    if (prefixed) {
        // Older WebKit-style API only.
        root.webkitRequestFullscreen = () => {
            root.requested++;
            doc.webkitFullscreenElement = root;
            return undefined;
        };
        doc.webkitExitFullscreen = () => { doc.exitCalls++; doc.webkitFullscreenElement = null; };
    } else {
        root.requestFullscreen = () => {
            root.requested++;
            doc.fullscreenElement = root;
            return Promise.resolve();
        };
        doc.exitFullscreen = () => {
            doc.exitCalls++;
            doc.fullscreenElement = null;
            return Promise.resolve();
        };
    }
    return { doc, button, root, pending };
}

function runToggle(source, env) {
    const start = source.indexOf('// assessment-fullscreen-toggle:start');
    const end = source.indexOf('// assessment-fullscreen-toggle:end');
    assert.ok(start >= 0 && end > start, 'The fullscreen toggle script is present');
    const script = source.slice(start, end).replace(/\r/g, '');
    runInNewContext(script, { document: env.doc });
}

for (const view of views) {
    test(`${view.name} renders a keyboard-focusable fullscreen button in the header`, () => {
        const header = view.source.slice(view.source.indexOf('<div class="assessment-meta">'));
        const buttonTag = header.slice(0, header.indexOf('</button>') + '</button>'.length);
        assert.match(buttonTag, /<button type="button"/);
        assert.match(buttonTag, /id="assessment-fullscreen-toggle"/);
        assert.match(buttonTag, /aria-pressed="false"/);
        assert.match(buttonTag, /\bhidden>/, 'Button stays hidden until support is detected');
        assert.match(buttonTag, /Fullscreen<\/button>$/);
    });

    test(`${view.name} enters and exits fullscreen and keeps the label in sync`, () => {
        const env = makeEnv();
        runToggle(view.source, env);
        assert.equal(env.button.hidden, false, 'Button is shown when the Fullscreen API is supported');
        assert.equal(env.button.attrs['aria-pressed'], 'false');

        env.button.clickHandler();
        assert.equal(env.root.requested, 1, 'Clicking requests fullscreen on the document element');
        env.doc.fire('fullscreenchange');
        assert.equal(env.button.textContent, 'Exit Fullscreen');
        assert.equal(env.button.attrs['aria-pressed'], 'true');

        // Escape or the browser exiting fullscreen also fires fullscreenchange.
        env.doc.fullscreenElement = null;
        env.doc.fire('fullscreenchange');
        assert.equal(env.button.textContent, '⛶ Fullscreen');
        assert.equal(env.button.attrs['aria-pressed'], 'false');

        env.doc.fullscreenElement = env.root;
        env.doc.fire('fullscreenchange');
        env.button.clickHandler();
        assert.equal(env.doc.exitCalls, 1, 'Clicking while fullscreen exits it');
    });

    test(`${view.name} stays hidden when fullscreen is unsupported`, () => {
        const env = makeEnv({ supported: false });
        runToggle(view.source, env);
        assert.equal(env.button.hidden, true);
        assert.equal(env.button.clickHandler, null);
    });

    test(`${view.name} falls back to the webkit-prefixed API`, () => {
        const env = makeEnv({ prefixed: true });
        runToggle(view.source, env);
        assert.equal(env.button.hidden, false);
        env.button.clickHandler();
        assert.equal(env.root.requested, 1);
        env.doc.fire('webkitfullscreenchange');
        assert.equal(env.button.textContent, 'Exit Fullscreen');
    });
}
