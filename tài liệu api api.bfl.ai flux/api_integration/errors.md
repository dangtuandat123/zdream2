> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Errors

## HTTP Errors

Our API uses standard HTTP status codes to indicate the success or failure of your requests:

**400 - `Bad Request`**
There was an issue with the format or content of your request. Check your request parameters and ensure all required fields are properly formatted.

**402 - `Payment Required`**
Your account has insufficient credits or your payment method needs to be updated. Add credits to your account to continue using the API.

**403 - `Forbidden`**\
Your API key does not have permission to access the specified resource.

**422 - `Unprocessable Entity`**
The request you sent contains invalid request body or parameters.

**429 - `Too Many Requests`**
You've exceeded the rate limits for your account.

**500 - `Internal Server Error`**
An unexpected error occurred on our servers.

**503 - `Service Unavailable`**
Our service is temporarily unavailable, typically due to maintenance or high load. Try again in a few moments.

## Response Types

When checking the status of your generation requests using `get_result`, you'll receive one of these response types:

**`Ready`**
Your generation is complete and the result is available for download.

**`Pending`**
Your request is still being processed. Check back in a few moments.

**`Request Moderated`**
Your input (prompt or image) was flagged by our content moderation system before processing began.

**`Content Moderated`**
The generated output was flagged by our content moderation system after processing completed.

**`Task not found`**
The specified task ID does not exist or has expired.

**`Error`**
An error occurred during processing. Check the error details for more information.

## Moderation Responses

When content is flagged by our moderation system, you'll receive one of these status values:

### Request Moderated

Your input (prompt or image) was flagged by our content moderation system before processing began. This means the request was rejected at submission time.

Example response:

```json  theme={null}
{
  "id": "your-task-id",
  "status": "Request Moderated",
  "result": null
}
```

### Content Moderated

The generated output was flagged by our content moderation system after processing completed. The result cannot be delivered.

Example response:

```json  theme={null}
{
  "id": "your-task-id",
  "status": "Content Moderated",
  "result": null
}
```

### Adjusting moderation sensitivity

You can control the moderation sensitivity using the `safety_tolerance` parameter when submitting generation requests. This parameter accepts values from 0 to 6:

* **0**: Most strict moderation
* **6**: Least strict moderation

The default value is `2`. Lower values result in more aggressive content filtering, while higher values allow more content through.
