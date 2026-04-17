# SharedSpace AI Verification

This folder contains a starter `n8n` workflow for the article verification panel on [pages/write.php](C:\Users\Jtxth\Documents\Sharespace\pages\write.php).

## What it does

- Receives article data from `POST /api/ai-verify.php`
- Sends the article to OpenAI using `gpt-5-mini`
- Returns structured verification data for the right-hand AI panel

## Expected setup

1. Run `n8n` locally or on Hostinger so the webhook is available publicly
2. Import `ai-verification-workflow.json`
3. In `n8n`, set the `OPENAI_API_KEY` environment variable or replace it in the HTTP Request node
4. Open the write page and click `AI Fact Check`

## Example source link

You can paste a reference link into the new source URL field on the write page, for example:

`https://www.straitstimes.com/`

The workflow treats that URL as supporting context and includes it in the verification prompt.

## Response shape

The workflow returns JSON like this:

```json
{
  "trust_score": 82,
  "summary": "The article is mostly consistent and grounded in the supplied context.",
  "verdict": "Trust score is above 60%. Article can be published.",
  "metrics": {
    "factual_accuracy": 85,
    "source_quality": 78,
    "bias_detection": 72,
    "logical_consistency": 88,
    "completeness": 86
  },
  "source_label": "Straits Times reference supplied"
}
```

## Feedback sentiment workflow

This folder also contains `feedback-sentiment-workflow.json` for AI review of user feedback submitted from the profile page.

What it does:

- Receives `feedback_id`, `rating`, and `content` from `pages/submit-feedback.php`
- Uses AI to check whether the written comment matches the star rating
- Calls back into `/api/feedback-sentiment-callback.php`
- Updates `site_feedback.sentiment_label`, `sentiment_score`, `sentiment_status`, and `is_approved`

Required app config:

- `APP_BASE_URL`
- `N8N_FEEDBACK_SENTIMENT_WEBHOOK_URL`
- `FEEDBACK_SENTIMENT_CALLBACK_SECRET`

The callback secret should match the header sent by the workflow in `X-SharedSpace-Secret`.
