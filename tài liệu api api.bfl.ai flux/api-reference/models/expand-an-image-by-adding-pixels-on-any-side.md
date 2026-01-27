> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Expand an image by adding pixels on any side.

> Submits an image expansion task that adds the specified number of pixels to any combination of sides (top, bottom, left, right) while maintaining context.



## OpenAPI

````yaml https://api.bfl.ai/openapi.json post /v1/flux-pro-1.0-expand
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
  /v1/flux-pro-1.0-expand:
    post:
      tags:
        - Models
      summary: Expand an image by adding pixels on any side.
      description: >-
        Submits an image expansion task that adds the specified number of pixels
        to any combination of sides (top, bottom, left, right) while maintaining
        context.
      operationId: expand_v1_flux_pro_1_0_expand_post
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/FluxProExpandInputs'
      responses:
        '200':
          description: Successful Response
          content:
            application/json:
              schema:
                anyOf:
                  - $ref: '#/components/schemas/AsyncResponse'
                  - $ref: '#/components/schemas/AsyncWebhookResponse'
                title: Response Expand V1 Flux Pro 1 0 Expand Post
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
    FluxProExpandInputs:
      properties:
        image:
          type: string
          title: Image
          description: A Base64-encoded string representing the image you wish to expand.
        top:
          anyOf:
            - type: integer
              maximum: 2048
              minimum: 0
            - type: 'null'
          title: Top
          description: Number of pixels to expand at the top of the image
          default: 0
        bottom:
          anyOf:
            - type: integer
              maximum: 2048
              minimum: 0
            - type: 'null'
          title: Bottom
          description: Number of pixels to expand at the bottom of the image
          default: 0
        left:
          anyOf:
            - type: integer
              maximum: 2048
              minimum: 0
            - type: 'null'
          title: Left
          description: Number of pixels to expand on the left side of the image
          default: 0
        right:
          anyOf:
            - type: integer
              maximum: 2048
              minimum: 0
            - type: 'null'
          title: Right
          description: Number of pixels to expand on the right side of the image
          default: 0
        prompt:
          anyOf:
            - type: string
            - type: 'null'
          title: Prompt
          description: >-
            The description of the changes you want to make. This text guides
            the expansion process, allowing you to specify features, styles, or
            modifications for the expanded areas.
          default: ''
          example: ein fantastisches bild
        steps:
          anyOf:
            - type: integer
              maximum: 50
              minimum: 15
            - type: 'null'
          title: Steps
          description: Number of steps for the image generation process
          default: 50
          example: 50
        prompt_upsampling:
          anyOf:
            - type: boolean
            - type: 'null'
          title: Prompt Upsampling
          description: >-
            Whether to perform upsampling on the prompt. If active,
            automatically modifies the prompt for more creative generation
          default: false
        seed:
          anyOf:
            - type: integer
            - type: 'null'
          title: Seed
          description: Optional seed for reproducibility
        guidance:
          anyOf:
            - type: number
              maximum: 100
              minimum: 1.5
            - type: 'null'
          title: Guidance
          description: Guidance strength for the image generation process
          default: 60
        output_format:
          anyOf:
            - $ref: '#/components/schemas/OutputFormat'
            - type: 'null'
          description: Output format for the generated image. Can be 'jpeg' or 'png'.
          default: jpeg
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
        - image
      title: FluxProExpandInputs
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