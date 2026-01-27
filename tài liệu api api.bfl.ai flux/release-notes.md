> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Release Notes

> Product updates and announcements across BFL products

<Update label="January 15, 2026">
  ## FLUX.2 \[klein] Launch

  We're excited to announce **FLUX.2 \[klein]** — our fastest model family with **sub-second inference**.

  ### Key Features

  * **Sub-second generation** — Real-time image generation for interactive applications
  * **Two variants** — 4B from (\$0.014 / image) and 9B from (\$0.015/image) models
  * **Runs on consumer hardware** — As little as 13GB VRAM required
  * **Open weights available** — 4B under Apache 2.0, 9B under FLUX Non-Commercial License

  ### API Endpoints

  * `flux-2-klein-4b` — Fastest, ideal for high-volume and local deployment
  * `flux-2-klein-9b` — Balanced quality and speed with 8B Qwen3 text embedder

  Download open weights from [Hugging Face](https://huggingface.co/black-forest-labs) or get started with the [API documentation](/flux_2/flux2_text_to_image).
</Update>

<Update label="January 8, 2026">
  ## Credit Transfer

  You can now **transfer credits between organizations** directly from the dashboard.

  * Navigate to your organization's Credits page
  * Click "Transfer Credits" to move credits to another organization you own
  * Credits transfer instantly with full audit logging
</Update>

<Update label="December 16, 2025">
  ## FLUX.2 \[max] Launch

  Introducing **FLUX.2 \[max]** — our highest quality model with groundbreaking capabilities.

  ### Key Features

  * **Maximum quality** — Highest editing consistency, strongest prompt following, and faithful style representation
  * **Grounding search** — Generate images grounded in real-time information from the web
  * **Multi-reference editing** — Combine up to 10 input images while maintaining identity
  * **Vast world knowledge** — Create visuals of current events, real-time weather, or historical moments

  ### Grounding Search Examples

  FLUX.2 \[max] can search the web to visualize:

  * Yesterday's football scores
  * Real-time weather in any city
  * Recent news events
  * Historical moments from specific dates and locations

  Try it in the [playground](https://playground.bfl.ai) or via the [API](/flux_2/flux2_text_to_image).
</Update>

<Update label="December 15, 2025">
  ## Organizations & Projects

  We've introduced a new way to organize your teams and work with **Organizations** and **Projects**.

  ### What's New

  * **Organizations** - Create organizations to group your projects and team members. Each organization has its own billing, credits, and member management.
  * **Projects** - Organize your work into projects with dedicated API keys, usage tracking, and spending limits.
  * **Role-Based Access Control** - Invite team members with granular permissions: Administrator, Developer, Billing Manager, or Viewer.
  * **Project-Scoped API Keys** - API keys are now tied to specific projects for better security and tracking.
  * **Spending Limits** - Set daily, weekly, and monthly credit limits per project.
  * **Activity Logging** - Full audit trail of all organizational activity.

  ### For Existing Users

  Your account has been automatically migrated:

  * A "Default" organization was created with your existing credits
  * A "Default" project contains your existing API keys
  * **Your API keys continue to work** - no changes required

  Learn more in our [Organizations & Projects documentation](/account_management/organizations_projects).
</Update>

<Update label="November 25, 2025">
  ## FLUX.2 \[pro] and \[flex] Launch

  Introducing the next generation of FLUX models — **FLUX.2 \[pro]** and **FLUX.2 \[flex]**.

  ### FLUX.2 \[pro]

  Production-grade image generation at scale.

  * **Top performance at affordable price** — High quality generation from \$0.03/MP
  * **Fast turnaround** — Optimized for high-volume applications
  * **Multi-reference editing** — Combine up to 8 input images (10 in playground)
  * **Photorealistic output** — Closes the gap between generated and real imagery

  ### FLUX.2 \[flex]

  Fine-grained control for precise results.

  * **Specialized for typography** — Best-in-class text rendering
  * **Adjustable parameters** — Control inference steps (up to 50) and guidance scale (1.5–10)
  * **Detail preservation** — Maintains small details in complex scenes
  * **Production workflows** — Ideal for designs requiring exact specifications

  ### Key Capabilities

  Both models support:

  * **Exact color control** — Specify brand colors via hex codes with precision matching
  * **Structured prompting** — JSON-style prompts for automated workflows
  * **Multi-reference editing** — Character consistency, style transfer, and pose guidance
  * **Up to 4MP output** — High-resolution generation at any aspect ratio

  Get started with the [API documentation](/flux_2/flux2_text_to_image) or try them in the [playground](https://playground.bfl.ai).
</Update>

<Update label="October 31, 2025">
  ## API Deprecations - Now In Effect

  As of today, the Flux Pro 1.0 endpoints (`flux-pro-1.0`, `flux-pro-1.0-depth`, `flux-pro-1.0-canny`) and the Finetuning API are officially deprecated. See details in the October 3 announcement below.
</Update>

<Update label="October 3, 2025">
  ## API Deprecations

  ### October 31, 2025 - Flux Pro 1.0 Endpoints

  The following endpoints will be deprecated. Migrate to [flux-pro-1.1](https://docs.bfl.ai/flux_models/flux_1_1_pro#using-flux1-1-%5Bpro%5D-api-for-text-to-image-generation) or [flux-kontext-pro](https://docs.bfl.ai/kontext/kontext_text_to_image#using-flux-1-kontext-api-for-text-to-image-generation).

  **Deprecated endpoints:**

  * `flux-pro-1.0`
  * `flux-pro-1.0-depth`
  * `flux-pro-1.0-canny`

  **Migration targets:**

  * [FLUX1.1 \[pro\] documentation](https://docs.bfl.ai/flux_models/flux_1_1_pro)
  * [FLUX.1 Kontext \[pro\] documentation](https://docs.bfl.ai/kontext/kontext_text_to_image)

  ### October 31, 2025 - Finetuning API

  The finetuning API will be deprecated, including all finetuning-related endpoints.

  **Deprecated endpoints:**

  * `/v1/finetune`
  * `flux-pro-finetuned`
  * `flux-pro-1.1-ultra-finetuned`
  * `flux-pro-1.0-depth-finetuned`
  * `flux-pro-1.0-canny-finetuned`
  * `flux-pro-1.0-fill-finetuned`

  No migration path available. Finetuning functionality will be discontinued.
</Update>
