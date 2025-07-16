# Gmail Integration for Laravel Result Module

This implementation provides a minimal Gmail API integration for sending Result (Report Card) emails with delivery confirmation and auto-authentication support.

## Features

✅ **Email Sending via Gmail API** with custom FROM address (your school domain)  
✅ **Delivery Confirmation** tracking with message IDs  
✅ **OAuth2 Authentication** with auto token refresh  
✅ **Attachment Support** for PDF report cards  
✅ **Fallback System** - automatically uses Laravel Mail if Gmail fails  
✅ **Custom Domain Support** - send from your school email without "via gmail.com"  
✅ **Cloudflare Email Routing Compatible** - works with existing incoming email setup  

## Installation & Setup

### 1. Install Google API Client

```bash
composer require google/apiclient
```

### 2. Google Cloud Console Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable **Gmail API**
4. Go to **Credentials** → **Create Credentials** → **OAuth 2.0 Client IDs**
5. Set Application Type: **Web Application**
6. Add Authorized Redirect URI: `https://yourdomain.com/result/gmail/callback`
7. Copy **Client ID** and **Client Secret**

### 3. Environment Configuration

Add to your `.env` file:

```env
# Gmail API Configuration
GMAIL_CLIENT_ID=your-gmail-client-id-here
GMAIL_CLIENT_SECRET=your-gmail-client-secret-here
GMAIL_ENABLED=true
```

### 4. Database Migration

Run the migration to add Gmail tracking columns:

```bash
php artisan migrate
```

### 5. Gmail Account Setup

#### Configure "Send mail as" in Gmail:
1. Open Gmail Settings → **Accounts and Import**
2. Click **Add another email address** in "Send mail as" section
3. Add your school email: `reports@yourschool.com`
4. Verify ownership via email or DNS

#### Add DNS Records (Required for no "via gmail.com"):
Add these DNS records to your domain:

```
TXT record: "v=spf1 include:_spf.google.com ~all"
```

Google will provide DKIM keys after domain verification.

### 6. OAuth2 Authorization

1. Visit: `https://yourdomain.com/result/utility`
2. Click **"Authorize Gmail"** button (will be added to utility view)
3. Grant Gmail permissions
4. You'll be redirected back with success message

## Usage

### Automatic Integration

The system automatically uses Gmail API when:
- `GMAIL_ENABLED=true` in .env
- Gmail is properly authenticated
- All credentials are configured

If Gmail fails, it automatically falls back to Laravel Mail.

### Manual Usage

You can also dispatch Gmail jobs directly:

```php
use Modules\Result\Jobs\SendGmailResultEmail;

// Send via Gmail API
dispatch(new SendGmailResultEmail($data))->onQueue('result-notice');
```

## File Structure

```
Modules/Result/
├── Traits/
│   └── GmailTrait.php                     # Main Gmail functionality
├── Jobs/
│   └── SendGmailResultEmail.php           # Gmail email job with fallback
├── Database/Migrations/
│   └── 2024_01_15_000000_add_gmail_columns_to_email_logs.php
├── Http/Controllers/
│   └── UtilityController.php              # Gmail auth methods added
└── Routes/
    └── web.php                            # Gmail routes added
```

## API Endpoints

- `GET /result/gmail/auth` - Initialize Gmail OAuth2
- `GET /result/gmail/callback` - OAuth2 callback handler  
- `GET /result/gmail/status` - Check Gmail configuration status

## Email Flow

### Outgoing Emails (Your App → Recipients):
```
Laravel App → Gmail API → Gmail Servers → Recipients
```

### Incoming Emails (Replies from Recipients):
```
Recipients → yourschool.com → Cloudflare Email Routing → Gmail Inbox
```

### Delivery Tracking:
```
Gmail API → Message ID → Database Logging → Delivery Confirmation
```

## Delivery Confirmation Features

- **Immediate Confirmation**: Message accepted by Gmail
- **Message ID Tracking**: Unique ID for each sent email  
- **Database Logging**: Enhanced email logs with Gmail message IDs
- **Delivery Status**: Sent, Delivered, Failed tracking
- **Error Handling**: Detailed error logging and fallback

## Troubleshooting

### Gmail Not Working?
1. Check `.env` credentials are correct
2. Verify OAuth2 authorization completed
3. Check `storage/app/gmail_token.json` exists
4. Review logs in `storage/logs/laravel.log`

### Emails Show "via gmail.com"?
1. Add SPF DNS record: `"v=spf1 include:_spf.google.com ~all"`
2. Complete domain verification in Gmail
3. Add DKIM records provided by Google

### No Delivery Confirmation?
1. Check database migration was run
2. Verify `sm_email_sms_logs` table has new columns
3. Check Gmail API quotas in Google Cloud Console

## Benefits Over Current Laravel Mail

| Feature | Laravel Mail | Gmail Integration |
|---------|-------------|-------------------|
| Delivery Confirmation | ❌ Basic logging | ✅ Full tracking |
| Custom Domain | ⚠️ May show "via" | ✅ Clean sender |
| Rate Limits | ⚠️ SMTP limited | ✅ Gmail quotas |
| Bounce Handling | ❌ Limited | ✅ Advanced |
| Message Threading | ❌ None | ✅ Gmail features |
| Reliability | ⚠️ SMTP issues | ✅ Google infrastructure |

## Security Notes

- OAuth2 tokens are stored securely in `storage/app/gmail_token.json`
- Tokens automatically refresh before expiration
- All Gmail API calls use HTTPS
- No passwords stored, only OAuth2 tokens

## Support

For issues or questions:
1. Check logs in `storage/logs/laravel.log`
2. Verify Google Cloud Console API quotas
3. Test with `php artisan queue:work` to see job processing
4. Use `/result/gmail/status` endpoint to check configuration

---

**Note**: This implementation maintains full compatibility with your existing Cloudflare Email Routing setup for incoming emails while providing advanced Gmail API features for outgoing emails.