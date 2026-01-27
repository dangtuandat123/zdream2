> ## Documentation Index
> Fetch the complete documentation index at: https://docs.bfl.ml/llms.txt
> Use this file to discover all available pages before exploring further.

# My Finetunes

> List all finetune_ids created by the user



## OpenAPI

````yaml https://api.bfl.ai/openapi.json get /v1/my_finetunes
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
  /v1/my_finetunes:
    get:
      tags:
        - Utility
      summary: My Finetunes
      description: List all finetune_ids created by the user
      operationId: my_finetunes_v1_my_finetunes_get
      responses:
        '200':
          description: Successful Response
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/MyFinetunesResponse'
      security:
        - APIKeyHeader: []
      servers:
        - url: https://api.us1.bfl.ai
          description: BFL Finetune API
components:
  schemas:
    MyFinetunesResponse:
      properties:
        finetunes:
          items: {}
          type: array
          title: Finetunes
          description: List of finetunes created by the user
      type: object
      required:
        - finetunes
      title: MyFinetunesResponse
  securitySchemes:
    APIKeyHeader:
      type: apiKey
      in: header
      name: x-key

````