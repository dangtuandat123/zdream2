> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# FLUX.1 Fill [pro]

> Targeted and fast text-driven image inpainting.

## What is FLUX.1 Fill?

FLUX.1 Fill is a specialized inpainting model tailored for two complementary applications:

**Selecting specific regions within an image and editing them** - Transform parts of your image while preserving the surrounding context. Change objects, enhance details, or remove unwanted elements naturally.

**Adding new pixels at the image borders** - Extend images beyond their original boundaries to increase resolution or change aspect ratios. Perfect for expanding scenes or adapting content for different formats.

## Examples

### Inpainting: Object Replacement

This example shows how FLUX.1 Fill can replace specific parts of an image while keeping everything else intact. On the top part of the image, we replace the jacket with a different style. On the bottom part of the image, we replace the text on the neon sign.

<Tip>
  When using a mask, black areas will be preserved while white areas will be inpainted.
</Tip>

<Frame caption="Inpainting example">
  <img src="https://cdn.sanity.io/images/gsvmb6gz/production/838d5cc08592229eff705c3ca345286dac4d09da-1600x962.webp" />
</Frame>

The second example demonstrates shows how you can use a black and white mask to define the area to change. The original "KILL BILL" title becomes "FLUX PILL" using a mask.

<Columns cols={3}>
  <Frame caption="Original image">
    <img src="https://replicate.delivery/pbxt/M0gpKVE9wmEtOQFNDOpwz1uGs0u6nK2NcE85IihwlN0ZEnMF/kill-bill-poster.jpg" />
  </Frame>

  <Frame caption="Mask">
    <img src="https://replicate.delivery/pbxt/M0gpLCYdCLbnhcz95Poy66q30XW9VSCN65DoDQ8IzdzlQonw/kill-bill-mask.png" />
  </Frame>

  <Frame caption="Inpainted image">
    <img src="https://replicate.delivery/xezq/Hc95qpsbiwK4ARfhuT6RHmEc4x89jS8dFMCdw8uW9iHogLGKA/tmp54g7ws1t.jpg" />
  </Frame>
</Columns>

## Using FLUX.1 Fill API

FLUX.1 Fill supports two approaches for specifying edit areas:

* **Separate Mask**: Provide two files - your image plus a separate black/white mask image. Black areas preserve the original, white areas get inpainted.
* **Alpha Channel**: Provide one PNG/WebP with transparency. Transparent areas get inpainted, opaque areas are preserved. No separate mask file needed.

### Create a Request

<CodeGroup>
  ```bash curl theme={null}
  curl -X POST "https://api.bfl.ai/v1/flux-pro-1.0-fill" \
    -H "x-key: $BFL_API_KEY" \
    -H "Content-Type: application/json" \
    -d '{
      "prompt": "<your prompt>",
      "image": "<base64 encoded image>",
      "mask": "<base64 encoded mask>",
      "steps": 50,
      "guidance": 30,
      "output_format": "jpeg",
      "safety_tolerance": 2
    }'
  ```

  ```python Python theme={null}

  # Install `requests` (e.g. `pip install requests`) 
  # and `Pillow` (e.g. `pip install Pillow`)

  import os
  import requests
  import base64
  from PIL import Image
  from io import BytesIO

  # Load and encode your image
  # Replace "<your_image.jpg>" with the path to your image file
  image = Image.open("<your_image.jpg>")
  buffered = BytesIO()
  image.save(buffered, format="JPEG") # Or "PNG" if your image is PNG
  img_str = base64.b64encode(buffered.getvalue()).decode()

  mask = Image.open("<your_mask.png>")
  buffered = BytesIO()
  mask.save(buffered, format="PNG")
  mask_str = base64.b64encode(buffered.getvalue()).decode()


  response = requests.post(
      "https://api.bfl.ai/v1/flux-pro-1.0-fill",
      headers={
          "x-key": api_key,
          "Content-Type": "application/json"
      },
      json={
          "prompt": "<your prompt>",
          "image": img_str,
          "mask": mask_str,
          "steps": 50,
          "guidance": 30,
          "output_format": "jpeg"
      }
  )

  result = response.json()
  request_id = result["id"]
  polling_url = result["polling_url"]
  ```
</CodeGroup>

### Poll for Results

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
