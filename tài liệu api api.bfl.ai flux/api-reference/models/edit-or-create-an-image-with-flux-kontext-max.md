> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Edit or create an image with Flux Kontext Max



## OpenAPI

````yaml https://api.bfl.ai/openapi.json post /v1/flux-kontext-max
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
  /v1/flux-kontext-max:
    post:
      tags:
        - Models
      summary: Edit or create an image with Flux Kontext Max
      operationId: generate_flux_kontext_max_v1_flux_kontext_max_post
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/FluxKontextProInputs'
      responses:
        '200':
          description: Successful Response
          content:
            application/json:
              schema:
                anyOf:
                  - $ref: '#/components/schemas/AsyncResponse'
                  - $ref: '#/components/schemas/AsyncWebhookResponse'
                title: Response Generate Flux Kontext Max V1 Flux Kontext Max Post
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
    FluxKontextProInputs:
      properties:
        prompt:
          type: string
          title: Prompt
          description: Text prompt for image generation.
          example: ein fantastisches bild
        input_image:
          anyOf:
            - type: string
            - type: 'null'
          title: Input Image
          description: Base64 encoded image or URL to use with Kontext.
        input_image_2:
          anyOf:
            - type: string
            - type: 'null'
          title: Input Image 2
          description: >-
            Base64 encoded image or URL to use with Kontext. *Experimental
            Multiref*
        input_image_3:
          anyOf:
            - type: string
            - type: 'null'
          title: Input Image 3
          description: >-
            Base64 encoded image or URL to use with Kontext. *Experimental
            Multiref*
        input_image_4:
          anyOf:
            - type: string
            - type: 'null'
          title: Input Image 4
          description: >-
            Base64 encoded image or URL to use with Kontext. *Experimental
            Multiref*
        seed:
          anyOf:
            - type: integer
            - type: 'null'
          title: Seed
          description: Optional seed for reproducibility.
          example: 42
        aspect_ratio:
          anyOf:
            - type: string
            - type: 'null'
          title: Aspect Ratio
          description: Aspect ratio of the image between 21:9 and 9:21
        output_format:
          anyOf:
            - $ref: '#/components/schemas/OutputFormat'
            - type: 'null'
          description: Output format for the generated image. Can be 'jpeg' or 'png'.
          default: png
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
        prompt_upsampling:
          type: boolean
          title: Prompt Upsampling
          description: >-
            Whether to perform upsampling on the prompt. If active,
            automatically modifies the prompt for more creative generation.
          default: false
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
      type: object
      required:
        - prompt
      title: FluxKontextProInputs
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