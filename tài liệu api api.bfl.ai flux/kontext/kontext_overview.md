> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Introduction

**FLUX.1 Kontext \[pro]** is a model that combines text-to-image generation with advanced image editing.

<Tip>
  **Want to try first?** Test FLUX.1 Kontext \[pro] & \[max] in our [playground](https://playground.bfl.ai) before integrating the API.
</Tip>

## What Can You Do?

<Tabs>
  <Tab title="Text-to-Image">
    Create stunning images from scratch using simple text prompts. FLUX.1 Kontext \[pro] delivers fast generation with strong prompt adherence.

    <Columns cols={2}>
      <Frame caption="'A small furry elephant pet looks out from a cat house'">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/4fc50f2d7b8abbdbdb5bdcf979309e3e6df1d31e-1024x1024.jpg" />
      </Frame>

      <Frame caption="'A cute robot repairing a pickup truck, van gogh style'">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/f4879d430a114e9a28f62e42192365976bd9d545-1024x1024.jpg" />
      </Frame>

      <Frame caption="'Close-up of a vintage car hood under heavy rain'">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/3bce31811d0a95960f2a30ca8c548af9463b78c8-1024x1024.jpg" />
      </Frame>

      <Frame caption="'A close-up of a face adorned with intricate black and blue patterns'">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/cf8197b308cbd564f5fd43eedf52fce612b49806-1024x1024.jpg" />
      </Frame>
    </Columns>
  </Tab>

  <Tab title="Image Editing">
    Edit specific parts of images while keeping everything else untouched using simple text prompts. Just describe what you want to change and get the results.

    <Columns cols={2}>
      <Frame caption="Before: Original car">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/3ae6ee032b85373b84934574f3ac3bb2fb792d64-2048x1365.jpg" />
      </Frame>

      <Frame caption="After: 'Change the car color to red'">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/b404ea99e309e5b4bab6fcd82a4a13ad18f2c04b-1248x832.jpg" />
      </Frame>
    </Columns>

    <Columns cols={3}>
      <Frame caption="Original image">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/4d494eb773077a7dc11918d99ca1ace6782bf524-4480x6720.jpg" />
      </Frame>

      <Frame caption="First edit">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/a9fa83bf62463aab279c669286454fc17fb7fc2a-832x1248.png" />
      </Frame>

      <Frame caption="Second edit">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/5cb3aac3a7e7dc25b57d863a4740307eeafe073c-832x1248.png" />
      </Frame>
    </Columns>
  </Tab>

  <Tab title="Character Consistency">
    Maintain character identity and unique features across multiple scenes and iterative edits.

    <Columns cols={2}>
      <Frame caption="Input Image">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/41fcbaa8d77c2c2d5bb49467ae6b5a89572022fa-1125x750.jpg" />
      </Frame>

      <Frame caption="Remove the object from her face">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/800f6631c695ff5be4b82ef9cae0981073a0fecd-1248x832.jpg" />
      </Frame>
    </Columns>

    <Columns cols={2}>
      <Frame caption="She is now taking a selfie in the streets of Freiburg, it's a lovely day out.">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/2090d7d3b1ee0fb83cef25fb94b179163208417b-1248x832.jpg" />
      </Frame>

      <Frame caption="It's now snowing, everything is covered in snow.">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/cc78fef1c0785656280a120647fb313fda6b977a-1248x832.jpg" />
      </Frame>
    </Columns>
  </Tab>

  <Tab title="Text Editing">
    Replace text in signs, posters, and labels with precision while maintaining the original styling and context. Simply use quotes around the text you want to change: `Replace 'old text' with 'new text'`.

    <Columns cols={2}>
      <Frame caption="Original: 'Choose joy'">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/1bcbfec679e9456a7ad24c341a987ff90baa29b4-1024x768.jpg" />
      </Frame>

      <Frame caption="Edited: Replace 'joy' with 'BFL'">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/6cc8691da257f2ee6b7b39c5dcf16985d05e6c08-1184x880.jpg" />
      </Frame>
    </Columns>
  </Tab>

  <Tab title="Style Transformation">
    Transform any image into completely different artistic styles or use reference images to apply their style to new scenes.

    <Columns cols={3}>
      <Frame caption="Original architectural photo">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/cea7c2566880221759691bcd3fe32032a6517722-1408x792.jpg" />
      </Frame>

      <Frame caption="Converted to pencil sketch">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/b3c1a2881d29f1a24a7dac87a29ea5e1e239215d-1392x752.jpg" />
      </Frame>

      <Frame caption="Transformed to oil painting">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/dd190a7f7c52fd80b1e6735dce7e3840f9b0d69a-1392x752.jpg" />
      </Frame>
    </Columns>

    <Columns cols={2}>
      <Frame caption="Style reference">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/4f8dfc5abd8dcd90ab6684639851fc51636cb5d2-1610x2052.jpg?w=880&h=1184&fit=crop&auto=format" />
      </Frame>

      <Frame caption="'Using this style, a bunny, dog and cat having tea'">
        <img src="https://cdn.sanity.io/images/gsvmb6gz/production/9543fd35e3e04680b564dd57715649b2132d0fd8-880x1184.jpg?w=880&h=1184&fit=crop&auto=format" />
      </Frame>
    </Columns>
  </Tab>
</Tabs>

## Which Model to Choose?

<Tabs>
  <Tab title="FLUX.1 Kontext [max]">
    **Best Output Quality**

    Premium model with industry-leading results. Perfect when quality is the top priority.

    * Industry-leading typography
    * Maximum prompt adherence
    * Premium consistency
    * **\$0.08 per image**
  </Tab>

  <Tab title="FLUX.1 Kontext [pro]">
    **Fast Production Ready**

    Best balance of speed and quality for most applications. Fast and with a unified editing and generation mode.

    * 5-6 seconds generation time
    * Unified editing & generation
    * Great prompt following
    * **\$0.04 per image**
  </Tab>

  <Tab title="FLUX.1 Kontext [dev]">
    **Open Weights & Research**

    Perfect for local development and customization. Download the weights and run on your own infrastructure.

    * Editing mode only
    * Good for customization & fine-tuning
    * Open weights, non-commercial license
    * **Free** (commercial licensing available)
  </Tab>
</Tabs>

<Note>
  **FLUX.1 Kontext \[dev]** has a non-commercial license, commercial use is possible through [licensing](https://bfl.ai/licensing).
</Note>

## Getting Started

<Columns cols={2}>
  <Card title="Try in Playground" href="https://playground.bfl.ai">
    **Start creating immediately** - Test Kontext \[pro] and \[max] in your browser. No setup required.
  </Card>

  <Card title="API Start" href="/kontext/kontext_text_to_image">
    Simple REST API integration with \[pro] and \[max].
  </Card>
</Columns>

<Columns cols={2}>
  <Card title="Learn Best Practices" href="/guides/prompting_guide_kontext_i2i">
    **Master prompting** - Get the best results with our prompting guide.
  </Card>

  <Card title="Local Development" href="https://huggingface.co/black-forest-labs/FLUX.1-Kontext-dev">
    **Download & customize** - Use \[dev] weights locally, or on your own infrastructure.
  </Card>
</Columns>
