<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Language Contribution</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body style="background:#f5f7fb;">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="mb-2">Language Contribution</h2>
                    <p class="text-muted mb-4">
                        Submit your translated JSON package for <strong>{{ strtoupper($invitation->locale_code) }}</strong>.
                    </p>

                    @php
                        $missingEntries = $missingEntries ?? [];
                        $sourceModuleCount = $sourceModuleCount ?? 0;
                        $missingCount = 0;
                        foreach ($missingEntries as $moduleItems) {
                            $missingCount += is_array($moduleItems) ? count($moduleItems) : 0;
                        }
                    @endphp

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-4">
                        <h5 class="mb-3">Invitation Details</h5>
                        <div><strong>Contributor:</strong> {{ $invitation->contributor_name ?: 'Contributor' }}</div>
                        <div><strong>Email:</strong> {{ $invitation->contributor_email }}</div>
                        <div><strong>Status:</strong> {{ ucfirst($invitation->status) }}</div>
                        <div><strong>Expires:</strong> {{ optional($invitation->expires_at)->format('Y-m-d H:i') ?: 'No expiry' }}</div>
                    </div>

                    @if ($sourcePackage)
                        <div class="mb-4 p-3 border rounded bg-light">
                            <h5 class="mb-2">Source Package ({{ strtoupper($sourceLocale ?? 'en') }})</h5>
                            <p class="mb-2">Download the current source package, translate it, then upload the translated JSON below.</p>
                            <a class="btn btn-outline-primary"
                                         href="{{ route('language-marketplace.packages.download', ['package' => $sourcePackage->id]) }}">
                                Download source package
                            </a>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('language-marketplace.contribute.submit', ['token' => $invitation->invite_token]) }}" enctype="multipart/form-data">
                        @csrf

                    <div class="mb-4 p-3 border rounded bg-white">
                        <h5 class="mb-2">Auto-detected untranslated labels</h5>
                        <p class="text-muted mb-3">
                            We detected <strong>{{ $missingCount }}</strong> untranslated labels by comparing source {{ strtoupper($sourceLocale ?? 'en') }} with target {{ strtoupper($invitation->locale_code) }}.
                            Fill any fields below and submit directly.
                        </p>

                        @if ($sourceModuleCount === 0)
                            <div class="alert alert-warning">
                                No source modules were found for the selected source locale. Publish a valid source package first, or use EN source.
                            </div>
                        @endif

                        @if ($missingCount > 0)
                            <div style="max-height:420px; overflow:auto;">
                                @foreach ($missingEntries as $module => $items)
                                    <div class="border rounded p-2 mb-3">
                                        <h6 class="mb-2">Module: {{ $module }}</h6>
                                        @foreach ($items as $entry)
                                            <div class="form-group mb-2">
                                                <label class="mb-1 d-block">
                                                    <strong>{{ $entry['key'] }}</strong>
                                                    <small class="text-muted d-block">Source: {{ $entry['source'] }}</small>
                                                    @if (!empty($entry['current']))
                                                        <small class="text-muted d-block">Current: {{ $entry['current'] }}</small>
                                                    @endif
                                                </label>
                                                <input type="text"
                                                       class="form-control"
                                                       name="auto_translations[{{ $module }}][{{ $entry['key'] }}]"
                                                       value="{{ old('auto_translations.' . $module . '.' . $entry['key']) }}"
                                                       placeholder="Insert translation">
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-success mb-0">No untranslated labels detected for this language pair.</div>
                        @endif
                    </div>

                        <div class="form-group">
                            <label for="language_payload_file">Upload translated JSON file</label>
                            <input type="file" class="form-control" id="language_payload_file" name="language_payload_file" accept=".json,.txt">
                        </div>
                        <div class="form-group">
                            <label for="language_payload_json">Or paste translated JSON</label>
                            <textarea class="form-control" id="language_payload_json" name="language_payload_json" rows="10" placeholder='{"modules": {"messages": {"welcome": "Bonjour"}}}'></textarea>
                        </div>
                        <div class="form-group">
                            <label for="message">Message to reviewer</label>
                            <textarea class="form-control" id="message" name="message" rows="3" placeholder="Optional note for the admin reviewer"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit translation</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
