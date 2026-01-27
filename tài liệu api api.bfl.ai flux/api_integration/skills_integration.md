> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# BFL Agent Skills

> Reusable capabilities that teach AI agents how to work with FLUX models.

Skills are reusable capabilities for AI agents. Install them with a single command to enhance your agent with access to procedural knowledge — in this case, everything about FLUX prompting and API integration.

When you install BFL Skills, your AI coding assistant (Claude Code, Cursor, Windsurf, or any compatible tool) gains expert-level knowledge of FLUX. It knows how to write effective prompts, which model to use for each task, and how to integrate the API properly.

## Installation

<Tabs>
  <Tab title="Claude Code">
    ```bash  theme={null}
    /plugin marketplace add black-forest-labs/bfl_skills
    /plugin install flux-best-practices@bfl-skills
    ```
  </Tab>

  <Tab title="Cursor">
    ```bash  theme={null}
    npx skills add black-forest-labs/bfl_skills
    ```

    Or add manually by placing the skill files in `.cursor/skills/` in your project.
  </Tab>

  <Tab title="Other Tools">
    ```bash  theme={null}
    npx skills add black-forest-labs/bfl_skills
    ```

    Skills follow the open [agentskills.io](https://agentskills.io) specification and work with any compatible tool.
  </Tab>
</Tabs>

## What Your Agent Learns

### flux-best-practices

Prompting knowledge for all FLUX models:

* **Prompt structure** — The formula that works: `[Subject] + [Action] + [Style] + [Context] + [Lighting] + [Technical]`
* **No negative prompts** — FLUX doesn't support them. Describe what you want, not what you don't.
* **Lighting vocabulary** — Golden hour, softbox, rim light, Rembrandt, volumetric fog, and more
* **Hex colors** — Precise color control with `#RRGGBB` format
* **Typography** — How to render text in images using quoted strings
* **Model selection** — When to use FLUX.2 \[klein] vs \[max] vs \[pro], when to use Kontext, etc.

### bfl-api

API integration patterns:

* **Async polling** — Use the `polling_url` from responses, implement exponential backoff
* **Rate limits** — 24 concurrent requests (6 for Kontext Max), how to handle 429s
* **URL expiration** — Download images within 10 minutes
* **Regional endpoints** — `api.eu.bfl.ai` for GDPR, `api.us.bfl.ai` for US data residency
* **Webhooks** — Production webhook setup and verification

## Example: Before and After

Without skills, your agent might generate this prompt:

```
a cat sitting
```

With BFL Skills, your agent writes prompts like this:

```
A fluffy orange tabby cat with bright green eyes sitting regally on a vintage
velvet armchair, afternoon sunlight streaming through lace curtains creating
warm golden hour lighting, shallow depth of field with soft bokeh background,
shot on medium format camera
```

And when writing integration code, it knows to:

```python  theme={null}
# Use the polling_url (don't construct URLs manually)
polling_url = response.json()["polling_url"]

# Poll with exponential backoff
delay = 1
while True:
    result = requests.get(polling_url, headers={"x-key": API_KEY}).json()
    if result["status"] == "completed":
        # Download immediately — URL expires in 10 min
        image_url = result["result"]["url"]
        break
    time.sleep(delay)
    delay = min(delay * 2, 30)
```

## Using with MCP

Skills and [MCP](/api_integration/mcp_integration) serve different purposes:

|                  | Skills                            | MCP                                      |
| ---------------- | --------------------------------- | ---------------------------------------- |
| **What it does** | Teaches your agent FLUX knowledge | Lets your agent generate images directly |
| **Use case**     | Writing code that uses FLUX       | Generating images in conversation        |

You can use both together. MCP for direct image generation in Claude, Skills for writing FLUX integrations in your codebase.

## Updating

```bash  theme={null}
npx skills update black-forest-labs/bfl_skills
```

## Resources

* [BFL Skills on GitHub](https://github.com/black-forest-labs/bfl_skills)
* [agentskills.io specification](https://agentskills.io)
