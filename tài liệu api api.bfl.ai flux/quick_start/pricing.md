> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Overview

> Pricing for FLUX models through our API or Playground.

<Info>
  **Simple credit-based pricing:** 1 credit = \$0.01 USD • Pay per image • Same price for API and Playground
</Info>

## Pricing

The following table shows pricing for all FLUX models and their variants:

### FLUX.2 Models

FLUX.2 uses **megapixel-based pricing** — cost scales with output resolution.

| Model              | Text-to-Image | Image Editing | Best For                              |
| ------------------ | ------------- | ------------- | ------------------------------------- |
| FLUX.2 \[klein] 4B | from \$0.014  | from \$0.014  | Real-time, high volume                |
| FLUX.2 \[klein] 9B | from \$0.015  | from \$0.015  | Balanced quality/speed                |
| FLUX.2 \[pro]      | from \$0.03   | from \$0.045  | Production workflows, fast turnaround |
| FLUX.2 \[flex]     | from \$0.06   | from \$0.12   | Maximum quality, adjustable controls  |
| FLUX.2 \[dev]      | Free          | Free          | Local development (non-commercial)    |

<Note>
  **Klein pricing explained:** The first megapixel costs a flat rate, then each additional MP adds to the total. For example, a 2MP image with Klein 4B costs $0.014 + $0.001 = \$0.015.
</Note>

<Info>
  FLUX.2 pricing varies by output resolution. Use the [pricing calculator](https://bfl.ai/pricing) for exact costs.
</Info>

### FLUX.1 Models

| Model                 | Credits per Image | Price per Image | Description                                                                                     |
| --------------------- | ----------------- | --------------- | ----------------------------------------------------------------------------------------------- |
| FLUX.1 Kontext \[pro] | 4 credits         | \$0.04          | Create and edit images with text and images                                                     |
| FLUX.1 Kontext \[max] | 8 credits         | \$0.08          | Create and edit images with text and images, maximum quality                                    |
|                       |                   |                 |                                                                                                 |
| FLUX1.1 \[pro]        | 4 credits         | \$0.04          | The standard for text-to-image generation with fast, reliable and consistently stunning results |
| FLUX1.1 \[pro] Ultra  | 6 credits         | \$0.06          | Ultra high-resolution image creation - with more pixels in every picture                        |
| FLUX1.1 \[pro] Raw    | 6 credits         | \$0.06          | High-quality, with a genuine feel of candid photography                                         |
| FLUX.1 Fill \[pro]    | 5 credits         | \$0.05          | Targeted and fast text-driven image inpainting                                                  |
|                       |                   |                 |                                                                                                 |

## Batch Pricing

<Check>
  **Batch requests multiply the base cost by the number of images**
</Check>

**Example:** FLUX.1 Kontext \[pro] batch of 4 images

* Single image: 4 credits (\$0.04)
* Batch of 4: **16 credits (\$0.16)**

## Getting Started

<Columns cols={2}>
  <Card title="Quick Start" href="/quick_start/get_started" icon="rocket">
    Create account, add credits, and get API keys
  </Card>

  <Card title="Generate Images" href="/quick_start/generating_images" icon="image">
    Start creating with our API
  </Card>

  <Card title="Try Playground" href="https://playground.bfl.ai" icon="play">
    Try our models without writing code
  </Card>
</Columns>
