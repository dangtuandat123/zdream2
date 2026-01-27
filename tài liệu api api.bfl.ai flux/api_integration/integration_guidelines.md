> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# FLUX API Integration Guide

> Learn best essantial information for integrating with FLUX API endpoints, including endpoint selection, polling best practices, and proper handling of generated content.

## API Endpoints Overview

### Primary Global Endpoint

**`api.bfl.ai`** - Primary Endpoint

* Routes requests across all available clusters globally
* Provides automatic failover between clusters for enhanced uptime
* Intelligent load distribution prevents bottlenecks during high traffic periods
* **Important:** Always use the `polling_url` returned in responses when using this endpoint
* **Suitable for:** Standard inference

### Regional Endpoints

**`api.eu.bfl.ai`** - European Multi-cluster Endpoint

* Multi-cluster routing limited to EU regions
* GDPR compliant
* Provides the same uptime and load balancing benefits within EU regions

**`api.us.bfl.ai`** - US Multi-cluster Endpoint

* Multi-cluster routing limited to US regions
* Provides the same uptime and load balancing benefits within US regions

## Key Benefits of New Endpoints

<Columns cols={3}>
  <Card title="Enhanced Reliability" icon="shield-check">
    Reduced downtime through automatic cluster failover
  </Card>

  <Card title="Better Performance" icon="gauge">
    Intelligent traffic distribution prevents overload during peak usage
  </Card>

  <Card title="Seamless Experience" icon="sparkles">
    Load balancing happens transparently on our end
  </Card>
</Columns>

## Polling URL Usage

When using the primary global endpoint (`api.bfl.ai`) or regional endpoints (`api.eu.bfl.ai`, `api.us.bfl.ai`), you **must** use the `polling_url` returned in the initial request response.

<Note>
  **Webhook Users:** If you're using webhooks to receive results, no changes are needed. The `polling_url` requirement only applies when implementing async polling behavior to check request status.
</Note>

### Example Implementation

<CodeGroup>
  ```python polling_example.py theme={null}
  import requests
  import time
  import os

  # Submit request to global endpoint
  response = requests.post(
      'https://api.bfl.ai/v1/flux-pro-1.1',
      headers={
          'accept': 'application/json',
          'x-key': os.environ.get("BFL_API_KEY"),
          'Content-Type': 'application/json',
      },
      json={
          'prompt': 'A serene landscape with mountains',
          'aspect_ratio': '16:9'
      }
  )

  data = response.json()
  request_id = data['id']
  polling_url = data['polling_url']  # Use this URL for polling

  # Poll using the returned polling_url
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

  ```bash polling_example.sh theme={null}
  # Submit request and extract polling URL
  response=$(curl -X 'POST' \
    'https://api.bfl.ai/v1/flux-pro-1.1' \
    -H 'accept: application/json' \
    -H "x-key: ${BFL_API_KEY}" \
    -H 'Content-Type: application/json' \
    -d '{
      "prompt": "A serene landscape with mountains",
      "aspect_ratio": "16:9"
    }')

  request_id=$(echo $response | jq -r .id)
  polling_url=$(echo $response | jq -r .polling_url)

  # Poll using the polling URL
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
</CodeGroup>

## Content Delivery and Storage Guidelines

### Delivery URLs

Generated images are served from region-specific delivery URLs (e.g., `delivery-eu.bfl.ai` for European regions).

### Important Delivery Considerations

<Warning>
  **Not for Direct Serving:** The `result.sample` URLs from delivery endpoints are not meant to be served directly to end users.
</Warning>

<Warning>
  **No CORS Support:** We do not enable CORS on delivery URLs, which means they cannot be used directly in web browsers for cross-origin requests.
</Warning>

<Warning>
  **10-Minute Expiration:** Generated images expire after 10 minutes and become inaccessible.
</Warning>

<Note>
  **Network Access:** If your infrastructure uses firewalls or network restrictions, ensure you whitelist the delivery endpoints (e.g., `delivery-eu.bfl.ai`) to allow downloading generated images.
</Note>

### Recommended Image Handling

**Download and Re-serve Pattern:**

<CodeGroup>
  ```python download_and_serve.py theme={null}
  import requests
  import os
  from datetime import datetime
  from typing import Dict, Any

  def download_and_store_image(result_url: str, local_path: str) -> str:
      """
      Download image from BFL delivery URL and store locally
      """
      response = requests.get(result_url)
      response.raise_for_status()
      
      with open(local_path, 'wb') as f:
          f.write(response.content)
      
      return local_path

  def handle_generation_result(result: Dict[str, Any]) -> Dict[str, Any]:
      """
      Process generation result and store image locally
      """
      if result['status'] == 'Ready':
          sample_url = result['result']['sample']
          
          # Generate unique filename
          timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
          filename = f"generated_image_{timestamp}.jpg"
          local_path = os.path.join("./images", filename)
          
          # Ensure directory exists
          os.makedirs(os.path.dirname(local_path), exist_ok=True)
          
          # Download and store
          stored_path = download_and_store_image(sample_url, local_path)
          
          # Now serve from your own infrastructure
          return {
              'status': 'ready',
              'local_path': stored_path,
              'public_url': f"https://yourdomain.com/images/{filename}"
          }
      
      return result
  ```

  ```javascript download_and_serve.js theme={null}
  const fs = require('fs');
  const path = require('path');
  const https = require('https');

  async function downloadAndStoreImage(resultUrl, localPath) {
      return new Promise((resolve, reject) => {
          const file = fs.createWriteStream(localPath);
          
          https.get(resultUrl, (response) => {
              response.pipe(file);
              
              file.on('finish', () => {
                  file.close();
                  resolve(localPath);
              });
              
              file.on('error', (err) => {
                  fs.unlink(localPath, () => {}); // Delete incomplete file
                  reject(err);
              });
          }).on('error', reject);
      });
  }

  async function handleGenerationResult(result) {
      if (result.status === 'Ready') {
          const sampleUrl = result.result.sample;
          
          // Generate unique filename
          const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
          const filename = `generated_image_${timestamp}.jpg`;
          const localPath = path.join('./images', filename);
          
          // Ensure directory exists
          fs.mkdirSync(path.dirname(localPath), { recursive: true });
          
          // Download and store
          const storedPath = await downloadAndStoreImage(sampleUrl, localPath);
          
          // Return path for serving from your infrastructure
          return {
              status: 'ready',
              localPath: storedPath,
              publicUrl: `https://yourdomain.com/images/${filename}`
          };
      }
      
      return result;
  }
  ```
</CodeGroup>

## Migration Checklist

<Steps>
  <Step title="Update API Endpoints">
    * Replace legacy endpoints with appropriate new endpoints based on your needs
    * Use `api.bfl.ai` for global load balancing
    * Use `api.eu.bfl.ai` or `api.us.bfl.ai` for regional preferences
  </Step>

  <Step title="Implement Polling URL Handling">
    * Ensure your code extracts and uses the `polling_url` from API responses
    * Update polling logic to use the provided polling URL instead of hardcoded endpoints
  </Step>

  <Step title="Implement Proper Image Handling">
    * Set up download and re-serve infrastructure for generated images
    * Plan for 10-minute expiration window
    * Consider implementing CDN or cloud storage for better performance
  </Step>
</Steps>

## Best Practices

### Error Handling

<CodeGroup>
  ```python error_handling.py theme={null}
  import requests
  import time
  from typing import Dict, Any, Optional

  def robust_api_call(url: str, headers: Dict[str, str], json_data: Dict[str, Any], max_retries: int = 3) -> Dict[str, Any]:
      """
      Robust API call with retry logic and proper error handling
      """
      for attempt in range(max_retries):
          try:
              response = requests.post(url, headers=headers, json=json_data)
              
              if response.status_code == 429:
                  # Rate limit exceeded, wait and retry
                  wait_time = 2 ** attempt  # Exponential backoff
                  time.sleep(wait_time)
                  continue
                  
              elif response.status_code == 402:
                  # Insufficient credits
                  raise Exception("Insufficient credits. Please add credits to your account.")
                  
              elif response.status_code >= 400:
                  # Other client/server errors
                  response.raise_for_status()
              
              return response.json()
              
          except requests.exceptions.RequestException as e:
              if attempt == max_retries - 1:
                  raise e
              time.sleep(2 ** attempt)
      
      raise Exception(f"Failed after {max_retries} attempts")
  ```
</CodeGroup>

### Rate Limiting

<Note>
  * Maximum 24 concurrent requests for most endpoints
  * Maximum 6 concurrent requests for `flux-kontext-max`
  * Implement exponential backoff for 429 responses
</Note>

### Content Management

* Download images immediately upon generation completion
* Implement proper error handling for expired URLs
* Consider implementing a queue system for high-volume applications
* Use appropriate storage solutions (CDN, cloud storage) for serving images to users
