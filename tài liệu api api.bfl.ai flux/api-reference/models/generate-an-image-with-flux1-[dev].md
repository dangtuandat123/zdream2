> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Generate an image with FLUX.1 [dev].

> Submits an image generation task with FLUX.1 [dev].



## OpenAPI

````yaml https://api.bfl.ai/openapi.json post /v1/flux-dev
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
  /v1/flux-dev:
    post:
      tags:
        - Models
      summary: Generate an image with FLUX.1 [dev].
      description: Submits an image generation task with FLUX.1 [dev].
      operationId: flux_dev_v1_flux_dev_post
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/FluxDevInputs'
      responses:
        '200':
          description: Successful Response
          content:
            application/json:
              schema:
                anyOf:
                  - $ref: '#/components/schemas/AsyncResponse'
                  - $ref: '#/components/schemas/AsyncWebhookResponse'
                title: Response Flux Dev V1 Flux Dev Post
        '422':
          description: Validation Error
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/HTTPValidationError'
      security:
        - APIKeyHeader: []
components:
  schemas:
    FluxDevInputs:
      properties:
        prompt:
          type: string
          title: Prompt
          description: Text prompt for image generation.
          default: ''
          example: ein fantastisches bild
        image_prompt:
          anyOf:
            - type: string
            - type: 'null'
          title: Image Prompt
          description: Optional base64 encoded image to use as a prompt for generation.
        width:
          type: integer
          multipleOf: 32
          maximum: 1440
          minimum: 256
          title: Width
          description: Width of the generated image in pixels. Must be a multiple of 32.
          default: 1024
        height:
          type: integer
          multipleOf: 32
          maximum: 1440
          minimum: 256
          title: Height
          description: Height of the generated image in pixels. Must be a multiple of 32.
          default: 768
        steps:
          anyOf:
            - type: integer
              maximum: 50
              minimum: 1
            - type: 'null'
          title: Steps
          description: Number of steps for the image generation process.
          default: 28
          example: 28
        prompt_upsampling:
          type: boolean
          title: Prompt Upsampling
          description: >-
            Whether to perform upsampling on the prompt. If active,
            automatically modifies the prompt for more creative generation.
          default: false
        seed:
          anyOf:
            - type: integer
            - type: 'null'
          title: Seed
          description: Optional seed for reproducibility.
          example: 42
        guidance:
          anyOf:
            - type: number
              maximum: 5
              minimum: 1.5
            - type: 'null'
          title: Guidance
          description: >-
            Guidance scale for image generation. High guidance scales improve
            prompt adherence at the cost of reduced realism.
          default: 3
          example: 3
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
      title: FluxDevInputs
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