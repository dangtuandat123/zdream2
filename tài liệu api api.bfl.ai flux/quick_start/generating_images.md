> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Image Generation with Text Prompts

> Complete guide to FLUX API endpoints for AI image generation. Learn text-to-image creation, API polling, regional endpoints, and code examples.

Our API endpoints enable media creation with BFL models. It follows an asynchronous design, where you first make a request for a generation and then query for the result of your request.

## API Endpoints

<AccordionGroup>
  <Accordion title="Primary Global Endpoint">
    **`api.bfl.ai`** - Recommended for most use cases

    * Routes requests across all available clusters globally
    * Automatic failover between clusters for enhanced uptime
    * Intelligent load distribution prevents bottlenecks during high traffic

    <Warning>
      Always use the `polling_url` returned in responses when using this endpoint
    </Warning>
  </Accordion>

  <Accordion title="Regional Endpoints">
    🇪🇺 **`api.eu.bfl.ai`** - European Multi-cluster

    * Multi-cluster routing limited to EU regions
    * GDPR compliant

    🇺🇸 **`api.us.bfl.ai`** - US Multi-cluster

    * Multi-cluster routing limited to US regions
  </Accordion>

  <Accordion title="Legacy Regional Endpoints">
    🇪🇺 **`api.eu1.bfl.ai`** - EU Single-cluster

    * Single cluster, no automatic failover
  </Accordion>
</AccordionGroup>

<Note>
  For enhanced reliability and performance, we recommend using the global endpoint `api.bfl.ai` or regional endpoints `api.eu.bfl.ai`/`api.us.bfl.ai` for inference tasks.
</Note>

## Available Endpoints

We currently support the following endpoints for image generation:

1. `/flux-2-max`
2. `/flux-2-pro`
3. `/flux-2-flex`
4. `/flux-2-klein-4b`
5. `/flux-2-klein-9b`
6. `/flux-kontext-max`
7. `/flux-kontext-pro`
8. `/flux-pro-1.1-ultra`
9. `/flux-pro-1.1`
10. `/flux-pro`
11. `/flux-dev`

## Create Your First Image

### Submit Generation Request

To submit an image generation task with FLUX.2 \[pro], create a request:

<CodeGroup>
  ```bash submit_request.sh theme={null}
  # Install curl and jq, then run:
  # Make sure to set your API key: export BFL_API_KEY="your_key_here"

  request=$(curl -X 'POST' \
    'https://api.bfl.ai/v1/flux-2-pro' \
    -H 'accept: application/json' \
    -H "x-key: ${BFL_API_KEY}" \
    -H 'Content-Type: application/json' \
    -d '{
    "prompt": "A cat on its back legs running like a human is holding a big silver fish with its arms. The cat is running away from the shop owner and has a panicked look on his face. The scene is situated in a crowded market.",
    "aspect_ratio": "1:1",
  }')

  echo $request
  request_id=$(jq -r .id <<< $request)
  polling_url=$(jq -r .polling_url <<< $request)
  echo "Request ID: ${request_id}"
  echo "Polling URL: ${polling_url}"
  ```

  ```python submit_request.py theme={null}
  # Install requests: pip install requests

  import os
  import requests

  request = requests.post(
      'https://api.bfl.ai/v1/flux-2-pro',
      headers={
          'accept': 'application/json',
          'x-key': os.environ.get("BFL_API_KEY"),
          'Content-Type': 'application/json',
      },
      json={
          'prompt': 'A cat on its back legs running like a human is holding a big silver fish with its arms. The cat is running away from the shop owner and has a panicked look on his face. The scene is situated in a crowded market.',
          "aspect_ratio": "1:1"
      },
  ).json()

  print(request)
  request_id = request["id"]
  polling_url = request["polling_url"]
  print(f"Request ID: {request_id}")
  print(f"Polling URL: {polling_url}")
  ```
</CodeGroup>

A successful response will be a json object containing the request's id and a `polling_url` that should be used to retrieve the result.

<Warning>
  **Important:** When using the global endpoint (`api.bfl.ai`) or regional endpoints (`api.eu.bfl.ai`, `api.us.bfl.ai`), you must use the `polling_url` returned in the response for checking request status.
</Warning>

### Poll for Results

To retrieve the result, poll the endpoint using the `polling_url`:

<CodeGroup>
  ```bash poll_results.sh theme={null}
  # This assumes that the request_id and polling_url variables are set from the previous step

  while true
  do
    sleep 0.5
    result=$(curl -s -X 'GET' \
      "${polling_url}" \
      -H 'accept: application/json' \
      -H "x-key: ${BFL_API_KEY}")
    
    status=$(jq -r .status <<< $result)
    echo "Status: $status"
    
    if [ "$status" == "Ready" ]
    then
      echo "Result: $(jq -r .result.sample <<< $result)"
      break
    elif [ "$status" == "Error" ] || [ "$status" == "Failed" ]
    then
      echo "Generation failed: $result"
      break
    fi
  done
  ```

  ```python poll_results.py theme={null}
  # This assumes request_id and polling_url are set from the previous step
  import time

  while True:
      time.sleep(0.5)
      result = requests.get(
          polling_url,
          headers={
              'accept': 'application/json',
              'x-key': os.environ.get("BFL_API_KEY"),
          },
          params={
              'id': request_id,
          },
      ).json()
      
      status = result["status"]
      print(f"Status: {status}")
      
      if status == "Ready":
          print(f"Result: {result['result']['sample']}")
          break
      elif status in ["Error", "Failed"]:
          print(f"Generation failed: {result}")
          break
  ```
</CodeGroup>

A successful response will be a JSON object containing the result, where `result['sample']` is a signed URL for retrieval.

<Warning>
  Our signed URLs are only valid for 10 minutes. Please retrieve your result within this timeframe.
</Warning>

<Warning>
  **Image Delivery:** The `result.sample` URLs are served from delivery endpoints (`delivery-eu.bfl.ai`, `delivery-us.bfl.ai`) and are not meant to be served directly to users. We recommend downloading the image and re-serving it from your own infrastructure. We do not enable CORS on delivery URLs.
</Warning>

See our [reference documentation](https://docs.bfl.ai/api-reference/) for a full list of options and our [inference repo](https://github.com/black-forest-labs/flux).

## Limits

<Warning>
  **Rate Limits:** Sending requests to our API is limited to 24 active tasks. If you exceed your limit, you'll receive a status code `429` and must wait until one of your previous tasks has finished.
</Warning>

<Warning>
  **Rate Limits:** Additionally, due to capacity issues, for `flux-kontext-max`, the requests to our API is limited to 6 active tasks.
</Warning>

<Note>
  **Credits:** If you run out of credits (status code `402`), visit [https://api.bfl.ai](https://api.bfl.ai), sign in and click "Add" to buy additional credits. See also [managing your account](https://docs.bfl.ai/quick_start/managing_account).
</Note>

<Tip>
  If you require higher volumes, please contact us at [flux@blackforestlabs.ai](mailto:flux@blackforestlabs.ai)
</Tip>
