# SharedSpace AI Verification Rubric

This workflow verifies a submitted article by comparing it against trusted news sources, then scoring it with a fixed 100-point rubric. The goal is not to decide whether an article is popular or well-written. The goal is to estimate whether it is reliable enough to publish on SharedSpace.

## Trusted Sources

The workflow treats these as the only trusted sources:

- CNA / Channel NewsAsia
- The Straits Times

If the writer provides an exact article URL from one of these sources, the workflow treats that link as the main evidence anchor. Homepage and section URLs do not count.

## Score Breakdown

The trust score is out of 100:

- Factual Accuracy: 45 points
- Source Quality: 25 points
- Bias / Neutrality: 10 points
- Logical Consistency: 10 points
- Completeness: 10 points

Final formula:

```text
Trust Score = Factual Accuracy + Source Quality + Bias / Neutrality + Logical Consistency + Completeness
```

The category maximums already add up to 100, so the workflow does not multiply the scores again.

## What Each Category Means

Factual Accuracy checks whether the core claims are supported by CNA/ST. If a core claim is contradicted, the score is capped aggressively.

Source Quality checks the strength of the matched evidence. An exact CNA/ST article URL scores highest. Multiple trusted matches also score strongly. No trusted match scores 0.

Bias / Neutrality checks for sensational or loaded language. The workflow scans for words such as "shocking", "bombshell", "secret", and "must share". Neutral reporting keeps a high score.

Logical Consistency checks whether the article makes sense internally. If an exact trusted source is provided and no contradiction is found, this receives the high band.

Completeness checks whether enough basic news elements are present, such as title, summary, content, category, reference link, and a date/time signal.

## Publish Decision

The workflow returns one of three decisions:

- `auto_publish`: score is 81 to 100 and no hard-fail rule is triggered.
- `needs_review`: score is 60 to 80.
- `unreliable`: score is below 60, contradicted, or too weak to verify.

For now, `needs_review` does not auto-route to category experts.

## Hard Overrides

Some situations override the normal score:

- If a trusted source directly contradicts the headline or a core claim, the article is forced to `unreliable`.
- If there is no trusted CNA/ST match, source quality becomes 0 and the final score is capped.
- If the article is low-information, it cannot become `auto_publish`.
- If the article is `unreliable`, the workflow returns misinformation highlights pointing to false, misleading, or unsupported lines.

## Why The Workflow Is More Consistent

The AI model is used to search for sources and provide structured notes, but the final score is recalculated in the last n8n Code node. This keeps the final trust score more stable because source quality, bias, logical consistency, completeness, and the final total are handled by fixed code rules instead of the model freely guessing a number.
