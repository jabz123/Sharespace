# SharedSpace AI Verification Rubric

This workflow verifies a submitted article by comparing it against trusted news sources, then scoring it with a fixed 100-point rubric. The goal is not to decide whether an article is popular or well-written. The goal is to estimate whether it is reliable enough to publish on SharedSpace.

## Trusted Sources

The workflow treats these as Tier 1 sources:

- Reuters
- CNA / Channel NewsAsia
- The Straits Times

If the writer provides an exact article URL from one of these sources, the workflow treats that link as the main evidence anchor. This is important because a copied or adapted Straits Times article should not be penalized just because another website does not repeat every detail.

## Score Breakdown

The trust score is out of 100:

- Factual Accuracy: 35 points
- Source Quality: 20 points
- Bias / Neutrality: 20 points
- Logical Consistency: 15 points
- Completeness: 10 points

Final formula:

```text
Trust Score = Factual Accuracy + Source Quality + Bias / Neutrality + Logical Consistency + Completeness
```

The category maximums already add up to 100, so the workflow does not multiply the scores again.

## What Each Category Means

Factual Accuracy checks whether the core claims are supported by trusted sources. The workflow snaps the score into fixed bands such as 32, 24, 14, 4, or 0 so repeated checks are more stable.

Source Quality checks the strength of the matched evidence. An exact Tier 1 reference URL scores highest. Multiple trusted matches also score strongly. No trusted match scores poorly.

Bias / Neutrality checks for sensational or loaded language. The workflow scans for words such as "shocking", "bombshell", "secret", and "must share". Neutral reporting keeps a high score.

Logical Consistency checks whether the article makes sense internally. If an exact trusted source is provided and no contradiction is found, this receives the high band.

Completeness checks whether enough basic news elements are present, such as title, summary, content, category, reference link, and a date/time signal.

## Publish Decision

The workflow returns one of three decisions:

- `safe_to_publish`: score is at least 75, factual accuracy is strong, and neutrality is acceptable.
- `needs_review`: article has some support but should be checked or improved.
- `not_reliable`: score is too low, factual accuracy is too weak, or no trusted source supports the article.

## Hard Overrides

Some situations override the normal score:

- If a Tier 1 source directly contradicts the headline or core claim, the article is forced to `not_reliable`.
- If there is no trusted source match and factual accuracy is very low, the article is forced to `not_reliable`.

## Why The Workflow Is More Consistent

The AI model is used to search for sources and provide structured notes, but the final score is recalculated in the last n8n Code node. This keeps the final trust score more stable because source quality, bias, logical consistency, completeness, and the final total are handled by fixed code rules instead of the model freely guessing a number.
