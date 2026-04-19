<!-- Add this to your settings view -->
<div class="form-group">
    <label for="footer_content">{{ __('admin_settings_pages.admin_settings.footer_content') }}</label>
    <textarea name="footer_content" id="footer_content" class="form-control">{{ \App\Models\Setting::where('key', 'footer_content')->value('value') }}</textarea>
</div>
