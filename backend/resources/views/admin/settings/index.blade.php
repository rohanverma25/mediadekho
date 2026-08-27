@extends('admin.layouts.app')

@section('title', 'Site Settings')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
@endpush

@section('content')
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header">Branding</div>
            <div class="card-body">
                <label class="form-label">Logo</label>
                <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Leave blank to keep the current logo. Falls back to the default "MD" mark on the storefront if none is set.</div>
                @if ($setting->logo_url)
                    <img src="{{ $setting->logo_url }}" alt="Current logo" class="mt-2 rounded border" width="120" height="120" style="object-fit:contain;">
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Contact Info</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror" value="{{ old('contact_phone', $setting->contact_phone) }}">
                        @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror" value="{{ old('contact_email', $setting->contact_email) }}">
                        @error('contact_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="contact_address" class="form-control @error('contact_address') is-invalid @enderror" rows="1">{{ old('contact_address', $setting->contact_address) }}</textarea>
                        @error('contact_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Additional Emails <span class="text-muted small">(shown with a title on the Contact Us page)</span></span>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddEmail"><i class="bi bi-plus-lg"></i> Add Email</button>
            </div>
            <div class="card-body">
                <div id="contactEmailsRows">
                    @foreach (old('contact_emails', $setting->contact_emails ?? []) as $index => $row)
                        <div class="row g-2 mb-2 contact-email-row">
                            <div class="col-md-4">
                                <input type="text" name="contact_emails[{{ $index }}][title]" class="form-control" placeholder="Title (e.g. Sales)" value="{{ $row['title'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="contact_emails[{{ $index }}][email]" class="form-control" placeholder="email@mediadekho.com" value="{{ $row['email'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 btn-remove-email"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-muted small mb-0" id="noEmailsHint" style="{{ old('contact_emails', $setting->contact_emails ?? []) ? 'display:none;' : '' }}">No additional emails added yet.</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Office Addresses <span class="text-muted small">(shown with a title on the Contact Us page)</span></span>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddAddress"><i class="bi bi-plus-lg"></i> Add Address</button>
            </div>
            <div class="card-body">
                <div id="contactAddressesRows">
                    @foreach (old('contact_addresses', $setting->contact_addresses ?? []) as $index => $row)
                        <div class="row g-2 mb-2 contact-address-row">
                            <div class="col-md-3">
                                <input type="text" name="contact_addresses[{{ $index }}][title]" class="form-control" placeholder="Title (e.g. Head Office)" value="{{ $row['title'] ?? '' }}">
                            </div>
                            <div class="col-md-7">
                                <textarea name="contact_addresses[{{ $index }}][address]" class="form-control" rows="1" placeholder="Full address">{{ $row['address'] ?? '' }}</textarea>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 btn-remove-address"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-muted small mb-0" id="noAddressesHint" style="{{ old('contact_addresses', $setting->contact_addresses ?? []) ? 'display:none;' : '' }}">No additional addresses added yet.</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Map</div>
            <div class="card-body">
                <label class="form-label">Google Maps Embed URL <span class="text-muted">(optional)</span></label>
                <input type="text" name="map_embed_url" class="form-control @error('map_embed_url') is-invalid @enderror" placeholder="https://www.google.com/maps/embed?..." value="{{ old('map_embed_url', $setting->map_embed_url) }}">
                @error('map_embed_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">
                    Open your location on Google Maps → Share → Embed a map → copy the <code>src</code> URL from the iframe and paste it here.
                    Leave blank and the Contact Us page will build a basic map automatically from your primary address instead.
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Footer Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Footer Description</label>
                    <textarea name="footer_description" class="form-control @error('footer_description') is-invalid @enderror" rows="3">{{ old('footer_description', $setting->footer_description) }}</textarea>
                    @error('footer_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><i class="bi bi-facebook"></i> Facebook URL</label>
                        <input type="url" name="facebook_url" class="form-control @error('facebook_url') is-invalid @enderror" value="{{ old('facebook_url', $setting->facebook_url) }}">
                        @error('facebook_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><i class="bi bi-instagram"></i> Instagram URL</label>
                        <input type="url" name="instagram_url" class="form-control @error('instagram_url') is-invalid @enderror" value="{{ old('instagram_url', $setting->instagram_url) }}">
                        @error('instagram_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><i class="bi bi-linkedin"></i> LinkedIn URL</label>
                        <input type="url" name="linkedin_url" class="form-control @error('linkedin_url') is-invalid @enderror" value="{{ old('linkedin_url', $setting->linkedin_url) }}">
                        @error('linkedin_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><i class="bi bi-youtube"></i> YouTube URL</label>
                        <input type="url" name="youtube_url" class="form-control @error('youtube_url') is-invalid @enderror" value="{{ old('youtube_url', $setting->youtube_url) }}">
                        @error('youtube_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Payment Gateway (Razorpay)</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Key ID</label>
                        <input type="text" name="razorpay_key_id" class="form-control @error('razorpay_key_id') is-invalid @enderror" value="{{ old('razorpay_key_id', $setting->razorpay_key_id) }}" placeholder="rzp_test_xxxxxxxxxxxx">
                        @error('razorpay_key_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Key Secret</label>
                        <input type="password" name="razorpay_key_secret" class="form-control @error('razorpay_key_secret') is-invalid @enderror" placeholder="{{ $setting->razorpay_key_secret ? '•••••••• (unchanged — leave blank to keep current secret)' : 'Not set' }}" autocomplete="new-password">
                        @error('razorpay_key_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-text">
                    Get these from your Razorpay Dashboard → Settings → API Keys. Use Test Mode keys (<code>rzp_test_...</code>) while developing — the Key Secret is stored encrypted and never shown again once saved.
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Email / SMTP</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control @error('smtp_host') is-invalid @enderror" value="{{ old('smtp_host', $setting->smtp_host) }}" placeholder="smtp.gmail.com">
                        @error('smtp_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Port</label>
                        <input type="text" name="smtp_port" class="form-control @error('smtp_port') is-invalid @enderror" value="{{ old('smtp_port', $setting->smtp_port) }}" placeholder="587">
                        @error('smtp_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Encryption</label>
                        <select name="smtp_encryption" class="form-select @error('smtp_encryption') is-invalid @enderror">
                            <option value="" @selected(old('smtp_encryption', $setting->smtp_encryption) === null || old('smtp_encryption', $setting->smtp_encryption) === '')>None</option>
                            <option value="tls" @selected(old('smtp_encryption', $setting->smtp_encryption) === 'tls')>TLS</option>
                            <option value="ssl" @selected(old('smtp_encryption', $setting->smtp_encryption) === 'ssl')>SSL</option>
                        </select>
                        @error('smtp_encryption')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_username" class="form-control @error('smtp_username') is-invalid @enderror" value="{{ old('smtp_username', $setting->smtp_username) }}">
                        @error('smtp_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_password" class="form-control @error('smtp_password') is-invalid @enderror" placeholder="{{ $setting->smtp_password ? '•••••••• (unchanged — leave blank to keep current password)' : 'Not set' }}" autocomplete="new-password">
                        @error('smtp_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">From Address</label>
                        <input type="email" name="mail_from_address" class="form-control @error('mail_from_address') is-invalid @enderror" value="{{ old('mail_from_address', $setting->mail_from_address) }}" placeholder="hello@mediadekho.com">
                        @error('mail_from_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">From Name</label>
                        <input type="text" name="mail_from_name" class="form-control @error('mail_from_name') is-invalid @enderror" value="{{ old('mail_from_name', $setting->mail_from_name) }}" placeholder="Media Dekho">
                        @error('mail_from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Notification Email <span class="text-muted">(admin copies go here)</span></label>
                        <input type="email" name="notification_email" class="form-control @error('notification_email') is-invalid @enderror" value="{{ old('notification_email', $setting->notification_email) }}" placeholder="{{ $setting->contact_email ?: 'defaults to Contact Email above' }}">
                        @error('notification_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-text">
                    Leave the host blank to keep emails going to the server's log only (nothing is actually delivered). Fill these in with a real SMTP provider (Gmail app password, Hostinger's own mail account, Mailgun, etc.) to start sending real emails — the password is stored encrypted and never shown again once saved.
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Scripts</div>
            <div class="card-body">
                <div class="alert alert-warning py-2 px-3 small">
                    <i class="bi bi-exclamation-triangle"></i> Only paste code from sources you trust — this runs on every visitor's page (analytics tags, chat widgets, pixels, etc).
                </div>
                <div class="mb-3">
                    <label class="form-label">Header Scripts <span class="text-muted">(injected into &lt;head&gt;)</span></label>
                    <textarea name="header_scripts" class="form-control font-monospace @error('header_scripts') is-invalid @enderror" rows="5" placeholder="<!-- e.g. Google Analytics / Meta Pixel -->">{{ old('header_scripts', $setting->header_scripts) }}</textarea>
                    @error('header_scripts')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Footer Scripts <span class="text-muted">(injected before &lt;/body&gt;)</span></label>
                    <textarea name="footer_scripts" class="form-control font-monospace @error('footer_scripts') is-invalid @enderror" rows="5" placeholder="<!-- e.g. chat widget embed -->">{{ old('footer_scripts', $setting->footer_scripts) }}</textarea>
                    @error('footer_scripts')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">About Us Page</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">About Us Content <span class="text-muted">(shown at /about on the storefront)</span></label>
                    <textarea name="about_us" id="setting_about_us" class="form-control @error('about_us') is-invalid @enderror" rows="8">{{ old('about_us', $setting->about_us) }}</textarea>
                    @error('about_us')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Legal Pages</div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Privacy Policy <span class="text-muted">(shown at /privacy-policy on the storefront)</span></label>
                    <textarea name="privacy_policy" id="setting_privacy_policy" class="form-control @error('privacy_policy') is-invalid @enderror" rows="6">{{ old('privacy_policy', $setting->privacy_policy) }}</textarea>
                    @error('privacy_policy')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Terms of Use <span class="text-muted">(shown at /terms-of-service on the storefront)</span></label>
                    <textarea name="terms_of_use" id="setting_terms_of_use" class="form-control @error('terms_of_use') is-invalid @enderror" rows="6">{{ old('terms_of_use', $setting->terms_of_use) }}</textarea>
                    @error('terms_of_use')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Save Settings
        </button>
    </form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script>
$(function () {
    const editorOptions = {
        height: 260,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['codeview']],
        ],
    };

    $('#setting_about_us').summernote(editorOptions);
    $('#setting_privacy_policy').summernote(editorOptions);
    $('#setting_terms_of_use').summernote(editorOptions);

    let emailIndex = $('.contact-email-row').length;

    function toggleEmailsHint() {
        $('#noEmailsHint').toggle($('.contact-email-row').length === 0);
    }

    $('#btnAddEmail').on('click', function () {
        const i = emailIndex++;
        $('#contactEmailsRows').append(`
            <div class="row g-2 mb-2 contact-email-row">
                <div class="col-md-4">
                    <input type="text" name="contact_emails[${i}][title]" class="form-control" placeholder="Title (e.g. Sales)">
                </div>
                <div class="col-md-6">
                    <input type="email" name="contact_emails[${i}][email]" class="form-control" placeholder="email@mediadekho.com">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100 btn-remove-email"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `);
        toggleEmailsHint();
    });

    $('#contactEmailsRows').on('click', '.btn-remove-email', function () {
        $(this).closest('.contact-email-row').remove();
        toggleEmailsHint();
    });

    let addressIndex = $('.contact-address-row').length;

    function toggleAddressesHint() {
        $('#noAddressesHint').toggle($('.contact-address-row').length === 0);
    }

    $('#btnAddAddress').on('click', function () {
        const i = addressIndex++;
        $('#contactAddressesRows').append(`
            <div class="row g-2 mb-2 contact-address-row">
                <div class="col-md-3">
                    <input type="text" name="contact_addresses[${i}][title]" class="form-control" placeholder="Title (e.g. Head Office)">
                </div>
                <div class="col-md-7">
                    <textarea name="contact_addresses[${i}][address]" class="form-control" rows="1" placeholder="Full address"></textarea>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100 btn-remove-address"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `);
        toggleAddressesHint();
    });

    $('#contactAddressesRows').on('click', '.btn-remove-address', function () {
        $(this).closest('.contact-address-row').remove();
        toggleAddressesHint();
    });
});
</script>
@endpush
