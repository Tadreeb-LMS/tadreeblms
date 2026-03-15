<!-- Add this to your settings view -->
<div class="form-group">
    <label for="footer_content">Footer Content</label>
    <textarea name="footer_content" id="footer_content" class="form-control">{{ \App\Models\Setting::where('key', 'footer_content')->value('value') }}</textarea>
</div>
