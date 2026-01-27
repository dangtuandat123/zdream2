> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Generate an image with FLUX.1 Fill [pro] finetune using an input image and mask.

> Submits an image generation task with the FLUX.1 Fill [pro] finetune model using an input image and mask. Mask can be applied to alpha channel or submitted as a separate image.



## OpenAPI

````yaml https://api.bfl.ai/openapi.json post /v1/flux-pro-1.0-fill-finetuned
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
  /v1/flux-pro-1.0-fill-finetuned:
    post:
      tags:
        - Models
      summary: >-
        Generate an image with FLUX.1 Fill [pro] finetune using an input image
        and mask.
      description: >-
        Submits an image generation task with the FLUX.1 Fill [pro] finetune
        model using an input image and mask. Mask can be applied to alpha
        channel or submitted as a separate image.
      operationId: flux_pro_1_0_fill_finetuned_v1_flux_pro_1_0_fill_finetuned_post
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/FinetuneFluxProFillInputs'
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
                  Response Flux Pro 1 0 Fill Finetuned V1 Flux Pro 1 0 Fill
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
    FinetuneFluxProFillInputs:
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
          default: 1.1
        image:
          type: string
          title: Image
          description: >-
            A Base64-encoded string representing the image you wish to modify.
            Can contain alpha mask if desired.
        mask:
          anyOf:
            - type: string
            - type: 'null'
          title: Mask
          description: >-
            A Base64-encoded string representing a mask for the areas you want
            to modify in the image. The mask should be the same dimensions as
            the image and in black and white. Black areas (0%) indicate no
            modification, while white areas (100%) specify areas for inpainting.
            Optional if you provide an alpha mask in the original image.
            Validation: The endpoint verifies that the dimensions of the mask
            match the original image.
        prompt:
          type: string
          title: Prompt
          description: >-
            The description of the changes you want to make. This text guides
            the inpainting process, allowing you to specify features, styles, or
            modifications for the masked area.
          default: ''
          example: ein fantastisches bild
        steps:
          type: integer
          maximum: 50
          minimum: 15
          title: Steps
          description: Number of steps for the image generation process
          default: 50
          example: 50
        prompt_upsampling:
          type: boolean
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
          type: number
          maximum: 100
          minimum: 1.5
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
        - finetune_id
        - image
      title: FinetuneFluxProFillInputs
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