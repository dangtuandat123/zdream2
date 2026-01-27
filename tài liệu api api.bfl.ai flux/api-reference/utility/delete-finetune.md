> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Delete Finetune

> Delete a finetune_id that was created by the user



## OpenAPI

````yaml https://api.bfl.ai/openapi.json post /v1/delete_finetune
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
  /v1/delete_finetune:
    post:
      tags:
        - Utility
      summary: Delete Finetune
      description: Delete a finetune_id that was created by the user
      operationId: delete_finetune_v1_delete_finetune_post
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/DeleteFinetuneInputs'
      responses:
        '200':
          description: Successful Response
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/DeleteFinetuneResponse'
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
    DeleteFinetuneInputs:
      properties:
        finetune_id:
          type: string
          title: Finetune Id
          description: ID of the fine-tuned model you want to delete.
          example: my-finetune
      type: object
      required:
        - finetune_id
      title: DeleteFinetuneInputs
    DeleteFinetuneResponse:
      properties:
        status:
          type: string
          title: Status
          description: Status of the deletion
        message:
          type: string
          title: Message
          description: Message about the deletion
        deleted_finetune_id:
          type: string
          title: Deleted Finetune Id
          description: ID of the deleted finetune
        timestamp:
          type: string
          title: Timestamp
          description: Timestamp of the deletion
      type: object
      required:
        - status
        - message
        - deleted_finetune_id
        - timestamp
      title: DeleteFinetuneResponse
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