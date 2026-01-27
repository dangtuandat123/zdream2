> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# FLUX1.1 [pro] Image Generation

**FLUX1.1 \[pro]** is the standard for text-to-image generation with fast, reliable and consistently stunning results.

To generate an image from text, you’ll make a request to the `/flux-pro-1.1` endpoint.

## Key Features

<Columns cols={2}>
  <Card title="Fast & Reliable" icon="bolt">
    State-of-the-art inference speeds with consistent, stunning results every time
  </Card>

  <Card title="Strong Prompt Adherence" icon="sparkles">
    Proven baseline for accurate text-to-image generation that follows your prompts precisely
  </Card>

  <Card title="Scalable Generation" icon="chart-line">
    Robust architecture ideal for high-volume, production-ready image generation
  </Card>

  <Card title="Competitive Pricing" icon="dollar-sign">
    Superior image quality at just \$0.04 per image
  </Card>
</Columns>

## Examples of Image Generation with FLUX.1 \[pro]

<Columns cols={2}>
  <Frame>
    <img src="https://cdn.sanity.io/images/gsvmb6gz/production/074b4e010cc13d3e8584b8888348ce89f0522e28-1184x880.jpg" />
  </Frame>

  <Frame>
    <img src="https://cdn.sanity.io/images/gsvmb6gz/production/6cdadce93dd2a8f6a76556a19d69780948116aca-1184x880.jpg" />
  </Frame>
</Columns>

<Columns cols={2}>
  <Frame>
    <img src="https://cdn.sanity.io/images/gsvmb6gz/production/cd1e7c4a8386a5b72ec752be90433d2f9796877b-1184x880.jpg" />
  </Frame>

  <Frame>
    <img src="https://cdn.sanity.io/images/gsvmb6gz/production/6ec0190be3b1e022f5d837b209ee6b52933fc4fb-1184x880.jpg" />
  </Frame>
</Columns>

<Columns cols={4}>
  <Frame>
    <img src="https://cdn.sanity.io/images/gsvmb6gz/production/c48bac2625bedb8b53e0e70808e55cc11d3ef900-720x1456.jpg" />
  </Frame>

  <Frame>
    <img src="https://cdn.sanity.io/images/gsvmb6gz/production/525573b8bb34a9bcb5f4d2ca97dc4e2413ac9e8a-720x1456.jpg" />
  </Frame>

  <Frame>
    <img src="https://cdn.sanity.io/images/gsvmb6gz/production/ab13228b461ecabcc1be853a1d3067af735dc6fa-720x1456.jpg" />
  </Frame>

  <Frame>
    <img src="https://cdn.sanity.io/images/gsvmb6gz/production/1c903ae30d3d610a266283df9860c3e1508a47bc-720x1456.jpg" />
  </Frame>
</Columns>

## Using FLUX1.1 \[pro] API for Text-to-Image Generation

### Create a Request

Use the `/flux-pro-1.1` endpoint for standard FLUX1.1 \[pro] generation:

<CodeGroup>
  ```bash create_request.sh theme={null}
  request=$(curl -X POST \
    'https://api.bfl.ai/v1/flux-pro-1.1' \
    -H 'accept: application/json' \
    -H "x-key: ${BFL_API_KEY}" \
    -H 'Content-Type: application/json' \
    -d '{
      "prompt": "A futuristic city skyline at sunset with flying cars",
      "width": 1024,
      "height": 1024
  }')

  request_id=$(echo $request | jq -r .id)
  polling_url=$(echo $request | jq -r .polling_url)
  ```

  ```python create_request.py theme={null}
  import requests
  import os

  request = requests.post(
      'https://api.bfl.ai/v1/flux-pro-1.1',
      headers={
          'accept': 'application/json',
          'x-key': os.environ.get("BFL_API_KEY"),
          'Content-Type': 'application/json',
      },
      json={
          'prompt': 'A futuristic city skyline at sunset with flying cars',
          'width': 1024,
          'height': 1024
      }
  ).json()

  print(request)
  request_id = request["id"]
  polling_url = request["polling_url"] # Use this URL for polling
  ```
</CodeGroup>

### Poll for Result

After submitting a request, you need to poll using the returned `polling_url` to  retrieve the output when ready.

<CodeGroup>
  ```bash poll_result.sh theme={null}
  while true; do
    sleep 0.5
    result=$(curl -s -X 'GET' \
      "${polling_url}" \
      -H 'accept: application/json' \
      -H "x-key: ${BFL_API_KEY}")
    
    status=$(echo $result | jq -r .status)
    echo "Status: $status"
    
    if [ "$status" == "Ready" ]; then
      echo "Result: $(echo $result | jq -r .result.sample)"
      break
    elif [ "$status" == "Error" ] || [ "$status" == "Failed" ]; then
      echo "Generation failed: $result"
      break
    fi
  done
  ```

  ```python poll_result.py theme={null}
  # This assumes that the `polling_url` variable is set.
  import time
  import os
  import requests

  while True:
    time.sleep(0.5)
    result = requests.get(
        polling_url,
        headers={
            'accept': 'application/json',
            'x-key': os.environ.get("BFL_API_KEY"),
        }
    ).json()
    
    if result['status'] == 'Ready':
        print(f"Image ready: {result['result']['sample']}")
        break
    elif result['status'] in ['Error', 'Failed']:
        print(f"Generation failed: {result}")
        break
  ```
</CodeGroup>

A successful response will be a json object containing the result, and `result['sample']` is a signed URL for retrieval.

<Warning>
  Our signed URLs are only valid for 10 minutes. Please retrieve your result within this timeframe.
</Warning>

### FLUX1.1 \[pro] Parameters

| Parameter           | Type    | Default | Description                                                                        | Required |
| ------------------- | ------- | ------- | ---------------------------------------------------------------------------------- | -------- |
| `prompt`            | string  |         | Text description of the desired image                                              | **Yes**  |
| `width`             | integer | 1024    | Image width in pixels                                                              | No       |
| `height`            | integer | 1024    | Image height in pixels                                                             | No       |
| `prompt_upsampling` | boolean | false   | Enhance prompt for better results                                                  | No       |
| `seed`              | integer | null    | Seed for reproducible results. Accepts any integer                                 | No       |
| `safety_tolerance`  | integer | 2       | Content moderation level. Value ranges from 0 (most strict) to 6 (more permissive) | No       |
| `output_format`     | string  | "jpeg"  | Desired format of the output image. Can be "jpeg" or "png"                         | No       |
| `webhook_url`       | string  | null    | URL for asynchronous completion notification. Must be a valid HTTP/HTTPS URL       | No       |
| `webhook_secret`    | string  | null    | Secret for webhook signature verification, sent in the `X-Webhook-Secret` header   | No       |

## Pricing

FLUX1.1 \[pro] is available at **\$0.04 per image**.
