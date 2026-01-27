> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Quick Start

> Create your account, add credits, and generate your first AI image

<img noZoom src="https://cdn.sanity.io/images/2gpum2i6/production/7a2a3a2a8d594db99b03a24b413a3f59da2f7e9e-2401x1284.jpg?w=1284&h=512&fit=crop&auto=format" style={{ borderRadius: '24px' }} alt="FLUX AI Image Generation" />

Start generating AI images with FLUX in three steps: create an account, add credits, and make your first API call.

<Steps>
  <Step title="Create your account">
    Go to [dashboard.bfl.ai](https://dashboard.bfl.ai) and register your account. You'll need to confirm your email before logging in.

    <Frame caption="Register on our platform">
      <img src="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/register.jpg?fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=25408a1ec8abceaff5e5a62e34a4aa6d" alt="BFL API registration page" data-og-width="458" width="458" data-og-height="577" height="577" data-path="images/register.jpg" data-optimize="true" data-opv="3" srcset="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/register.jpg?w=280&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=3270b429596e0e2d7dfedfc0016f9d82 280w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/register.jpg?w=560&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=43769d01c7ea53d7c8ae46795d806617 560w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/register.jpg?w=840&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=6b642e2c1e18bd2453682231dd1376ba 840w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/register.jpg?w=1100&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=4f1e9f755bf2eefe36cb14f745c11947 1100w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/register.jpg?w=1650&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=860088ba675d0f883c5660687b014675 1650w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/register.jpg?w=2500&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=e78666e3526eb97dd5a53f6dc95ae8dd 2500w" />
    </Frame>

    When you create your account, we automatically set up:

    * A **Default Organization** - your workspace for managing team members and billing
    * A **Default Project** - where your API keys and usage are tracked

    <Tip>
      **Organizations** group your team and billing. **Projects** isolate your work with separate API keys and usage tracking. Think of organizations as your company and projects as different apps or environments.
    </Tip>
  </Step>

  <Step title="Add credits to your account">
    Credits power all API and Playground usage.

    <Info>
      **Simple pricing:** 1 credit = \$0.01 USD
    </Info>

    **To add credits:**

    1. Go to [dashboard.bfl.ai](https://dashboard.bfl.ai)
    2. In your organization sidebar, navigate to **API → Credits**
    3. Click **Add Credits**
    4. Choose your amount and complete payment via Stripe

    <Frame caption="Click Add Credits to purchase">
      <img src="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=8c98e18b3f4278ad9a047a4eb4a6dd04" alt="Add credits button in dashboard" data-og-width="533" width="533" data-og-height="262" height="262" data-path="images/add_credits.jpg" data-optimize="true" data-opv="3" srcset="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?w=280&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=594327b6bf7dd5bbc0151eb3c064ee42 280w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?w=560&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=7a7af2a1cd89dd4b364cba43b5651d37 560w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?w=840&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=5be502297daf84b6903549ae2d509dee 840w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?w=1100&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=427e6ca8c773ff34b4cb34124ab96fa1 1100w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?w=1650&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=d190b3907478b07b6519a719fe14799d 1650w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/add_credits.jpg?w=2500&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=831b33d63f85945f3d71745c07dd47d9 2500w" />
    </Frame>

    <Tip>
      Credits are available immediately after payment. Start with \$10-20 to experiment with different models.
    </Tip>

    [Full credits & billing guide](/account_management/credits_billing)
  </Step>

  <Step title="Create your API key">
    <Info>
      **Prefer an interactive experience?** Use our [Dashboard Quickstart](https://dashboard.bfl.ai/get-started) to create your API key and make your first call.
    </Info>

    Navigate to **API → Keys** in your project sidebar and click **Add Key**.

    <Note>
      Give your API key a descriptive name that helps you identify its purpose later, like "Production App" or "Testing Environment". Each API key is scoped to its project.
    </Note>

    <Frame caption="Create your FLUX API key">
      <img src="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_0.png?fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=7be1fb6e91b6ea0b1a8aa116f537dd25" alt="Click Add Key to create API access" data-og-width="1056" width="1056" data-og-height="510" height="510" data-path="images/api_key_0.png" data-optimize="true" data-opv="3" srcset="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_0.png?w=280&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=55d787c588b0625d0ced994a230f38a5 280w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_0.png?w=560&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=fa9d405f4ce5e45a0fc5c01b4f7c9108 560w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_0.png?w=840&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=179fb751189491a94a3cf9018547bd72 840w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_0.png?w=1100&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=58819ab61a67d1ced1fefe7f5c384558 1100w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_0.png?w=1650&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=c4f58cb1710c6072c409414d82dbfc8a 1650w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_0.png?w=2500&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=ea7aad5f99682a68eb3a0041736a0304 2500w" />
    </Frame>
  </Step>

  <Step title="Copy your API key">
    Your new API key will be displayed. **Copy it now** - for security reasons, you won't be able to see the full key again.

    <Warning>
      Your API key is only shown once when created. Make sure to copy and store it securely before closing the dialog.
    </Warning>

    <Frame caption="Copy your API key - it's only shown once">
      <img src="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_1.png?fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=1b7b3be17b302fc6d143a8c18870f796" alt="Copy your API key" data-og-width="1516" width="1516" data-og-height="708" height="708" data-path="images/api_key_1.png" data-optimize="true" data-opv="3" srcset="https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_1.png?w=280&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=eebf77bcb8e54ae3507fe90729850950 280w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_1.png?w=560&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=e94b8da878fd36679d4169f28c44a372 560w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_1.png?w=840&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=949fb5bff7a1ec1ba744264b2920ce39 840w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_1.png?w=1100&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=253859b42953e31b4cca24f7cc41d91b 1100w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_1.png?w=1650&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=08a2ee629b292cba68f9b7654613c13d 1650w, https://mintcdn.com/bfl/OQ5B17YmedKOM4zs/images/api_key_1.png?w=2500&fit=max&auto=format&n=OQ5B17YmedKOM4zs&q=85&s=2290e73af761652d941d2507d114d5a9 2500w" />
    </Frame>
  </Step>

  <Step title="Set up your environment">
    Add your API key to your environment variables:

    ```bash  theme={null}
    export BFL_API_KEY="your_api_key_here"
    ```

    Or store it securely in your application's configuration.

    <Tip>
      Keep your API key secure and never expose it in client-side code. Treat it like a password!
    </Tip>
  </Step>

  <Step title="Make your first API call">
    Generate your first image with FLUX.2. See the [image generation](/quick_start/generating_images) for complete code examples in Python, TypeScript, and cURL.

    <Tip>
      Want to skip the code? Try our [Playground](https://playground.bfl.ai) to generate images directly in your browser.
    </Tip>
  </Step>
</Steps>

<Note>
  See our [full pricing page](/quick_start/pricing) for complete details or use the [pricing calculator](https://bfl.ai/pricing).
</Note>

## Next Steps

<Columns cols={2}>
  <Card title="Generate Your First Image" icon="image" href="/quick_start/generating_images">
    Learn how to create images using our API
  </Card>

  <Card title="Try the Playground" icon="play" href="https://playground.bfl.ai">
    Test models instantly without writing code
  </Card>

  <Card title="Explore FLUX.2" icon="sparkles" href="/flux_2/flux2_overview">
    Our latest and most powerful model family
  </Card>

  <Card title="Prompting Guide" icon="graduation-cap" href="/guides/prompting_summary">
    Get better results with effective prompts
  </Card>
</Columns>
