# SharedSpace AI Verification

This folder contains a starter `n8n` workflow for the article verification panel on [pages/write.php](C:\Users\Jtxth\Documents\Sharespace\pages\write.php).

## What it does

- Receives article data from `POST /api/ai-verify.php`
- Sends the article to Groq through an OpenAI-compatible chat completion request
- Returns structured verification data for the right-hand AI panel

## Expected local setup

1. Run `n8n` locally so the webhook is available at `http://127.0.0.1:5678/webhook/sharedspace-ai-verify`
2. Import `ai-verification-workflow.json`
3. In `n8n`, set the `GROQ_API_KEY` environment variable or replace it in the HTTP Request node
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
