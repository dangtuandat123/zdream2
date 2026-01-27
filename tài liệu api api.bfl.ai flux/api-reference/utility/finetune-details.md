> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Finetune Details

> Get details about the training parameters and other metadata connected to a specific finetune_id that was created by the user.



## OpenAPI

````yaml https://api.bfl.ai/openapi.json get /v1/finetune_details
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
  /v1/finetune_details:
    get:
      tags:
        - Utility
      summary: Finetune Details
      description: >-
        Get details about the training parameters and other metadata connected
        to a specific finetune_id that was created by the user.
      operationId: finetune_details_v1_finetune_details_get
      parameters:
        - name: finetune_id
          in: query
          required: true
          schema:
            type: string
            title: Finetune Id
      responses:
        '200':
          description: Successful Response
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/FinetuneDetailResponse'
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
    FinetuneDetailResponse:
      properties:
        finetune_details:
          additionalProperties: true
          type: object
          title: Finetune Details
          description: Details about the parameters used for finetuning
      type: object
      required:
        - finetune_details
      title: FinetuneDetailResponse
    HTTPValidationError:
      properties:
        detail:
          items:
            $ref: '#/components/schemas/ValidationError'
          type: array
          title: Detail
      type: object
      title: HTTPValidationError
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