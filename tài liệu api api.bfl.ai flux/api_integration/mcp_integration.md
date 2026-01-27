> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# MCP Integration with FLUX

> Use FLUX image generation models in Claude Desktop, Claude.ai, or any MCP-compatible application.

The FLUX MCP server provides access to all FLUX models through the Model Context Protocol. Generate and edit images using natural language in Claude.

## Available Models

| Model                     | Description                                                                    |
| ------------------------- | ------------------------------------------------------------------------------ |
| **FLUX.2 \[pro]**         | Latest model with t2i & i2i (up to 9 images) - **Recommended**                 |
| **FLUX.2 \[flex]**        | Customizable generation and editing (up to 10 images, up to 14 MP total input) |
| **FLUX.1.1 \[pro]**       | Fast, high-quality generation                                                  |
| **FLUX.1.1 \[pro] Ultra** | Maximum quality up to 4MP resolution                                           |
| **FLUX.1 Kontext \[pro]** | Context-aware generation and editing                                           |
| **FLUX.1 Kontext \[max]** | Advanced context editing                                                       |
| **FLUX.1 Fill \[pro]**    | Intelligent inpainting                                                         |
| **FLUX.1 Expand \[pro]**  | Smart outpainting                                                              |

## Setup Instructions

### Claude Desktop

<Steps>
  <Step title="Get your BFL API key">
    Sign up at [Black Forest Labs](https://api.bfl.ai/) to obtain your API key.
  </Step>

  <Step title="Open Claude Desktop configuration">
    Locate your configuration file:

    * **macOS**: `~/Library/Application Support/Claude/claude_desktop_config.json`
    * **Windows**: `%APPDATA%\Claude\claude_desktop_config.json`
  </Step>

  <Step title="Add MCP server configuration">
    Add the following configuration to your `claude_desktop_config.json` file:

    ```json  theme={null}
    {
      "mcpServers": {
        "bfl-flux": {
          "command": "npx",
          "args": [
            "-y",
            "mcp-remote",
            "https://flux-mcp-bfl-e21faf2e.koyeb.app/",
            "--header",
            "x-key: ${BFL_API_KEY}"
          ],
          "env": {
            "BFL_API_KEY": "your_bfl_api_key_here"
          }
        }
      }
    }
    ```

    <Warning>
      Replace `your_bfl_api_key_here` with your actual BFL API key obtained in Step 1.
    </Warning>
  </Step>

  <Step title="Restart Claude Desktop">
    Quit and restart Claude Desktop to load the new MCP server configuration. The FLUX tools will appear automatically in Claude's tool list.
  </Step>
</Steps>

### Claude.ai

<Steps>
  <Step title="Get your BFL API key">
    Sign up at [Black Forest Labs](https://api.bfl.ai/) to obtain your API key.
  </Step>

  <Step title="Access Custom Connectors">
    Navigate to [Claude.ai](https://claude.ai) and go to **Settings** → **Custom Connectors**.
  </Step>

  <Step title="Add a new connector">
    Configure the connector with the following details:

    * **URL**: `https://flux-mcp-bfl-e21faf2e.koyeb.app/`
    * **Headers**: Add a custom header
      * **Name**: `x-key`
      * **Value**: Your BFL API key
  </Step>

  <Step title="Save and start using">
    Save the connector configuration and start a new conversation. You can now ask Claude to generate images using FLUX models.
  </Step>
</Steps>

## Usage

Once configured, ask Claude to generate or edit images using natural language:

* "Generate an image of a sunset over mountains"
* "Create a portrait of a robot in cyberpunk style"
* "Edit this image to change the sky to a dramatic sunset"
* "Expand this image to make it wider"

Claude automatically selects the appropriate model and handles the generation process. The MCP server includes automatic polling and returns results when ready.

## Pricing

You are billed directly by Black Forest Labs based on your API usage. Each user uses their own API key for full cost control. Standard BFL API pricing applies - see [bfl.ai/pricing](https://bfl.ai/pricing).

## Technical Details

### Authentication

Your API key is passed via the `x-key` header with each request. This provides secure per-user authentication.

### Available Tools

| Tool Name                                     | Endpoint                  |
| --------------------------------------------- | ------------------------- |
| Generate images with FLUX 2 Pro (recommended) | `/v1/flux-2-pro`          |
| Generate images with FLUX 2 Flex              | `/v1/flux-2-flex`         |
| Generate images with FLUX 1.1 Pro             | `/v1/flux-pro-1.1`        |
| Generate images with FLUX 1.1 Ultra           | `/v1/flux-pro-1.1-ultra`  |
| Generate & Edit with FLUX Kontext Pro         | `/v1/flux-kontext-pro`    |
| Generate & Edit with FLUX Kontext Max         | `/v1/flux-kontext-max`    |
| Inpainting with FLUX Fill Pro                 | `/v1/flux-pro-1.0-fill`   |
| Outpainting with FLUX Expand Pro              | `/v1/flux-pro-1.0-expand` |
| get\_result                                   | `/v1/get_result`          |
| wait\_for\_result                             | Custom polling tool       |

## Troubleshooting

<AccordionGroup>
  <Accordion title="Tools not appearing in Claude Desktop">
    1. Verify your `claude_desktop_config.json` is formatted correctly (valid JSON)
    2. Ensure you've fully restarted Claude Desktop (quit and reopen)
    3. Check that your API key is set correctly in the `env` section
    4. Look for error messages in Claude Desktop's developer console
  </Accordion>

  <Accordion title="Authentication errors">
    * Verify your BFL API key is valid and active
    * Ensure the API key is correctly entered without extra spaces or quotes
    * Check that your key has sufficient credits for generation
    * Confirm you're using the correct header name: `x-key`
  </Accordion>

  <Accordion title="Generation timeouts">
    * The default timeout is 300 seconds (5 minutes)
    * Check the BFL API status at [status.bfl.ai](https://status.bfl.ai)
    * Try again with a simpler prompt or lower resolution
    * Verify your network connection is stable
  </Accordion>

  <Accordion title="Image quality issues">
    * Use more detailed and specific prompts
    * Try FLUX.2 \[flex]
    * Refer to the [Prompting Guide](/guides/prompting_summary) for best practices
    * Experiment with different aspect ratios and parameters
  </Accordion>
</AccordionGroup>

## Best Practices

* **Use specific prompts**: Include details about style, lighting, composition, and subject for best results
* **Start with FLUX.2 \[pro]**: Offers the best balance of quality, speed, and capabilities
* **Iterate on results**: Refine your prompt if the first generation isn't perfect
* **Monitor usage**: Track API usage through the [BFL dashboard](https://dashboard.bfl.ai/)
