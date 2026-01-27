> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# Get Result

> An endpoint for getting generation task result.



## OpenAPI

````yaml https://api.bfl.ai/openapi.json get /v1/get_result
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
  /v1/get_result:
    get:
      tags:
        - Utility
      summary: Get Result
      description: An endpoint for getting generation task result.
      operationId: get_result_v1_get_result_get
      parameters:
        - name: id
          in: query
          required: true
          schema:
            type: string
            title: Id
      responses:
        '200':
          description: Successful Response
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ResultResponse'
        '422':
          description: Validation Error
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/HTTPValidationError'
components:
  schemas:
    ResultResponse:
      properties:
        id:
          type: string
          title: Id
          description: Task id for retrieving result
        status:
          $ref: '#/components/schemas/StatusResponse'
        result:
          anyOf:
            - {}
            - type: 'null'
          title: Result
        progress:
          anyOf:
            - type: number
            - type: 'null'
          title: Progress
        details:
          anyOf:
            - additionalProperties: true
              type: object
            - type: 'null'
          title: Details
        preview:
          anyOf:
            - additionalProperties: true
              type: object
            - type: 'null'
          title: Preview
      type: object
      required:
        - id
        - status
      title: ResultResponse
    HTTPValidationError:
      properties:
        detail:
          items:
            $ref: '#/components/schemas/ValidationError'
          type: array
          title: Detail
      type: object
      title: HTTPValidationError
    StatusResponse:
      type: string
      enum:
        - Task not found
        - Pending
        - Request Moderated
        - Content Moderated
        - Ready
        - Error
      title: StatusResponse
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

````