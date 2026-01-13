@php
    $step = $step ?? 1;
@endphp
<style>
/* ===== LMS Stepper ===== */
.stepper {
    display: flex;
    justify-content: space-between;
    list-style: none;
    padding: 0;
    margin-bottom: 30px;
    overflow: visible !important;
}

.stepper li {
    position: relative;
    flex: 1;
    text-align: center;
    overflow: visible !important;
}

/* Dotted connector line — FIXED */
.stepper li:not(:last-child)::after {
    content: "";
    position: absolute;
    top: 18px;
    left: 50%;
    width: 100%;
    height: 0;
    border-top: 2px dotted #ced4da;
    transform: translateX(18px);
    z-index: 0;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Step circle */
.stepper .circle {
    position: relative;
    z-index: 1;
    width: 36px;
    height: 36px;
    line-height: 36px;
    border-radius: 50%;
    display: inline-block;
    background: #dee2e6;
    color: #6c757d;
    font-weight: 600;
}

/* Step label */
.stepper .label {
    display: block;
    margin-top: 6px;
    font-size: 14px;
    color: #6c757d;
}

/* Completed step */
.stepper li.completed .circle {
    background: #a77c2a;
    color: #fff;
}

.stepper li.completed .label {
    color: #a77c2a;
    font-weight: 600;
}

.stepper li.completed:not(:last-child)::after {
    border-top-color: #a77c2a;
}

/* Active step */
.stepper li.active .circle {
    background: #a77c2a;
    color: #fff;
}

.stepper li.active .label {
    color: #a77c2a;
    font-weight: 600;
}

.stepper li.active:not(:last-child)::after {
    border-top-color: #a77c2a;
}

/* Mobile support */
@media (max-width: 576px) {
    .stepper {
        flex-direction: column;
    }

    .stepper li {
        margin-bottom: 20px;
    }

    .stepper li::after {
        display: none !important;
    }
}
</style>

<ul class="stepper stepper-horizontal mb-4">
    <li class="{{ $step > 1 ? 'completed' : ($step == 1 ? 'active' : '') }}">
        <span class="circle">1</span>
        <span class="label">Course</span>
    </li>

    {{-- <li class="{{ $step > 2 ? 'completed' : ($step == 2 ? 'active' : '') }}">
        <span class="circle">2</span>
        <span class="label">Lesson</span>
    </li> --}}

    <li class="{{ $step > 2 ? 'completed' : ($step == 2 ? 'active' : '') }}">
        <span class="circle">3</span>
        <span class="label">Questions</span>
    </li>

    <li class="{{ $step == 3 ? 'active' : '' }}">
        <span class="circle">3</span>
        <span class="label">Feedback</span>
    </li>
</ul>
