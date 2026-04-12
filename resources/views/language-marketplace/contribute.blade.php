<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('language_marketplace_pages.contribute.page_title') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body style="background:#f5f7fb;">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="mb-2">{{ __('language_marketplace_pages.contribute.title') }}</h2>
                    <p class="text-muted mb-4">
                        {{ __('language_marketplace_pages.contribute.subtitle', ['locale' => strtoupper($invitation->locale_code)]) }}
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
                        <h5 class="mb-3">{{ __('language_marketplace_pages.contribute.invitation_details') }}</h5>
                        <div><strong>{{ __('language_marketplace_pages.contribute.contributor') }}:</strong> {{ $invitation->contributor_name ?: __('language_marketplace_pages.contribute.default_contributor') }}</div>
                        <div><strong>{{ __('language_marketplace_pages.contribute.email') }}:</strong> {{ $invitation->contributor_email }}</div>
                        <div><strong>{{ __('language_marketplace_pages.contribute.status') }}:</strong> {{ ucfirst($invitation->status) }}</div>
                        <div><strong>{{ __('language_marketplace_pages.contribute.expires') }}:</strong> {{ optional($invitation->expires_at)->format('Y-m-d H:i') ?: __('language_marketplace_pages.contribute.no_expiry') }}</div>
                    </div>

                    @if ($sourcePackage)
                        <div class="mb-4 p-3 border rounded bg-light">
                            <h5 class="mb-2">{{ __('language_marketplace_pages.contribute.source_package', ['locale' => strtoupper($sourceLocale ?? 'en')]) }}</h5>
                            <p class="mb-2">{{ __('language_marketplace_pages.contribute.source_package_help') }}</p>
                            <a class="btn btn-outline-primary"
                                         href="{{ route('language-marketplace.packages.download', ['package' => $sourcePackage->id]) }}">
                                {{ __('language_marketplace_pages.contribute.download_source_package') }}
                            </a>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('language-marketplace.contribute.submit', ['token' => $invitation->invite_token]) }}" enctype="multipart/form-data">
                        @csrf

                    <div class="mb-4 p-3 border rounded bg-white">
                        <h5 class="mb-2">{{ __('language_marketplace_pages.contribute.auto_detected_title') }}</h5>
                        <p class="text-muted mb-3">
                            {{ __('language_marketplace_pages.contribute.auto_detected_desc', ['count' => $missingCount, 'source' => strtoupper($sourceLocale ?? 'en'), 'target' => strtoupper($invitation->locale_code)]) }}
                        </p>

                        @if ($sourceModuleCount === 0)
                            <div class="alert alert-warning">
                                {{ __('language_marketplace_pages.contribute.no_source_modules') }}
                            </div>
                        @endif

                        @if ($missingCount > 0)
                            <div style="max-height:420px; overflow:auto;">
                                @foreach ($missingEntries as $module => $items)
                                    <div class="border rounded p-2 mb-3">
                                        <h6 class="mb-2">{{ __('language_marketplace_pages.contribute.module') }}: {{ $module }}</h6>
                                        @foreach ($items as $entry)
                                            <div class="form-group mb-2">
                                                <label class="mb-1 d-block">
                                                    <strong>{{ $entry['key'] }}</strong>
                                                    <small class="text-muted d-block">{{ __('language_marketplace_pages.contribute.source') }}: {{ $entry['source'] }}</small>
                                                    @if (!empty($entry['current']))
                                                        <small class="text-muted d-block">{{ __('language_marketplace_pages.contribute.current') }}: {{ $entry['current'] }}</small>
                                                    @endif
                                                </label>
                                                <input type="text"
                                                       class="form-control"
                                                       name="auto_translations[{{ $module }}][{{ $entry['key'] }}]"
                                                       value="{{ old('auto_translations.' . $module . '.' . $entry['key']) }}"
                                                       placeholder="{{ __('language_marketplace_pages.contribute.insert_translation') }}">
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-success mb-0">{{ __('language_marketplace_pages.contribute.no_untranslated_labels') }}</div>
                        @endif
                    </div>

                        <div class="form-group">
                            <label for="language_payload_file">{{ __('language_marketplace_pages.contribute.upload_translated_json_file') }}</label>
                            <input type="file" class="form-control" id="language_payload_file" name="language_payload_file" accept=".json,.txt">
                        </div>
                        <div class="form-group">
                            <label for="language_payload_json">{{ __('language_marketplace_pages.contribute.or_paste_translated_json') }}</label>
                            <textarea class="form-control" id="language_payload_json" name="language_payload_json" rows="10" placeholder='{"modules": {"messages": {"welcome": "Bonjour"}}}'></textarea>
                        </div>
                        <div class="form-group">
                            <label for="message">{{ __('language_marketplace_pages.contribute.message_to_reviewer') }}</label>
                            <textarea class="form-control" id="message" name="message" rows="3" placeholder="{{ __('language_marketplace_pages.contribute.optional_note') }}"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('language_marketplace_pages.contribute.submit_translation') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
