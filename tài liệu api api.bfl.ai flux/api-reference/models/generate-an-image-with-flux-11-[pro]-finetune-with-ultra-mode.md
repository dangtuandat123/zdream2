> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Generate an image with FLUX 1.1 [pro] finetune with ultra mode.

> Submits an image generation task with FLUX 1.1 [pro] finetune with ultra mode.



## OpenAPI

````yaml https://api.bfl.ai/openapi.json post /v1/flux-pro-1.1-ultra-finetuned
openapi: 3.1.0
info:
  title: BFL API
  description: Authorize with an API key from your user profile.
  version: 0.0.1
servers:
  - url: https://api.bfl.ai
    description: BFL API
security: []
tags:
  - name: Models
    description: >-
      Generation task endpoints. These endpoints allow you to submit generation
      tasks.
  - name: Utility
    description: >-
      These utility endpoints allow you to check the results of submitted tasks
      and to manage your finetunes.
paths:
  /v1/flux-pro-1.1-ultra-finetuned:
    post:
      tags:
        - Models
      summary: Generate an image with FLUX 1.1 [pro] finetune with ultra mode.
      description: >-
        Submits an image generation task with FLUX 1.1 [pro] finetune with ultra
        mode.
      operationId: generate_bigblue_finetuned_v1_flux_pro_1_1_ultra_finetuned_post
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/FinetuneFluxUltraInput'
      responses:
        '200':
          description: Successful Response
          content:
            application/json:
              schema:
                anyOf:
                  - $ref: '#/components/schemas/AsyncResponse'
                  - $ref: '#/components/schemas/AsyncWebhookResponse'
                title: >-
                  Response Generate Bigblue Finetuned V1 Flux Pro 1 1 Ultra
                  Finetuned Post
        '422':
          description: Validation Error
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/HTTPValidationError'
      security:
        - APIKeyHeader: []
      servers:
        - url: https://api.us1.bfl.ai
          description: BFL Finetune API
components:
  schemas:
    FinetuneFluxUltraInput:
      properties:
        finetune_id:
          type: string
          title: Finetune Id
          description: ID of the fine-tuned model you want to use.
          example: my-finetune
        finetune_strength:
          type: number
          maximum: 2
          minimum: 0
          title: Finetune Strength
          description: >-
            Strength of the fine-tuned model. 0.0 means no influence, 1.0 means
            full influence. Allowed values up to 2.0
          default: 1.2
        prompt:
          anyOf:
            - type: string
            - type: 'null'
          title: Prompt
          description: The prompt to use for image generation.
          default: ''
          example: A beautiful landscape with mountains and a lake
        seed:
          anyOf:
            - type: integer
            - type: 'null'
          title: Seed
          description: >-
            Optional seed for reproducibility. If not provided, a random seed
            will be used.
          example: 42
        aspect_ratio:
          type: string
          title: Aspect Ratio
          description: Aspect ratio of the image between 21:9 and 9:21
          default: '16:9'
        safety_tolerance:
          type: integer
          maximum: 6
          minimum: 0
          title: Safety Tolerance
          description: >-
            Tolerance level for input and output moderation. Between 0 and 6, 0
            being most strict, 6 being least strict.
          default: 2
          example: 2
        output_format:
          anyOf:
            - $ref: '#/components/schemas/OutputFormat'
            - type: 'null'
          description: Output format for the generated image. Can be 'jpeg' or 'png'.
          default: jpeg
        image_prompt:
          anyOf:
            - type: string
            - type: 'null'
          title: Image Prompt
          description: Optional image to remix in base64 format
        image_prompt_strength:
          type: number
          maximum: 1
          minimum: 0
          title: Image Prompt Strength
          description: Blend between the prompt and the image prompt
          default: 0.1
        prompt_upsampling:
          type: boolean
          title: Prompt Upsampling
          description: >-
            Whether to perform upsampling on the prompt. If active,
            automatically modifies the prompt for more creative generation
          default: false
        webhook_url:
          anyOf:
            - type: string
              maxLength: 2083
              minLength: 1
              format: uri
            - type: 'null'
          title: Webhook Url
          description: URL to receive webhook notifications
        webhook_secret:
          anyOf:
            - type: string
            - type: 'null'
          title: Webhook Secret
          description: Optional secret for webhook signature verification
      type: object
      required:
        - finetune_id
      title: FinetuneFluxUltraInput
    AsyncResponse:
      properties:
        id:
          type: string
          title: Id
        polling_url:
          type: string
          title: Polling Url
        cost:
          anyOf:
            - type: number
            - type: 'null'
          title: Cost
          description: Cost in credits for this request
        input_mp:
          anyOf:
            - type: number
            - type: 'null'
          title: Input Mp
          description: Input megapixels (2 decimal places)
        output_mp:
          anyOf:
            - type: number
            - type: 'null'
          title: Output Mp
          description: Output megapixels (2 decimal places)
      type: object
      required:
        - id
        - polling_url
      title: AsyncResponse
    AsyncWebhookResponse:
      properties:
        id:
          type: string
          title: Id
        status:
          type: string
          title: Status
        webhook_url:
          type: string
          title: Webhook Url
        cost:
          anyOf:
            - type: number
            - type: 'null'
          title: Cost
          description: Cost in credits for this request
        input_mp:
          anyOf:
            - type: number
            - type: 'null'
          title: Input Mp
          description: Input megapixels (2 decimal places)
        output_mp:
          anyOf:
            - type: number
            - type: 'null'
          title: Output Mp
          description: Output megapixels (2 decimal places)
      type: object
      required:
        - id
        - status
        - webhook_url
      title: AsyncWebhookResponse
    HTTPValidationError:
      properties:
        detail:
          items:
            $ref: '#/components/schemas/ValidationError'
          type: array
          title: Detail
      type: object
      title: HTTPValidationError
    OutputFormat:
      type: string
      enum:
        - jpeg
        - png
      title: OutputFormat
    ValidationError:
      properties:
        loc:
          items:
            anyOf:
              - type: string
              - type: integer
          type: array
          title: Location
        msg:
          type: string
          title: Message
        type:
          type: string
          title: Error Type
      type: object
      required:
        - loc
        - msg
        - type
      title: ValidationError
  securitySchemes:
    APIKeyHeader:
      type: apiKey
      in: header
      name: x-key

````