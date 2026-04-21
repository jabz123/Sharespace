# SharedSpace AI Verification

This folder contains a starter `n8n` workflow for the article verification panel on [pages/write.php](C:\Users\Jtxth\Documents\Sharespace\pages\write.php).

## What it does

- Receives article data from `POST /api/ai-verify.php`
- Searches only CNA and The Straits Times for matching coverage
- Scores the draft with the SharedSpace trust rubric
- Returns publish routing plus misinformation highlights for unreliable drafts

## Expected setup

1. Run `n8n` locally or on Hostinger so the webhook is available publicly
2. Import `ai-verification-workflow.json`
3. In `n8n`, set the `OPENAI_API_KEY` environment variable or replace it in the HTTP Request node
4. Open the write page and click `AI Fact Check`

## Trusted source link

You can paste an exact CNA or ST article link into the source URL field on the write page, for example:

`https://www.straitstimes.com/singapore/example-article-slug`

Homepage and section URLs do not count as trusted article evidence.

## Response shape

The workflow returns JSON like this:

```json
{
  "trust_score": 82,
  "publish_decision": "auto_publish",
  "summary": "Both CNA and ST support the core story. The supplied reference URL appears to be a valid exact CNA/ST article link.",
  "verdict": "Reliable. Auto publish approved because the CNA/ST evidence is strong enough for direct publication.",
  "metrics": {
    "factual_accuracy": 85,
    "source_quality": 78,
    "bias_detection": 72,
    "logical_consistency": 88,
    "completeness": 86
  },
  "source_label": "Compared against 2 trusted CNA/ST sources.",
  "misinformation_highlights": []
}
```

## Publish routing

- `81-100`: `auto_publish`
- `60-80`: `needs_review`
- `0-59`: `unreliable`

For now, `needs_review` does not auto-route to category experts and does not unlock publishing.

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
