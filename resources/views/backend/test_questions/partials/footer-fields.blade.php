@php
    $questionFooter = $question ?? null;
    $solutionValue = $questionFooter->solution ?? '';
    $commentValue = $questionFooter->comment ?? '';
    $scoreValue = $questionFooter->score ?? $questionFooter->marks ?? '';
@endphp

@push('after-styles')
<style>
    .question-roadmap {
        margin-top: 24px;
    }

    .question-roadmap-steps {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 22px;
    }

    .question-roadmap-step {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 54px;
        padding: 10px 12px;
        border: 1px solid #dfe7f3;
        border-radius: 10px;
        background: #fff;
        color: #536179;
        font-weight: 700;
        text-align: left;
        cursor: pointer;
    }

    .question-roadmap-step.is-hidden,
    .question-roadmap-panel.is-hidden {
        display: none !important;
    }

    .question-roadmap-step:hover,
    .question-roadmap-step.is-active {
        border-color: #233e74;
        color: #233e74;
        background: #f7faff;
    }

    .question-roadmap-step.is-complete {
        border-color: #bde7d6;
        background: #f2fbf7;
        color: #166445;
    }

    .question-roadmap-step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        border-radius: 50%;
        background: #e8eef9;
        color: #233e74;
        font-size: 0.85rem;
    }

    .question-roadmap-content {
        min-height: 360px;
    }

    .question-roadmap-panel {
        min-width: 0;
        margin: 0;
        padding: 0;
        overflow: hidden;
        background: #fff;
        border: 1px solid #dfe7f3;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(35, 62, 116, 0.06);
    }

    .question-roadmap.is-ready .question-roadmap-panel {
        display: none;
    }

    .question-roadmap.is-ready .question-roadmap-panel.is-active {
        display: block;
    }

    .question-roadmap-panel legend {
        display: flex;
        align-items: center;
        width: 100%;
        gap: 10px;
        margin: 0;
        padding: 16px 20px;
        background: #f7faff;
        border-bottom: 1px solid #dfe7f3;
        color: #233e74;
        font-size: 1rem;
        font-weight: 700;
    }

    .question-roadmap-panel-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border-radius: 50%;
        background: #e8eef9;
        color: #233e74;
    }

    .question-roadmap-panel-body {
        padding: 20px;
    }

    .question-roadmap-panel .form-control,
    .question-roadmap-panel .cke_chrome {
        background: #fff;
    }

    .question-roadmap-panel .cke_chrome {
        width: 100% !important;
    }

    .question-roadmap-score .form-control {
        width: 180px;
        max-width: 100%;
        min-height: 48px;
        font-size: 1rem;
        font-weight: 700;
        text-align: center;
    }

    .question-roadmap-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 18px;
    }

    .question-roadmap-actions .btn[disabled] {
        opacity: 0.55;
        cursor: not-allowed;
    }

    @media (max-width: 991.98px) {
        .question-roadmap-steps {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .question-roadmap-steps {
            grid-template-columns: 1fr;
        }

        .question-roadmap-panel legend,
        .question-roadmap-panel-body {
            padding: 15px;
        }

        .question-roadmap-score .form-control {
            width: 100%;
        }
    }
</style>
@endpush

@push('after-scripts')
<script>
    (function () {
        function editorValue(id) {
            if (typeof window.getEditorContent === 'function') {
                return window.getEditorContent(id);
            }

            if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances && CKEDITOR.instances[id]) {
                return CKEDITOR.instances[id].getData();
            }

            var field = document.getElementById(id);
            return field ? field.value : '';
        }

        function resizeEditors(panel) {
            if (typeof CKEDITOR === 'undefined' || !CKEDITOR.instances) {
                return;
            }

            panel.querySelectorAll('textarea.editor').forEach(function (textarea) {
                var editor = CKEDITOR.instances[textarea.id];
                if (editor) {
                    editor.resize('100%', editor.config.height || 180);
                }
            });
        }

        function setupQuestionRoadmap(root) {
            var steps = Array.prototype.slice.call(root.querySelectorAll('[data-roadmap-target]'));
            var panels = Array.prototype.slice.call(root.querySelectorAll('[data-roadmap-panel]'));
            var previousButton = root.querySelector('.question-roadmap-prev');
            var nextButton = root.querySelector('.question-roadmap-next');
            var activePanelName = 'question';

            function isShortAnswer() {
                var questionType = document.getElementById('question_type');
                return questionType && questionType.value === '3';
            }

            function isPanelAvailable(name) {
                return name !== 'options' || !isShortAnswer();
            }

            function availablePanels() {
                return panels.filter(function (panel) {
                    return isPanelAvailable(panel.getAttribute('data-roadmap-panel'));
                });
            }

            function currentPanelIndex() {
                return availablePanels().findIndex(function (panel) {
                    return panel.getAttribute('data-roadmap-panel') === activePanelName;
                });
            }

            function panelComplete(name) {
                if (!isPanelAvailable(name)) {
                    return false;
                }

                if (name === 'question') {
                    return editorValue('question').trim() !== '';
                }

                if (name === 'options') {
                    return Array.isArray(window.options) && window.options.length > 0;
                }

                if (name === 'marks') {
                    var score = document.getElementById('score');
                    return score && score.value !== '';
                }

                if (name === 'solution') {
                    return editorValue('solution').trim() !== '';
                }

                if (name === 'comment') {
                    return editorValue('comment').trim() !== '';
                }

                return false;
            }

            function updateProgress() {
                var visibleStepNumber = 1;

                steps.forEach(function (step) {
                    var target = step.getAttribute('data-roadmap-target');
                    var isAvailable = isPanelAvailable(target);
                    var stepNumber = step.querySelector('.question-roadmap-step-number');

                    step.classList.toggle('is-hidden', !isAvailable);
                    step.classList.toggle('is-complete', panelComplete(target));

                    if (isAvailable && stepNumber) {
                        stepNumber.textContent = visibleStepNumber;
                        visibleStepNumber += 1;
                    }
                });

                panels.forEach(function (panel) {
                    var target = panel.getAttribute('data-roadmap-panel');
                    panel.classList.toggle('is-hidden', !isPanelAvailable(target));
                });
            }

            function showPanel(index) {
                var visiblePanels = availablePanels();

                if (!visiblePanels.length) {
                    return;
                }

                index = Math.max(0, Math.min(index, visiblePanels.length - 1));
                activePanelName = visiblePanels[index].getAttribute('data-roadmap-panel');

                root.classList.add('is-ready');
                panels.forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.getAttribute('data-roadmap-panel') === activePanelName);
                });
                steps.forEach(function (step) {
                    step.classList.toggle('is-active', step.getAttribute('data-roadmap-target') === activePanelName);
                });

                if (previousButton) {
                    previousButton.disabled = index === 0;
                }

                if (nextButton) {
                    nextButton.disabled = index === visiblePanels.length - 1;
                    nextButton.textContent = 'Continue';
                }

                updateProgress();
                resizeEditors(visiblePanels[index]);
            }

            function showPanelByName(name) {
                var targetIndex = availablePanels().findIndex(function (panel) {
                    return panel.getAttribute('data-roadmap-panel') === name;
                });

                if (targetIndex >= 0) {
                    showPanel(targetIndex);
                } else {
                    showPanel(0);
                }
            }

            steps.forEach(function (step) {
                step.addEventListener('click', function () {
                    var target = step.getAttribute('data-roadmap-target');
                    showPanelByName(target);
                });
            });

            if (previousButton) {
                previousButton.addEventListener('click', function () {
                    showPanel(currentPanelIndex() - 1);
                });
            }

            if (nextButton) {
                nextButton.addEventListener('click', function () {
                    showPanel(currentPanelIndex() + 1);
                });
            }

            root.addEventListener('input', updateProgress);
            root.addEventListener('change', updateProgress);
            root.addEventListener('click', function () {
                window.setTimeout(updateProgress, 0);
            });

            if (typeof CKEDITOR !== 'undefined') {
                CKEDITOR.on('instanceReady', function (event) {
                    event.editor.on('change', updateProgress);
                    var visiblePanels = availablePanels();
                    var activeIndex = Math.max(0, currentPanelIndex());
                    resizeEditors(visiblePanels[activeIndex]);
                    updateProgress();
                });
            }

            var questionType = document.getElementById('question_type');
            if (questionType) {
                questionType.addEventListener('change', function () {
                    if (!isPanelAvailable(activePanelName)) {
                        activePanelName = 'question';
                    }

                    showPanelByName(activePanelName);
                });
            }

            if (typeof window.showOptions === 'function') {
                window.showOptions();
            }

            showPanel(0);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-question-roadmap]').forEach(setupQuestionRoadmap);
        });
    })();
</script>
@endpush

<fieldset class="question-roadmap-panel question-roadmap-score" data-roadmap-panel="marks">
    <legend>
        <span class="question-roadmap-panel-icon"><i class="fa fa-star"></i></span>
        Marks <span style="color:red">*</span>
    </legend>
    <div class="question-roadmap-panel-body">
        <input
            type="number"
            class="form-control"
            name="score"
            id="score"
            placeholder="Enter Marks"
            value="{{ $scoreValue }}"
            min="1"
            max="999"
            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,3);"
            required
        />
    </div>
</fieldset>

<fieldset class="question-roadmap-panel notextarea" data-roadmap-panel="solution">
    <legend>
        <span class="question-roadmap-panel-icon"><i class="fa fa-lightbulb-o"></i></span>
        Solution
    </legend>
    <div class="question-roadmap-panel-body">
        <textarea class="form-control textarea-col editor" rows="3" name="solution" id="solution" data-collapsible-toolbar="1">{{ $solutionValue }}</textarea>
    </div>
</fieldset>

<fieldset class="question-roadmap-panel notextarea" data-roadmap-panel="comment">
    <legend>
        <span class="question-roadmap-panel-icon"><i class="fa fa-comment-o"></i></span>
        Comment
    </legend>
    <div class="question-roadmap-panel-body">
        <textarea class="form-control textarea-col editor" rows="3" name="comment" id="comment" data-collapsible-toolbar="1">{{ $commentValue }}</textarea>
    </div>
</fieldset>